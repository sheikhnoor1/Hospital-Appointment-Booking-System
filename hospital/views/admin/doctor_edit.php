<?php
$pageTitle = 'Edit Doctor — MediBook';
require BASE_PATH . '/views/layouts/header.php';
$weekdays = ['Monday','Tuesday','Wednesday','Thursday','Friday'];
$doctorDays = array_map('trim', explode(',', $doctor['available_days'] ?? ''));
?>
<div class="page-header">
  <div class="container">
    <div>
      <div class="breadcrumb"><a href="<?= BASE_URL ?>/index.php?page=admin/doctors">Doctors</a> › <span>Edit Doctor</span></div>
      <h1>Edit Doctor</h1>
      <p>Update <?= sanitize($doctor['name']) ?>'s profile</p>
    </div>
  </div>
</div>

<div class="container" style="padding-bottom:48px;">
  <?php if ($success): ?><div class="alert alert-success"><span class="alert-icon">✓</span><?= sanitize($success) ?></div><?php endif; ?>
  <?php if (!empty($errors)): ?><div class="alert alert-error"><span class="alert-icon">⚠️</span>Please fix the errors below.</div><?php endif; ?>

  <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;">
    <div class="card">
      <div class="card-header"><h2 class="card-title">Doctor Information</h2></div>
      <form method="POST" action="<?= BASE_URL ?>/index.php?page=admin/doctor-edit" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="id" value="<?= (int)$doctor['id'] ?>">
        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'error' : '' ?>"
                   value="<?= sanitize($old['name'] ?? $doctor['name']) ?>">
            <?php if (!empty($errors['name'])): ?><div class="form-error">⚠ <?= sanitize($errors['name']) ?></div><?php endif; ?>
          </div>
          <div class="form-group">
            <label class="form-label">Email (read-only)</label>
            <input type="text" class="form-control" value="<?= sanitize($doctor['email']) ?>" disabled>
          </div>
        </div>
        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label">Specialization</label>
            <select name="specialization_id" class="form-control <?= isset($errors['specialization_id']) ? 'error' : '' ?>">
              <?php foreach ($specs as $s): ?>
                <option value="<?= $s['id'] ?>" <?= $s['id'] == $doctor['specialization_id'] ? 'selected' : '' ?>><?= sanitize($s['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['specialization_id'])): ?><div class="form-error">⚠ <?= sanitize($errors['specialization_id']) ?></div><?php endif; ?>
          </div>
          <div class="form-group">
            <label class="form-label">Consultation Fee ($)</label>
            <input type="text" name="consultation_fee" class="form-control <?= isset($errors['consultation_fee']) ? 'error' : '' ?>"
                   value="<?= sanitize($old['fee'] ?? $doctor['consultation_fee']) ?>">
            <?php if (!empty($errors['consultation_fee'])): ?><div class="form-error">⚠ <?= sanitize($errors['consultation_fee']) ?></div><?php endif; ?>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Bio</label>
          <textarea name="bio" class="form-control" rows="4"><?= sanitize($old['bio'] ?? $doctor['bio']) ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Available Days</label>
          <div class="checkbox-group">
            <?php foreach ($weekdays as $day): ?>
              <label class="checkbox-item">
                <input type="checkbox" name="available_days[]" value="<?= $day ?>"
                       <?= in_array($day, $doctorDays) ? 'checked' : '' ?>>
                <?= $day ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Replace Photo (JPG/JPEG/PNG, max 5MB)</label>
          <input type="file" name="photo" class="form-control <?= isset($errors['photo']) ? 'error' : '' ?>" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
          <?php if (!empty($errors['photo'])): ?><div class="form-error">⚠ <?= sanitize($errors['photo']) ?></div><?php endif; ?>
        </div>
        <div style="display:flex;gap:10px;">
          <button type="submit" class="btn btn-primary">Save Changes</button>
          <a href="<?= BASE_URL ?>/index.php?page=admin/doctors" class="btn btn-ghost">Back to Doctors</a>
        </div>
      </form>
    </div>

    <div>
      <div class="card">
        <div class="card-header"><h2 class="card-title">Current Photo</h2></div>
        <?php if ($doctor['photo_path']): ?>
          <img src="<?= UPLOAD_URL . sanitize($doctor['photo_path']) ?>" style="width:100%;border-radius:8px;object-fit:cover;max-height:220px;">
        <?php else: ?>
          <div style="text-align:center;padding:40px;color:var(--gray-400);">
            <div style="font-size:4rem;">👤</div>
            <p style="font-size:.85rem;">No photo uploaded</p>
          </div>
        <?php endif; ?>
      </div>
      <div class="card mt-2">
        <div class="card-header"><h2 class="card-title">Danger Zone</h2></div>
        <form method="POST" action="<?= BASE_URL ?>/index.php?page=admin/doctor-deactivate" onsubmit="return confirm('Toggle this doctor\'s active status?')">
          <input type="hidden" name="id" value="<?= (int)$doctor['id'] ?>">
          <p class="text-muted mb-2" style="font-size:.85rem;">Currently: <strong><?= $doctor['is_active'] ? 'Active' : 'Inactive' ?></strong></p>
          <button type="submit" class="btn btn-danger btn-sm"><?= $doctor['is_active'] ? 'Deactivate Doctor' : 'Activate Doctor' ?></button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
