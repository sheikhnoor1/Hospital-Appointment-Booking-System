<?php
$pageTitle = 'Doctor Dashboard — MediBook';
require BASE_PATH . '/views/layouts/header.php';
$statusColors = ['Pending'=>'pending','Confirmed'=>'confirmed','Completed'=>'completed','Cancelled'=>'cancelled'];
$doctorDays = array_filter(array_map('trim', explode(',', $doctor['available_days'] ?? '')));

// Build a date-based agenda for the next 7 days
$scheduleDays = [];
$appointmentsByDate = [];
foreach ($weekAppointments as $appointment) {
  $appointmentsByDate[$appointment['appointment_date']][] = $appointment;
}

for ($offset = 0; $offset < 7; $offset++) {
  $date = date('Y-m-d', strtotime("+$offset day"));
  $dayName = date('l', strtotime($date));
  $scheduleDays[] = [
    'date' => $date,
    'day_name' => $dayName,
    'date_label' => date('M j', strtotime($date)),
    'is_today' => $offset === 0,
    'is_working' => in_array($dayName, $doctorDays, true),
    'appointments' => $appointmentsByDate[$date] ?? [],
  ];
}
?>
<div class="page-header">
  <div class="container">
    <div>
      <h1>My Dashboard</h1>
      <p>Today — <?= date('l, F j, Y') ?></p>
    </div>
  </div>
</div>

