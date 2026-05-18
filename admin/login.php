<?php
require_once __DIR__ . '/../includes/functions.php';
if (isLoggedInAdmin()) redirect('/admin/dashboard.php');
$pageTitle = 'Admin Login';
require __DIR__ . '/../includes/head.php';
$d = 'div';
?>
<nav class="navbar navbar-dark navbar-purple"><div class="container"><span class="navbar-brand navbar-brand-custom">CCS Admin</span></div></nav>
<<?= $d ?> class="auth-wrapper"><<?= $d ?> class="container"><<?= $d ?> class="row justify-content-center">
<<?= $d ?> class="col-md-4">
<<?= $d ?> class="card auth-card">
<<?= $d ?> class="auth-header text-center"><h4 class="fw-bold mb-0">Admin Login</h4></<?= $d ?>>
<<?= $d ?> class="card-body p-4">
<form method="post" action="<?= BASE_URL ?>/api/admin/login.php">
<label class="form-label-custom">Username</label>
<input type="text" name="username" class="form-control mb-3" value="admin" required>
<label class="form-label-custom">Password</label>
<input type="password" name="password" class="form-control mb-4" required>
<button class="btn btn-primary-purple w-100">Login</button>
</form>
<p class="text-center mt-3 mb-0 small"><a href="<?= BASE_URL ?>/">Back to site</a></p>
</<?= $d ?>></<?= $d ?>></<?= $d ?>></<?= $d ?>></<?= $d ?>>
<?php require __DIR__ . '/../includes/footer.php'; ?>
