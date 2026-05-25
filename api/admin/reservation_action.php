<?php
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$id = (int) ($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

$stmt = getDB()->prepare("SELECT * FROM sit_in_records WHERE id = ? AND status = 'Reserved'");
$stmt->execute([$id]);
$record = $stmt->fetch();

if (!$record) {
    redirect('/admin/reservations.php?toast=' . urlencode('Reservation not found.') . '&toast_type=error');
}

if ($action === 'accept') {
    getDB()->prepare("UPDATE sit_in_records SET status = 'Approved', approved_by = ? WHERE id = ?")
        ->execute([$_SESSION['admin_id'], $id]);
    createNotification(
        'student',
        $record['student_id'],
        null,
        $id,
        'Reservation Approved',
        'Congrats! Your reservation with us is accepted and acknowledged by our Laboratory Supervisor. Happy learning!',
        false
    );
    $msg = 'Reservation accepted.';
} elseif ($action === 'reject') {
    getDB()->prepare("UPDATE sit_in_records SET status = 'Rejected' WHERE id = ?")->execute([$id]);
    createNotification(
        'student',
        $record['student_id'],
        null,
        $id,
        'Reservation Rejected',
        'We are sorry, your reservation was rejected. Please review your request and try again or contact the lab supervisor.',
        false
    );
    $msg = 'Reservation rejected.';
} else {
    $msg = 'Invalid action.';
}

redirect('/admin/reservations.php?toast=' . urlencode($msg));
