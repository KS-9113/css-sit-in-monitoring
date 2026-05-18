<?php
require_once __DIR__ . '/../includes/functions.php';
requireStudent();
$student = getStudentById((int) $_SESSION['student_id']);
$pageTitle = 'Edit Profile';
require __DIR__ . '/../includes/head.php';
require __DIR__ . '/../includes/student_navbar.php';
$pic = getProfilePictureUrl($student['profile_picture'] ?? null);
$d = 'div';
?>
<<?= $d ?> class="container py-4">
<a href="<?= BASE_URL ?>/student/dashboard.php" class="btn-back mb-3"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
<<?= $d ?> class="card main-card shadow-sm border-0">
<<?= $d ?> class="card-header text-white py-3" style="background:#6f42c1;border-radius:20px 20px 0 0"><h5 class="mb-0 fw-bold">Edit Profile</h5></<?= $d ?>>
<<?= $d ?> class="card-body p-4">
<form method="post" action="<?= BASE_URL ?>/api/profile.php" enctype="multipart/form-data">
<<?= $d ?> class="row g-3">
<<?= $d ?> class="col-12 text-center mb-3">
<img src="<?= htmlspecialchars($pic) ?>" class="profile-avatar rounded-circle" alt="">
<<?= $d ?> class="mt-2"><label class="form-label-custom">Profile Picture</label><input type="file" name="profile_picture" class="form-control" accept="image/*"></<?= $d ?>>
</<?= $d ?>>
<<?= $d ?> class="col-md-4"><label class="form-label-custom">First Name</label><input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($student['first_name']) ?>" required></<?= $d ?>>
<<?= $d ?> class="col-md-4"><label class="form-label-custom">Middle Name</label><input type="text" name="middle_name" class="form-control" value="<?= htmlspecialchars($student['middle_name'] ?? '') ?>"></<?= $d ?>>
<<?= $d ?> class="col-md-4"><label class="form-label-custom">Last Name</label><input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($student['last_name']) ?>" required></<?= $d ?>>
<<?= $d ?> class="col-md-6"><label class="form-label-custom">Email Address</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($student['email']) ?>" required></<?= $d ?>>
<<?= $d ?> class="col-md-6"><label class="form-label-custom">Address</label><input type="text" name="address" class="form-control" value="<?= htmlspecialchars($student['address']) ?>" required></<?= $d ?>>
<<?= $d ?> class="col-12"><hr><h6 class="fw-bold">Change Password (optional)</h6></<?= $d ?>>
<<?= $d ?> class="col-md-6"><label class="form-label-custom">Password</label><div class="input-group"><input type="password" name="password" id="p1" class="form-control"><span class="input-group-text password-toggle" data-target="#p1"><i class="bi bi-eye"></i></span></div></<?= $d ?>>
<<?= $d ?> class="col-md-6"><label class="form-label-custom">Confirm Password</label><<?= $d ?> class="input-group"><input type="password" name="confirm_password" id="p2" class="form-control"><span class="input-group-text password-toggle" data-target="#p2"><i class="bi bi-eye"></i></span></<?= $d ?>></<?= $d ?>>
</<?= $d ?>>
<<?= $d ?> class="d-flex gap-2 mt-4">
<a href="<?= BASE_URL ?>/student/dashboard.php" class="btn btn-outline-secondary">Cancel</a>
<button type="submit" class="btn btn-primary-purple">Save Changes</button>
</<?= $d ?>>
</form>
</<?= $d ?>>
</<?= $d ?>>
</<?= $d ?>>
<?php require __DIR__ . '/../includes/footer.php'; ?>
