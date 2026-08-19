<?php
$pageTitle = 'Page Not Found — MediBook';
require BASE_PATH . '/views/layouts/header.php';
?>
<div class="container flex-center" style="min-height:60vh;flex-direction:column;text-align:center;padding:60px 24px;">
  <div style="font-size:6rem;line-height:1;margin-bottom:20px;">🔍</div>
  <h1 style="font-size:3rem;color:var(--navy);margin-bottom:8px;">404</h1>
  <h2 style="font-weight:400;color:var(--gray-600);margin-bottom:20px;">Page Not Found</h2>
  <p class="text-muted" style="max-width:400px;margin-bottom:32px;">
    The page you're looking for doesn't exist or has been moved.
  </p>
  <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary btn-lg">Go to Home</a>
</div>
<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
