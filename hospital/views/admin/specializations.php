<?php
$pageTitle = 'Specializations — MediBook';
require BASE_PATH . '/views/layouts/header.php';
?>
<div class="page-header">
  <div class="container"><div><h1>Specializations</h1><p>Manage medical specializations</p></div></div>
</div>

<div class="container" style="padding-bottom:48px;">
  <?php if ($success): ?>
    <div class="alert alert-success"><span class="alert-icon">✓</span><?= sanitize($success) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-error"><span class="alert-icon">⚠️</span><?= sanitize($error) ?></div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:360px 1fr;gap:24px;">
    <!-- Add Form -->
    <div class="card">
      <div class="card-header"><h2 class="card-title">➕ Add Specialization</h2></div>
      <form method="POST" action="<?= BASE_URL ?>/index.php?page=admin/specializations" novalidate>
        <input type="hidden" name="_action" value="create">
        <div class="form-group">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control <?= isset($errors['name']) && empty($old['edit_id']) ? 'error' : '' ?>"
                 value="<?= sanitize(empty($old['edit_id']) ? ($old['name'] ?? '') : '') ?>"
                 placeholder="e.g. Cardiology">
          <?php if (!empty($errors['name']) && empty($old['edit_id'])): ?>
            <div class="form-error">⚠ <?= sanitize($errors['name']) ?></div>
          <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Add Specialization</button>
      </form>
    </div>

    <!-- List -->
    <div class="card">
      <div class="card-header"><h2 class="card-title">🏷️ All Specializations</h2></div>
      <?php if (empty($specs)): ?>
        <p class="text-muted">No specializations yet.</p>
      <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead><tr><th>#</th><th>Name</th><th style="width:180px;">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($specs as $s): ?>
              <tr id="spec-row-<?= $s['id'] ?>">
                <td><?= (int)$s['id'] ?></td>
                <td><?= sanitize($s['name']) ?></td>
                <td>
                  <div style="display:flex;gap:6px;">
                    <button class="btn btn-ghost btn-sm" onclick="openEditSpec(<?= $s['id'] ?>,'<?= addslashes($s['name']) ?>')">Edit</button>
                    <form method="POST" action="<?= BASE_URL ?>/index.php?page=admin/specializations" style="display:inline;"
                          onsubmit="return confirm('Delete this specialization? This cannot be undone.')">
                      <input type="hidden" name="_action" value="delete">
                      <input type="hidden" name="id" value="<?= $s['id'] ?>">
                      <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
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
</div>

<!-- Edit Modal -->
<div class="modal-backdrop" id="edit-modal">
  <div class="modal">
    <button class="modal-close" onclick="closeEditModal()">✕</button>
    <h2 class="modal-title">✏️ Edit Specialization</h2>
    <form method="POST" action="<?= BASE_URL ?>/index.php?page=admin/specializations" novalidate>
      <input type="hidden" name="_action" value="edit">
      <input type="hidden" name="id" id="edit-spec-id">
      <div class="form-group">
        <label class="form-label">Name</label>
        <input type="text" name="name" id="edit-spec-name" class="form-control <?= !empty($errors['name']) && !empty($old['edit_id']) ? 'error' : '' ?>"
               value="<?= sanitize($old['edit_name'] ?? '') ?>">
        <?php if (!empty($errors['name']) && !empty($old['edit_id'])): ?>
          <div class="form-error">⚠ <?= sanitize($errors['name']) ?></div>
        <?php endif; ?>
      </div>
      <div style="display:flex;gap:10px;">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <button type="button" class="btn btn-ghost" onclick="closeEditModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditSpec(id, name) {
  document.getElementById('edit-spec-id').value = id;
  document.getElementById('edit-spec-name').value = name;
  document.getElementById('edit-modal').classList.add('open');
}
function closeEditModal() {
  document.getElementById('edit-modal').classList.remove('open');
}
<?php if (!empty($old['edit_id'])): ?>
openEditSpec(<?= (int)$old['edit_id'] ?>, '<?= addslashes($old['edit_name'] ?? '') ?>');
<?php endif; ?>
</script>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
