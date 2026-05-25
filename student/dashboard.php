<?php
require_once __DIR__ . '/../includes/functions.php';
requireStudent();
$student = getStudentById((int) $_SESSION['student_id']);
$fullName = getStudentFullName($student);
$pic = getProfilePictureUrl($student['profile_picture'] ?? null);
$db = getDB();
$announcements = $db->query("SELECT * FROM announcements WHERE start_date <= CURDATE() AND end_date >= CURDATE() ORDER BY created_at DESC")->fetchAll();
$pageTitle = 'Student Dashboard';
require __DIR__ . '/../includes/head.php';
require __DIR__ . '/../includes/student_navbar.php';
$dc = 'd' . 'iv';
?>
<<?= $dc ?> class="container-fluid py-4 px-3 px-lg-4">
<<?= $dc ?> class="row g-4">
<<?= $dc ?> class="col-lg-3">
<<?= $dc ?> class="card main-card shadow-sm border-0 p-4 text-center">
<img src="<?= htmlspecialchars($pic) ?>" class="profile-avatar rounded-circle mx-auto mb-3" alt="Profile">
<h5 class="fw-bold mb-1"><?= htmlspecialchars($fullName) ?></h5>
<p class="text-muted small mb-3">Id: <?= htmlspecialchars($student['id_number']) ?></p>
<p class="mb-1 small"><strong>Course:</strong> <?= htmlspecialchars($student['course']) ?></p>
<p class="mb-1 small"><strong>Year Level:</strong> <?= htmlspecialchars($student['year_level']) ?></p>
<?php if (!empty($student['section'])): ?><p class="mb-3 small"><strong>Section:</strong> <?= htmlspecialchars($student['section']) ?></p><?php else: ?><p class="mb-3 small"><strong>Section:</strong> —</p><?php endif; ?>
<hr>
<h6 class="fw-bold text-start">Personal Information</h6>
<p class="small text-start mb-1"><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>
<p class="small text-start mb-1"><strong>Address:</strong> <?= htmlspecialchars($student['address']) ?></p>
<div class="mb-3 text-start">
    <p class="small mb-2"><strong>Remaining Sessions</strong></p>
    <div class="progress mb-2" style="height: 16px; border-radius: 10px;">
        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= min(100, round(($student['remaining_sessions'] / 30) * 100)) ?>%;" aria-valuenow="<?= (int)$student['remaining_sessions'] ?>" aria-valuemin="0" aria-valuemax="30"></div>
    </div>
    <p class="small mb-0"><strong><?= (int)$student['remaining_sessions'] ?></strong> sessions left</p>
</div>
</<?= $dc ?>>
</<?= $dc ?>>
<<?= $dc ?> class="col-lg-4">
<<?= $dc ?> class="card main-card shadow-sm border-0 h-100">
<<?= $dc ?> class="card-header bg-white border-0 pt-4 px-4"><h5 class="fw-bold mb-0"><i class="bi bi-megaphone text-primary me-2"></i>Announcements</h5></<?= $dc ?>>
<<?= $dc ?> class="card-body px-4 pb-4">
<?php if (empty($announcements)): ?>
<p class="text-muted small">No announcements at this time.</p>
<?php else: foreach ($announcements as $a): ?>
<<?= $dc ?> class="announcement-item">
<h6 class="fw-bold mb-1"><?= htmlspecialchars($a['title']) ?></h6>
<p class="announcement-dates mb-2"><i class="bi bi-calendar-range me-1"></i><?= date('M d, Y', strtotime($a['start_date'])) ?> — <?= date('M d, Y', strtotime($a['end_date'])) ?></p>
<p class="small mb-0"><?= nl2br(htmlspecialchars($a['content'])) ?></p>
</<?= $dc ?>>
<?php endforeach; endif; ?>
</<?= $dc ?>>
</<?= $dc ?>>
</<?= $dc ?>>
<<?= $dc ?> class="col-lg-5"><?php require __DIR__ . '/../includes/rules_card.php'; ?></<?= $dc ?>>
</<?= $dc ?>>
</<?= $dc ?>>
<?php require __DIR__ . '/../includes/footer.php'; ?>
