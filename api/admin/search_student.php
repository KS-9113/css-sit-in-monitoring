<?php
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$idNumber = trim($_GET['id_number'] ?? '');
if ($idNumber === '') {
    echo '<p class="text-muted">Enter an ID number.</p>';
    exit;
}

$db = getDB();
$stmt = $db->prepare('SELECT * FROM students WHERE id_number = ?');
$stmt->execute([$idNumber]);
$student = $stmt->fetch();

if (!$student) {
    echo '<p class="text-danger">Student not found.</p>';
    exit;
}

$name = htmlspecialchars(getStudentFullName($student));
$close = '</' . 'div>';
$open = '<' . 'div';
echo "{$open} class='border rounded p-3 mb-3'><h6 class='fw-bold'>{$name}</h6>";
echo "<p class='small mb-1'>ID: " . htmlspecialchars($student['id_number']) . "</p>";
echo "<p class='small mb-1'>Course: " . htmlspecialchars($student['course']) . " | Year: " . htmlspecialchars($student['year_level']) . "</p>";
echo "<p class='small mb-0'>Remaining Sessions: <strong>" . (int)$student['remaining_sessions'] . "</strong></p>{$close}";

$stmt = $db->prepare("SELECT r.*, l.lab_name FROM sit_in_records r JOIN laboratories l ON l.id=r.laboratory_id WHERE r.student_id=? AND r.status IN ('Reserved','Approved','On Going') ORDER BY r.scheduled_date, r.scheduled_time_in");
$stmt->execute([$student['id']]);
$reservations = $stmt->fetchAll();

if ($reservations) {
    echo '<h6 class="fw-bold mt-3">Active / Upcoming Reservations</h6>';
    foreach ($reservations as $r) {
        $badge = match($r['status']) {
            'Approved' => 'success',
            'On Going' => 'primary',
            default => 'info'
        };
        echo "{$open} class='border rounded p-2 mb-2 small'>";
        echo "<span class='badge bg-{$badge}'>" . htmlspecialchars($r['status']) . "</span> ";
        echo htmlspecialchars($r['lab_name']) . " PC " . (int)$r['pc_number'];
        echo " — " . date('M d, Y h:i A', strtotime($r['scheduled_date'].' '.$r['scheduled_time_in']));
        echo $close;
    }
} else {
    echo '<p class="text-muted small mt-3">No active reservation. Create one below.</p>';
    $labs = $db->query('SELECT id, lab_name FROM laboratories')->fetchAll();
    echo '<form method="post" action="' . BASE_URL . '/api/admin/create_reservation.php" class="row g-2">';
    echo '<input type="hidden" name="student_id" value="' . (int)$student['id'] . '">';
    echo "{$open} class='col-md-6'><select name='purpose' class='form-select form-select-sm' required><option value=''>Purpose</option>";
    foreach ([
        'C# Programming',
        'TypeScript Programming',
        'Python Programming',
        'PHP Programming',
        'JavaScript Programming',
        'Java Programming',
        'C++ Programming',
        'Others'
    ] as $p) {
        echo '<option>' . htmlspecialchars($p) . '</option>';
    }
    echo "</select>{$close}{$open} class='col-md-6'><select name='laboratory_id' class='form-select form-select-sm' required>";
    foreach ($labs as $l) {
        echo '<option value="'.$l['id'].'">'.htmlspecialchars($l['lab_name']).'</option>';
    }
    echo "</select>{$close}{$open} class='col-4'><select name='pc_number' class='form-select form-select-sm' required>";
    for ($i = 1; $i <= 50; $i++) {
        echo "<option value='$i'>PC $i</option>";
    }
    echo "</select>{$close}{$open} class='col-4'><input type='date' name='scheduled_date' class='form-control form-control-sm' required>{$close}";
    echo "{$open} class='col-4'><input type='time' name='scheduled_time_in' class='form-control form-control-sm' required>{$close}";
    echo "{$open} class='col-12'><button class='btn btn-sm btn-primary-purple'>Create Reservation for Student</button>{$close}</form>";
}
