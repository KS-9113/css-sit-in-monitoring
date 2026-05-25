<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$db = getDB();
$notifications = [];
try {
    $stmt = $db->prepare('SELECT n.*, r.status AS reservation_status, r.scheduled_date, r.scheduled_time_in, s.id_number, s.first_name, s.middle_name, s.last_name, l.lab_name
        FROM notifications n
        LEFT JOIN sit_in_records r ON r.id = n.reservation_id
        LEFT JOIN students s ON s.id = r.student_id
        LEFT JOIN laboratories l ON l.id = r.laboratory_id
        WHERE n.recipient_type = ? AND n.is_deleted = 0
        ORDER BY n.created_at DESC');
    $stmt->execute(['admin']);
    $notifications = $stmt->fetchAll();
} catch (PDOException $e) {
    $notifications = [];
}
$pageTitle = 'Admin Notifications';
require __DIR__ . '/../includes/head.php';
require __DIR__ . '/../includes/admin_navbar.php';
?>
<div class="container-fluid py-4 px-4">
    <h4 class="fw-bold mb-4">Admin Notifications</h4>
    <div class="row g-4">
        <div class="col-12">
            <div class="card main-card border-0 shadow-sm p-4">
                <h5 class="fw-bold mb-3">Reservation Alerts</h5>
                <?php if (empty($notifications)): ?>
                    <div class="alert alert-secondary">No reservation notifications at this time.</div>
                <?php else: ?>
                    <?php foreach ($notifications as $note): ?>
                        <div class="notification-item p-3 mb-3 rounded-4 border shadow-sm">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="fw-semibold mb-1"><?= htmlspecialchars($note['title']) ?></h6>
                                    <p class="text-muted small mb-1"><?= nl2br(htmlspecialchars($note['message'])) ?></p>
                                </div>
                                <span class="text-muted small"><?= date('M d, Y h:i A', strtotime($note['created_at'])) ?></span>
                            </div>
                            <?php if ($note['reservation_id'] && $note['lab_name']): ?>
                                <div class="mb-3 small text-muted">Reservation for <strong><?= htmlspecialchars($note['id_number']) ?> - <?= htmlspecialchars(trim($note['first_name'] . ' ' . $note['middle_name'] . ' ' . $note['last_name'])) ?></strong> in <strong><?= htmlspecialchars($note['lab_name']) ?></strong> on <strong><?= date('M d, Y', strtotime($note['scheduled_date'])) ?> <?= date('h:i A', strtotime($note['scheduled_time_in'])) ?></strong>.</div>
                            <?php endif; ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php if ($note['reservation_status'] === 'Reserved'): ?>
                                    <form method="post" action="<?= BASE_URL ?>/api/admin/reservation_action.php" class="d-inline">
                                        <input type="hidden" name="id" value="<?= (int)$note['reservation_id'] ?>">
                                        <input type="hidden" name="action" value="accept">
                                        <button type="submit" class="btn btn-sm btn-success">Accept</button>
                                    </form>
                                    <form method="post" action="<?= BASE_URL ?>/api/admin/reservation_action.php" class="d-inline">
                                        <input type="hidden" name="id" value="<?= (int)$note['reservation_id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-secondary ms-auto" disabled>Delete</button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-secondary" disabled>Accept</button>
                                    <button type="button" class="btn btn-sm btn-secondary" disabled>Reject</button>
                                    <form method="post" action="<?= BASE_URL ?>/api/admin/delete_notification.php" class="d-inline ms-auto">
                                        <input type="hidden" name="id" value="<?= (int)$note['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>