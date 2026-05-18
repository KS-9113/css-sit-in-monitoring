<?php
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$q = trim($_GET['q'] ?? '');
$from = trim($_GET['from'] ?? '');
$to = trim($_GET['to'] ?? '');
$sql = "SELECT r.*, s.id_number, s.first_name, s.middle_name, s.last_name, s.course, s.year_level, l.lab_name
        FROM sit_in_records r
        JOIN students s ON s.id = r.student_id
        JOIN laboratories l ON l.id = r.laboratory_id WHERE 1=1";
$params = [];
if ($q !== '') {
    $sql .= ' AND (s.id_number LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ? OR s.year_level LIKE ? OR CONCAT(s.first_name," ",s.last_name) LIKE ?)';
    $like = '%'.$q.'%';
    $params = array_fill(0, 5, $like);
}
if ($from !== '') { $sql .= ' AND DATE(r.booked_on) >= ?'; $params[] = $from; }
if ($to !== '') { $sql .= ' AND DATE(r.booked_on) <= ?'; $params[] = $to; }
$sql .= ' ORDER BY r.booked_on DESC';
$stmt = getDB()->prepare($sql);
$stmt->execute($params);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="sit_in_records.csv"');
$out = fopen('php://output', 'w');
fputcsv($out, ['ID Number', 'Name', 'Course', 'Year Level', 'Lab', 'PC Number', 'Booked On', 'Status', 'Time In', 'Time Out', 'Duration Minutes']);
while ($row = $stmt->fetch()) {
    fputcsv($out, [
        $row['id_number'],
        trim($row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . $row['last_name']),
        $row['course'],
        $row['year_level'],
        $row['lab_name'],
        $row['pc_number'],
        $row['booked_on'],
        $row['status'],
        $row['time_in'],
        $row['time_out'],
        $row['duration_minutes'],
    ]);
}
fclose($out);
exit;
