<?php
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$labId = (int) ($_GET['lab_id'] ?? 1);
$pcNumber = (int) ($_GET['pc_number'] ?? 0);

if ($labId <= 0 || $pcNumber <= 0) {
    jsonResponse(['error' => 'Invalid lab or PC number'], 400);
}

$db = getDB();

$occupied = $db->prepare("SELECT r.*, s.id_number, s.first_name, s.middle_name, s.last_name, l.lab_name FROM sit_in_records r JOIN students s ON s.id = r.student_id JOIN laboratories l ON l.id = r.laboratory_id WHERE r.laboratory_id = ? AND r.pc_number = ? AND r.status = 'On Going' LIMIT 1");
$occupied->execute([$labId, $pcNumber]);
$occupiedRecord = $occupied->fetch();

$reserved = $db->prepare("SELECT r.*, s.id_number, s.first_name, s.middle_name, s.last_name, l.lab_name FROM sit_in_records r JOIN students s ON s.id = r.student_id JOIN laboratories l ON l.id = r.laboratory_id WHERE r.laboratory_id = ? AND r.pc_number = ? AND r.scheduled_date = CURDATE() AND r.status IN ('Reserved', 'Approved') AND r.id NOT IN (SELECT id FROM sit_in_records WHERE status = 'On Going') ORDER BY r.scheduled_time_in");
$reserved->execute([$labId, $pcNumber]);
$reservedRecords = $reserved->fetchAll();

$response = [
    'occupied' => null,
    'reserved' => []
];

if ($occupiedRecord) {
    $durationMinutes = computeDuration($occupiedRecord['time_in'], date('Y-m-d H:i:s'));
    $response['occupied'] = [
        'id' => (int)$occupiedRecord['id'],
        'student_name' => trim($occupiedRecord['first_name'] . ' ' . ($occupiedRecord['middle_name'] ? $occupiedRecord['middle_name'] . ' ' : '') . $occupiedRecord['last_name']),
        'student_id' => $occupiedRecord['id_number'],
        'purpose' => $occupiedRecord['purpose'],
        'lab_name' => $occupiedRecord['lab_name'],
        'scheduled_date' => $occupiedRecord['scheduled_date'],
        'scheduled_time_in' => $occupiedRecord['scheduled_time_in'],
        'time_in' => $occupiedRecord['time_in'],
        'duration_minutes' => $durationMinutes
    ];
}

foreach ($reservedRecords as $r) {
    $response['reserved'][] = [
        'id' => (int)$r['id'],
        'student_name' => trim($r['first_name'] . ' ' . ($r['middle_name'] ? $r['middle_name'] . ' ' : '') . $r['last_name']),
        'student_id' => $r['id_number'],
        'purpose' => $r['purpose'],
        'status' => $r['status'],
        'scheduled_date' => $r['scheduled_date'],
        'scheduled_time_in' => $r['scheduled_time_in']
    ];
}

jsonResponse($response);
