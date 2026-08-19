<?php
$pageTitle = 'Login — MediBook';
$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old']);
require BASE_PATH . '/views/layouts/header.php';
?>
<style>
.auth-wrap{min-height:calc(100vh - 64px);display:flex;align-items:center;justify-content:center;padding:40px 24px;background:linear-gradient(135deg,var(--cream) 0%,var(--gray-100) 100%);}
.auth-card{background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow-lg);width:100%;max-width:440px;overflow:hidden;}
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
      <div style="font-size:2.5rem;margin-bottom:8px;">🏥</div>
      <h1>Welcome Back</h1>
      <p>Sign in to your MediBook account</p>
    </div>
    <div class="auth-body">
      <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-error"><span class="alert-icon">⚠️</span><?= sanitize($errors['general']) ?></div>
      <?php endif; ?>

      <?php $successMsg = getFlash('success'); if ($successMsg): ?>
        <div class="alert alert-success"><span class="alert-icon">✓</span><?= sanitize($successMsg) ?></div>
      <?php endif; ?>

      <form method="POST" action="<?= BASE_URL ?>/index.php?page=login" novalidate>
        <div class="form-group">
          <label class="form-label" for="email">Email Address</label>
          <input type="text" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'error' : '' ?>"
                 value="<?= sanitize($old['email'] ?? '') ?>" placeholder="you@example.com" autocomplete="email">
          <?php if (!empty($errors['email'])): ?>
            <div class="form-error">⚠ <?= sanitize($errors['email']) ?></div>
          <?php endif; ?>
        </div>
        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <input type="password" id="password" name="password" class="form-control <?= isset($errors['password']) ? 'error' : '' ?>"
                 placeholder="Enter your password" autocomplete="current-password">
          <?php if (!empty($errors['password'])): ?>
            <div class="form-error">⚠ <?= sanitize($errors['password']) ?></div>
          <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary w-full btn-lg mt-2">Sign In</button>
      </form>

      <div class="auth-footer">
        <p>Don't have an account? <a href="<?= BASE_URL ?>/index.php?page=register">Register here</a></p>
        <p class="mt-2" style="font-size:.8rem;color:var(--gray-400);">
          Demo Admin: admin@hospital.com / password
        </p>
      </div>
    </div>
  </div>
</div>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
