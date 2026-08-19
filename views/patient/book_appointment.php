<?php
$pageTitle = 'Book Appointment — MediBook';
require BASE_PATH . '/views/layouts/header.php';
?>
<div class="page-header">
  <div class="container">
    <div>
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/index.php?page=patient/home">Doctors</a> ›
        <a href="<?= BASE_URL ?>/index.php?page=patient/doctor-profile&id=<?= $doctor['id'] ?>"><?= sanitize($doctor['name']) ?></a> ›
        <span>Book Appointment</span>
      </div>
      <h1>Confirm Appointment</h1>
      <p>Review the time, fee, and visit reason before submitting</p>
    </div>
  </div>
</div>

<div class="container" style="padding-bottom:60px;">
  <div style="display:grid;grid-template-columns:minmax(0,1.6fr) minmax(320px,1fr);gap:28px;align-items:start;max-width:1100px;">
    <div>
      <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
          <h2 class="card-title">Step 1: Review Appointment</h2>
          <span class="badge badge-confirmed">Ready to book</span>
        </div>

        <?php if (!empty($errors['general'])): ?>
          <div class="alert alert-error"><span class="alert-icon">⚠️</span><?= sanitize($errors['general']) ?></div>
        <?php endif; ?>
        <?php if (!empty($errors['appointment_time'])): ?>
          <div class="alert alert-error"><span class="alert-icon">⚠️</span><?= sanitize($errors['appointment_time']) ?></div>
        <?php endif; ?>

        <div class="card" style="background:linear-gradient(135deg, rgba(10,138,122,.06), rgba(11,29,58,.04));border:1px solid var(--gray-100);box-shadow:none;">
          <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap;">
            <?php if ($doctor['photo_path']): ?>
              <img src="<?= UPLOAD_URL . sanitize($doctor['photo_path']) ?>" style="width:92px;height:92px;border-radius:18px;object-fit:cover;box-shadow:var(--shadow-sm);">
            <?php else: ?>
              <div style="width:92px;height:92px;border-radius:18px;background:var(--gray-100);display:flex;align-items:center;justify-content:center;font-size:2rem;">👤</div>
            <?php endif; ?>
            <div style="flex:1;min-width:220px;">
              <div style="font-family:'DM Serif Display',serif;font-size:1.6rem;color:var(--navy);line-height:1.1;"><?= sanitize($doctor['name']) ?></div>
              <div style="margin-top:6px;color:var(--teal);font-weight:600;"><?= sanitize($doctor['specialization_name']) ?></div>
              <?php if (!empty($doctor['available_days'])): ?>
                <div class="text-muted" style="font-size:.9rem;margin-top:8px;">Available: <?= sanitize($doctor['available_days']) ?></div>
              <?php endif; ?>
            </div>
            <div style="text-align:right;min-width:140px;">
              <div class="text-muted" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.06em;">Consultation Fee</div>
              <div style="font-size:2rem;font-family:'DM Serif Display',serif;color:var(--navy);">$<?= number_format($doctor['consultation_fee'], 2) ?></div>
            </div>
          </div>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/index.php?page=patient/book" novalidate>
          <input type="hidden" name="doctor_id" value="<?= (int)$doctor['id'] ?>">

          <div class="form-row form-row-2">
            <div class="form-group">
              <label class="form-label">Appointment Date</label>
              <input type="date" name="appointment_date" class="form-control"
                     value="<?= sanitize($old['date'] ?? $date) ?>" readonly style="background:var(--gray-50);">
            </div>
            <div class="form-group">
              <label class="form-label">Time Slot</label>
              <input type="text" name="appointment_time" class="form-control"
                     value="<?= sanitize($old['time'] ?? $time) ?>" readonly style="background:var(--gray-50);">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Reason for Visit</label>
            <textarea name="reason" class="form-control <?= isset($errors['reason']) ? 'error' : '' ?>"
                      rows="5" placeholder="Tell the doctor what you are experiencing..."><?= sanitize($old['reason'] ?? '') ?></textarea>
            <?php if (!empty($errors['reason'])): ?><div class="form-error">⚠ <?= sanitize($errors['reason']) ?></div><?php endif; ?>
          </div>

          <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            <button type="submit" class="btn btn-primary btn-lg">Confirm Booking</button>
            <a href="<?= BASE_URL ?>/index.php?page=patient/doctor-profile&id=<?= $doctor['id'] ?>" class="btn btn-ghost btn-lg">Back to Doctor</a>
          </div>
        </form>
      </div>

      <div class="card">
        <div class="card-header"><h2 class="card-title">What happens next</h2></div>
        <div style="display:grid;gap:12px;">
          <div style="display:flex;gap:12px;align-items:flex-start;">
            <div class="stat-icon stat-icon-teal" style="width:36px;height:36px;font-size:1rem;">1</div>
            <div>
              <div style="font-weight:600;">Submit the booking</div>
              <div class="text-muted" style="font-size:.9rem;">Your request is recorded immediately after confirm.</div>
            </div>
          </div>
          <div style="display:flex;gap:12px;align-items:flex-start;">
            <div class="stat-icon stat-icon-navy" style="width:36px;height:36px;font-size:1rem;">2</div>
            <div>
              <div style="font-weight:600;">Track status</div>
              <div class="text-muted" style="font-size:.9rem;">Use My Appointments to see pending, confirmed, or completed visits.</div>
            </div>
          </div>
          <div style="display:flex;gap:12px;align-items:flex-start;">
            <div class="stat-icon stat-icon-warn" style="width:36px;height:36px;font-size:1rem;">3</div>
            <div>
              <div style="font-weight:600;">Keep the appointment ID</div>
              <div class="text-muted" style="font-size:.9rem;">Your confirmation page includes the booking reference.</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div>
      <div class="card" style="position:sticky;top:84px;">
        <div class="card-header">
          <h2 class="card-title">Booking Summary</h2>
          <span class="badge badge-pending">Pending</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid var(--gray-200);">
          <?php if ($doctor['photo_path']): ?>
            <img src="<?= UPLOAD_URL . sanitize($doctor['photo_path']) ?>" style="width:56px;height:56px;border-radius:14px;object-fit:cover;">
          <?php else: ?>
            <div style="width:56px;height:56px;border-radius:14px;background:var(--gray-100);display:flex;align-items:center;justify-content:center;font-size:1.4rem;">👤</div>
          <?php endif; ?>
          <div>
            <div style="font-weight:700;"><?= sanitize($doctor['name']) ?></div>
            <div style="font-size:.85rem;color:var(--teal);font-weight:600;"><?= sanitize($doctor['specialization_name']) ?></div>
          </div>
        </div>
        <div style="display:grid;gap:10px;font-size:.95rem;">
          <div style="display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid var(--gray-100);">
            <span class="text-muted">Date</span>
            <strong><?= date('l, M j, Y', strtotime($date)) ?></strong>
          </div>
          <div style="display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid var(--gray-100);">
            <span class="text-muted">Time</span>
            <strong><?= sanitize($time) ?></strong>
          </div>
          <div style="display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid var(--gray-100);">
            <span class="text-muted">Consultation Fee</span>
            <strong>$<?= number_format($consultationFee, 2) ?></strong>
          </div>
          <div style="display:flex;justify-content:space-between;gap:12px;padding:10px 0;">
            <span class="text-muted">Total Amount</span>
            <strong style="font-size:1.15rem;color:var(--navy);">$<?= number_format($totalFee, 2) ?></strong>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
