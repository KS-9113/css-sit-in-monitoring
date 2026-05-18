<?php
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$q = trim($_GET['q'] ?? '');
if ($q === '') {
    jsonResponse([]);
}
$db = getDB();
$like = '%' . $q . '%';
$stmt = $db->prepare('SELECT id, id_number, first_name, middle_name, last_name FROM students WHERE remaining_sessions > 0 AND (id_number LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR CONCAT(first_name, " ", last_name) LIKE ?) ORDER BY id_number LIMIT 15');
$stmt->execute([$like, $like, $like, $like]);
$rows = $stmt->fetchAll();
$data = array_map(function ($row) {
    return [
        'id' => $row['id'],
        'id_number' => $row['id_number'],
        'label' => trim($row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . $row['last_name']),
    ];
}, $rows);
jsonResponse($data);
