<?php
require_once BASE_PATH . '/models/UserModel.php';
require_once BASE_PATH . '/models/DoctorModel.php';
require_once BASE_PATH . '/models/SpecializationModel.php';
require_once BASE_PATH . '/models/AppointmentModel.php';

class AdminController {
    private $userModel;
    private $doctorModel;
    private $specModel;
    private $appointmentModel;

    public function __construct() {
        $this->userModel   = new UserModel();
        $this->doctorModel = new DoctorModel();
        $this->specModel   = new SpecializationModel();
        $this->appointmentModel = new AppointmentModel();
    }

    public function dashboard() {
        requireRole('admin');
        $totalDoctors = count($this->doctorModel->getAllAdmin());
        $allUsers = $this->userModel->getAllUsers();
        $totalPatients = count(array_filter($allUsers, fn($u) => $u['role'] === 'patient'));
        $doctorStats = $this->appointmentModel->getDoctorRevenueBreakdown();
        $totalAppointments = array_sum(array_map(fn($row) => (int)$row['total_appointments'], $doctorStats));
        $revenueSummary = $this->appointmentModel->getAdminRevenueSummary();
        $totalRevenue = $revenueSummary['total_revenue'];
        $todayRevenue = $revenueSummary['today_revenue'];
        require BASE_PATH . '/views/admin/dashboard.php';
    }

    // --- Specializations ---
    public function specializations() {
        requireRole('admin');
        $specs   = $this->specModel->getAll();
        $errors  = $_SESSION['form_errors'] ?? [];
        $old     = $_SESSION['old'] ?? [];
        $success = getFlash('success');
        $error   = getFlash('error');
        unset($_SESSION['form_errors'], $_SESSION['old']);
        require BASE_PATH . '/views/admin/specializations.php';
    }

