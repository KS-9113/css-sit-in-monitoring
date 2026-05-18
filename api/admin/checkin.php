<?php
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$id = (int) ($_POST['id'] ?? 0);
$db = getDB();
$stmt = $db->prepare("SELECT * FROM sit_in_records WHERE id = ? AND status = 'Approved'");
$stmt->execute([$id]);
$record = $stmt->fetch();

if (!$record) {
    redirect('/admin/reservations.php?toast=' . urlencode('Cannot check in this reservation.') . '&toast_type=error');
}
if (isPcOccupied((int)$record['laboratory_id'], (int)$record['pc_number'])) {
    redirect('/admin/reservations.php?toast=' . urlencode('PC is already in use.') . '&toast_type=error');
}

$now = date('Y-m-d H:i:s');
$db->prepare("UPDATE sit_in_records SET status = 'On Going', time_in = ? WHERE id = ?")->execute([$now, $id]);

$returnTo = trim($_POST['return_to'] ?? '');
if ($returnTo !== '') {
    $returnTo = '/' . ltrim($returnTo, '/');
    redirect($returnTo . (strpos($returnTo, '?') === false ? '?toast=' . urlencode('Student checked in.') : '&toast=' . urlencode('Student checked in.')));
}
redirect('/admin/sitin.php?lab_id=' . (int)$record['laboratory_id'] . '&toast=' . urlencode('Student checked in.'));
