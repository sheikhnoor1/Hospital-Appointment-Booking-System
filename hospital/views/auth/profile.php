<?php
$pageTitle = 'My Profile — MediBook';
require BASE_PATH . '/views/layouts/header.php';
?>

<div class="page-header">
  <div class="container">
    <div>
      <h1>My Profile</h1>
      <p>Manage your account information</p>
    </div>
  </div>
</div>

<div class="container" style="padding-bottom:48px;">
  <?php if ($success): ?>
    <div class="alert alert-success"><span class="alert-icon">✓</span><?= sanitize($success) ?></div>
  <?php endif; ?>

  <?php if (!empty($errors['current_password']) || !empty($errors['new_password']) || !empty($errors['confirm_new_password'])): ?>
    <div class="alert alert-error">
      <span class="alert-icon">⚠️</span>
      <?= sanitize(array_values(array_filter([$errors['current_password']??'',$errors['new_password']??'',$errors['confirm_new_password']??'']))[0] ?? '') ?>
    </div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
    <!-- Profile Info -->
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">👤 Personal Information</h2>
        <span class="badge badge-<?= $_SESSION['role'] ?>"><?= ucfirst($_SESSION['role']) ?></span>
      </div>

      <?php if ($_SESSION['role'] === 'patient' && isset($upcomingCount)): ?>
        <div class="alert alert-info mb-3">
          <span class="alert-icon">📅</span>
          You have <strong><?= (int)$upcomingCount ?></strong> upcoming appointment<?= $upcomingCount != 1 ? 's' : '' ?>.
          <a href="<?= BASE_URL ?>/index.php?page=patient/appointments" style="margin-left:8px;">View all →</a>
        </div>
      <?php endif; ?>

      <form method="POST" action="<?= BASE_URL ?>/index.php?page=profile/update" novalidate>
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'error' : '' ?>"
                 value="<?= sanitize($old['name'] ?? $user['name'] ?? '') ?>">
          <?php if (!empty($errors['name'])): ?><div class="form-error">⚠ <?= sanitize($errors['name']) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="text" class="form-control" value="<?= sanitize($user['email']) ?>" disabled>
          <div class="form-hint">Email cannot be changed.</div>
        </div>
        <div class="form-group">
          <label class="form-label">Phone Number</label>
          <input type="text" name="phone" class="form-control <?= isset($errors['phone']) ? 'error' : '' ?>"
                 value="<?= sanitize($old['phone'] ?? $user['phone'] ?? '') ?>" placeholder="+1 234 567 8900">
          <?php if (!empty($errors['phone'])): ?><div class="form-error">⚠ <?= sanitize($errors['phone']) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
          <label class="form-label">Date of Birth</label>
          <input type="date" name="dob" class="form-control <?= isset($errors['dob']) ? 'error' : '' ?>"
                 value="<?= sanitize($old['dob'] ?? $user['dob'] ?? '') ?>">
          <?php if (!empty($errors['dob'])): ?><div class="form-error">⚠ <?= sanitize($errors['dob']) ?></div><?php endif; ?>
        </div>
        <?php if (!empty($user['blood_group'])): ?>
        <div class="form-group">
          <label class="form-label">Blood Group</label>
          <input type="text" class="form-control" value="<?= sanitize($user['blood_group']) ?>" disabled>
        </div>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </form>
    </div>

    <!-- Change Password -->
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">🔒 Change Password</h2>
      </div>
      <form method="POST" action="<?= BASE_URL ?>/index.php?page=profile/password" novalidate>
        <div class="form-group">
          <label class="form-label">Current Password</label>
          <input type="password" name="current_password" class="form-control <?= isset($errors['current_password']) ? 'error' : '' ?>"
                 placeholder="Enter current password">
          <?php if (!empty($errors['current_password'])): ?><div class="form-error">⚠ <?= sanitize($errors['current_password']) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
          <label class="form-label">New Password</label>
          <input type="password" name="new_password" class="form-control <?= isset($errors['new_password']) ? 'error' : '' ?>"
                 placeholder="Min. 6 characters">
          <?php if (!empty($errors['new_password'])): ?><div class="form-error">⚠ <?= sanitize($errors['new_password']) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm New Password</label>
          <input type="password" name="confirm_new_password" class="form-control <?= isset($errors['confirm_new_password']) ? 'error' : '' ?>"
                 placeholder="Repeat new password">
          <?php if (!empty($errors['confirm_new_password'])): ?><div class="form-error">⚠ <?= sanitize($errors['confirm_new_password']) ?></div><?php endif; ?>
        </div>
        <button type="submit" class="btn btn-secondary">Change Password</button>
      </form>
    </div>
  </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
