<?php
require_once __DIR__ . '/../includes/functions.php';
requireStudent();
$db = getDB();
$notifications = [];
try {
    $stmt = $db->prepare('SELECT n.*, COALESCE(r.status, "Unknown") AS reservation_status, COALESCE(l.lab_name, "") AS lab_name
        FROM notifications n
        LEFT JOIN sit_in_records r ON r.id = n.reservation_id
        LEFT JOIN laboratories l ON l.id = r.laboratory_id
        WHERE n.recipient_type = ? AND n.student_id = ? AND n.is_deleted = 0
        ORDER BY n.created_at DESC');
    $stmt->execute(['student', (int) $_SESSION['student_id']]);
    $notifications = $stmt->fetchAll();
} catch (PDOException $e) {
    $notifications = [];
}
$pageTitle = 'Notifications';
require __DIR__ . '/../includes/head.php';
require __DIR__ . '/../includes/student_navbar.php';
?>
<div class="container-fluid py-4 px-3 px-lg-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="card main-card border-0 shadow-sm p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="fw-bold mb-1">Your Notifications</h4>
                        <p class="small text-muted mb-0">Updates about your reservation status and lab activity.</p>
                    </div>
                </div>
                <?php if (empty($notifications)): ?>
                    <div class="alert alert-secondary">No notifications yet. Your reservation updates will appear here.</div>
                <?php else: ?>
                    <?php foreach ($notifications as $note): ?>
                        <div class="notification-item p-3 mb-3 rounded-4 border shadow-sm">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="fw-semibold mb-1"><?= htmlspecialchars($note['title']) ?></h6>
                                    <p class="small text-muted mb-1"><?= nl2br(htmlspecialchars($note['message'])) ?></p>
                                </div>
                                <span class="text-muted small"><?= date('M d, Y h:i A', strtotime($note['created_at'])) ?></span>
                            </div>
                            <?php if (!empty($note['lab_name']) || !empty($note['reservation_status'])): ?>
                                <p class="small text-muted mb-2">Status: <strong><?= htmlspecialchars($note['reservation_status']) ?></strong><?php if (!empty($note['lab_name'])): ?> • Lab: <strong><?= htmlspecialchars($note['lab_name']) ?></strong><?php endif; ?></p>
                            <?php endif; ?>
                            <form method="post" action="<?= BASE_URL ?>/api/delete_notification.php" class="d-inline">
                                <input type="hidden" name="id" value="<?= (int)$note['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>