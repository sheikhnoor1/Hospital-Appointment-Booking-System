<div class="doctor-card">
  <div class="doctor-card-photo">
    <?php if (!empty($d['photo_path'])): ?>
      <img src="<?= UPLOAD_URL . sanitize($d['photo_path']) ?>" alt="<?= sanitize($d['name']) ?>">
    <?php else: ?>
      <div class="initials"><?= sanitize(mb_substr($d['name'], 0, 1)) ?></div>
    <?php endif; ?>
  </div>
  <div class="doctor-card-body">
    <div class="doctor-card-name"><?= sanitize($d['name']) ?></div>
    <div class="doctor-card-spec"><?= sanitize($d['specialization_name']) ?></div>
    <div class="doctor-card-fee">💰 Consultation Fee: <strong>$<?= number_format($d['consultation_fee'], 2) ?></strong></div>
    <div class="doctor-card-days">📅 <?= sanitize($d['available_days'] ?: 'Schedule TBD') ?></div>
    <a href="<?= BASE_URL ?>/index.php?page=patient/doctor-profile&id=<?= $d['id'] ?>" class="btn btn-outline w-full">View Profile & Book</a>
  </div>
</div>
