<?php
require_once __DIR__ . '/../includes/functions.php';

$labId = (int) ($_GET['lab_id'] ?? 1);
$db = getDB();
$occupied = $db->prepare("SELECT id, pc_number FROM sit_in_records WHERE laboratory_id = ? AND status = 'On Going'");
$occupied->execute([$labId]);
$occupiedPcs = [];
foreach ($occupied->fetchAll() as $row) {
    $occupiedPcs[$row['pc_number']] = $row['id'];
}

$reserved = $db->prepare("SELECT pc_number FROM sit_in_records WHERE laboratory_id = ? AND scheduled_date = CURDATE() AND status IN ('Reserved', 'Approved') AND id NOT IN (SELECT id FROM sit_in_records WHERE status = 'On Going')");
$reserved->execute([$labId]);
$reservedPcs = array_column($reserved->fetchAll(), 'pc_number');

$pcs = [];
for ($i = 1; $i <= 50; $i++) {
    if (isset($occupiedPcs[$i])) {
        $pcs[] = ['pc' => $i, 'status' => 'occupied', 'record_id' => $occupiedPcs[$i]];
    } elseif (in_array($i, $reservedPcs, true)) {
        $pcs[] = ['pc' => $i, 'status' => 'reserved'];
    } else {
        $pcs[] = ['pc' => $i, 'status' => 'available'];
    }
}

header('Content-Type: application/json');
echo json_encode(['pcs' => $pcs]);
