<?php
require_once BASE_PATH . '/models/UserModel.php';
require_once BASE_PATH . '/models/DoctorModel.php';
require_once BASE_PATH . '/models/AppointmentModel.php';

class ApiController {
    private $userModel;
    private $doctorModel;
    private $appointmentModel;

    public function __construct() {
        $this->userModel        = new UserModel();
        $this->doctorModel      = new DoctorModel();
        $this->appointmentModel = new AppointmentModel();
    }

    // POST /api/users/toggle-active
    public function toggleUserActive() {
        if (!isLoggedIn() || $_SESSION['role'] !== 'admin')
            jsonResponse(['error' => 'Unauthorized'], 403);

        $data = json_decode(file_get_contents('php://input'), true);
        $id   = (int)($data['user_id'] ?? 0);
        if ($id <= 0) jsonResponse(['error' => 'Invalid user ID'], 400);

        $newStatus = $this->userModel->toggleActive($id);
        jsonResponse(['ok' => true, 'is_active' => (int)$newStatus]);
    }

    // GET /api/doctors?specialization_id=
    public function getDoctors() {
        if (!isLoggedIn()) jsonResponse(['error' => 'Unauthorized'], 401);
        $specId  = isset($_GET['specialization_id']) ? (int)$_GET['specialization_id'] : null;
        $doctors = $this->doctorModel->getAll($specId);
        $result  = [];
        foreach ($doctors as $d) {
            $result[] = [
                'id'               => $d['id'],
                'name'             => $d['name'],
                'specialization'   => $d['specialization_name'],
                'fee'              => $d['consultation_fee'],
                'photo'            => $d['photo_path'] ? UPLOAD_URL . $d['photo_path'] : null,
                'available_days'   => $d['available_days'],
                'bio'              => $d['bio'],
            ];
        }
        jsonResponse($result);
    }

    // GET /api/doctors/stats
    public function getDoctorStats() {
        if (!isLoggedIn() || $_SESSION['role'] !== 'admin')
            jsonResponse(['error' => 'Unauthorized'], 403);
        $stats = $this->doctorModel->getStats();
        jsonResponse($stats);
    }

    // GET /api/doctors/{id}/slots?date=YYYY-MM-DD
    public function getDoctorSlots() {
        if (!isLoggedIn()) jsonResponse(['error' => 'Unauthorized'], 401);
        $doctorId = (int)($_GET['doctor_id'] ?? 0);
        $date     = $_GET['date'] ?? '';

        if ($doctorId <= 0) jsonResponse(['error' => 'Invalid doctor'], 400);
        if (!validateDate($date)) jsonResponse(['error' => 'Invalid date'], 400);

        // Check date is not in the past
        if ($date < date('Y-m-d')) jsonResponse(['error' => 'Date is in the past'], 400);

        $doctor = $this->doctorModel->findById($doctorId);
        if (!$doctor) jsonResponse(['error' => 'Doctor not found'], 404);

        $dayName   = date('l', strtotime($date));
        $availDays = array_map('trim', explode(',', $doctor['available_days'] ?? ''));
        if (!in_array($dayName, $availDays))
            jsonResponse(['slots' => [], 'message' => 'Doctor not available on this day']);

        $allSlots    = timeSlots();
        $bookedSlots = $this->doctorModel->getBookedSlots($doctorId, $date);
        $available   = array_values(array_filter($allSlots, fn($s) => !in_array($s . ':00', $bookedSlots) && !in_array($s, $bookedSlots)));

        jsonResponse(['slots' => $available, 'date' => $date, 'day' => $dayName]);
    }

    // POST /api/appointments/cancel
    public function cancelAppointment() {
        if (!isLoggedIn() || $_SESSION['role'] !== 'patient')
            jsonResponse(['error' => 'Unauthorized'], 403);

        $data          = json_decode(file_get_contents('php://input'), true);
        $appointmentId = (int)($data['appointment_id'] ?? 0);
        if ($appointmentId <= 0) jsonResponse(['error' => 'Invalid appointment ID'], 400);

        $ok = $this->appointmentModel->cancelByPatient($appointmentId, $_SESSION['user_id']);
        if ($ok)
            jsonResponse(['ok' => true, 'message' => 'Appointment cancelled']);
        else
            jsonResponse(['ok' => false, 'message' => 'Could not cancel appointment'], 400);
    }

    // PUT /api/appointments/{id}
    public function updateAppointmentStatus() {
        if (!isLoggedIn()) jsonResponse(['error' => 'Unauthorized'], 401);

        $id   = (int)($_GET['appointment_id'] ?? 0);
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) $data = $_POST;

        $status = trim($data['status'] ?? '');
        $reason = trim($data['reason'] ?? '');
        $role   = $_SESSION['role'];

        if ($id <= 0) jsonResponse(['error' => 'Invalid appointment ID'], 400);

        $validStatuses = ['Pending','Confirmed','Completed','Cancelled','No-Show'];
        if (!in_array($status, $validStatuses))
            jsonResponse(['error' => 'Invalid status'], 400);

        $appointment = $this->appointmentModel->findById($id);
        if (!$appointment) jsonResponse(['error' => 'Appointment not found'], 404);

        // Role-based permission
        if ($role === 'doctor') {
            $doctor = (new DoctorModel())->findByUserId($_SESSION['user_id']);
            if (!$doctor || $doctor['id'] != $appointment['doctor_id'])
                jsonResponse(['error' => 'Forbidden'], 403);
            if (!in_array($status, ['Completed','No-Show']))
                jsonResponse(['error' => 'Doctors can only set Completed or No-Show'], 403);
        } elseif ($role === 'admin') {
            // admin can set any status; reason required for cancel
            if ($status === 'Cancelled' && empty($reason))
                jsonResponse(['error' => 'Cancellation reason is required'], 400);
        } else {
            jsonResponse(['error' => 'Forbidden'], 403);
        }

        $this->appointmentModel->updateStatus($id, $status, $reason ?: null);
        jsonResponse(['ok' => true, 'new_status' => $status]);
    }
}
