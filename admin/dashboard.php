<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'create_announcement') {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $startDate = $_POST['start_date'] ?? date('Y-m-d');
        $endDate = $_POST['end_date'] ?? date('Y-m-d');
        if ($title !== '' && $content !== '' && $startDate !== '' && $endDate !== '') {
            $stmt = $db->prepare('INSERT INTO announcements (title, content, start_date, end_date, created_by) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$title, $content, $startDate, $endDate, $_SESSION['admin_id']]);
            redirect('/admin/dashboard.php?toast=' . urlencode('Announcement created.'));
        }
    }
    if (isset($_POST['action']) && $_POST['action'] === 'delete_announcement') {
        $id = (int) ($_POST['announcement_id'] ?? 0);
        $stmt = $db->prepare('UPDATE announcements SET end_date = CURDATE() WHERE id = ?');
        $stmt->execute([$id]);
        redirect('/admin/dashboard.php?toast=' . urlencode('Announcement removed.'));
    }
}

$stats = [
    'students' => (int) $db->query('SELECT COUNT(*) FROM students')->fetchColumn(),
    'pending' => (int) $db->query("SELECT COUNT(*) FROM sit_in_records WHERE status = 'Reserved'")->fetchColumn(),
    'ongoing' => (int) $db->query("SELECT COUNT(*) FROM sit_in_records WHERE status = 'On Going'")->fetchColumn(),
    'today_hours' => (int) $db->query("SELECT COALESCE(SUM(duration_minutes),0) FROM sit_in_records WHERE status='Completed' AND DATE(time_out)=CURDATE()")->fetchColumn(),
];

$totalPcs = (int) $db->query('SELECT COALESCE(SUM(pc_count),0) FROM laboratories')->fetchColumn();
$activeOnGoing = (int) $db->query("SELECT COUNT(*) FROM sit_in_records WHERE status = 'On Going'")->fetchColumn();
$busyPercent = $totalPcs > 0 ? min(100, round($activeOnGoing / $totalPcs * 100)) : 0;
$busyColor = $busyPercent >= 80 ? 'danger' : ($busyPercent >= 50 ? 'warning' : 'success');

$purposeCounts = $db->query("SELECT purpose, COUNT(*) AS cnt FROM sit_in_records WHERE scheduled_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY purpose ORDER BY cnt DESC")->fetchAll();
$being = $middle = $notSo = 0;
foreach ($purposeCounts as $row) {
    if ($row['cnt'] >= 8) {
        $being += $row['cnt'];
    } elseif ($row['cnt'] >= 4) {
        $middle += $row['cnt'];
    } else {
        $notSo += $row['cnt'];
    }
}
if ($being + $middle + $notSo === 0) {
    $notSo = 1;
}

$announcements = $db->query('SELECT a.*, COALESCE(ad.full_name, "Admin") AS admin_name FROM announcements a LEFT JOIN admins ad ON ad.id = a.created_by ORDER BY a.created_at DESC')->fetchAll();

