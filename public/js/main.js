// MediBook — main.js
// Global utility: escape HTML
function escHtml(str) {
  const div = document.createElement('div');
  div.textContent = String(str);
  return div.innerHTML;
}

// Auto-dismiss alerts after 6 seconds
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.alert').forEach(function (el) {
    setTimeout(function () {
      el.style.transition = 'opacity 0.5s';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 500);
    }, 6000);
  });

  // Close modal on backdrop click
  document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
    backdrop.addEventListener('click', function (e) {
      if (e.target === backdrop) backdrop.classList.remove('open');
    });
  });

  // Close modal on Escape key
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-backdrop.open').forEach(function (m) {
        m.classList.remove('open');
      });
    }
  });
});

// Update appointment status via API and update badge in-place
async function updateAppointmentStatus(id, status, reason = '') {
  try {
    const url = `${BASE_URL}/index.php?page=api/appointments&appointment_id=${encodeURIComponent(id)}`;
    const resp = await fetch(url, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({ status: status, reason: reason })
    });
    const json = await resp.json();
    if (!resp.ok || !json.ok) throw new Error(json.error || 'Update failed');

    // update badge text and class for any matching badges on the page
    const newStatus = json.new_status;
    const badgeSelectors = [`#doc-badge-${id}`, `#appt-badge-${id}`];
    badgeSelectors.forEach(sel => {
      const el = document.querySelector(sel);
      if (!el) return;
      el.textContent = newStatus;
      el.className = 'badge badge-' + (newStatus.toLowerCase().replace(/\s+/g,'-'));
    });

    return { ok: true, new_status: newStatus };
  } catch (err) {
    console.error('updateAppointmentStatus error', err);
    return { ok: false, error: err.message };
  }
}
