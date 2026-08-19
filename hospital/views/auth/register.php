<?php
$pageTitle = 'Register — MediBook';
require BASE_PATH . '/views/layouts/header.php';
?>
<style>
.auth-wrap{min-height:calc(100vh - 64px);display:flex;align-items:center;justify-content:center;padding:40px 24px;background:linear-gradient(135deg,var(--cream) 0%,var(--gray-100) 100%);}
.auth-card{background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow-lg);width:100%;max-width:540px;overflow:hidden;}
.auth-header{background:var(--navy);padding:36px 40px 28px;text-align:center;}
.auth-header h1{color:var(--white);font-size:1.8rem;margin-bottom:6px;}
.auth-header p{color:rgba(255,255,255,.6);font-size:.9rem;}
.auth-body{padding:36px 40px;}
.auth-footer{text-align:center;margin-top:20px;font-size:.9rem;color:var(--gray-600);}
.auth-footer a{color:var(--teal);font-weight:600;}
</style>

<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-header">
      <div style="font-size:2.5rem;margin-bottom:8px;">✨</div>
      <h1>Create Account</h1>
      <p>Join MediBook as a Patient</p>
    </div>
    <div class="auth-body">
      <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-warning"><span class="alert-icon">⚠️</span><?= sanitize($errors['general']) ?></div>
      <?php endif; ?>
      <form method="POST" action="<?= BASE_URL ?>/index.php?page=register" novalidate>
        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label" for="name">Full Name</label>
            <input type="text" id="name" name="name" class="form-control <?= isset($errors['name']) ? 'error' : '' ?>"
                   value="<?= sanitize($old['name'] ?? '') ?>" placeholder="John Smith">
            <?php if (!empty($errors['name'])): ?><div class="form-error">⚠ <?= sanitize($errors['name']) ?></div><?php endif; ?>
          </div>
          <div class="form-group">
            <label class="form-label" for="email">Email Address</label>
            <input type="text" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'error' : '' ?>"
                   value="<?= sanitize($old['email'] ?? '') ?>" placeholder="you@example.com">
            <?php if (!empty($errors['email'])): ?><div class="form-error">⚠ <?= sanitize($errors['email']) ?></div><?php endif; ?>
          </div>
        </div>
        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control <?= isset($errors['password']) ? 'error' : '' ?>"
                   placeholder="Min. 6 characters">
            <?php if (!empty($errors['password'])): ?><div class="form-error">⚠ <?= sanitize($errors['password']) ?></div><?php endif; ?>
          </div>
          <div class="form-group">
            <label class="form-label" for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password"
                   class="form-control <?= isset($errors['confirm_password']) ? 'error' : '' ?>"
                   placeholder="Repeat password">
            <?php if (!empty($errors['confirm_password'])): ?><div class="form-error">⚠ <?= sanitize($errors['confirm_password']) ?></div><?php endif; ?>
          </div>
        </div>
        <div class="form-row form-row-3">
          <div class="form-group">
            <label class="form-label" for="dob">Date of Birth</label>
            <input type="date" id="dob" name="dob" class="form-control <?= isset($errors['dob']) ? 'error' : '' ?>"
                   value="<?= sanitize($old['dob'] ?? '') ?>">
            <?php if (!empty($errors['dob'])): ?><div class="form-error">⚠ <?= sanitize($errors['dob']) ?></div><?php endif; ?>
          </div>
          <div class="form-group">
            <label class="form-label" for="blood_group">Blood Group</label>
            <select id="blood_group" name="blood_group" class="form-control <?= isset($errors['blood_group']) ? 'error' : '' ?>">
              <option value="">-- Select --</option>
              <?php foreach (['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg): ?>
                <option value="<?= $bg ?>" <?= ($old['blood_group'] ?? '') === $bg ? 'selected' : '' ?>><?= $bg ?></option>
              <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['blood_group'])): ?><div class="form-error">⚠ <?= sanitize($errors['blood_group']) ?></div><?php endif; ?>
          </div>
          <div class="form-group">
            <label class="form-label" for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone" class="form-control <?= isset($errors['phone']) ? 'error' : '' ?>"
                   value="<?= sanitize($old['phone'] ?? '') ?>" placeholder="+1 234 567 8900">
            <?php if (!empty($errors['phone'])): ?><div class="form-error">⚠ <?= sanitize($errors['phone']) ?></div><?php endif; ?>
          </div>
        </div>
        <button type="submit" class="btn btn-primary w-full btn-lg mt-1">Create Patient Account</button>
      </form>
      <div class="auth-footer">
        Already have an account? <a href="<?= BASE_URL ?>/index.php?page=login">Sign in</a>
      </div>
    </div>
  </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