$pageTitle = 'Admin Dashboard';
require __DIR__ . '/../includes/head.php';
require __DIR__ . '/../includes/admin_navbar.php';
$d = 'div';
?>
<<?= $d ?> class="container-fluid py-4 px-4">
    <<?= $d ?> class="row g-4 mb-4">
        <<?= $d ?> class="col-md-3"><<?= $d ?> class="card main-card p-4 text-center border-0 shadow-sm"><h2 class="text-primary fw-bold"><?= $stats['students'] ?></h2><p class="mb-0 text-muted">Total Students</p></<?= $d ?>></<?= $d ?>>
        <<?= $d ?> class="col-md-3"><<?= $d ?> class="card main-card p-4 text-center border-0 shadow-sm"><h2 class="text-warning fw-bold"><?= $stats['pending'] ?></h2><p class="mb-0 text-muted">Pending Reservations</p></<?= $d ?>></<?= $d ?>>
        <<?= $d ?> class="col-md-3"><<?= $d ?> class="card main-card p-4 text-center border-0 shadow-sm"><h2 class="text-success fw-bold"><?= $stats['ongoing'] ?></h2><p class="mb-0 text-muted">Sessions On Going</p></<?= $d ?>></<?= $d ?>>
        <<?= $d ?> class="col-md-3"><<?= $d ?> class="card main-card p-4 text-center border-0 shadow-sm"><h2 class="fw-bold" style="color:#6f42c1"><?= round($stats['today_hours']/60,1) ?>h</h2><p class="mb-0 text-muted">Sit-In Hours Today</p></<?= $d ?>></<?= $d ?>>
    </<?= $d ?>>

    <<?= $d ?> class="row g-4">
        <<?= $d ?> class="col-lg-6">
            <<?= $d ?> class="card main-card p-4 border-0 shadow-sm h-100">
                <h5 class="fw-bold mb-4">Lab Occupancy & Analytics</h5>
                <p class="small text-muted mb-2">Current laboratory occupancy across all active PCs.</p>
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2"><span class="small">Occupancy</span><span class="small fw-bold"><?= $busyPercent ?>%</span></div>
                    <div class="progress" style="height: 18px; border-radius: 12px;">
                        <div class="progress-bar bg-<?= $busyColor ?>" role="progressbar" style="width: <?= $busyPercent ?>%" aria-valuenow="<?= $busyPercent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="small text-muted mt-2 mb-0"><?= $activeOnGoing ?> seats currently in use from <?= $totalPcs ?> available PCs.</p>
                </div>
                <hr>
                <h6 class="fw-semibold mb-3">Programming Language Practice</h6>
                <div class="d-flex justify-content-center">
                    <canvas id="practicePie" class="dashboard-analytics-chart" height="180"></canvas>
                </div>
            </<?= $d ?>
        </<?= $d ?>>

        <<?= $d ?> class="col-lg-6">
            <<?= $d ?> class="card main-card p-4 border-0 shadow-sm h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">Announcements</h5>
                        <p class="small text-muted mb-0">Create, view, and remove announcements at any time.</p>
                    </div>
                </div>
                <?php if (empty($announcements)): ?>
                    <div class="alert alert-secondary">No announcements yet. Create one to show students important updates.</div>
                <?php else: foreach ($announcements as $a): ?>
                    <div class="announcement-item position-relative">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="fw-bold mb-1"><?= htmlspecialchars($a['title']) ?></h6>
                                <p class="announcement-dates mb-1"><i class="bi bi-calendar-range me-1"></i><?= date('M d, Y', strtotime($a['start_date'])) ?> — <?= date('M d, Y', strtotime($a['end_date'])) ?></p>
                            </div>
                            <form method="post" class="ms-3">
                                <input type="hidden" name="action" value="delete_announcement">
                                <input type="hidden" name="announcement_id" value="<?= (int)$a['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </div>
                        <p class="small mb-2"><?= nl2br(htmlspecialchars($a['content'])) ?></p>
                        <p class="small text-muted mb-0">Created by <?= htmlspecialchars($a['admin_name']) ?></p>
                    </div>
                <?php endforeach; ?>
                <?php endif; ?>
                <hr>
                <h6 class="fw-semibold mb-3">Create Announcement</h6>
                <form method="post">
                    <input type="hidden" name="action" value="create_announcement">
                    <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Content</label><textarea name="content" class="form-control" rows="3" required></textarea></div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                        <div class="col-md-6"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required></div>
                    </div>
                    <button class="btn btn-primary-purple w-100">Save Announcement</button>
                </form>
            </<?= $d ?>
        </<?= $d ?>>
    </<?= $d ?>>
</<?= $d ?>>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const practiceData = {
    labels: ['Being Practiced', 'In The Middle', 'Not So Practiced'],
    datasets: [{
        data: [<?= $being ?>, <?= $middle ?>, <?= $notSo ?>],
        backgroundColor: ['#198754', '#fd7e14', '#dc3545']
    }]
};
new Chart(document.getElementById('practicePie'), {
    type: 'doughnut',
    data: practiceData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '65%',
        plugins: { legend: { position: 'bottom' } }
    }
});
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
