<?php
$pageTitle = 'My Appointments — MediBook';
require BASE_PATH . '/views/layouts/header.php';
$groups = ['Pending'=>[],'Confirmed'=>[],'Completed'=>[],'Cancelled'=>[],'No-Show'=>[]];
foreach ($appointments as $a) $groups[$a['status']][] = $a;
$statusColors = ['Pending'=>'pending','Confirmed'=>'confirmed','Completed'=>'completed','Cancelled'=>'cancelled','No-Show'=>'no-show'];
?>
<div class="page-header">
  <div class="container">
    <div><h1>My Appointments</h1><p>Track and manage your bookings</p></div>
    <div class="page-header-actions">
      <a href="<?= BASE_URL ?>/index.php?page=patient/home" class="btn btn-primary">+ New Booking</a>
    </div>
  </div>
</div>

<div class="container" style="padding-bottom:60px;">
  <?php if ($success): ?><div class="alert alert-success"><span class="alert-icon">✓</span><?= sanitize($success) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="alert alert-error"><span class="alert-icon">⚠️</span><?= sanitize($error) ?></div><?php endif; ?>

  <div class="stats-grid" style="margin-bottom:22px;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));">
    <div class="stat-card">
      <div class="stat-icon stat-icon-teal">💳</div>
      <div>
        <div class="stat-label">Total Spent</div>
        <div class="stat-value">$<?= number_format($expenseSummary['total_spent'], 2) ?></div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon stat-icon-info">🗓️</div>
      <div>
        <div class="stat-label">Today's Expense</div>
        <div class="stat-value">$<?= number_format($expenseSummary['today_expense'], 2) ?></div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon stat-icon-warn">⏳</div>
      <div>
        <div class="stat-label">Upcoming Expense</div>
        <div class="stat-value">$<?= number_format($expenseSummary['upcoming_expense'], 2) ?></div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon stat-icon-navy">↩️</div>
      <div>
        <div class="stat-label">Cancelled Value</div>
        <div class="stat-value">$<?= number_format($expenseSummary['cancelled_value'], 2) ?></div>
      </div>
    </div>
  </div>

  <?php if (empty($appointments)): ?>
    <div class="card text-center" style="padding:64px 28px;max-width:720px;margin:0 auto;">
      <div style="font-size:3rem;margin-bottom:16px;">📅</div>
      <h2 style="margin-bottom:6px;">No appointments yet</h2>
      <p class="text-muted mt-1">Browse our doctors and book your first appointment.</p>
      <a href="<?= BASE_URL ?>/index.php?page=patient/home" class="btn btn-primary mt-3">Browse Doctors</a>
    </div>
  <?php else: ?>
    <?php foreach ($groups as $status => $list): ?>
      <?php if (empty($list)) continue; ?>
      <div class="card" style="padding:0;margin-bottom:24px;overflow:hidden;">
        <div class="card-header" style="margin-bottom:0;">
          <h3 style="display:flex;align-items:center;gap:10px;font-size:1.1rem;">
            <span class="badge badge-<?= $statusColors[$status] ?>" style="font-size:.8rem;"><?= $status ?></span>
            <span style="font-size:1rem;color:var(--gray-400);font-weight:500;"><?= count($list) ?> appointment<?= count($list) != 1 ? 's' : '' ?></span>
          </h3>
        </div>
        <div class="table-wrap" style="box-shadow:none;border-radius:0;">
          <table>
            <thead><tr><th>ID</th><th>Doctor</th><th>Specialization</th><th>Date</th><th>Time</th><th>Fee</th><th>Reason</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($list as $a): ?>
              <tr id="appt-row-<?= $a['id'] ?>">
                <td>#<?= $a['id'] ?></td>
                <td>
                  <div style="font-weight:700;"><?= sanitize($a['doctor_name']) ?></div>
                </td>
                <td><?= sanitize($a['specialization']) ?></td>
                <td><?= date('M j, Y', strtotime($a['appointment_date'])) ?></td>
                <td><?= substr($a['appointment_time'], 0, 5) ?></td>
                <td><strong>$<?= number_format($a['fee_at_booking'], 2) ?></strong></td>
                <td style="max-width:180px;"><div class="truncate" title="<?= sanitize($a['reason']) ?>"><?= sanitize($a['reason']) ?></div></td>
                <td><span class="badge badge-<?= $statusColors[$a['status']] ?>" id="badge-<?= $a['id'] ?>"><?= $a['status'] ?></span></td>
                <td>
                  <?php if ($a['status'] === 'Pending'): ?>
                    <form method="POST" action="<?= BASE_URL ?>/index.php?page=patient/appointments" onsubmit="return confirm('Are you sure you want to cancel this appointment?')">
                      <input type="hidden" name="appointment_id" value="<?= (int)$a['id'] ?>">
                      <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                    </form>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
