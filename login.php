<?php
require_once __DIR__ . '/includes/functions.php';
if (isLoggedInStudent()) redirect('/student/dashboard.php');
$pageTitle = 'Login - CCS Sit-In';
require __DIR__ . '/includes/head.php';
?>
<nav class="navbar navbar-dark navbar-purple">
    <div class="container"><a class="navbar-brand navbar-brand-custom" href="<?= BASE_URL ?>/">CCS Sit-In Monitoring</a></div>
</nav>
<div class="auth-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <a href="<?= BASE_URL ?>/" class="btn-back mb-3"><i class="bi bi-arrow-left"></i> Back</a>
                <div class="card auth-card">
                    <div class="auth-header text-center">
                        <h3 class="mb-1 fw-bold">Login</h3>
                        <p class="mb-0 opacity-90 small">Access your monitoring dashboard.</p>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <form method="post" action="<?= BASE_URL ?>/api/login.php">
                            <div class="mb-3">
                                <label class="form-label-custom">ID Number</label>
                                <input type="text" name="id_number" class="form-control" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label-custom">Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="loginPass" class="form-control" required>
                                    <span class="input-group-text password-toggle" data-target="#loginPass"><i class="bi bi-eye"></i></span>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary-purple w-100 btn-lg">Login To Dashboard</button>
                        </form>
                        <p class="text-center mt-4 mb-0 small">New Student? <a href="<?= BASE_URL ?>/register.php" class="fw-semibold" style="color:#6f42c1">Create Account</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
