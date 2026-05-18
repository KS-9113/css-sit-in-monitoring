<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'CCS Sit-In Monitoring';
require __DIR__ . '/includes/head.php';
?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-purple sticky-top">
    <div class="container">
        <a class="navbar-brand navbar-brand-custom" href="<?= BASE_URL ?>/index.php">
            <i class="bi bi-pc-display-horizontal me-2"></i>CCS Sit-In Monitoring
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navMain">
            <ul class="navbar-nav align-items-lg-center gap-1">     
            </ul>
        </div>
    </div>
</nav>

<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-5 mb-lg-0">
                <span class="badge rounded-pill mb-3 px-3 py-2" style="background:#e9d5ff;color:#6f42c1;">University of Cebu — CCS</span>
                <h1 class="hero-title mb-4">Study Smart. Reserve Your Lab Time.</h1>
                <p class="hero-subtitle mb-4">
                    The CCS Sit-In Monitoring System helps you book computer laboratory sessions, track your remaining sit-in credits, and stay productive. Focus on learning—let us handle the scheduling.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary-purple btn-lg px-4">Get Started</a>
                    <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline-secondary btn-lg px-4">Student Login</a>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-lg-5" style="background:linear-gradient(145deg,#6f42c1,#4a2c8a);color:#fff;">
                        <h4 class="fw-bold mb-3"><i class="bi bi-lightbulb me-2"></i>Study Hard, Achieve More</h4>
                        <ul class="list-unstyled mb-0 small opacity-90">
                            <li class="mb-2"><i class="bi bi-check2-circle me-2"></i>Plan your lab sessions ahead of time</li>
                            <li class="mb-2"><i class="bi bi-check2-circle me-2"></i>Use computers for academic purposes only</li>
                            <li class="mb-2"><i class="bi bi-check2-circle me-2"></i>Respect lab rules and fellow students</li>
                            <li><i class="bi bi-check2-circle me-2"></i>Every session is a step toward your goals</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4 mt-5">
            <div class="col-md-4">
                <div class="card feature-card shadow-sm p-4">
                    <div class="feature-icon mb-3"><i class="bi bi-calendar-check"></i></div>
                    <h5 class="fw-bold">Easy Reservations</h5>
                    <p class="text-muted small mb-0">Book your preferred lab, PC, date, and time in minutes.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card shadow-sm p-4">
                    <div class="feature-icon mb-3"><i class="bi bi-graph-up"></i></div>
                    <h5 class="fw-bold">Track Your Progress</h5>
                    <p class="text-muted small mb-0">View sit-in history, duration, and remaining sessions anytime.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card shadow-sm p-4">
                    <div class="feature-icon mb-3"><i class="bi bi-shield-check"></i></div>
                    <h5 class="fw-bold">Fair & Organized</h5>
                    <p class="text-muted small mb-0">Real-time PC availability keeps lab usage orderly for everyone.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
