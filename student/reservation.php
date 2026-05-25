<?php
require_once __DIR__ . '/../includes/functions.php';
requireStudent();
$student = getStudentById((int) $_SESSION['student_id']);
$labs = getDB()->query('SELECT * FROM laboratories WHERE is_active = 1 ORDER BY lab_name')->fetchAll();
$hasActive = studentHasActiveReservation($student['id']);
$pageTitle = 'Booking Reservation';
require __DIR__ . '/../includes/head.php';
$d = 'div';
?>
<nav class="navbar navbar-dark navbar-purple">
<<?= $d ?> class="container-fluid px-4">
<span class="navbar-brand navbar-brand-custom">Booking Reservation</span>
<a href="<?= BASE_URL ?>/student/dashboard.php" class="btn btn-light btn-sm">Back to Dashboard</a>
</<?= $d ?>>
</nav>
<<?= $d ?> class="container py-4">
<<?= $d ?> class="card main-card shadow-sm border-0 mx-auto" style="max-width:720px">
<<?= $d ?> class="card-header text-white py-3" style="background:#6f42c1"><h5 class="mb-0 fw-bold">Book A Sit-In Session</h5><small class="opacity-90">Please fill out the details below to reserve your spot</small></<?= $d ?>>
<<?= $d ?> class="card-body p-4">
<?php if ($hasActive): ?>
<<?= $d ?> class="alert alert-warning">You already have an active reservation. Complete or cancel it before booking another.</<?= $d ?>>
<?php elseif ((int)$student['remaining_sessions'] <= 0): ?>
<<?= $d ?> class="alert alert-danger">No remaining sessions. Contact the lab administrator.</<?= $d ?>>
<?php else: ?>
<form method="post" action="<?= BASE_URL ?>/api/reservation.php">
<<?= $d ?> class="row g-3">
<<?= $d ?> class="col-md-4"><label class="form-label-custom">ID Number</label><input class="form-control" value="<?= htmlspecialchars($student['id_number']) ?>" readonly></<?= $d ?>>
<<?= $d ?> class="col-md-4"><label class="form-label-custom">Student Name</label><input class="form-control" value="<?= htmlspecialchars(getStudentFullName($student)) ?>" readonly></<?= $d ?>>
<<?= $d ?> class="col-md-4"><label class="form-label-custom">Remaining Sessions</label><input class="form-control" value="<?= (int)$student['remaining_sessions'] ?>" readonly></<?= $d ?>>
<<?= $d ?> class="col-md-6"><label class="form-label-custom">Purpose of Sit-In</label>
<select name="purpose" class="form-select" required>
<option value="">Select purpose</option>
<?php foreach ([
    'C# Programming',
    'TypeScript Programming',
    'Python Programming',
    'PHP Programming',
    'JavaScript Programming',
    'Java Programming',
    'C++ Programming',
    'Others'
] as $p): ?>
<option><?= $p ?></option>
<?php endforeach; ?>
</select></<?= $d ?>>
<<?= $d ?> class="col-md-6"><label class="form-label-custom">Laboratory room</label>
<select name="laboratory_id" id="labSelect" class="form-select" required>
<option value="">Select laboratory</option>
<?php foreach ($labs as $lab): ?><option value="<?= $lab['id'] ?>"><?= htmlspecialchars($lab['lab_name']) ?></option><?php endforeach; ?>
</select></<?= $d ?>>
<<?= $d ?> class="col-md-4"><label class="form-label-custom">PC number</label>
<select name="pc_number" id="pcSelect" class="form-select" required><option value="">Select PC</option><?php for($i=1;$i<=50;$i++): ?><option value="<?= $i ?>">PC <?= $i ?></option><?php endfor; ?></select></<?= $d ?>>
<<?= $d ?> class="col-md-4"><label class="form-label-custom">Preferred Date</label><input type="date" name="scheduled_date" class="form-control" min="<?= date('Y-m-d') ?>" required></<?= $d ?>>
<<?= $d ?> class="col-md-4"><label class="form-label-custom">Time-In</label><input type="time" name="scheduled_time_in" class="form-control" required></<?= $d ?>>
</<?= $d ?>>
<button type="submit" class="btn btn-primary-purple w-100 mt-3">Confirm Reservation</button>
</form>
<?php endif; ?>
</<?= $d ?>></<?= $d ?>></<?= $d ?>>
<?php require __DIR__ . '/../includes/footer.php'; ?>
