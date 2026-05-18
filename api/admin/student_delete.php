<?php
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();
$id = (int) ($_POST['id'] ?? 0);
getDB()->prepare('DELETE FROM students WHERE id = ?')->execute([$id]);
redirect('/admin/students.php?toast=' . urlencode('Student account deleted.'));
