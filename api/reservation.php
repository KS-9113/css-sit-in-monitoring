<?php
require_once __DIR__ . '/../includes/functions.php';
requireStudent();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/student/reservation.php');
}

$studentId = (int) $_SESSION['student_id'];
$student = getStudentById($studentId);

if ((int) $student['remaining_sessions'] <= 0) {
    redirect('/student/reservation.php?toast=' . urlencode('No remaining sessions.') . '&toast_type=error');
}
if (studentHasActiveReservation($studentId)) {
    redirect('/student/reservation.php?toast=' . urlencode('You already have an active reservation.') . '&toast_type=error');
}

$purpose = $_POST['purpose'] ?? '';
$labId = (int) ($_POST['laboratory_id'] ?? 0);
$pc = (int) ($_POST['pc_number'] ?? 0);
$date = $_POST['scheduled_date'] ?? '';
$timeIn = $_POST['scheduled_time_in'] ?? '';

if (!$purpose || !$labId || $pc < 1 || $pc > 50 || !$date || !$timeIn) {
    redirect('/student/reservation.php?toast=' . urlencode('Please complete all fields.') . '&toast_type=error');
}

if (isPcOccupied($labId, $pc) || isPcBooked($labId, $pc, $date)) {
    redirect('/student/reservation.php?toast=' . urlencode('Selected PC is not available.') . '&toast_type=error');
}

$db = getDB();
$stmt = $db->prepare('INSERT INTO sit_in_records (sit_in_no, student_id, purpose, laboratory_id, pc_number, scheduled_date, scheduled_time_in, status) VALUES (?,?,?,?,?,?,?,?)');
$stmt->execute([generateSitInNo(), $studentId, $purpose, $labId, $pc, $date, $timeIn, 'Reserved']);
$reservationId = (int) $db->lastInsertId();
createNotification(
    'admin',
    null,
    null,
    $reservationId,
    'New Sit-In Reservation',
    'A new reservation request was submitted for lab booking. Review and approve or reject it from the Admin Notifications page.',
    true
);

redirect('/student/history.php?toast=' . urlencode('Reservation submitted. Awaiting approval.'));
