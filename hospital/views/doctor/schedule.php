<?php
$pageTitle = 'My Schedule — MediBook';
require BASE_PATH . '/views/layouts/header.php';
?>
<div class="page-header">
  <div class="container">
    <div>
      <div class="breadcrumb"><a href="<?= BASE_URL ?>/index.php?page=doctor/dashboard">Dashboard</a> › <span>My Schedule</span></div>
      <h1>My Schedule</h1>
      <p>Change the days you are available for appointments.</p>
    </div>
  </div>
</div>

<div class="container" style="padding-bottom:60px;max-width:920px;">
  <?php if ($success): ?><div class="alert alert-success"><span class="alert-icon">✓</span><?= sanitize($success) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-error"><span class="alert-icon">⚠️</span><?= sanitize($error) ?></div><?php endif; ?>

  <div class="card" style="padding:0;overflow:hidden;">
    <div class="card-header" style="margin-bottom:0;">
      <div>
        <h2 class="card-title">Available Days</h2>
        <p class="text-muted" style="margin-top:4px;">Select one or more days. Patients will only see slots on the days you choose.</p>
      </div>
      <a href="<?= BASE_URL ?>/index.php?page=doctor/dashboard" class="btn btn-ghost btn-sm">Back to Dashboard</a>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/index.php?page=doctor/schedule" style="padding:24px;">
      <div class="checkbox-group" style="margin-bottom:24px;">
        <?php foreach ($weekdays as $day): ?>
          <label class="checkbox-item">
            <input type="checkbox" name="available_days[]" value="<?= $day ?>" <?= in_array($day, $doctorDays) ? 'checked' : '' ?>>
            <?= $day ?>
          </label>
        <?php endforeach; ?>
      </div>

      <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <button type="submit" class="btn btn-primary">Save Schedule</button>
        <span class="text-muted" style="font-size:.9rem;">Current schedule: <?= sanitize($doctor['available_days'] ?: 'Not set') ?></span>
      </div>
    </form>
  </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>