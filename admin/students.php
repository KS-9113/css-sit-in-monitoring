<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$q = trim($_GET['q'] ?? '');
$courseFilter = trim($_GET['course'] ?? '');
$yearFilter = trim($_GET['year'] ?? '');
$sectionFilter = trim($_GET['section'] ?? '');
$sql = 'SELECT * FROM students WHERE 1=1';
$params = [];
if ($q !== '') {
    $sql .= ' AND (id_number LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR course LIKE ? OR year_level LIKE ? OR CONCAT(first_name," ",last_name) LIKE ?)';
    $like = '%' . $q . '%';
    $params = array_fill(0, 6, $like);
}
if ($courseFilter !== '') {
    $sql .= ' AND course = ?';
    $params[] = $courseFilter;
}
if ($yearFilter !== '') {
    $sql .= ' AND year_level = ?';
    $params[] = $yearFilter;
}
$hasSection = tableHasColumn('students', 'section');
if ($hasSection && $sectionFilter !== '') {
    $sql .= ' AND section = ?';
    $params[] = $sectionFilter;
}
$sql .= ' ORDER BY last_name, first_name';
$stmt = getDB()->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();
$courses = getDB()->query('SELECT DISTINCT course FROM students WHERE course != "" ORDER BY course')->fetchAll(PDO::FETCH_COLUMN);
$years = getDB()->query('SELECT DISTINCT year_level FROM students WHERE year_level != "" ORDER BY year_level')->fetchAll(PDO::FETCH_COLUMN);
$sections = $hasSection ? getDB()->query('SELECT DISTINCT section FROM students WHERE section IS NOT NULL AND section != "" ORDER BY section')->fetchAll(PDO::FETCH_COLUMN) : [];
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
<form class="row g-3 mb-3" method="get">
    <<?= $d ?> class="col-md-4"><input type="search" name="q" class="form-control" placeholder="Search name, ID, course, year..." value="<?= htmlspecialchars($q) ?>"></<?= $d ?>>
    <<?= $d ?> class="col-md-2"><select name="course" class="form-select"><option value="">All Courses</option><?php foreach ($courses as $course): ?><option value="<?= htmlspecialchars($course) ?>" <?= $course === $courseFilter ? 'selected' : '' ?>><?= htmlspecialchars($course) ?></option><?php endforeach; ?></select></<?= $d ?>>
    <<?= $d ?> class="col-md-2"><select name="year" class="form-select"><option value="">All Years</option><?php foreach ($years as $year): ?><option value="<?= htmlspecialchars($year) ?>" <?= $year === $yearFilter ? 'selected' : '' ?>><?= htmlspecialchars($year) ?></option><?php endforeach; ?></select></<?= $d ?>>
    <?php if ($hasSection): ?>
    <<?= $d ?> class="col-md-2"><select name="section" class="form-select"><option value="">All Sections</option><?php foreach ($sections as $section): ?><option value="<?= htmlspecialchars($section) ?>" <?= $section === $sectionFilter ? 'selected' : '' ?>><?= htmlspecialchars($section) ?></option><?php endforeach; ?></select></<?= $d ?>>
    <?php endif; ?>
    <<?= $d ?> class="col-md-2 d-grid"><button type="submit" class="btn btn-primary-purple">Filter</button></<?= $d ?>>
</form>
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
