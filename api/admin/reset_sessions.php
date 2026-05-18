<?php
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();
getDB()->exec('UPDATE students SET remaining_sessions = 30');
redirect('/admin/students.php?toast=' . urlencode('All student sessions reset to 30 successfully.'));
