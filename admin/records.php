<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$q = trim($_GET['q'] ?? '');
$from = trim($_GET['from'] ?? '');
$to = trim($_GET['to'] ?? '');
$sql = "SELECT r.*, s.id_number, s.first_name, s.middle_name, s.last_name, s.course, s.year_level, l.lab_name
        FROM sit_in_records r
        JOIN students s ON s.id = r.student_id
        JOIN laboratories l ON l.id = r.laboratory_id WHERE 1=1";
$params = [];
if ($q !== '') {
    $sql .= ' AND (s.id_number LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ? OR s.year_level LIKE ? OR CONCAT(s.first_name," ",s.last_name) LIKE ?)';
    $like = '%'.$q.'%';
    $params = array_fill(0, 5, $like);
}
if ($from !== '') { $sql .= ' AND DATE(r.booked_on) >= ?'; $params[] = $from; }
if ($to !== '') { $sql .= ' AND DATE(r.booked_on) <= ?'; $params[] = $to; }
$sql .= ' ORDER BY r.booked_on DESC LIMIT 500';
$stmt = getDB()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
$pageTitle = 'Sit-In Records';
require __DIR__ . '/../includes/head.php';
require __DIR__ . '/../includes/admin_navbar.php';
$d = 'div';
?>
<h4 class="fw-bold pt-3 ms-3">Sit-In Records</h4>
<<?= $d ?> class="container-fluid py-4 px-4">
<form method="get" class="row g-2 mb-4 align-items-end">
    <div class="col-md-4"><label class="form-label-custom">Search</label><input name="q" class="form-control" placeholder="Search ID, name, year..." value="<?= htmlspecialchars($q) ?>"></div>
    <div class="col-md-3"><label class="form-label-custom">From</label><input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>"></div>
    <div class="col-md-3"><label class="form-label-custom">To</label><input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>"></div>
    <div class="col-md-2 text-end"><button class="btn btn-primary-purple w-100">Filter</button></div>
</form>
<div class="d-flex justify-content-end mb-3"><a href="<?= BASE_URL ?>/api/admin/export_records.php?q=<?= urlencode($q) ?>&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>" class="btn btn-success">Download CSV</a></div>
<<?= $d ?> class="card border-0 shadow-sm"><<?= $d ?> class="table-scroll card-body p-0">
<table class="table table-sm table-hover mb-0"><thead class="table-light"><tr>
<th>ID Number</th><th>Name</th><th>Course</th><th>Year</th><th>Lab</th><th>PC</th><th>Booked On</th><th>Time In</th><th>Time Out</th><th>Duration</th>
</tr></thead><tbody>
<?php foreach ($rows as $r): $dur = $r['duration_minutes'] ?? computeDuration($r['time_in'], $r['time_out']); ?>
<tr>
<td><?= htmlspecialchars($r['id_number']) ?></td>
<td><?= htmlspecialchars(getStudentFullName($r)) ?></td>
<td><?= htmlspecialchars($r['course']) ?></td>
<td><?= htmlspecialchars($r['year_level']) ?></td>
<td><?= htmlspecialchars($r['lab_name']) ?></td>
<td><?= (int)$r['pc_number'] ?></td>
<td><?= date('M d, Y h:i A', strtotime($r['booked_on'])) ?></td>
<td><?= !empty($r['time_in']) ? date('h:i A', strtotime($r['time_in'])) : '—' ?></td>
<td><?= !empty($r['time_out']) ? date('h:i A', strtotime($r['time_out'])) : '—' ?></td>
<td><?= formatDuration($dur) ?></td>
</tr>
<?php endforeach; ?>
</tbody></table></<?= $d ?>></<?= $d ?>></<?= $d ?>>
<?php require __DIR__ . '/../includes/footer.php'; ?>