<div class="container" style="padding-bottom:60px;">
  <?php $success = getFlash('success'); $error = getFlash('error'); ?>
  <?php if ($success): ?><div class="alert alert-success"><span class="alert-icon">✓</span><?= sanitize($success) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-error"><span class="alert-icon">⚠️</span><?= sanitize($error) ?></div><?php endif; ?>
  <div class="card" style="margin-bottom:22px;">
    <div style="display:flex;justify-content:space-between;gap:16px;align-items:center;flex-wrap:wrap;">
      <div>
        <h2 style="margin-bottom:6px;">Schedule</h2>
        <p class="text-muted" style="margin-bottom:0;">Current availability: <?= sanitize($doctor['available_days'] ?: 'Not set') ?></p>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <a href="<?= BASE_URL ?>/index.php?page=doctor/schedule" class="btn btn-primary">Edit Schedule</a>
        <a href="<?= BASE_URL ?>/index.php?page=doctor/appointments" class="btn btn-ghost">Manage Appointments</a>
      </div>
    </div>
  </div>

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon stat-icon-teal">📅</div>
      <div>
        <div class="stat-label">Today's Appointments</div>
        <div class="stat-value"><?= count($todayAppointments) ?></div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon stat-icon-warn">📆</div>
      <div>
        <div class="stat-label">This Week</div>
        <div class="stat-value"><?= count($weekAppointments) ?></div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon stat-icon-navy">✅</div>
      <div>
        <div class="stat-label">Confirmed Today</div>
        <div class="stat-value"><?= count(array_filter($todayAppointments, fn($a) => $a['status'] === 'Confirmed')) ?></div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon stat-icon-info">💰</div>
      <div>
        <div class="stat-label">Total Income</div>
        <div class="stat-value">$<?= number_format($incomeSummary['total_income'], 2) ?></div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon stat-icon-navy">🗓️</div>
      <div>
        <div class="stat-label">Today's Income</div>
        <div class="stat-value">$<?= number_format($incomeSummary['today_income'], 2) ?></div>
      </div>
    </div>
  </div>

  <!-- Today's Appointments -->
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">🏥 Appointments for <?= sanitize($displayLabel ?? 'Today') ?></h2>
    </div>
    <?php if (empty($displayAppointments)): ?>
      <p class="text-muted text-center" style="padding:32px 0;">No upcoming appointments found.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Time</th><th>Patient</th><th>Reason</th><th>Fee</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
              <?php foreach ($displayAppointments as $a): ?>
            <tr id="today-row-<?= $a['id'] ?>">
              <td><strong><?= substr($a['appointment_time'], 0, 5) ?></strong></td>
              <td><?= sanitize($a['patient_name']) ?></td>
              <td style="max-width:200px;"><div class="truncate" title="<?= sanitize($a['reason']) ?>"><?= sanitize($a['reason']) ?></div></td>
              <td>$<?= number_format($a['fee_at_booking'], 2) ?></td>
              <td><span class="badge badge-<?= $statusColors[$a['status']] ?>" id="doc-badge-<?= $a['id'] ?>"><?= $a['status'] ?></span></td>
              <td>
                <div style="display:flex;gap:6px;align-items:center;">
                  <?php if (!in_array($a['status'], ['Completed','Cancelled'])): ?>
                    <form method="POST" action="<?= BASE_URL ?>/index.php?page=doctor/dashboard" style="display:inline;" onsubmit="return confirm('Mark this appointment as Completed?')">
                      <input type="hidden" name="appointment_id" value="<?= (int)$a['id'] ?>">
                      <input type="hidden" name="status" value="Completed">
                      <button type="submit" class="btn btn-success btn-sm">✓</button>
                    </form>
                    <form method="POST" action="<?= BASE_URL ?>/index.php?page=doctor/dashboard" style="display:inline;" onsubmit="return confirm('Cancel this appointment for the patient?')">
                      <input type="hidden" name="appointment_id" value="<?= (int)$a['id'] ?>">
                      <input type="hidden" name="status" value="Cancelled">
                      <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                    </form>

                    <details style="display:inline;margin-left:6px;">
                      <summary class="btn btn-ghost btn-sm">Reschedule</summary>
                      <form method="POST" action="<?= BASE_URL ?>/index.php?page=doctor/reschedule" style="margin-top:8px;display:flex;gap:8px;align-items:center;">
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
                                  // if slot booked by another appointment
                                  if (in_array($ts, $normalized, true) && $ts !== substr($a['appointment_time'], 0, 5)) $disabled = true;
                                  // if today and slot is within next 15 minutes
                                  if ($isToday && strtotime($a['appointment_date'] . ' ' . $ts) < $minTs) $disabled = true;
                                ?>
                                <option value="<?= $ts ?>" <?= $ts === substr($a['appointment_time'], 0, 5) ? 'selected' : '' ?> <?= $disabled ? 'disabled' : '' ?>><?= $ts ?><?= $disabled ? ' (unavailable)' : '' ?></option>
                              <?php endforeach; ?>
                            </select>
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                      </form>
                    </details>
                  <?php else: ?>
                    <span class="text-muted" style="font-size:.85rem;">Done</span>
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

  <!-- Weekly Grid -->
  <div class="card">
    <div class="card-header">
      <div>
        <h2 class="card-title">📆 Weekly Schedule (Next 7 Days)</h2>
        <p class="text-muted" style="margin-top:4px;margin-bottom:0;">A day-by-day agenda with your availability and booked visits.</p>
      </div>
    </div>
    <div class="schedule-board">
      <?php foreach ($scheduleDays as $day): ?>
        <div class="schedule-day <?= $day['is_today'] ? 'is-today' : '' ?>">
          <div class="schedule-day-header">
            <div>
              <div class="schedule-day-name"><?= $day['day_name'] ?></div>
              <div class="schedule-day-date"><?= $day['date_label'] ?></div>
            </div>
            <div style="text-align:right;">
              <div class="schedule-day-count"><?= count($day['appointments']) ?></div>
              <div class="schedule-day-count-label">appointment<?= count($day['appointments']) === 1 ? '' : 's' ?></div>
            </div>
          </div>

          <div class="schedule-day-meta">
            <?php if ($day['is_today']): ?>
              <span class="badge badge-confirmed badge-compact">Today</span>
            <?php endif; ?>
            <?php if ($day['is_working']): ?>
              <span class="badge badge-pending badge-compact">Working</span>
            <?php else: ?>
              <span class="badge badge-cancelled badge-compact">Off</span>
            <?php endif; ?>
          </div>

          <?php if (!empty($day['appointments'])): ?>
            <div class="schedule-appt-list">
              <?php foreach ($day['appointments'] as $a): ?>
                <div class="schedule-appt">
                  <div class="schedule-appt-top">
                    <div style="display:flex;gap:12px;align-items:center;">
                      <strong><?= substr($a['appointment_time'], 0, 5) ?></strong>
                      <div style="font-size:.9rem;color:var(--gray-600);"><?= sanitize($a['patient_name']) ?></div>
                    </div>
                    <div style="display:flex;gap:8px;align-items:center;">
                      <span class="badge badge-<?= $statusColors[$a['status']] ?> badge-compact"><?= $a['status'] ?></span>
                    </div>
                  </div>
                  <div class="schedule-appt-reason"><?= sanitize($a['reason']) ?></div>
                  <div class="schedule-appt-actions">
                    <div class="schedule-appt-fee">$<?= number_format($a['fee_at_booking'], 2) ?></div>
                    <div class="schedule-appt-action-buttons">
                      <?php if (!in_array($a['status'], ['Completed','Cancelled'])): ?>
                        <form method="POST" action="<?= BASE_URL ?>/index.php?page=doctor/dashboard" onsubmit="return confirm('Cancel this appointment for the patient?')">
                          <input type="hidden" name="appointment_id" value="<?= (int)$a['id'] ?>">
                          <input type="hidden" name="status" value="Cancelled">
                          <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                        </form>
                        <details>
                          <summary class="btn btn-ghost btn-sm">Reschedule</summary>
                          <form method="POST" action="<?= BASE_URL ?>/index.php?page=doctor/reschedule" class="schedule-reschedule-form">
                            <input type="hidden" name="appointment_id" value="<?= (int)$a['id'] ?>">
                            <input type="date" name="new_date" value="<?= sanitize($a['appointment_date']) ?>" min="<?= date('Y-m-d') ?>" class="form-control schedule-reschedule-input">
                            <?php
                              $dm = new DoctorModel();
                              $booked = $dm->getBookedSlots($doctor['id'], $a['appointment_date']);
                              $normalized = array_map(fn($t) => substr($t, 0, 5), $booked);
                              $isToday = $a['appointment_date'] === date('Y-m-d');
                              $minTs = time() + 15 * 60;
                            ?>
                            <select name="new_time" class="form-control schedule-reschedule-input">
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
          <?php else: ?>
            <div class="schedule-empty">
              <div class="schedule-empty-icon">🗓️</div>
              <div class="schedule-empty-text">No appointments scheduled.</div>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
