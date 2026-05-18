<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$q = trim($_GET['q'] ?? '');
$sql = "SELECT r.*, s.id_number, s.first_name, s.middle_name, s.last_name, l.lab_name FROM sit_in_records r
        JOIN students s ON s.id=r.student_id JOIN laboratories l ON l.id=r.laboratory_id WHERE r.status IN ('Reserved','Approved')";
$params = [];
if ($q !== '') { $sql .= ' AND (s.id_number LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ?)'; $like='%'.$q.'%'; $params=[$like,$like,$like]; }
$sql .= ' ORDER BY r.scheduled_date, r.scheduled_time_in';
$stmt = getDB()->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();
$pageTitle = 'Reservations';
require __DIR__ . '/../includes/head.php';
require __DIR__ . '/../includes/admin_navbar.php';
$d = 'd' . 'iv';
?>
<h4 class="fw-bold pt-3 ms-3">Reservations</h4>
<<?= $d ?> class="container-fluid py-4 px-4">
<form method="get" class="mb-3"><input name="q" class="form-control" placeholder="Search name or ID..." value="<?= htmlspecialchars($q) ?>"></form>
<table class="table table-hover bg-white rounded shadow-sm">
<thead class="table-light"><tr><th>Student</th><th>ID</th><th>Lab / PC</th><th>Schedule</th><th>Booked On</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($rows as $r): ?>
<tr>
<td><?= htmlspecialchars(getStudentFullName($r)) ?></td>
<td><?= htmlspecialchars($r['id_number']) ?></td>
<td><?= htmlspecialchars($r['lab_name']) ?> PC <?= (int)$r['pc_number'] ?></td>
<td><?= date('M d, Y', strtotime($r['scheduled_date'])) ?> <?= date('h:i A', strtotime($r['scheduled_time_in'])) ?></td>
<td><?= date('M d, Y h:i A', strtotime($r['booked_on'])) ?></td>
<td><span class="badge <?= statusBadgeClass($r['status']) ?>"><?= $r['status'] ?></span></td>
<td class="text-nowrap">
<?php if ($r['status']==='Reserved'): ?>
<form method="post" action="<?= BASE_URL ?>/api/admin/reservation_action.php" class="d-inline"><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="accept"><button class="btn btn-sm btn-success">Accept</button></form>
<form method="post" action="<?= BASE_URL ?>/api/admin/reservation_action.php" class="d-inline"><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="reject"><button class="btn btn-sm btn-danger">Reject</button></form>
<?php elseif ($r['status']==='Approved'): ?>
<form method="post" action="<?= BASE_URL ?>/api/admin/checkin.php" class="d-inline"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-primary">Check In</button></form>
<?php endif; ?>
</td></tr>
<?php endforeach; ?>
</tbody></table></<?= $d ?>>
<?php require __DIR__ . '/../includes/footer.php'; ?>
