<?php
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$sql = "SELECT f.created_at, s.id_number, s.first_name, s.last_name, l.lab_name, f.rating, f.comments
        FROM feedback f JOIN students s ON s.id=f.student_id JOIN laboratories l ON l.id=f.laboratory_id WHERE 1=1";
$params = [];
if ($from !== '') { $sql .= ' AND DATE(f.created_at) >= ?'; $params[] = $from; }
if ($to !== '') { $sql .= ' AND DATE(f.created_at) <= ?'; $params[] = $to; }
$sql .= ' ORDER BY f.created_at DESC';
$stmt = getDB()->prepare($sql);
$stmt->execute($params);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="feedback_report.csv"');
$out = fopen('php://output', 'w');
fputcsv($out, ['Date', 'ID Number', 'Name', 'Laboratory', 'Rating', 'Comments']);
while ($row = $stmt->fetch()) {
    fputcsv($out, [
        $row['created_at'],
        $row['id_number'],
        $row['first_name'] . ' ' . $row['last_name'],
        $row['lab_name'],
        $row['rating'],
        $row['comments'],
    ]);
}
fclose($out);
exit;
