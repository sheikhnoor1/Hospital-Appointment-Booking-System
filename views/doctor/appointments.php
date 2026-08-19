<?php
$pageTitle = 'All Appointments — MediBook';
require BASE_PATH . '/views/layouts/header.php';
$statusColors = ['Pending'=>'pending','Confirmed'=>'confirmed','Completed'=>'completed','Cancelled'=>'cancelled','No-Show'=>'no-show'];
?>
<div class="page-header">
  <div class="container">
    <div>
      <h1>Patient Appointments</h1>
      <p>All appointments for your patients. Use actions to update status or reschedule.</p>
    </div>
  </div>
</div>

<div class="container" style="padding-bottom:60px;">
  <?php if (!empty($success)): ?><div class="alert alert-success"><span class="alert-icon">✓</span><?= sanitize($success) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="alert alert-error"><span class="alert-icon">⚠️</span><?= sanitize($error) ?></div><?php endif; ?>

  <div class="card" style="margin-bottom:18px;">
    <form method="GET" action="<?= BASE_URL ?>/index.php" style="display:flex;gap:8px;align-items:center;padding:12px;">
      <input type="hidden" name="page" value="doctor/appointments">
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <div>
          <label class="form-label">Date</label>
          <input type="date" name="date" class="form-control" value="<?= sanitize($_GET['date'] ?? '') ?>">
        </div>
        <div>
          <label class="form-label">Status</label>
          <select name="status" class="form-control">
            <option value="">Any</option>
            <?php foreach (array_keys($statusColors) as $s): ?>
              <option value="<?= $s ?>" <?= (($_GET['status'] ?? '') === $s) ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="display:flex;align-items:flex-end;">
          <button class="btn btn-primary">Filter</button>
        </div>
      </div>
    </form>
  </div>

  <div class="card">
    <div class="card-header"><h2 class="card-title">Appointments</h2></div>
    <?php if (empty($appointments)): ?>
      <p class="text-muted text-center" style="padding:36px 0;">No appointments found for the selected filter.</p>
    <?php else: ?>
      <?php
        // Group appointments by date
        $byDate = [];
        foreach ($appointments as $a) {
          $byDate[$a['appointment_date']][] = $a;
        }
        ksort($byDate);
      ?>

      <?php foreach ($byDate as $date => $list): ?>
        <div style="margin-bottom:18px;">
          <h3 style="margin:8px 0;"><?= date('l, F j, Y', strtotime($date)) ?> <small style="color:var(--gray-500);font-weight:600;margin-left:8px;">(<?= count($list) ?>)</small></h3>
          <div style="display:flex;flex-direction:column;gap:10px;">
            <?php foreach ($list as $a): ?>
              <div class="card" style="padding:12px;display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;">
                <div style="min-width:160px;">
                  <div style="font-weight:700;"><?= substr($a['appointment_time'], 0, 5) ?> — <?= sanitize($a['patient_name']) ?></div>
                  <div class="text-muted" style="font-size:.9rem;max-width:560px;"><?= sanitize($a['reason']) ?></div>
                </div>
                <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                  <div style="text-align:right;min-width:92px;">
                    <div style="font-weight:700;color:var(--teal);">$<?= number_format($a['fee_at_booking'], 2) ?></div>
                    <div style="font-size:.85rem;color:var(--gray-600);margin-top:4px;"><span class="badge badge-<?= $statusColors[$a['status']] ?> badge-compact"><?= $a['status'] ?></span></div>
                  </div>
                  <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <?php if (!in_array($a['status'], ['Completed','Cancelled','No-Show'])): ?>
                      <form method="POST" action="<?= BASE_URL ?>/index.php?page=doctor/appointments" onsubmit="return confirm('Mark this appointment as Completed?')">
                        <input type="hidden" name="appointment_id" value="<?= (int)$a['id'] ?>">
                        <input type="hidden" name="status" value="Completed">
                        <button type="submit" class="btn btn-success btn-sm">Complete</button>
                      </form>
                      <form method="POST" action="<?= BASE_URL ?>/index.php?page=doctor/appointments" onsubmit="return confirm('Cancel this appointment for the patient?')">
                        <input type="hidden" name="appointment_id" value="<?= (int)$a['id'] ?>">
                        <input type="hidden" name="status" value="Cancelled">
                        <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                      </form>

                      <details>
                        <summary class="btn btn-ghost btn-sm">Reschedule</summary>
                        <form method="POST" action="<?= BASE_URL ?>/index.php?page=doctor/reschedule" style="margin-top:8px;display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                          <input type="hidden" name="appointment_id" value="<?= (int)$a['id'] ?>">
                          <input type="date" name="new_date" value="<?= sanitize($a['appointment_date']) ?>" min="<?= date('Y-m-d') ?>" class="form-control" style="width:140px;">
                          <select name="new_time" class="form-control" style="width:120px;">
                            <?php
                              $dm = new DoctorModel();
                              $booked = $dm->getBookedSlots($doctor['id'], $a['appointment_date']);
                              $normalized = array_map(fn($t) => substr($t,0,5), $booked);
                              $isToday = $a['appointment_date'] === date('Y-m-d');
                              $minTs = time() + 15 * 60;
                            ?>
                            <?php foreach (timeSlots() as $ts): ?>
                              <?php
                                $disabled = false;
                                if (in_array($ts, $normalized, true) && $ts !== substr($a['appointment_time'], 0, 5)) $disabled = true;
                                if ($isToday && strtotime($a['appointment_date'] . ' ' . $ts) < $minTs) $disabled = true;
                              ?>
                              <option value="<?= $ts ?>" <?= $ts === substr($a['appointment_time'], 0, 5) ? 'selected' : '' ?> <?= $disabled ? 'disabled' : '' ?>><?= $ts ?><?= $disabled ? ' (unavailable)' : '' ?></option>
                            <?php endforeach; ?>
                          </select>
                          <button type="submit" class="btn btn-primary btn-sm">Save</button>
                        </form>
                      </details>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
