<?php
$pageTitle = 'Booking Confirmed — MediBook';
require BASE_PATH . '/views/layouts/header.php';
?>
<div class="container" style="padding:56px 24px 70px;max-width:1040px;">
  <div class="confirm-card" style="max-width:none;margin:0;">
    <div class="card" style="padding:34px;">
      <div style="display:flex;justify-content:space-between;gap:18px;align-items:flex-start;flex-wrap:wrap;margin-bottom:24px;">
        <div style="display:flex;gap:16px;align-items:center;">
          <div class="confirm-icon" style="margin:0;">✅</div>
          <div>
            <h1 style="margin-bottom:6px;">Appointment Booked</h1>
            <p class="text-muted">Your booking is submitted and waiting for approval.</p>
          </div>
        </div>
        <div style="text-align:right;">
          <div class="text-muted" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.06em;">Reference ID</div>
          <div class="confirm-id" style="margin:2px 0 0;line-height:1;">#<?= (int)$appointment['id'] ?></div>
        </div>
      </div>

      <div class="stats-grid" style="margin-bottom:20px;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));">
        <div class="stat-card">
          <div class="stat-icon stat-icon-teal">👨‍⚕️</div>
          <div>
            <div class="stat-label">Doctor</div>
            <div style="font-weight:700;line-height:1.2;"><?= sanitize($appointment['doctor_name']) ?></div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon stat-icon-navy">📅</div>
          <div>
            <div class="stat-label">Date & Time</div>
            <div style="font-weight:700;line-height:1.2;"><?= date('M j, Y', strtotime($appointment['appointment_date'])) ?> · <?= substr($appointment['appointment_time'], 0, 5) ?></div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon stat-icon-warn">💰</div>
          <div>
            <div class="stat-label">Consultation Fee</div>
            <div class="stat-value" style="font-size:1.5rem;">$<?= number_format($appointment['consultation_fee'] ?? 0, 2) ?></div>
          </div>
        </div>
      </div>

      <div class="confirm-details" style="margin:0 0 24px;">
        <div class="confirm-detail-row">
          <span class="confirm-detail-label">Specialization</span>
          <strong><?= sanitize($appointment['specialization']) ?></strong>
        </div>
        <div class="confirm-detail-row">
          <span class="confirm-detail-label">Status</span>
          <span class="badge badge-pending">Pending</span>
        </div>
        <div class="confirm-detail-row">
          <span class="confirm-detail-label">Reason</span>
          <span style="max-width:520px;text-align:right;"><?= sanitize($appointment['reason']) ?></span>
        </div>
      </div>

      <div style="display:flex;gap:12px;justify-content:flex-end;flex-wrap:wrap;">
        <a href="<?= BASE_URL ?>/index.php?page=patient/appointments" class="btn btn-primary btn-lg">View Appointments</a>
        <a href="<?= BASE_URL ?>/index.php?page=patient/home" class="btn btn-ghost btn-lg">Browse More Doctors</a>
      </div>
    </div>
  </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
