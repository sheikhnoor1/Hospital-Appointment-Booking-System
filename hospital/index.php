<?php
session_start();
define('BASE_PATH', __DIR__);

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/config/app.php';

$page   = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// ── Route map ─────────────────────────────────────────────
// Auth routes
if (in_array($page, ['login','register','logout','profile','profile/update','profile/password'])) {
    require_once BASE_PATH . '/controllers/AuthController.php';
    $auth = new AuthController();

    switch ($page) {
        case 'login':    $method === 'POST' ? $auth->login() : $auth->showLogin(); break;
        case 'register': $method === 'POST' ? $auth->register() : $auth->showRegister(); break;
        case 'logout':   $auth->logout(); break;
        case 'profile':  $auth->showProfile(); break;
        case 'profile/update':   $auth->updateProfile(); break;
        case 'profile/password': $auth->changePassword(); break;
    }
    exit;
}

// Admin routes
if (strpos($page, 'admin/') === 0) {
    require_once BASE_PATH . '/controllers/AdminController.php';
    require_once BASE_PATH . '/controllers/AppointmentController.php';
    $admin = new AdminController();
    $appt  = new AppointmentController();

    switch ($page) {
        case 'admin/dashboard':          $admin->dashboard(); break;
        case 'admin/specializations':
            if ($method === 'POST') {
                $act = $_POST['_action'] ?? 'create';
                if ($act === 'edit') $admin->editSpec();
                elseif ($act === 'delete') $admin->deleteSpec();
                else $admin->createSpec();
            } else {
                $admin->specializations();
            }
            break;
        case 'admin/doctors':
            $method === 'POST' ? $admin->createDoctor() : $admin->doctors(); break;
        case 'admin/doctor-edit':
            $method === 'POST' ? $admin->updateDoctor() : $admin->editDoctorForm(); break;
        case 'admin/doctor-deactivate':
            $admin->deactivateDoctor(); break;
        case 'admin/users':
            $method === 'POST' ? $admin->toggleUserActive() : $admin->users(); break;
        case 'admin/user-edit':
            $method === 'POST' ? $admin->updateUser() : $admin->editUserForm(); break;
        case 'admin/revenue-report':
            $admin->revenueReport(); break;
        case 'admin/appointments':
            $method === 'POST' ? $appt->updateStatus() : $appt->adminList(); break;
        default:
            http_response_code(404);
            require BASE_PATH . '/views/errors/404.php';
    }
    exit;
}

// Patient routes
if (strpos($page, 'patient/') === 0) {
    require_once BASE_PATH . '/controllers/PatientController.php';
    $patient = new PatientController();

    switch ($page) {
        case 'patient/home':         $patient->home(); break;
        case 'patient/doctor-profile': $patient->doctorProfile(); break;
        case 'patient/book':
            $method === 'POST' ? $patient->book() : $patient->bookingForm(); break;
        case 'patient/confirmation': $patient->confirmation(); break;
        case 'patient/appointments':
            $method === 'POST' ? $patient->cancelAppointment() : $patient->appointments(); break;
        default:
            http_response_code(404);
            require BASE_PATH . '/views/errors/404.php';
    }
    exit;
}

// Doctor routes
if (strpos($page, 'doctor/') === 0) {
    require_once BASE_PATH . '/controllers/DoctorController.php';
    $doctorController = new DoctorController();
    require_once BASE_PATH . '/controllers/AppointmentController.php';
    $appt = new AppointmentController();

    switch ($page) {
        case 'doctor/dashboard':
            $method === 'POST' ? $appt->updateStatus() : $doctorController->dashboard();
            break;
        case 'doctor/appointments':
            $method === 'POST' ? $appt->updateStatus() : $doctorController->appointments();
            break;
        case 'doctor/reschedule':
            $appt->reschedule();
            break;
        case 'doctor/schedule':
            $method === 'POST' ? $doctorController->updateSchedule() : $doctorController->schedule();
            break;
        default:
            http_response_code(404);
            require BASE_PATH . '/views/errors/404.php';
    }
    exit;
}

// Default: landing page or 404
if ($page === 'home' || $page === '') {
    if (isLoggedIn()) {
        switch ($_SESSION['role']) {
            case 'admin':  redirect('/index.php?page=admin/dashboard'); break;
            case 'doctor': redirect('/index.php?page=doctor/dashboard'); break;
            default:       redirect('/index.php?page=patient/home'); break;
        }
    }
    require BASE_PATH . '/views/landing.php';
    exit;
}

if ($page === 'unauthorized') {
    http_response_code(403);
    require BASE_PATH . '/views/errors/403.php';
    exit;
}

http_response_code(404);
require BASE_PATH . '/views/errors/404.php';
