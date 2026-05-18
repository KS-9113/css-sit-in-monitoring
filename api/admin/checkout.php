<?php
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$id = (int) ($_POST['id'] ?? 0);
$db = getDB();
$stmt = $db->prepare("SELECT * FROM sit_in_records WHERE id = ? AND status = 'On Going'");
$stmt->execute([$id]);
$record = $stmt->fetch();

if ($record) {
    $now = date('Y-m-d H:i:s');
    $dur = computeDuration($record['time_in'], $now);
    $db->prepare("UPDATE sit_in_records SET time_out = ?, duration_minutes = ?, status = 'Completed' WHERE id = ?")
        ->execute([$now, $dur, $id]);
    deductSessionIfNeeded($id);
}

$returnTo = trim($_POST['return_to'] ?? '');
if ($returnTo !== '') {
    $returnTo = '/' . ltrim($returnTo, '/');
    redirect($returnTo . (strpos($returnTo, '?') === false ? '?toast=' . urlencode('Session completed.') : '&toast=' . urlencode('Session completed.')));
}
redirect('/admin/sitin.php?lab_id=' . (int)($record['laboratory_id'] ?? $_GET['lab_id'] ?? 1) . '&toast=' . urlencode('Session completed.'));
