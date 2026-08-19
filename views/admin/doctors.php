<?php
$pageTitle = 'Manage Doctors — MediBook';
require BASE_PATH . '/views/layouts/header.php';
$weekdays = ['Monday','Tuesday','Wednesday','Thursday','Friday'];
?>
<div class="page-header">
  <div class="container">
    <div><h1>Doctors</h1><p>Manage doctor profiles and schedules</p></div>
    <div class="page-header-actions">
      <button class="btn btn-primary" onclick="document.getElementById('add-doctor-modal').classList.add('open')">+ Add Doctor</button>
    </div>
  </div>
</div>

<div class="container" style="padding-bottom:48px;">
  <?php if ($success): ?><div class="alert alert-success"><span class="alert-icon">✓</span><?= sanitize($success) ?></div><?php endif; ?>
  <?php if ($error):   ?><div class="alert alert-error"><span class="alert-icon">⚠️</span><?= sanitize($error) ?></div><?php endif; ?>

  <div class="card">
    <div class="card-header">
      <h2 class="card-title">👨‍⚕️ All Doctors</h2>
      <span class="text-muted" style="font-size:.85rem;"><?= count($doctors) ?> total</span>
    </div>
    <?php if (empty($doctors)): ?>
      <p class="text-muted text-center" style="padding:40px 0;">No doctors registered yet. Add one to get started.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Photo</th><th>Name</th><th>Specialization</th><th>Fee</th><th>Available Days</th><th>Status</th><th>Appointments</th><th>Actions</th></tr></thead>
          <tbody>
          <?php foreach ($doctors as $d): ?>
            <tr>
              <td>
                <?php if ($d['photo_path']): ?>
                  <img src="<?= UPLOAD_URL . sanitize($d['photo_path']) ?>" class="photo-preview">
                <?php else: ?>
                  <div class="photo-placeholder">👤</div>
                <?php endif; ?>
              </td>
              <td><strong><?= sanitize($d['name']) ?></strong><br><small class="text-muted"><?= sanitize($d['email']) ?></small></td>
              <td><?= sanitize($d['specialization_name']) ?></td>
              <td>$<?= number_format($d['consultation_fee'], 2) ?></td>
              <td><small><?= sanitize($d['available_days'] ?? 'Not set') ?></small></td>
              <td>
                <span class="badge badge-<?= $d['is_active'] ? 'active' : 'inactive' ?>">
                  <?= $d['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
              </td>
              <td><strong><?= (int)$d['total_appointments'] ?></strong></td>
              <td>
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                  <a href="<?= BASE_URL ?>/index.php?page=admin/doctor-edit&id=<?= $d['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
                  <form method="POST" action="<?= BASE_URL ?>/index.php?page=admin/doctor-deactivate" style="display:inline;" onsubmit="return confirm('Toggle this doctor\'s active status?')">
                    <input type="hidden" name="id" value="<?= $d['id'] ?>">
                    <button type="submit" class="btn btn-sm <?= $d['is_active'] ? 'btn-warning' : 'btn-success' ?>">
                      <?= $d['is_active'] ? 'Deactivate' : 'Activate' ?>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Add Doctor Modal -->
<div class="modal-backdrop" id="add-doctor-modal">
  <div class="modal" style="max-width:620px;">
    <button class="modal-close" onclick="this.closest('.modal-backdrop').classList.remove('open')">✕</button>
    <h2 class="modal-title">➕ Add New Doctor</h2>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error"><span class="alert-icon">⚠️</span>Please fix the errors below.</div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/index.php?page=admin/doctors" enctype="multipart/form-data" novalidate>
      <div class="form-row form-row-2">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'error' : '' ?>"
                 value="<?= sanitize($old['name'] ?? '') ?>" placeholder="Dr. Jane Smith">
          <?php if (!empty($errors['name'])): ?><div class="form-error">⚠ <?= sanitize($errors['name']) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="text" name="email" class="form-control <?= isset($errors['email']) ? 'error' : '' ?>"
                 value="<?= sanitize($old['email'] ?? '') ?>" placeholder="doctor@hospital.com">
          <?php if (!empty($errors['email'])): ?><div class="form-error">⚠ <?= sanitize($errors['email']) ?></div><?php endif; ?>
        </div>
      </div>
      <div class="form-row form-row-2">
        <div class="form-group">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'error' : '' ?>" placeholder="Min. 6 characters">
          <?php if (!empty($errors['password'])): ?><div class="form-error">⚠ <?= sanitize($errors['password']) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
          <label class="form-label">Specialization</label>
          <select name="specialization_id" class="form-control <?= isset($errors['specialization_id']) ? 'error' : '' ?>">
            <option value="">-- Select --</option>
            <?php foreach ($specs as $s): ?>
              <option value="<?= $s['id'] ?>" <?= ($old['spec_id'] ?? '') == $s['id'] ? 'selected' : '' ?>><?= sanitize($s['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (!empty($errors['specialization_id'])): ?><div class="form-error">⚠ <?= sanitize($errors['specialization_id']) ?></div><?php endif; ?>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Bio</label>
        <textarea name="bio" class="form-control" rows="3" placeholder="Brief professional bio..."><?= sanitize($old['bio'] ?? '') ?></textarea>
      </div>
      <div class="form-row form-row-2">
        <div class="form-group">
          <label class="form-label">Consultation Fee ($)</label>
          <input type="text" name="consultation_fee" class="form-control <?= isset($errors['consultation_fee']) ? 'error' : '' ?>"
                 value="<?= sanitize($old['fee'] ?? '') ?>" placeholder="e.g. 150">
          <?php if (!empty($errors['consultation_fee'])): ?><div class="form-error">⚠ <?= sanitize($errors['consultation_fee']) ?></div><?php endif; ?>
        </div>
        <div class="form-group">
          <label class="form-label">Photo (JPG/JPEG/PNG, max 5MB)</label>
          <input type="file" name="photo" class="form-control <?= isset($errors['photo']) ? 'error' : '' ?>" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
          <?php if (!empty($errors['photo'])): ?><div class="form-error">⚠ <?= sanitize($errors['photo']) ?></div><?php endif; ?>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Available Days</label>
        <div class="checkbox-group">
          <?php foreach ($weekdays as $day): ?>
            <label class="checkbox-item">
              <input type="checkbox" name="available_days[]" value="<?= $day ?>"
                     <?= in_array($day, $old['days'] ?? []) ? 'checked' : '' ?>>
              <?= $day ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
      <div style="display:flex;gap:10px;margin-top:8px;">
        <button type="submit" class="btn btn-primary">Add Doctor</button>
        <button type="button" class="btn btn-ghost" onclick="this.closest('.modal-backdrop').classList.remove('open')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<?php if (!empty($errors)): ?>
<script>document.getElementById('add-doctor-modal').classList.add('open');</script>
<?php endif; ?>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
