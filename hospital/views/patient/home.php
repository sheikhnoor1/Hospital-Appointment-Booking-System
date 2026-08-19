<?php
$pageTitle = 'Browse Doctors — MediBook';
require BASE_PATH . '/views/layouts/header.php';
?>
<div class="page-header">
  <div class="container">
    <div>
      <h1>Find Your Doctor</h1>
      <p>Browse specialists and book your appointment online</p>
    </div>
  </div>
</div>

<div class="container" style="padding-bottom:60px;">
  <form method="GET" action="<?= BASE_URL ?>/index.php" class="filter-bar" style="margin-bottom:28px;align-items:flex-end;">
    <input type="hidden" name="page" value="patient/home">
    <div class="form-group" style="flex:1;">
      <label class="form-label">Filter by Specialization</label>
      <select name="specialization_id" class="form-control" style="max-width:300px;" onchange="this.form.submit()">
        <option value="">All Specializations</option>
        <?php foreach ($specs as $s): ?>
          <option value="<?= $s['id'] ?>" <?= (isset($_GET['specialization_id']) && (int)$_GET['specialization_id'] === (int)$s['id']) ? 'selected' : '' ?>><?= sanitize($s['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <noscript>
      <button type="submit" class="btn btn-primary">Apply Filter</button>
    </noscript>
  </form>

  <!-- Doctor Grid -->
  <div id="doctor-grid" class="doctor-grid">
    <?php if (empty($doctors)): ?>
      <p class="text-muted">No doctors available at this time.</p>
    <?php else: ?>
      <?php foreach ($doctors as $d): ?>
        <?php include BASE_PATH . '/views/patient/_doctor_card.php'; ?>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
