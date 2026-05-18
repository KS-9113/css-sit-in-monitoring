<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$q = trim($_GET['q'] ?? '');
$sql = 'SELECT * FROM students WHERE 1=1';
$params = [];
if ($q !== '') {
    $sql .= ' AND (id_number LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR course LIKE ? OR year_level LIKE ? OR CONCAT(first_name," ",last_name) LIKE ?)';
    $like = '%' . $q . '%';
    $params = array_fill(0, 6, $like);
}
$sql .= ' ORDER BY last_name, first_name';
$stmt = getDB()->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();
$pageTitle = 'Students';
require __DIR__ . '/../includes/head.php';
require __DIR__ . '/../includes/admin_navbar.php';
$d = 'div';
?>

<<?= $d ?> class="container-fluid py-4 px-4">
<<?= $d ?> class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
<h4 class="fw-bold">Student List</h4>
<form method="post" action="<?= BASE_URL ?>/api/admin/reset_sessions.php" onsubmit="return confirm('Reset ALL students to 30 sessions?')">
<button class="btn btn-warning">Reset Sessions</button></form>
</<?= $d ?>>
<form class="mb-3" method="get"><input type="search" name="q" class="form-control" placeholder="Search name, ID, course, year (e.g. 1st Year)..." value="<?= htmlspecialchars($q) ?>"></form>
<<?= $d ?> class="card main-card border-0 shadow-sm"><<?= $d ?> class="table-scroll card-body p-0">
<table class="table table-hover mb-0"><thead class="table-light"><tr>
<th>ID Number</th><th>Name</th><th>Year Level</th><th>Course</th><th>Remaining Sessions</th><th>Actions</th>
</tr></thead><tbody>
<?php foreach ($students as $s): ?>
<tr>
<td><?= htmlspecialchars($s['id_number']) ?></td>
<td><?= htmlspecialchars(getStudentFullName($s)) ?></td>
<td><?= htmlspecialchars($s['year_level']) ?></td>
<td><?= htmlspecialchars($s['course']) ?></td>
<td><?= (int)$s['remaining_sessions'] ?></td>
<td>
<a href="<?= BASE_URL ?>/admin/edit_student.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
<form method="post" action="<?= BASE_URL ?>/api/admin/student_delete.php" class="d-inline" onsubmit="return confirm('Delete this account?')">
<input type="hidden" name="id" value="<?= $s['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form>
</td></tr>
<?php endforeach; ?>
</tbody></table></<?= $d ?>></<?= $d ?>></<?= $d ?>>
<?php require __DIR__ . '/../includes/footer.php'; ?>
