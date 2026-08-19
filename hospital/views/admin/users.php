<?php
$pageTitle = 'Users — MediBook';
require BASE_PATH . '/views/layouts/header.php';
?>
<div class="page-header">
  <div class="container"><div><h1>User Management</h1><p>Control account access and active status</p></div></div>
</div>

<div class="container" style="padding-bottom:48px;">
  <?php if ($success): ?><div class="alert alert-success"><span class="alert-icon">✓</span><?= sanitize($success) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="alert alert-error"><span class="alert-icon">⚠️</span><?= sanitize($error) ?></div><?php endif; ?>

  <div class="card">
    <div class="card-header">
      <h2 class="card-title">👥 All Users</h2>
      <span class="text-muted" style="font-size:.85rem;"><?= count($users) ?> total</span>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
          <tr id="user-row-<?= $u['id'] ?>">
            <td><?= sanitize($u['name']) ?></td>
            <td><?= sanitize($u['email']) ?></td>
            <td><span class="badge badge-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
            <td>
              <span class="badge badge-<?= $u['is_active'] ? 'active' : 'inactive' ?>" id="status-badge-<?= $u['id'] ?>">
                <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
              </span>
            </td>
            <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
            <td>
              <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <a href="<?= BASE_URL ?>/index.php?page=admin/user-edit&id=<?= (int)$u['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                  <form method="POST" action="<?= BASE_URL ?>/index.php?page=admin/users" style="display:inline;">
                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                    <button type="submit" class="toggle-btn <?= $u['is_active'] ? 'active' : 'inactive' ?>">
                      <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
                    </button>
                  </form>
                <?php else: ?>
                  <span class="text-muted" style="font-size:.82rem;">You</span>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
