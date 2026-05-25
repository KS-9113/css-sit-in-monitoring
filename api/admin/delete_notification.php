<?php
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        archiveNotification($id, 'admin');
    }
}
redirect('/admin/notifications.php');