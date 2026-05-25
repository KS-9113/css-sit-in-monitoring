<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$id = (int) ($_GET['id'] ?? 0);
$s = getStudentById($id);
if (!$s) redirect('/admin/students.php');
$pageTitle = 'Edit Student';
require __DIR__ . '/../includes/head.php';
require __DIR__ . '/../includes/admin_navbar.php';
$d = 'div';
?>
<<?= $d ?> class="container py-4" style="max-width:800px">
<a href="<?= BASE_URL ?>/admin/students.php" class="btn-back mb-3"><i class="bi bi-arrow-left"></i> Back</a>
<<?= $d ?> class="card main-card border-0 shadow-sm p-4">
<h4 class="fw-bold mb-3">Edit Student Account</h4>
<form method="post" action="<?= BASE_URL ?>/api/admin/student_save.php">
<input type="hidden" name="id" value="<?= $id ?>">
<<?= $d ?> class="row g-3">
<<?= $d ?> class="col-md-6"><label class="form-label-custom">ID Number</label><input name="id_number" class="form-control" value="<?= htmlspecialchars($s['id_number']) ?>" required></<?= $d ?>>
<<?= $d ?> class="col-md-6"><label class="form-label-custom">Email</label><input name="email" type="email" class="form-control" value="<?= htmlspecialchars($s['email']) ?>" required></<?= $d ?>>
<<?= $d ?> class="col-md-4"><label class="form-label-custom">First Name</label><input name="first_name" class="form-control" value="<?= htmlspecialchars($s['first_name']) ?>" required></<?= $d ?>>
<<?= $d ?> class="col-md-4"><label class="form-label-custom">Middle Name</label><input name="middle_name" class="form-control" value="<?= htmlspecialchars($s['middle_name'] ?? '') ?>"></<?= $d ?>>
<<?= $d ?> class="col-md-4"><label class="form-label-custom">Last Name</label><input name="last_name" class="form-control" value="<?= htmlspecialchars($s['last_name']) ?>" required></<?= $d ?>>
<<?= $d ?> class="col-md-4"><label class="form-label-custom">Course</label><input name="course" class="form-control" value="<?= htmlspecialchars($s['course']) ?>"></<?= $d ?>>
<<?= $d ?> class="col-md-4"><label class="form-label-custom">Year Level</label><input name="year_level" class="form-control" value="<?= htmlspecialchars($s['year_level']) ?>"></<?= $d ?>><<?= $d ?> class="col-md-4"><label class="form-label-custom">Section</label><input name="section" class="form-control" value="<?= htmlspecialchars($s['section'] ?? '') ?>"></<?= $d ?><<?= $d ?> class="col-md-4"><label class="form-label-custom">Remaining Sessions</label><input name="remaining_sessions" type="number" class="form-control" value="<?= (int)$s['remaining_sessions'] ?>"></<?= $d ?>>
<<?= $d ?> class="col-12"><label class="form-label-custom">Address</label><input name="address" class="form-control" value="<?= htmlspecialchars($s['address']) ?>"></<?= $d ?>>
<<?= $d ?> class="col-md-6"><label class="form-label-custom">New Password (optional)</label><input name="password" type="password" class="form-control"></<?= $d ?>>
</<?= $d ?>>
<button class="btn btn-primary-purple mt-3">Save Changes</button>
</form>
</<?= $d ?>></<?= $d ?>>
<?php require __DIR__ . '/../includes/footer.php'; ?>
