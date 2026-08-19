<?php
$pageTitle = sanitize($doctor['name']) . ' — MediBook';
require BASE_PATH . '/views/layouts/header.php';

// Generate next 7 days
$days = [];
$availDays = array_map('trim', explode(',', $doctor['available_days'] ?? ''));
for ($i = 0; $i < 7; $i++) {
    $ts = strtotime("+$i days");
    $dayName = date('l', $ts);
    $days[] = [
        'ts'        => $ts,
        'date'      => date('Y-m-d', $ts),
        'dayName'   => $dayName,
        'label'     => date('D', $ts),
        'num'       => date('j', $ts),
        'month'     => date('M', $ts),
        'available' => in_array($dayName, $availDays),
    ];
}
?>
<div class="page-header">
  <div class="container">
    <div>
      <div class="breadcrumb"><a href="<?= BASE_URL ?>/index.php?page=patient/home">Doctors</a> › <span><?= sanitize($doctor['name']) ?></span></div>
      <h1><?= sanitize($doctor['name']) ?></h1>
      <p><?= sanitize($doctor['specialization_name']) ?> · Consultation fee $<?= number_format($doctor['consultation_fee'], 2) ?></p>
    </div>
  </div>
</div>

<div class="container" style="padding-bottom:60px;">
  <div style="display:grid;grid-template-columns:minmax(0,1.35fr) minmax(320px,1fr);gap:28px;align-items:start;">
    <!-- Doctor Info -->
    <div>
      <div class="card">
        <div style="display:flex;gap:20px;align-items:flex-start;margin-bottom:20px;flex-wrap:wrap;">
          <div style="flex-shrink:0;">
            <?php if ($doctor['photo_path']): ?>
              <img src="<?= UPLOAD_URL . sanitize($doctor['photo_path']) ?>" style="width:100px;height:100px;border-radius:20px;object-fit:cover;box-shadow:var(--shadow-sm);">
            <?php else: ?>
              <div style="width:100px;height:100px;border-radius:20px;background:var(--gray-100);display:flex;align-items:center;justify-content:center;font-size:2.5rem;">👤</div>
            <?php endif; ?>
          </div>
          <div style="flex:1;min-width:240px;">
            <h2 style="margin-bottom:4px;"><?= sanitize($doctor['name']) ?></h2>
            <div style="color:var(--teal);font-weight:600;margin-bottom:8px;"><?= sanitize($doctor['specialization_name']) ?></div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:10px;">
              <span class="badge badge-pending">Consultation $<?= number_format($doctor['consultation_fee'], 2) ?></span>
              <span class="badge badge-confirmed">Next 7 days</span>
            </div>
            <div style="color:var(--gray-600);font-size:.95rem;">📅 Available: <?= sanitize($doctor['available_days'] ?: 'TBD') ?></div>
          </div>
        </div>
        <?php if ($doctor['bio']): ?>
          <div style="background:var(--gray-50);border-radius:12px;padding:18px;font-size:.95rem;line-height:1.75;color:var(--gray-700);">
            <?= nl2br(sanitize($doctor['bio'])) ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Booking Panel -->
    <div>
      <div class="card" style="position:sticky;top:84px;">
        <div class="card-header"><h2 class="card-title">📅 Book Appointment</h2></div>

        <form method="GET" action="<?= BASE_URL ?>/index.php" class="mb-3">
          <input type="hidden" name="page" value="patient/doctor-profile">
          <input type="hidden" name="id" value="<?= (int)$doctor['id'] ?>">
          <div class="form-group">
            <label class="form-label" for="date-picker">Select a Date</label>
            <input id="date-picker" type="date" name="date" class="form-control" value="<?= sanitize($selectedDate ?? '') ?>" min="<?= date('Y-m-d') ?>">
          </div>
          <button type="submit" class="btn btn-primary w-full">Check Availability</button>
        </form>

        <?php if (!empty($selectedDate)): ?>
          <div class="form-label" style="margin-bottom:10px;">Select a Time Slot</div>
          <?php if (!empty($slotMessage)): ?>
            <div class="text-muted" style="font-size:.9rem;margin-bottom:12px;"><?= sanitize($slotMessage) ?></div>
          <?php endif; ?>

          <?php if (!empty($availableSlots)): ?>
            <div class="time-slots">
              <?php foreach ($availableSlots as $slot): ?>
                <a class="time-btn" href="<?= BASE_URL ?>/index.php?page=patient/book&doctor_id=<?= (int)$doctor['id'] ?>&date=<?= sanitize($selectedDate) ?>&time=<?= urlencode($slot) ?>"><?= sanitize($slot) ?></a>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="card" style="padding:16px;background:var(--gray-50);box-shadow:none;margin-bottom:0;">
              <div class="text-muted" style="font-size:.9rem;">Choose a date to see available slots.</div>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
