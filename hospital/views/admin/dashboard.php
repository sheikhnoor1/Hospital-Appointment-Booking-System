<?php
$pageTitle = 'Admin Dashboard — MediBook';
require BASE_PATH . '/views/layouts/header.php';
?>

<div class="page-header">
  <div class="container">
    <div>
      <h1>Admin Dashboard</h1>
      <p>System overview and management</p>
    </div>
    <div class="page-header-actions">
      <a href="<?= BASE_URL ?>/index.php?page=admin/doctors" class="btn btn-primary">+ Add Doctor</a>
    </div>
  </div>
</div>

<div class="container" style="padding-bottom:48px;">
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon stat-icon-teal">👨‍⚕️</div>
      <div>
        <div class="stat-label">Total Doctors</div>
        <div class="stat-value"><?= (int)$totalDoctors ?></div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon stat-icon-navy">🧑‍🤝‍🧑</div>
      <div>
        <div class="stat-label">Patients</div>
        <div class="stat-value"><?= (int)$totalPatients ?></div>
      </div>
    </div>
    <div class="stat-card" id="stat-total-appts">
      <div class="stat-icon stat-icon-warn">📅</div>
      <div>
        <div class="stat-label">Total Appointments</div>
        <div class="stat-value" id="stat-appt-count"><?= (int)$totalAppointments ?></div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon stat-icon-info">💵</div>
      <div>
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value">$<?= number_format($totalRevenue, 2) ?></div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon stat-icon-navy">🗓️</div>
      <div>
        <div class="stat-label">Today's Revenue</div>
        <div class="stat-value">$<?= number_format($todayRevenue, 2) ?></div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h2 class="card-title">📊 Doctor Revenue Stats</h2>
      <a href="<?= BASE_URL ?>/index.php?page=admin/doctors" class="btn btn-ghost btn-sm">Manage Doctors</a>
    </div>
    <div class="table-wrap">
      <table id="stats-table">
        <thead>
          <tr><th>Doctor</th><th>Appointments</th><th>Total Revenue</th><th>Today's Revenue</th></tr>
        </thead>
        <tbody>
          <?php if (empty($doctorStats)): ?>
            <tr><td colspan="4" style="text-align:center;color:var(--gray-400);">No doctors yet.</td></tr>
          <?php else: ?>
            <?php foreach ($doctorStats as $row): ?>
              <tr>
                <td><?= sanitize($row['name']) ?></td>
                <td><strong><?= (int)$row['total_appointments'] ?></strong></td>
                <td><strong>$<?= number_format($row['total_revenue'], 2) ?></strong></td>
                <td>$<?= number_format($row['today_revenue'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
    <a href="<?= BASE_URL ?>/index.php?page=admin/specializations" class="card card-sm" style="text-decoration:none;display:flex;align-items:center;gap:16px;transition:box-shadow .2s;" onmouseover="this.style.boxShadow='var(--shadow)'" onmouseout="this.style.boxShadow='var(--shadow-sm)'">
      <div class="stat-icon stat-icon-info" style="flex-shrink:0;">🏷️</div>
      <div><div style="font-weight:600;color:var(--navy);">Specializations</div><div class="text-muted" style="font-size:.85rem;">Manage categories</div></div>
    </a>
    <a href="<?= BASE_URL ?>/index.php?page=admin/appointments" class="card card-sm" style="text-decoration:none;display:flex;align-items:center;gap:16px;transition:box-shadow .2s;" onmouseover="this.style.boxShadow='var(--shadow)'" onmouseout="this.style.boxShadow='var(--shadow-sm)'">
      <div class="stat-icon stat-icon-warn" style="flex-shrink:0;">📋</div>
      <div><div style="font-weight:600;color:var(--navy);">Appointments</div><div class="text-muted" style="font-size:.85rem;">View & manage all</div></div>
    </a>
    <a href="<?= BASE_URL ?>/index.php?page=admin/users" class="card card-sm" style="text-decoration:none;display:flex;align-items:center;gap:16px;transition:box-shadow .2s;" onmouseover="this.style.boxShadow='var(--shadow)'" onmouseout="this.style.boxShadow='var(--shadow-sm)'">
      <div class="stat-icon stat-icon-navy" style="flex-shrink:0;">👥</div>
      <div><div style="font-weight:600;color:var(--navy);">Users</div><div class="text-muted" style="font-size:.85rem;">Account control</div></div>
    </a>
  </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
