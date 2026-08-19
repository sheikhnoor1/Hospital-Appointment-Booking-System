<?php
define('BASE_URL', 'http://localhost/hospital');
define('BASE_PATH', __DIR__ . '/..');
define('UPLOAD_PATH', BASE_PATH . '/public/uploads/doctors/');
define('UPLOAD_URL', BASE_URL . '/public/uploads/doctors/');
define('MAX_PHOTO_SIZE', 5 * 1024 * 1024); // 5MB

function redirect($path) {
    header('Location: ' . BASE_URL . $path);
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('/index.php?page=login');
    }
}

function requireRole($role) {
    requireLogin();
    if (is_array($role)) {
        if (!in_array($_SESSION['role'], $role)) {
            redirect('/index.php?page=unauthorized');
        }
    } else {
        if ($_SESSION['role'] !== $role) {
            redirect('/index.php?page=unauthorized');
        }
    }
}

function sanitize($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function isAjax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function getFlash($key) {
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

function setFlash($key, $msg) {
    $_SESSION['flash'][$key] = $msg;
}

function validateEmail($email) {
    $email = trim($email);
    if (empty($email)) return false;
    if (strpos($email, '@') === false) return false;
    $parts = explode('@', $email);
    if (count($parts) !== 2) return false;
    if (empty($parts[0]) || empty($parts[1])) return false;
    if (strpos($parts[1], '.') === false) return false;
    return true;
}

function validateDate($date) {
    if (empty($date)) return false;
    $parts = explode('-', $date);
    if (count($parts) !== 3) return false;
    list($y, $m, $d) = $parts;
    if (!ctype_digit($y) || !ctype_digit($m) || !ctype_digit($d)) return false;
    return checkdate((int)$m, (int)$d, (int)$y);
}

function validatePhone($phone) {
    $phone = trim($phone);
    if (empty($phone)) return false;
    $digits = '';
    for ($i = 0; $i < strlen($phone); $i++) {
        $c = $phone[$i];
        if (ctype_digit($c) || $c === '+' || $c === '-' || $c === ' ') {
            if (ctype_digit($c)) $digits .= $c;
        } else {
            return false;
        }
    }
    return strlen($digits) >= 7 && strlen($digits) <= 15;
}

function timeSlots() {
    $slots = [];
    $start = strtotime('09:00');
    $end   = strtotime('17:00');
    for ($t = $start; $t < $end; $t += 1800) {
        $slots[] = date('H:i', $t);
    }
    return $slots;
}
