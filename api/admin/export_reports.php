<?php
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$from = trim($_GET['from'] ?? '');
$to = trim($_GET['to'] ?? '');
$sql = "SELECT r.*, s.id_number, s.first_name, s.middle_name, s.last_name, l.lab_name
        FROM sit_in_records r
        JOIN students s ON s.id = r.student_id
        JOIN laboratories l ON l.id = r.laboratory_id
        WHERE r.status = 'Completed'";
$params = [];
if ($from !== '') { $sql .= ' AND DATE(r.time_in) >= ?'; $params[] = $from; }
if ($to !== '') { $sql .= ' AND DATE(r.time_in) <= ?'; $params[] = $to; }
$sql .= ' ORDER BY r.time_in DESC';
$stmt = getDB()->prepare($sql);
$stmt->execute($params);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="sit_in_reports.csv"');
$out = fopen('php://output', 'w');
fputcsv($out, ['ID Number', 'Name', 'Course', 'Year Level', 'Lab', 'PC Number', 'Time In', 'Time Out', 'Duration Minutes', 'Booked On']);
while ($row = $stmt->fetch()) {
    fputcsv($out, [
        $row['id_number'],
        trim($row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . $row['last_name']),
        $row['course'],
        $row['year_level'],
        $row['lab_name'],
        $row['pc_number'],
        $row['time_in'],
        $row['time_out'],
        $row['duration_minutes'],
        $row['booked_on'],
    ]);
}
fclose($out);
exit;
