<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Register - CCS Sit-In';
require __DIR__ . '/includes/head.php';
?>
<nav class="navbar navbar-dark navbar-purple">
    <div class="container"><a class="navbar-brand navbar-brand-custom" href="<?= BASE_URL ?>/">CCS Sit-In Monitoring</a></div>
</nav>
<div class="auth-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <a href="<?= BASE_URL ?>/" class="btn-back mb-3"><i class="bi bi-arrow-left"></i> Back</a>
                <div class="card auth-card">
                    <div class="auth-header">
                        <h3 class="mb-1 fw-bold">Create Account</h3>
                        <p class="mb-0 opacity-90 small">Fill in your details to start using the monitoring system</p>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <form id="registerForm" method="post" action="<?= BASE_URL ?>/api/register.php">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label-custom">ID Number</label>
                                    <input type="text" name="id_number" class="form-control" required pattern="[0-9]+" placeholder="e.g. 23833353">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label-custom">Email Address</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label-custom">First Name</label>
                                    <input type="text" name="first_name" class="form-control" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label-custom">Middle Name</label>
                                    <input type="text" name="middle_name" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label-custom">Last Name</label>
                                    <input type="text" name="last_name" class="form-control" required>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label-custom">Course</label>
                                    <select class="form-select" name="course" required>
                                        <?php include __DIR__ . '/includes/course_options.php'; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label-custom">Year Level</label>
                                    <select class="form-select" name="year_level" required>
                                        <option value="" disabled selected>Select year</option>
                                        <option>1st Year</option>
                                        <option>2nd Year</option>
                                        <option>3rd Year</option>
                                        <option>4th Year</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label-custom">Section</label>
                                    <input type="text" name="section" class="form-control" placeholder="e.g. C" required>
                                </div>
                                <div class="col-md-6 mb-3"></div>
                                <div class="col-12 mb-3">
                                    <label class="form-label-custom">Address</label>
                                    <textarea name="address" class="form-control" rows="2" required></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label-custom">Password</label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="regPass" class="form-control" required minlength="6">
                                        <span class="input-group-text password-toggle" data-target="#regPass"><i class="bi bi-eye"></i></span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label-custom">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" name="confirm_password" id="regPass2" class="form-control" required>
                                        <span class="input-group-text password-toggle" data-target="#regPass2"><i class="bi bi-eye"></i></span>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary-purple w-100 btn-lg mt-2">Register Student</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
