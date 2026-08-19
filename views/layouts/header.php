<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'MediBook — Hospital Appointment System' ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
</head>
<body>
<div class="wrapper">

<nav class="navbar">
  <div class="navbar-inner">
    <a href="<?= BASE_URL ?>/index.php" class="navbar-brand">
      <svg class="navbar-logo" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect width="40" height="40" rx="10" fill="#0A8A7A"/>
        <path d="M20 10v20M10 20h20" stroke="white" stroke-width="4" stroke-linecap="round"/>
      </svg>
      Medi<span>Book</span>
    </a>

    <nav class="navbar-nav">
      <?php if (isLoggedIn()): ?>
        <span class="navbar-user">Welcome, <?= sanitize($_SESSION['name']) ?></span>

        <?php if ($_SESSION['role'] === 'patient'): ?>
          <a href="<?= BASE_URL ?>/index.php?page=patient/home" class="nav-link">Browse Doctors</a>
          <a href="<?= BASE_URL ?>/index.php?page=patient/appointments" class="nav-link">My Appointments</a>
        <?php elseif ($_SESSION['role'] === 'admin'): ?>
          <a href="<?= BASE_URL ?>/index.php?page=admin/dashboard" class="nav-link">Dashboard</a>
          <a href="<?= BASE_URL ?>/index.php?page=admin/doctors" class="nav-link">Doctors</a>
          <a href="<?= BASE_URL ?>/index.php?page=admin/specializations" class="nav-link">Specializations</a>
          <a href="<?= BASE_URL ?>/index.php?page=admin/appointments" class="nav-link">Appointments</a>
          <a href="<?= BASE_URL ?>/index.php?page=admin/revenue-report" class="nav-link">Revenue Report</a>
          <a href="<?= BASE_URL ?>/index.php?page=admin/users" class="nav-link">Users</a>
        <?php elseif ($_SESSION['role'] === 'doctor'): ?>
          <a href="<?= BASE_URL ?>/index.php?page=doctor/dashboard" class="nav-link">Dashboard</a>
          <a href="<?= BASE_URL ?>/index.php?page=doctor/schedule" class="nav-link">My Schedule</a>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>/index.php?page=profile" class="nav-link">Profile</a>
        <a href="<?= BASE_URL ?>/index.php?page=logout" class="nav-link nav-btn">Logout</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/index.php?page=login" class="nav-link">Login</a>
        <a href="<?= BASE_URL ?>/index.php?page=register" class="nav-link nav-btn">Register</a>
      <?php endif; ?>
    </nav>
  </div>
</nav>

<main style="flex:1;">
