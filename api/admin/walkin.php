<?php
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$studentId = (int) ($_POST['student_id'] ?? 0);
$purpose = $_POST['purpose'] ?? '';
$labId = (int) ($_POST['laboratory_id'] ?? 0);
$pc = (int) ($_POST['pc_number'] ?? 0);

$student = getStudentById($studentId);
if (!$student || (int)$student['remaining_sessions'] <= 0) {
    redirect('/admin/sitin.php?toast=' . urlencode('Invalid student or no sessions left.') . '&toast_type=error');
}
if (isPcOccupied($labId, $pc)) {
    redirect('/admin/sitin.php?toast=' . urlencode('PC is currently in use.') . '&toast_type=error');
}

$db = getDB();
$now = date('Y-m-d H:i:s');
$db->prepare('INSERT INTO sit_in_records (sit_in_no, student_id, purpose, laboratory_id, pc_number, scheduled_date, scheduled_time_in, time_in, status, is_walk_in, approved_by) VALUES (?,?,?,?,?,CURDATE(),CURTIME(),?, ?, 1, ?)')
    ->execute([generateSitInNo(), $studentId, $purpose, $labId, $pc, $now, 'On Going', $_SESSION['admin_id']]);

redirect('/admin/sitin.php?toast=' . urlencode('Walk-in session started (auto-approved).'));
