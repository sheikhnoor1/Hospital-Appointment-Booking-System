<?php
require_once BASE_PATH . '/models/UserModel.php';

class AuthController {
    private $userModel;
    public function __construct() { $this->userModel = new UserModel(); }

    public function showLogin() {
        if (isLoggedIn()) $this->redirectByRole($_SESSION['role']);
        $error = getFlash('error');
        $success = getFlash('success');
        require BASE_PATH . '/views/auth/login.php';
    }

    public function login() {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors   = [];

        if (empty($email))    $errors['email']    = 'Email is required.';
        elseif (!validateEmail($email)) $errors['email'] = 'Enter a valid email address.';
        if (empty($password)) $errors['password'] = 'Password is required.';

        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old'] = ['email' => $email];
            redirect('/index.php?page=login');
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION['form_errors'] = ['general' => 'Invalid email or password.'];
            $_SESSION['old'] = ['email' => $email];
            redirect('/index.php?page=login');
        }

        if (!$user['is_active']) {
            $_SESSION['form_errors'] = ['general' => 'Your account has been deactivated. Contact admin.'];
            $_SESSION['old'] = ['email' => $email];
            redirect('/index.php?page=login');
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['role']    = $user['role'];

        $this->redirectByRole($user['role']);
    }

    public function showRegister() {
        if (isLoggedIn()) $this->redirectByRole($_SESSION['role']);
        $errors = $_SESSION['form_errors'] ?? [];
        $old    = $_SESSION['old'] ?? [];
        unset($_SESSION['form_errors'], $_SESSION['old']);
        require BASE_PATH . '/views/auth/register.php';
    }

    public function register() {
        $name        = trim($_POST['name'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $password    = $_POST['password'] ?? '';
        $confirm     = $_POST['confirm_password'] ?? '';
        $dob         = trim($_POST['dob'] ?? '');
        $blood_group = trim($_POST['blood_group'] ?? '');
        $phone       = trim($_POST['phone'] ?? '');
        $errors      = [];

        if (empty($name))          $errors['name']  = 'Full name is required.';
        elseif (strlen($name) < 2) $errors['name']  = 'Name must be at least 2 characters.';
        if (empty($email))              $errors['email']    = 'Email is required.';
        elseif (!validateEmail($email)) $errors['email']    = 'Enter a valid email address.';
        if (empty($password))           $errors['password'] = 'Password is required.';
        elseif (strlen($password) < 6)  $errors['password'] = 'Password must be at least 6 characters.';
        if ($password !== $confirm)     $errors['confirm_password'] = 'Passwords do not match.';
        if (!empty($dob) && !validateDate($dob)) $errors['dob'] = 'Enter a valid date of birth.';
        if (!empty($phone) && !validatePhone($phone)) $errors['phone'] = 'Enter a valid phone number.';

        $allowed_groups = ['A+','A-','B+','B-','O+','O-','AB+','AB-'];
        if (!empty($blood_group) && !in_array($blood_group, $allowed_groups))
            $errors['blood_group'] = 'Select a valid blood group.';

        if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
            $errors['general'] = 'Please fill all required fields.';
        }

        if (!$errors && $this->userModel->findByEmail($email))
            $errors['email'] = 'This email is already registered.';

        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old'] = compact('name','email','dob','blood_group','phone');
            redirect('/index.php?page=register');
        }

        try {
            $userId = $this->userModel->create(compact('name','email','password','dob','blood_group','phone'));
            $user = $this->userModel->findById($userId);

            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            setFlash('success', 'Registration successful! Welcome to MediBook.');
            redirect('/index.php?page=patient/home');
        } catch (Throwable $e) {
            $_SESSION['form_errors'] = ['general' => 'Registration failed. Please try again.'];
            $_SESSION['old'] = compact('name','email','dob','blood_group','phone');
            redirect('/index.php?page=register');
        }
    }

    public function logout() {
        session_destroy();
        redirect('/index.php?page=login');
    }

    public function showProfile() {
        requireLogin();
        $user   = $this->userModel->findById($_SESSION['user_id']);
        $errors = $_SESSION['form_errors'] ?? [];
        $old    = $_SESSION['old'] ?? [];
        $success = getFlash('success');
        unset($_SESSION['form_errors'], $_SESSION['old']);
        $upcomingCount = 0;
        if ($_SESSION['role'] === 'patient')
            $upcomingCount = $this->userModel->countUpcomingAppointments($_SESSION['user_id']);
        require BASE_PATH . '/views/auth/profile.php';
    }

    public function updateProfile() {
        requireLogin();
        $name  = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $dob   = trim($_POST['dob'] ?? '');
        $errors = [];

        if (empty($name))          $errors['name']  = 'Full name is required.';
        elseif (strlen($name) < 2) $errors['name']  = 'Name must be at least 2 characters.';
        if (!empty($phone) && !validatePhone($phone)) $errors['phone'] = 'Enter a valid phone number.';
        if (!empty($dob) && !validateDate($dob)) $errors['dob'] = 'Enter a valid date of birth.';

        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old'] = compact('name','phone','dob');
            redirect('/index.php?page=profile');
        }

        $this->userModel->update($_SESSION['user_id'], compact('name','phone','dob'));
        $_SESSION['name'] = $name;
        setFlash('success', 'Profile updated successfully.');
        redirect('/index.php?page=profile');
    }

    public function changePassword() {
        requireLogin();
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_new_password'] ?? '';
        $errors  = [];

        if (empty($current)) $errors['current_password'] = 'Current password is required.';
        if (empty($new))     $errors['new_password']     = 'New password is required.';
        elseif (strlen($new) < 6) $errors['new_password'] = 'New password must be at least 6 characters.';
        if ($new !== $confirm) $errors['confirm_new_password'] = 'Passwords do not match.';

        if (!$errors) {
            $user = $this->userModel->findById($_SESSION['user_id']);
            if (!password_verify($current, $user['password_hash']))
                $errors['current_password'] = 'Current password is incorrect.';
        }

        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            redirect('/index.php?page=profile');
        }

        $this->userModel->updatePassword($_SESSION['user_id'], $new);
        setFlash('success', 'Password changed successfully.');
        redirect('/index.php?page=profile');
    }

    private function redirectByRole($role) {
        switch ($role) {
            case 'admin':   redirect('/index.php?page=admin/dashboard'); break;
            case 'doctor':  redirect('/index.php?page=doctor/dashboard'); break;
            default:        redirect('/index.php?page=patient/home'); break;
        }
    }
}
