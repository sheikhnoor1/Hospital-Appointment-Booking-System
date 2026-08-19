<?php
require_once BASE_PATH . '/models/DoctorModel.php';
require_once BASE_PATH . '/models/AppointmentModel.php';
require_once BASE_PATH . '/models/SpecializationModel.php';

class PatientController {
    private $doctorModel;
    private $appointmentModel;
    private $specModel;

    public function __construct() {
        $this->doctorModel      = new DoctorModel();
        $this->appointmentModel = new AppointmentModel();
        $this->specModel        = new SpecializationModel();
    }

    public function home() {
        requireRole('patient');
        $specs   = $this->specModel->getAll();
        $selectedSpecId = isset($_GET['specialization_id']) && $_GET['specialization_id'] !== '' ? (int)$_GET['specialization_id'] : null;
        $doctors = $this->doctorModel->getAll($selectedSpecId);
        require BASE_PATH . '/views/patient/home.php';
    }

    public function doctorProfile() {
        requireRole('patient');
        $id     = (int)($_GET['id'] ?? 0);
        $doctor = $this->doctorModel->findById($id);
        if (!$doctor) redirect('/index.php?page=patient/home');

        $selectedDate = $_GET['date'] ?? '';
        $availableSlots = [];
        $slotMessage = '';

        if ($selectedDate !== '') {
            if (!validateDate($selectedDate)) {
                $slotMessage = 'Please choose a valid date.';
            } elseif ($selectedDate < date('Y-m-d')) {
                $slotMessage = 'Date is in the past.';
            } else {
                $dayName   = date('l', strtotime($selectedDate));
                $availDays = array_map('trim', explode(',', $doctor['available_days'] ?? ''));
                if (!in_array($dayName, $availDays, true)) {
                    $slotMessage = 'Doctor not available on this day.';
                } else {
                    $availableSlots = $this->doctorModel->getAvailableSlots($doctor['id'], $selectedDate);
                    if (empty($availableSlots)) {
                        $slotMessage = 'No available slots for this date.';
                    }
                }
            }
        }

        require BASE_PATH . '/views/patient/doctor_profile.php';
    }

    public function bookingForm() {
        requireRole('patient');
        $doctorId = (int)($_GET['doctor_id'] ?? 0);
        $date     = $_GET['date'] ?? '';
        $time     = $_GET['time'] ?? '';
        $doctor   = $this->doctorModel->findById($doctorId);
        if (!$doctor || empty($date) || empty($time)) redirect('/index.php?page=patient/home');

        // Validate date is a valid available day
        $dayName = date('l', strtotime($date));
        $availDays = array_map('trim', explode(',', $doctor['available_days'] ?? ''));
        if (!in_array($dayName, $availDays)) redirect('/index.php?page=patient/doctor-profile&id=' . $doctorId);

        $errors = $_SESSION['form_errors'] ?? [];
        $old    = $_SESSION['old'] ?? [];
        unset($_SESSION['form_errors'], $_SESSION['old']);
        $consultationFee = (float)$doctor['consultation_fee'];
        $totalFee = $consultationFee;
        require BASE_PATH . '/views/patient/book_appointment.php';
    }

    public function book() {
        requireRole('patient');
        $doctorId = (int)($_POST['doctor_id'] ?? 0);
        $date     = trim($_POST['appointment_date'] ?? '');
        $time     = trim($_POST['appointment_time'] ?? '');
        $reason   = trim($_POST['reason'] ?? '');
        $errors   = [];

        if ($doctorId <= 0) $errors['general'] = 'Invalid doctor.';
        if (empty($date) || !validateDate($date)) $errors['appointment_date'] = 'Invalid date.';
        if (empty($time)) $errors['appointment_time'] = 'Please select a time slot.';
        if (empty($reason)) $errors['reason'] = 'Please provide a reason for the appointment.';
        elseif (strlen($reason) < 5) $errors['reason'] = 'Reason must be at least 5 characters.';

        $doctor = null;
        if (!$errors) {
            $doctor = $this->doctorModel->findById($doctorId);
            if (!$doctor) $errors['general'] = 'Doctor not found.';
        }

        if (!$errors) {
            // Validate day availability
            $dayName   = date('l', strtotime($date));
            $availDays = array_map('trim', explode(',', $doctor['available_days'] ?? ''));
            if (!in_array($dayName, $availDays)) $errors['appointment_date'] = 'Doctor is not available on this day.';
        }

        if (!$errors) {
            // Validate time slot
            $validSlots = timeSlots();
            if (!in_array($time, $validSlots)) $errors['appointment_time'] = 'Invalid time slot.';
        }

        if (!$errors) {
            // Race condition check — re-query
            if ($this->appointmentModel->isSlotTaken($doctorId, $date, $time))
                $errors['appointment_time'] = 'This slot was just booked. Please select another.';
        }

        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old'] = compact('date','time','reason');
            redirect('/index.php?page=patient/book&doctor_id=' . $doctorId . '&date=' . $date . '&time=' . urlencode($time));
        }

        $appointmentId = $this->appointmentModel->create([
            'patient_id'       => $_SESSION['user_id'],
            'doctor_id'        => $doctorId,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'fee_at_booking'   => (float)$doctor['consultation_fee'],
            'reason'           => $reason
        ]);

        redirect('/index.php?page=patient/confirmation&id=' . $appointmentId);
    }

    public function confirmation() {
        requireRole('patient');
        $id          = (int)($_GET['id'] ?? 0);
        $appointment = $this->appointmentModel->findById($id);
        if (!$appointment || $appointment['patient_id'] != $_SESSION['user_id'])
            redirect('/index.php?page=patient/appointments');
        require BASE_PATH . '/views/patient/confirmation.php';
    }

    public function appointments() {
        requireRole('patient');
        $appointments = $this->appointmentModel->getByPatient($_SESSION['user_id']);
        $expenseSummary = $this->appointmentModel->getPatientExpenseSummary($_SESSION['user_id']);
        $success      = getFlash('success');
        $error        = getFlash('error');
        require BASE_PATH . '/views/patient/appointments.php';
    }

    public function cancelAppointment() {
        requireRole('patient');
        $appointmentId = (int)($_POST['appointment_id'] ?? 0);

        if ($appointmentId <= 0) {
            setFlash('error', 'Invalid appointment ID.');
            redirect('/index.php?page=patient/appointments');
        }

        $ok = $this->appointmentModel->cancelByPatient($appointmentId, $_SESSION['user_id']);
        if ($ok) {
            setFlash('success', 'Appointment cancelled.');
        } else {
            setFlash('error', 'Could not cancel appointment.');
        }
        redirect('/index.php?page=patient/appointments');
    }
}
