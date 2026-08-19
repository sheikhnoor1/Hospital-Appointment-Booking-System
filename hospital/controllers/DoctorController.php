<?php
require_once BASE_PATH . '/models/DoctorModel.php';
require_once BASE_PATH . '/models/AppointmentModel.php';

class DoctorController {
    private $doctorModel;
    private $appointmentModel;

    public function __construct() {
        $this->doctorModel      = new DoctorModel();
        $this->appointmentModel = new AppointmentModel();
    }

    public function dashboard() {
        requireRole('doctor');
        $doctor = $this->doctorModel->findByUserId($_SESSION['user_id']);
        if (!$doctor) redirect('/index.php?page=login');

        $todayAppointments = $this->appointmentModel->getTodayByDoctor($doctor['id']);
        $displayAppointments = array_values(array_filter(
            $todayAppointments,
            fn($a) => strtotime($a['appointment_time']) >= strtotime(date('H:i:s'))
        ));

        $displayLabel = 'Today';
        if (empty($displayAppointments)) {
            $nextDay = $this->appointmentModel->getNextAppointmentDayForDoctor($doctor['id']);
            if (!empty($nextDay['appointment_date'])) {
                $displayAppointments = $this->appointmentModel->getByDoctor($doctor['id'], $nextDay['appointment_date']);
                $displayLabel = date('l, F j, Y', strtotime($nextDay['appointment_date']));
            }
        }

        $weekAppointments   = $this->appointmentModel->getWeekByDoctor($doctor['id']);
        $incomeSummary      = $this->appointmentModel->getDoctorIncomeSummary($doctor['id']);
        require BASE_PATH . '/views/doctor/dashboard.php';
    }

    public function schedule() {
        requireRole('doctor');
        $doctor = $this->doctorModel->findByUserId($_SESSION['user_id']);
        if (!$doctor) redirect('/index.php?page=login');

        $weekdays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        $doctorDays = array_map('trim', explode(',', $doctor['available_days'] ?? ''));
        $success = getFlash('success');
        $error   = getFlash('error');

        require BASE_PATH . '/views/doctor/schedule.php';
    }

    public function updateSchedule() {
        requireRole('doctor');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/index.php?page=doctor/schedule');

        $doctor = $this->doctorModel->findByUserId($_SESSION['user_id']);
        if (!$doctor) redirect('/index.php?page=login');

        $days = $_POST['available_days'] ?? [];
        $allowedDays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        $days = array_values(array_unique(array_filter(array_map('trim', (array)$days), fn($day) => in_array($day, $allowedDays, true))));

        if (empty($days)) {
            setFlash('error', 'Please select at least one day for your schedule.');
            redirect('/index.php?page=doctor/schedule');
        }

        $availableDays = implode(', ', $days);
        $this->doctorModel->updateSchedule($doctor['id'], $availableDays);

        setFlash('success', 'Your schedule has been updated successfully.');
        redirect('/index.php?page=doctor/schedule');
    }

    public function appointments() {
        requireRole('doctor');
        $doctor = $this->doctorModel->findByUserId($_SESSION['user_id']);
        if (!$doctor) redirect('/index.php?page=login');

        $filters = ['doctor_id' => $doctor['id']];
        // optional filters
        if (!empty($_GET['date'])) $filters['date'] = $_GET['date'];
        if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];

        $appointments = $this->appointmentModel->getAll($filters);
        $success = getFlash('success');
        $error = getFlash('error');
        require BASE_PATH . '/views/doctor/appointments.php';
    }
}
