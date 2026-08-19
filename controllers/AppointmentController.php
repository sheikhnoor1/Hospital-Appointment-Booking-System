<?php
require_once BASE_PATH . '/models/AppointmentModel.php';
require_once BASE_PATH . '/models/DoctorModel.php';

class AppointmentController {
    private $appointmentModel;
    private $doctorModel;

    public function __construct() {
        $this->appointmentModel = new AppointmentModel();
        $this->doctorModel      = new DoctorModel();
    }

    public function adminList() {
        requireRole('admin');
        $filters = [
            'doctor_id' => $_GET['doctor_id'] ?? '',
            'date'      => $_GET['date'] ?? '',
            'status'    => $_GET['status'] ?? '',
        ];
        $appointments = $this->appointmentModel->getAll($filters);
        $doctors      = $this->doctorModel->getAllAdmin();
        $success      = getFlash('success');
        $error        = getFlash('error');
        require BASE_PATH . '/views/admin/appointments.php';
    }

    public function updateStatus() {
        requireLogin();

        $id     = (int)($_POST['appointment_id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        $role   = $_SESSION['role'];

        if ($id <= 0) {
            setFlash('error', 'Invalid appointment ID.');
            redirect($this->redirectAfterUpdate());
        }

        $validStatuses = ['Pending', 'Confirmed', 'Completed', 'Cancelled', 'No-Show'];
        if (!in_array($status, $validStatuses, true)) {
            setFlash('error', 'Invalid appointment status.');
            redirect($this->redirectAfterUpdate());
        }

        $appointment = $this->appointmentModel->findById($id);
        if (!$appointment) {
            setFlash('error', 'Appointment not found.');
            redirect($this->redirectAfterUpdate());
        }

        if ($role === 'doctor') {
            $doctor = (new DoctorModel())->findByUserId($_SESSION['user_id']);
            if (!$doctor || (int)$doctor['id'] !== (int)$appointment['doctor_id']) {
                setFlash('error', 'Forbidden.');
                redirect($this->redirectAfterUpdate());
            }

            // Doctors are allowed to mark Completed, No-Show, or Cancelled
            if (!in_array($status, ['Completed', 'No-Show', 'Cancelled'], true)) {
                setFlash('error', 'Doctors can only set Completed, No-Show, or Cancelled.');
                redirect($this->redirectAfterUpdate());
            }
        } elseif ($role === 'admin') {
            if ($status === 'Cancelled' && $reason === '') {
                setFlash('error', 'Cancellation reason is required.');
                redirect($this->redirectAfterUpdate());
            }
        } else {
            setFlash('error', 'Forbidden.');
            redirect($this->redirectAfterUpdate());
        }

        $this->appointmentModel->updateStatus($id, $status, $reason ?: null);
        setFlash('success', 'Appointment status updated.');
        redirect($this->redirectAfterUpdate());
    }

    public function reschedule() {
        requireRole('doctor');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/index.php?page=doctor/dashboard');

        $id = (int)($_POST['appointment_id'] ?? 0);
        $newDate = trim($_POST['new_date'] ?? '');
        $newTime = trim($_POST['new_time'] ?? '');

        if ($id <= 0 || $newDate === '' || $newTime === '') {
            setFlash('error', 'Invalid reschedule parameters.');
            redirect('/index.php?page=doctor/dashboard');
        }

        $doctor = (new DoctorModel())->findByUserId($_SESSION['user_id']);
        if (!$doctor) redirect('/index.php?page=login');

        $appointment = $this->appointmentModel->findById($id);
        if (!$appointment || (int)$appointment['doctor_id'] !== (int)$doctor['id']) {
            setFlash('error', 'Appointment not found or forbidden.');
            redirect('/index.php?page=doctor/dashboard');
        }

        // validate date
        $today = date('Y-m-d');
        if ($newDate < $today) {
            setFlash('error', 'New date must be today or later.');
            redirect('/index.php?page=doctor/dashboard');
        }

        // check doctor works that day
        $dayName = date('l', strtotime($newDate));
        $availDays = array_map('trim', explode(',', $doctor['available_days'] ?? ''));
        if (!in_array($dayName, $availDays, true)) {
            setFlash('error', 'Doctor is not available on the selected day.');
            redirect('/index.php?page=doctor/dashboard');
        }

        // Enforce 15-minute cutoff for same-day reschedules
        if ($newDate === $today) {
            $minTs = time() + 15 * 60;
            $selTs = strtotime($newDate . ' ' . $newTime);
            if ($selTs < $minTs) {
                setFlash('error', 'Selected time must be at least 15 minutes from now.');
                redirect('/index.php?page=doctor/dashboard');
            }
        }

        // check slot availability (allow if the slot is currently taken by this same appointment)
        $booked = $this->doctorModel->getBookedSlots($doctor['id'], $newDate);
        // normalize booked times (stored as HH:MM:SS)
        $normalized = array_map(fn($t) => substr($t, 0, 5), $booked);
        if (in_array($newTime, $normalized, true) && !($newDate === $appointment['appointment_date'] && $newTime === substr($appointment['appointment_time'], 0, 5))) {
            setFlash('error', 'Selected time slot is already booked.');
            redirect('/index.php?page=doctor/dashboard');
        }

        $ok = $this->appointmentModel->reschedule($id, $newDate, $newTime . ':00');
        if ($ok) {
            setFlash('success', 'Appointment rescheduled.');
        } else {
            setFlash('error', 'Failed to reschedule appointment.');
        }
        redirect('/index.php?page=doctor/dashboard');
    }

    private function redirectAfterUpdate() {
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $parts = parse_url($_SERVER['HTTP_REFERER']);
            if (!empty($parts['path'])) {
                $path = $parts['path'];
                $query = isset($parts['query']) ? '?' . $parts['query'] : '';
                $pathPos = strpos($path, '/index.php');
                if ($pathPos !== false) {
                    return substr($path, $pathPos) . $query;
                }
            }
        }

        return $_SESSION['role'] === 'doctor'
            ? '/index.php?page=doctor/dashboard'
            : '/index.php?page=admin/appointments';
    }
}
