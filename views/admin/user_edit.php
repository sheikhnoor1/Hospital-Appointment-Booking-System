<?php
$pageTitle = 'Edit User — MediBook';
require BASE_PATH . '/views/layouts/header.php';
?>
<div class="page-header">
  <div class="container">
    <div>
      <div class="breadcrumb"><a href="<?= BASE_URL ?>/index.php?page=admin/users">Users</a> › <span>Edit User</span></div>
      <h1>Edit User</h1>
      <p>Update account details for <?= sanitize($user['name']) ?></p>
    </div>
  </div>
</div>

<div class="container" style="padding-bottom:48px;max-width:760px;">
  <?php if (!empty($success)): ?><div class="alert alert-success"><span class="alert-icon">✓</span><?= sanitize($success) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="alert alert-error"><span class="alert-icon">⚠️</span><?= sanitize($error) ?></div><?php endif; ?>
  <?php if (!empty($errors)): ?><div class="alert alert-error"><span class="alert-icon">⚠️</span>Please fix the errors below.</div><?php endif; ?>

  <div class="card">
    <div class="card-header"><h2 class="card-title">User Information</h2></div>
    <form method="POST" action="<?= BASE_URL ?>/index.php?page=admin/user-edit" novalidate>
      <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">

      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'error' : '' ?>"
               value="<?= sanitize($old['name'] ?? $user['name']) ?>">
        <?php if (!empty($errors['name'])): ?><div class="form-error">⚠ <?= sanitize($errors['name']) ?></div><?php endif; ?>
      </div>

      <div class="form-group">
        <label class="form-label">Email</label>
        <input type="text" name="email" class="form-control <?= isset($errors['email']) ? 'error' : '' ?>"
               value="<?= sanitize($old['email'] ?? $user['email']) ?>">
        <?php if (!empty($errors['email'])): ?><div class="form-error">⚠ <?= sanitize($errors['email']) ?></div><?php endif; ?>
      </div>

      <div class="form-group">
        <label class="form-label">Phone Number</label>
        <input type="text" name="phone" class="form-control <?= isset($errors['phone']) ? 'error' : '' ?>"
               value="<?= sanitize($old['phone'] ?? ($user['phone'] ?? '')) ?>" placeholder="Optional">
        <?php if (!empty($errors['phone'])): ?><div class="form-error">⚠ <?= sanitize($errors['phone']) ?></div><?php endif; ?>
      </div>

      <div class="form-row form-row-2">
        <div class="form-group mb-0">
          <label class="form-label">Role</label>
          <input type="text" class="form-control" value="<?= ucfirst($user['role']) ?>" disabled>
        </div>
        <div class="form-group mb-0">
          <label class="form-label">Status</label>
          <input type="text" class="form-control" value="<?= $user['is_active'] ? 'Active' : 'Inactive' ?>" disabled>
        </div>
      </div>

      <div style="display:flex;gap:10px;margin-top:20px;">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="<?= BASE_URL ?>/index.php?page=admin/users" class="btn btn-ghost">Back to Users</a>
      </div>
    </form>
  </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
