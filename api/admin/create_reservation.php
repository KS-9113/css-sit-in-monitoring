<?php
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$studentId = (int) ($_POST['student_id'] ?? 0);
$purpose = $_POST['purpose'] ?? '';
$labId = (int) ($_POST['laboratory_id'] ?? 0);
$pc = (int) ($_POST['pc_number'] ?? 0);
$date = $_POST['scheduled_date'] ?? '';
$timeIn = $_POST['scheduled_time_in'] ?? '';

if (studentHasActiveReservation($studentId)) {
    redirect('/admin/dashboard.php?toast=' . urlencode('Student already has an active reservation.') . '&toast_type=error');
}

if (isPcOccupied($labId, $pc) || isPcBooked($labId, $pc, $date)) {
    redirect('/admin/dashboard.php?toast=' . urlencode('PC not available.') . '&toast_type=error');
}

$db = getDB();
$db->prepare('INSERT INTO sit_in_records (sit_in_no, student_id, purpose, laboratory_id, pc_number, scheduled_date, scheduled_time_in, status, approved_by) VALUES (?,?,?,?,?,?,?,?,?)')
    ->execute([generateSitInNo(), $studentId, $purpose, $labId, $pc, $date, $timeIn, 'Approved', $_SESSION['admin_id']]);
$reservationId = (int) $db->lastInsertId();
createNotification(
    'student',
    $studentId,
    null,
    $reservationId,
    'Reservation Created',
    'Your reservation has been created and approved by the administrator.',
    false
);

redirect('/admin/dashboard.php?toast=' . urlencode('Reservation created and approved for student.'));
