<?php
$student = getStudentById((int) $_SESSION['student_id']);
$fullName = getStudentFullName($student);
$pic = getProfilePictureUrl($student['profile_picture'] ?? null);
$studentNotificationCount = 0;
try {
    $stmt = getDB()->prepare('SELECT COUNT(*) FROM notifications WHERE recipient_type = ? AND student_id = ? AND is_deleted = 0');
    $stmt->execute(['student', $student['id']]);
    $studentNotificationCount = (int) $stmt->fetchColumn();
} catch (PDOException $e) {
    $studentNotificationCount = 0;
}
$current = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-purple student-navbar sticky-top">
    <div class="container-fluid px-3 px-lg-4">
        <a class="navbar-brand navbar-brand-custom" href="<?= BASE_URL ?>/student/dashboard.php">Student Dashboard</a>
        <div class="dropdown ms-auto">
            <a class="nav-link dropdown-toggle text-white d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                <img src="<?= htmlspecialchars($pic) ?>" alt="" class="rounded-circle" width="36" height="36" style="object-fit:cover;border:2px solid #fff">
                <span class="d-none d-md-inline"><?= htmlspecialchars($fullName) ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/student/dashboard.php"><i class="bi bi-house me-2"></i>Home</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/student/notifications.php"><i class="bi bi-bell me-2"></i>Notifications<?php if ($studentNotificationCount > 0): ?> <span class="badge bg-danger ms-2"><?= $studentNotificationCount ?></span><?php endif; ?></a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/student/profile.php"><i class="bi bi-person me-2"></i>Edit Profile</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/student/history.php"><i class="bi bi-clock-history me-2"></i>View Sit-In History</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/student/reservation.php"><i class="bi bi-calendar-plus me-2"></i>Reservation</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
