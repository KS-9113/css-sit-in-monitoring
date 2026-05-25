<?php
require_once __DIR__ . '/../includes/functions.php';
requireStudent();
$studentId = (int) $_SESSION['student_id'];
$search = trim($_GET['q'] ?? '');

$sql = "SELECT r.*, l.lab_name FROM sit_in_records r JOIN laboratories l ON l.id = r.laboratory_id WHERE r.student_id = ?";
$params = [$studentId];
if ($search !== '') {
    $sql .= " AND (r.sit_in_no LIKE ? OR r.purpose LIKE ? OR l.lab_name LIKE ? OR r.status LIKE ?)";
    $like = '%' . $search . '%';
    $params = array_merge($params, [$like, $like, $like, $like]);
}
$sql .= ' ORDER BY r.booked_on DESC';
$stmt = getDB()->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll();

$pageTitle = 'Sit-In Records';
require __DIR__ . '/../includes/head.php';
$d = 'div';
?>
<nav class="navbar navbar-dark navbar-purple">
<div class="container-fluid px-4">
<span class="navbar-brand navbar-brand-custom">History Information</span>
<a href="<?= BASE_URL ?>/student/dashboard.php" class="btn btn-light btn-sm">Back to Dashboard</a>
</<?= $d ?>>
</nav>
<<?= $d ?> class="container-fluid py-4 px-4">
<<?= $d ?> class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
<h4 class="fw-bold mb-0">Sit-in Records</h4>
<form class="d-flex" method="get"><input type="search" name="q" class="form-control" placeholder="Search records..." value="<?= htmlspecialchars($search) ?>"><button class="btn btn-primary-purple ms-2">Search</button></form>
</<?= $d ?>>
<<?= $d ?> class="card main-card shadow-sm border-0">
<<?= $d ?> class="card-body table-scroll p-0">
<table class="table table-hover mb-0 align-middle">
<thead class="table-light sticky-top"><tr>
<th>Sit-in No.</th><th>Purpose</th><th>Laboratory</th><th>PC no.</th>
<th>Scheduled For</th><th>Time Out</th><th>Duration</th><th>Booked On</th><th>Status</th><th>Action</th>
</tr></thead>
<tbody>
<?php $feedbackModals = ''; foreach ($records as $r):
    $dur = $r['duration_minutes'] ?? computeDuration($r['time_in'], $r['time_out']);
    $fb = getDB()->prepare('SELECT id FROM feedback WHERE sit_in_record_id = ?');
    $fb->execute([$r['id']]);
    $hasFeedback = (bool) $fb->fetch();
?>
<tr>
<td><?= htmlspecialchars($r['sit_in_no']) ?></td>
<td><?= htmlspecialchars($r['purpose']) ?></td>
<td><?= htmlspecialchars($r['lab_name']) ?></td>
<td>PC <?= (int) $r['pc_number'] ?></td>
<td><?= date('M d, Y', strtotime($r['scheduled_date'])) ?> <?= date('h:i A', strtotime($r['scheduled_time_in'])) ?></td>
<td><?= $r['time_out'] ? date('M d, Y h:i A', strtotime($r['time_out'])) : '—' ?></td>
<td><?= formatDuration($dur) ?></td>
<td><?= date('M d, Y h:i A', strtotime($r['booked_on'])) ?></td>
<td><span class="badge <?= statusBadgeClass($r['status']) ?>"><?= htmlspecialchars($r['status']) ?></span></td>
<td>
<?php if ($r['status'] === 'Reserved' || $r['status'] === 'Approved'): ?>
<form method="post" action="<?= BASE_URL ?>/api/cancel_reservation.php" class="d-inline" onsubmit="return confirm('Cancel this reservation?')">
<input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-outline-danger">Cancel</button></form>
<?php elseif ($r['status'] === 'On Going'): ?>
<form method="post" action="<?= BASE_URL ?>/api/checkout.php" class="d-inline"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-warning">Check Out</button></form>
<?php elseif ($r['status'] === 'Completed' && !$hasFeedback): ?>
<button class="btn btn-sm btn-primary-purple" data-bs-toggle="modal" data-bs-target="#fbModal<?= $r['id'] ?>">Feedback</button>
<?php elseif ($hasFeedback): ?><span class="text-muted small">Rated</span><?php else: ?>—<?php endif; ?>
</td>
</tr>
<?php if ($r['status'] === 'Completed' && !$hasFeedback):
    $feedbackModals .= '<div class="modal fade" id="fbModal' . $r['id'] . '" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Session Feedback</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="post" action="' . BASE_URL . '/api/feedback.php"><div class="modal-body"><input type="hidden" name="record_id" value="' . $r['id'] . '"><label class="form-label-custom">Rating (1-5 stars)</label><select name="rating" class="form-select mb-3" required>'; for ($i=1; $i<=5; $i++) { $feedbackModals .= '<option value="' . $i . '">' . $i . ' Star' . ($i > 1 ? 's' : '') . '</option>'; } $feedbackModals .= '</select><label class="form-label-custom">Your experience</label><textarea name="comments" class="form-control" rows="3" required></textarea></div><div class="modal-footer"><button type="submit" class="btn btn-primary-purple">Submit Feedback</button></div></form></div></div></div>';
endif; endforeach; ?>
<?php if (empty($records)): ?><tr><td colspan="10" class="text-center text-muted py-4">No records found.</td></tr><?php endif; ?>
</tbody></table>
</<?= $d ?>></<?= $d ?>></<?= $d ?>><?= $feedbackModals ?><?php require __DIR__ . '/../includes/footer.php'; ?>
