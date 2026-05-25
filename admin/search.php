<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$idNumber = trim($_GET['id_number'] ?? '');
$student = null;
$reservations = [];
$message = '';

if ($idNumber !== '') {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM students WHERE id_number = ?');
    $stmt->execute([$idNumber]);
    $student = $stmt->fetch();

    if (!$student) {
        $message = 'Student not found.';
    } else {
        $stmt = $db->prepare("SELECT r.*, l.lab_name FROM sit_in_records r JOIN laboratories l ON l.id = r.laboratory_id WHERE r.student_id = ? AND r.status IN ('Reserved','Approved','On Going') ORDER BY r.scheduled_date, r.scheduled_time_in");
        $stmt->execute([$student['id']]);
        $reservations = $stmt->fetchAll();
    }
}

$pageTitle = 'Student Search';
require __DIR__ . '/../includes/head.php';
require __DIR__ . '/../includes/admin_navbar.php';
$d = 'div';
?>
<h4 class="fw-bold pt-3 ms-3">Student Search</h4>
<<?= $d ?> class="container-fluid py-4 px-4">
    <<?= $d ?> class="card main-card border-0 shadow-sm p-4">
        <h4 class="fw-bold mb-3">Find Student</h4>
        <p class="text-muted small mb-4">Please enter the specific student ID Number to retrieve their profile information and their sit-in reservation details.</p>

        <form class="row g-3 mb-4" method="get" action="<?= BASE_URL ?>/admin/search.php">
            <<?= $d ?> class="col-md-9">
                <input type="text" name="id_number" class="form-control" placeholder="Student ID Number" value="<?= htmlspecialchars($idNumber) ?>" required>
            </<?= $d ?>>
            <<?= $d ?> class="col-md-3">
                <button class="btn btn-primary-purple w-100">Search</button>
            </<?= $d ?>>
        </form>

        <?php if ($idNumber === ''): ?>
            <div class="alert alert-info">Search by student ID number to view the student profile and active reservation details.</div>
        <?php elseif ($message !== ''): ?>
            <div class="alert alert-warning"><?= htmlspecialchars($message) ?></div>
        <?php else: ?>
            <<?= $d ?> class="border rounded p-4 mb-4">
                <h5 class="fw-bold mb-2"><?= htmlspecialchars(getStudentFullName($student)) ?></h5>
                <p class="mb-1 small"><strong>ID:</strong> <?= htmlspecialchars($student['id_number']) ?></p>
                <p class="mb-1 small"><strong>Course:</strong> <?= htmlspecialchars($student['course']) ?></p>
                <p class="mb-1 small"><strong>Year Level:</strong> <?= htmlspecialchars($student['year_level']) ?></p>
                <p class="mb-0 small"><strong>Remaining Sessions:</strong> <?= (int) $student['remaining_sessions'] ?></p>
            </<?= $d ?>>

            <?php if ($reservations): ?>
                <h5 class="fw-bold mb-3">Active / Upcoming Reservations</h5>
                <?php foreach ($reservations as $r): ?>
                    <<?= $d ?> class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge <?= statusBadgeClass($r['status']) ?>"><?= htmlspecialchars($r['status']) ?></span>
                            <small class="text-muted"><?= htmlspecialchars($r['lab_name']) ?> PC <?= (int)$r['pc_number'] ?></small>
                        </div>
                        <p class="mb-1 small"><strong>Purpose:</strong> <?= htmlspecialchars($r['purpose']) ?></p>
                        <p class="mb-1 small"><strong>Schedule:</strong> <?= date('M d, Y', strtotime($r['scheduled_date'])) ?> <?= date('h:i A', strtotime($r['scheduled_time_in'])) ?></p>
                        <p class="mb-1 small"><strong>Booked On:</strong> <?= date('M d, Y h:i A', strtotime($r['booked_on'])) ?></p>
                        <?php if ($r['time_in']): ?>
                            <p class="mb-1 small"><strong>Time In:</strong> <?= date('M d, Y h:i A', strtotime($r['time_in'])) ?></p>
                            <p class="mb-2 small"><strong>Duration:</strong> <span class="badge bg-info"><?= formatDuration(computeDuration($r['time_in'], $r['time_out'] ?? date('Y-m-d H:i:s'))) ?></span></p>
                            <?php if (!$r['time_out'] && $r['status'] === 'On Going'): ?>
                                <form method="post" action="<?= BASE_URL ?>/api/admin/checkout.php" class="d-inline">
                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                    <input type="hidden" name="return_to" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                                    <button type="submit" class="btn btn-sm btn-danger mt-2">Check Out</button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if ($r['status'] === 'Reserved'): ?>
                            <form method="post" action="<?= BASE_URL ?>/api/admin/reservation_action.php" class="d-inline me-1">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <input type="hidden" name="action" value="accept">
                                <button class="btn btn-sm btn-success">Accept</button>
                            </form>
                            <form method="post" action="<?= BASE_URL ?>/api/admin/reservation_action.php" class="d-inline">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <input type="hidden" name="action" value="reject">
                                <button class="btn btn-sm btn-danger">Reject</button>
                            </form>
                        <?php elseif ($r['status'] === 'Approved'): ?>
                            <form method="post" action="<?= BASE_URL ?>/api/admin/checkin.php" class="d-inline">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <input type="hidden" name="return_to" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                                <button type="submit" class="btn btn-sm btn-primary mt-2">Check In</button>
                            </form>
                        <?php endif; ?>
                    </<?= $d ?>>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-secondary">No active reservation found. You may create one from the Sit-In page.</div>
            <?php endif; ?>
        <?php endif; ?>
    </<?= $d ?>>
</<?= $d ?>>
<?php require __DIR__ . '/../includes/footer.php'; ?>
