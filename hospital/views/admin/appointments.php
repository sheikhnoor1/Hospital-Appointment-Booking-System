<?php
$pageTitle = 'Appointments — MediBook';
require BASE_PATH . '/views/layouts/header.php';
$statusColors = ['Pending'=>'pending','Confirmed'=>'confirmed','Completed'=>'completed','Cancelled'=>'cancelled','No-Show'=>'no-show'];
?>
<div class="page-header">
  <div class="container"><div><h1>Appointment Management</h1><p>View, filter, and manage all appointments</p></div></div>
</div>

<div class="container" style="padding-bottom:48px;">
  <?php if ($success): ?><div class="alert alert-success"><span class="alert-icon">✓</span><?= sanitize($success) ?></div><?php endif; ?>
  <?php if ($error):   ?><div class="alert alert-error"><span class="alert-icon">⚠️</span><?= sanitize($error) ?></div><?php endif; ?>

  <!-- Filters -->
  <form method="GET" action="<?= BASE_URL ?>/index.php" class="filter-bar">
    <input type="hidden" name="page" value="admin/appointments">
    <div class="form-group">
      <label class="form-label">Doctor</label>
      <select name="doctor_id" class="form-control" style="min-width:180px;">
        <option value="">All Doctors</option>
        <?php foreach ($doctors as $d): ?>
          <option value="<?= $d['id'] ?>" <?= ($_GET['doctor_id'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= sanitize($d['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">Date</label>
      <input type="date" name="date" class="form-control" value="<?= sanitize($_GET['date'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label class="form-label">Status</label>
      <select name="status" class="form-control">
        <option value="">All Statuses</option>
        <?php foreach (['Pending','Confirmed','Completed','Cancelled','No-Show'] as $st): ?>
          <option value="<?= $st ?>" <?= ($_GET['status'] ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="display:flex;gap:8px;align-items:flex-end;">
      <button type="submit" class="btn btn-primary">Filter</button>
      <a href="<?= BASE_URL ?>/index.php?page=admin/appointments" class="btn btn-ghost">Reset</a>
    </div>
  </form>

  <div class="card">
    <div class="card-header">
      <h2 class="card-title">📋 Appointments</h2>
      <span class="text-muted" style="font-size:.85rem;"><?= count($appointments) ?> record<?= count($appointments) != 1 ? 's' : '' ?></span>
    </div>
    <?php if (empty($appointments)): ?>
      <p class="text-muted text-center" style="padding:40px 0;">No appointments found for the selected filters.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead><tr><th>ID</th><th>Patient</th><th>Doctor</th><th>Specialization</th><th>Date & Time</th><th>Reason</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
          <?php foreach ($appointments as $a): ?>
            <tr id="appt-row-<?= $a['id'] ?>">
              <td>#<?= $a['id'] ?></td>
              <td><?= sanitize($a['patient_name']) ?></td>
              <td><?= sanitize($a['doctor_name']) ?></td>
              <td><?= sanitize($a['specialization']) ?></td>
              <td><?= date('M j, Y', strtotime($a['appointment_date'])) ?><br><small><?= substr($a['appointment_time'],0,5) ?></small></td>
              <td style="max-width:180px;"><div class="truncate" title="<?= sanitize($a['reason']) ?>"><?= sanitize($a['reason']) ?></div></td>
              <td><span class="badge badge-<?= $statusColors[$a['status']] ?? 'pending' ?>" id="appt-badge-<?= $a['id'] ?>"><?= $a['status'] ?></span></td>
              <td>
                <div style="display:flex;gap:4px;flex-wrap:wrap;">
                  <?php if ($a['status'] === 'Pending'): ?>
                    <form method="POST" action="<?= BASE_URL ?>/index.php?page=admin/appointments" style="display:inline;" onsubmit="return confirm('Confirm this appointment?')">
                      <input type="hidden" name="appointment_id" value="<?= (int)$a['id'] ?>">
                      <input type="hidden" name="status" value="Confirmed">
                      <button type="submit" class="btn btn-success btn-sm">Confirm</button>
                    </form>
                  <?php endif; ?>
                  <?php if (!in_array($a['status'], ['Cancelled','Completed'])): ?>
                    <button class="btn btn-danger btn-sm" onclick="openCancelModal(<?= $a['id'] ?>)">Cancel</button>
                  <?php endif; ?>
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

<!-- Cancel Reason Modal -->
<div class="modal-backdrop" id="cancel-modal">
  <div class="modal">
    <button class="modal-close" onclick="closeCancelModal()">✕</button>
    <h2 class="modal-title">❌ Cancel Appointment</h2>
    <p class="text-muted mb-3">Please provide a reason for cancellation.</p>
    <form method="POST" action="<?= BASE_URL ?>/index.php?page=admin/appointments">
      <input type="hidden" id="cancel-appt-id" name="appointment_id">
      <input type="hidden" name="status" value="Cancelled">
      <div class="form-group">
        <label class="form-label">Cancellation Reason</label>
        <textarea name="reason" id="cancel-reason" class="form-control" rows="3" placeholder="Reason for cancellation..." required></textarea>
      </div>
      <div style="display:flex;gap:10px;">
        <button type="submit" class="btn btn-danger">Cancel Appointment</button>
        <button type="button" class="btn btn-ghost" onclick="closeCancelModal()">Back</button>
      </div>
    </form>
  </div>
</div>

<script>
const BASE_URL = '<?= BASE_URL ?>';

function openCancelModal(id) {
  document.getElementById('cancel-appt-id').value = id;
  document.getElementById('cancel-reason').value = '';
  document.getElementById('cancel-modal').classList.add('open');
}
function closeCancelModal() { document.getElementById('cancel-modal').classList.remove('open'); }
</script>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
