<?php
$pageTitle = 'Revenue Report — MediBook';
require BASE_PATH . '/views/layouts/header.php';
$statusColors = ['Pending'=>'pending','Confirmed'=>'confirmed','Completed'=>'completed','Cancelled'=>'cancelled','No-Show'=>'no-show'];
?>

<div class="page-header no-print">
  <div class="container">
    <div>
      <h1>Revenue Report</h1>
      <p>Income analytics and doctor-wise revenue breakdown</p>
    </div>
    <div class="page-header-actions">
      <button type="button" class="btn btn-secondary" onclick="window.print()">Download PDF</button>
      <a href="<?= BASE_URL ?>/index.php?page=admin/dashboard" class="btn btn-ghost">Back</a>
    </div>
  </div>
</div>

<div class="container" style="padding-bottom:48px;">
  <?php if (!empty($errors)): ?>
    <div class="alert alert-error no-print"><span class="alert-icon">⚠️</span><?= sanitize(implode(' ', $errors)) ?></div>
  <?php endif; ?>

  <form method="GET" action="<?= BASE_URL ?>/index.php" class="filter-bar no-print">
    <input type="hidden" name="page" value="admin/revenue-report">
    <div class="form-group">
      <label class="form-label">From Date</label>
      <input type="date" name="from_date" class="form-control" value="<?= sanitize($fromDate) ?>">
    </div>
    <div class="form-group">
      <label class="form-label">To Date</label>
      <input type="date" name="to_date" class="form-control" value="<?= sanitize($toDate) ?>">
    </div>
    <div style="display:flex;gap:8px;align-items:flex-end;">
      <button type="submit" class="btn btn-primary">Apply</button>
      <a href="<?= BASE_URL ?>/index.php?page=admin/revenue-report" class="btn btn-ghost">Reset</a>
    </div>
  </form>

  <div class="card report-print-header">
    <div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;">
      <div>
        <h2 class="card-title">Revenue Report</h2>
        <p class="text-muted" style="font-size:.88rem;">
          Period:
          <?php if (!empty($fromDate) || !empty($toDate)): ?>
            <?= sanitize($fromDate ?: 'Beginning') ?> to <?= sanitize($toDate ?: 'Today') ?>
          <?php else: ?>
            All dates
          <?php endif; ?>
        </p>
      </div>
      <div class="text-right" style="font-size:.85rem;color:var(--gray-400);">
        Generated on <?= date('Y-m-d H:i') ?>
      </div>
    </div>
  </div>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon stat-icon-info">💵</div>
      <div>
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value">$<?= number_format($summary['total_revenue'], 2) ?></div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon stat-icon-navy">🗓️</div>
      <div>
        <div class="stat-label">Today's Revenue</div>
        <div class="stat-value">$<?= number_format($summary['today_revenue'], 2) ?></div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon stat-icon-warn">📋</div>
      <div>
        <div class="stat-label">Appointments</div>
        <div class="stat-value"><?= (int)$summary['total_appointments'] ?></div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Doctor-wise Revenue</h2>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Doctor</th><th>Appointments</th><th>Total Revenue</th><th>Today's Revenue</th></tr>
        </thead>
        <tbody>
          <?php if (empty($doctorStats)): ?>
            <tr><td colspan="4" class="text-center text-muted">No records found.</td></tr>
          <?php else: ?>
            <?php foreach ($doctorStats as $row): ?>
              <tr>
                <td><?= sanitize($row['name']) ?></td>
                <td><?= (int)$row['total_appointments'] ?></td>
                <td><strong>$<?= number_format($row['total_revenue'], 2) ?></strong></td>
                <td>$<?= number_format($row['today_revenue'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Appointment Revenue Rows</h2>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>ID</th><th>Date</th><th>Time</th><th>Doctor</th><th>Patient</th><th>Status</th><th>Fee</th></tr>
        </thead>
        <tbody>
          <?php if (empty($appointments)): ?>
            <tr><td colspan="7" class="text-center text-muted">No appointments found.</td></tr>
          <?php else: ?>
            <?php foreach ($appointments as $a): ?>
              <tr>
                <td>#<?= (int)$a['id'] ?></td>
                <td><?= sanitize($a['appointment_date']) ?></td>
                <td><?= sanitize(substr($a['appointment_time'], 0, 5)) ?></td>
                <td><?= sanitize($a['doctor_name']) ?></td>
                <td><?= sanitize($a['patient_name']) ?></td>
                <td><span class="badge badge-<?= $statusColors[$a['status']] ?? 'pending' ?>"><?= sanitize($a['status']) ?></span></td>
                <td>$<?= number_format((float)$a['fee_at_booking'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