    public function createSpec() {
        requireRole('admin');
        $name   = trim($_POST['name'] ?? '');
        $errors = [];
        if (empty($name))           $errors['name'] = 'Specialization name is required.';
        elseif (strlen($name) < 2)  $errors['name'] = 'Name must be at least 2 characters.';
        elseif (strlen($name) > 100) $errors['name'] = 'Name must not exceed 100 characters.';

        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old'] = ['name' => $name];
            redirect('/index.php?page=admin/specializations');
        }
        $this->specModel->create($name);
        setFlash('success', 'Specialization added.');
        redirect('/index.php?page=admin/specializations');
    }

    public function editSpec() {
        requireRole('admin');
        $id   = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $errors = [];
        if (empty($name))            $errors['name'] = 'Specialization name is required.';
        elseif (strlen($name) < 2)   $errors['name'] = 'Name must be at least 2 characters.';
        elseif (strlen($name) > 100) $errors['name'] = 'Name must not exceed 100 characters.';

        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old'] = ['edit_id' => $id, 'edit_name' => $name];
            redirect('/index.php?page=admin/specializations');
        }
        $this->specModel->update($id, $name);
        setFlash('success', 'Specialization updated.');
        redirect('/index.php?page=admin/specializations');
    }

    public function deleteSpec() {
        requireRole('admin');
        $id = (int)($_POST['id'] ?? 0);
        if ($this->specModel->hasDoctors($id)) {
            setFlash('error', 'Cannot delete: doctors are assigned to this specialization.');
        } else {
            $this->specModel->delete($id);
            setFlash('success', 'Specialization deleted.');
        }
        redirect('/index.php?page=admin/specializations');
    }

    // --- Doctors ---
    public function doctors() {
        requireRole('admin');
        $doctors = $this->doctorModel->getAllAdmin();
        $specs   = $this->specModel->getAll();
        $errors  = $_SESSION['form_errors'] ?? [];
        $old     = $_SESSION['old'] ?? [];
        $success = getFlash('success');
        $error   = getFlash('error');
        unset($_SESSION['form_errors'], $_SESSION['old']);
        require BASE_PATH . '/views/admin/doctors.php';
    }

    public function createDoctor() {
        requireRole('admin');
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $spec_id  = (int)($_POST['specialization_id'] ?? 0);
        $bio      = trim($_POST['bio'] ?? '');
        $fee      = trim($_POST['consultation_fee'] ?? '');
        $days     = $_POST['available_days'] ?? [];
        $errors   = [];

        if (empty($name))          $errors['name']     = 'Name is required.';
        if (empty($email))              $errors['email']    = 'Email is required.';
        elseif (!validateEmail($email)) $errors['email']    = 'Enter a valid email.';
        if (empty($password))      $errors['password'] = 'Password is required.';
        elseif (strlen($password) < 6) $errors['password'] = 'Password must be at least 6 characters.';
        if ($spec_id <= 0)         $errors['specialization_id'] = 'Select a specialization.';
        if (empty($fee) || !is_numeric($fee) || (float)$fee < 0)
            $errors['consultation_fee'] = 'Enter a valid consultation fee.';

        // Photo upload
        $photoPath = null;
        if (!empty($_FILES['photo']['name'])) {
            $photoPath = $this->handleDoctorPhotoUpload($_FILES['photo'], $errors);
        }

        if (!$errors && $this->userModel->findByEmail($email))
            $errors['email'] = 'This email is already registered.';

        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old'] = compact('name','email','spec_id','bio','fee','days');
            redirect('/index.php?page=admin/doctors');
        }

        $userId = $this->userModel->create(['name'=>$name,'email'=>$email,'password'=>$password,'role'=>'doctor']);
        $availDays = implode(',', array_filter($days, fn($d) => in_array($d, ['Monday','Tuesday','Wednesday','Thursday','Friday'])));
        $this->doctorModel->create([
            'user_id' => $userId, 'specialization_id' => $spec_id,
            'bio' => $bio, 'consultation_fee' => (float)$fee,
            'photo_path' => $photoPath, 'available_days' => $availDays
        ]);
        setFlash('success', 'Doctor added successfully.');
        redirect('/index.php?page=admin/doctors');
    }

    public function editDoctorForm() {
        requireRole('admin');
        $id     = (int)($_GET['id'] ?? 0);
        $doctor = $this->doctorModel->findById($id);
        if (!$doctor) { redirect('/index.php?page=admin/doctors'); }
        $specs   = $this->specModel->getAll();
        $errors  = $_SESSION['form_errors'] ?? [];
        $old     = $_SESSION['old'] ?? [];
        $success = getFlash('success');
        unset($_SESSION['form_errors'], $_SESSION['old']);
        require BASE_PATH . '/views/admin/doctor_edit.php';
    }

    public function updateDoctor() {
        requireRole('admin');
        $id      = (int)($_POST['id'] ?? 0);
        $name    = trim($_POST['name'] ?? '');
        $spec_id = (int)($_POST['specialization_id'] ?? 0);
        $bio     = trim($_POST['bio'] ?? '');
        $fee     = trim($_POST['consultation_fee'] ?? '');
        $days    = $_POST['available_days'] ?? [];
        $errors  = [];

        $doctor = $this->doctorModel->findById($id);
        if (!$doctor) redirect('/index.php?page=admin/doctors');

        if (empty($name))  $errors['name'] = 'Name is required.';
        if ($spec_id <= 0) $errors['specialization_id'] = 'Select a specialization.';
        if (empty($fee) || !is_numeric($fee) || (float)$fee < 0)
            $errors['consultation_fee'] = 'Enter a valid consultation fee.';

        // Photo upload
        if (!empty($_FILES['photo']['name'])) {
            $filename = $this->handleDoctorPhotoUpload($_FILES['photo'], $errors);
            if ($filename) {
                // delete old
                if ($doctor['photo_path'] && file_exists(UPLOAD_PATH . $doctor['photo_path'])) {
                    unlink(UPLOAD_PATH . $doctor['photo_path']);
                }
                $this->doctorModel->updatePhoto($id, $filename);
            }
        }

        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            redirect('/index.php?page=admin/doctor-edit&id=' . $id);
        }

        // Update user name
        $this->userModel->update($doctor['user_id'], ['name'=>$name,'phone'=>'','dob'=>null]);
        $availDays = implode(',', array_filter($days, fn($d) => in_array($d, ['Monday','Tuesday','Wednesday','Thursday','Friday'])));
        $this->doctorModel->update($id, [
            'specialization_id' => $spec_id,
            'bio' => $bio, 'consultation_fee' => (float)$fee,
            'available_days' => $availDays
        ]);
        setFlash('success', 'Doctor updated.');
        redirect('/index.php?page=admin/doctor-edit&id=' . $id);
    }

    public function deactivateDoctor() {
        requireRole('admin');
        $id     = (int)($_POST['id'] ?? 0);
        $doctor = $this->doctorModel->findById($id);
        if ($doctor) {
            $this->userModel->toggleActive($doctor['user_id']);
            setFlash('success', 'Doctor status updated.');
        }
        redirect('/index.php?page=admin/doctors');
    }

    // --- Users ---
    public function users() {
        requireRole('admin');
        $users   = $this->userModel->getAllUsers();
        $success = getFlash('success');
        $error   = getFlash('error');
        require BASE_PATH . '/views/admin/users.php';
    }

    public function toggleUserActive() {
        requireRole('admin');
        $id = (int)($_POST['user_id'] ?? 0);

        if ($id <= 0) {
            setFlash('error', 'Invalid user ID.');
            redirect('/index.php?page=admin/users');
        }

        if ($id === (int)$_SESSION['user_id']) {
            setFlash('error', 'You cannot change your own account status.');
            redirect('/index.php?page=admin/users');
        }

        $newStatus = $this->userModel->toggleActive($id);
        setFlash('success', $newStatus ? 'User activated.' : 'User deactivated.');
        redirect('/index.php?page=admin/users');
    }

    public function editUserForm() {
        requireRole('admin');
        $id = (int)($_GET['id'] ?? 0);
        $user = $this->userModel->findById($id);
        if (!$user) {
            setFlash('error', 'User not found.');
            redirect('/index.php?page=admin/users');
        }

        $errors = $_SESSION['form_errors'] ?? [];
        $old = $_SESSION['old'] ?? [];
        $success = getFlash('success');
        $error = getFlash('error');
        unset($_SESSION['form_errors'], $_SESSION['old']);

        require BASE_PATH . '/views/admin/user_edit.php';
    }

    public function updateUser() {
        requireRole('admin');
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $errors = [];

        $user = $this->userModel->findById($id);
        if (!$user) {
            setFlash('error', 'User not found.');
            redirect('/index.php?page=admin/users');
        }

        if ($name === '') {
            $errors['name'] = 'Name is required.';
        }
        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!validateEmail($email)) {
            $errors['email'] = 'Enter a valid email.';
        } else {
            $existing = $this->userModel->findByEmail($email);
            if ($existing && (int)$existing['id'] !== $id) {
                $errors['email'] = 'Email is already in use by another user.';
            }
        }
        if ($phone !== '' && !validatePhone($phone)) {
            $errors['phone'] = 'Enter a valid phone number.';
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old'] = ['name' => $name, 'email' => $email, 'phone' => $phone];
            redirect('/index.php?page=admin/user-edit&id=' . $id);
        }

        $this->userModel->updateByAdmin($id, [
            'name' => $name,
            'email' => $email,
            'phone' => $phone !== '' ? $phone : null,
        ]);

        setFlash('success', 'User profile updated.');
        redirect('/index.php?page=admin/users');
    }

    public function revenueReport() {
        requireRole('admin');

        $fromDate = trim($_GET['from_date'] ?? '');
        $toDate = trim($_GET['to_date'] ?? '');
        $errors = [];

        if ($fromDate !== '' && !validateDate($fromDate)) {
            $errors[] = 'From date is invalid.';
        }
        if ($toDate !== '' && !validateDate($toDate)) {
            $errors[] = 'To date is invalid.';
        }
        if ($fromDate !== '' && $toDate !== '' && $fromDate > $toDate) {
            $errors[] = 'From date cannot be after To date.';
        }

        if (!empty($errors)) {
            $fromDate = '';
            $toDate = '';
        }

        $summary = $this->appointmentModel->getAdminRevenueSummary($fromDate ?: null, $toDate ?: null);
        $doctorStats = $this->appointmentModel->getDoctorRevenueBreakdown($fromDate ?: null, $toDate ?: null);
        $appointments = $this->appointmentModel->getRevenueAppointmentRows($fromDate ?: null, $toDate ?: null);

        require BASE_PATH . '/views/admin/revenue_report.php';
    }

    private function ensureDoctorUploadDir() {
        if (!is_dir(UPLOAD_PATH)) {
            return mkdir(UPLOAD_PATH, 0775, true);
        }
        return is_writable(UPLOAD_PATH);
    }

    private function handleDoctorPhotoUpload($file, &$errors) {
        $uploadError = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($uploadError !== UPLOAD_ERR_OK) {
            if ($uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE) {
                $errors['photo'] = 'Photo exceeds upload limit. Set upload_max_filesize and post_max_size to at least 5M in php.ini.';
            } elseif ($uploadError === UPLOAD_ERR_NO_FILE) {
                $errors['photo'] = 'Please select a photo to upload.';
            } else {
                $errors['photo'] = 'Failed to upload photo.';
            }
            return null;
        }

        if (($file['size'] ?? 0) > MAX_PHOTO_SIZE) {
            $errors['photo'] = 'Photo must be under 5MB.';
            return null;
        }

        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowedExt, true)) {
            $errors['photo'] = 'Only JPG, JPEG, and PNG images are allowed.';
            return null;
        }

        $mime = '';
        if (function_exists('mime_content_type')) {
            $mime = (string)mime_content_type($file['tmp_name']);
        }
        $allowedMime = ['image/jpeg', 'image/pjpeg', 'image/jpg', 'image/png', 'image/x-png'];
        if ($mime !== '' && !in_array($mime, $allowedMime, true)) {
            $errors['photo'] = 'Only JPG, JPEG, and PNG images are allowed.';
            return null;
        }

        if (!$this->ensureDoctorUploadDir()) {
            $errors['photo'] = 'Upload folder is not writable.';
            return null;
        }

        $normalizedExt = ($ext === 'jpeg') ? 'jpg' : $ext;
        $filename = 'doc_' . time() . '_' . rand(100, 999) . '.' . $normalizedExt;
        if (!move_uploaded_file($file['tmp_name'], UPLOAD_PATH . $filename)) {
            $errors['photo'] = 'Failed to upload photo.';
            return null;
        }

        return $filename;
    }
}
