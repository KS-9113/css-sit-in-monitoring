<?php
require_once(__DIR__ . '/../includes/functions.php');
$pageTitle = 'CCS Sit-In Monitoring';
$db = getDB();

$studentLeaderboardSql = "SELECT s.id_number, s.first_name, s.middle_name, s.last_name, s.course, s.year_level,
    COUNT(r.id) AS total_sessions,
    COALESCE(SUM(r.duration_minutes), 0) AS total_minutes
    FROM students s
    LEFT JOIN sit_in_records r ON r.student_id = s.id AND r.status = 'Completed'
    GROUP BY s.id, s.id_number, s.first_name, s.middle_name, s.last_name, s.course, s.year_level
    ORDER BY total_sessions DESC, total_minutes DESC
    LIMIT 5";
$studentLeaderboard = $db->query($studentLeaderboardSql)->fetchAll();

$labLeaderboardSql = "SELECT l.lab_name, COUNT(r.id) AS sessions
    FROM laboratories l
    LEFT JOIN sit_in_records r ON r.laboratory_id = l.id AND r.status IN ('Approved','On Going','Completed')
    GROUP BY l.id, l.lab_name
    ORDER BY sessions DESC
    LIMIT 5";
$labLeaderboard = $db->query($labLeaderboardSql)->fetchAll();

$purposeLeaderboardSql = "SELECT purpose, COUNT(DISTINCT student_id) AS student_count, COUNT(*) AS total_sessions
    FROM sit_in_records
    WHERE status IN ('On Going','Completed') AND purpose IS NOT NULL AND TRIM(purpose) != ''
    GROUP BY purpose
    ORDER BY total_sessions DESC
    LIMIT 8";
$purposeLeaderboard = $db->query($purposeLeaderboardSql)->fetchAll();

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
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle btn-nav" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Explore
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="#leaderboard">Leaderboard</a></li>
                        <li><a class="dropdown-item" href="#features">Features</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link btn-nav" href="<?= BASE_URL ?>/register.php">Register</a></li>
                <li class="nav-item"><a class="nav-link btn-nav" href="<?= BASE_URL ?>/login.php">Login</a></li>
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
        <div class="row g-4 mt-5" id="features">
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

<section id="leaderboard" class="bg-white py-5">
    <div class="container">
        <div class="row align-items-center mb-4">
            <div class="col-md-8">
                <h2 class="fw-bold mb-2">Live Leaderboards</h2>
                <p class="text-muted mb-0">See the top students and busiest labs based on the latest completed and approved sessions.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary-purple">Register Your Account</a>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm p-4 h-100">
                    <h5 class="fw-bold mb-4">Top Students</h5>
                    <?php if (empty($studentLeaderboard)): ?>
                        <p class="text-muted mb-0">No completed sessions found yet. Add sample student sessions to see the leaderboard.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($studentLeaderboard as $index => $student): ?>
                                <div class="list-group-item border-0 px-0 py-3 d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars(getStudentFullName($student)) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($student['id_number']) ?> · <?= htmlspecialchars($student['course']) ?> · <?= htmlspecialchars($student['year_level']) ?></small>
                                    </div>
                                    <span class="badge bg-primary text-white rounded-pill"><?= (int)$student['total_sessions'] ?> sessions</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm p-4 h-100">
                    <h5 class="fw-bold mb-4">Most Active Labs</h5>
                    <?php if (empty($labLeaderboard)): ?>
                        <p class="text-muted mb-0">No lab sessions are available yet. Populate data to see lab usage rankings.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($labLeaderboard as $lab): ?>
                                <div class="list-group-item border-0 px-0 py-3 d-flex align-items-center justify-content-between">
                                    <div>
                                        <strong class="d-block"><?= htmlspecialchars($lab['lab_name']) ?></strong>
                                        <small class="text-muted">Active or completed sit-ins</small>
                                    </div>
                                    <span class="badge bg-success rounded-pill"><?= (int)$lab['sessions'] ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Top Purposes removed from landing page per request -->
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
