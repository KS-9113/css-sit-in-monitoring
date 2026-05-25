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
        // Set end_date to yesterday so the announcement is no longer considered active
        $stmt = $db->prepare("UPDATE announcements SET end_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY) WHERE id = ?");
        $stmt->execute([$id]);
        redirect('/admin/dashboard.php?toast=' . urlencode('Announcement archived.'));
    }
    if (isset($_POST['action']) && $_POST['action'] === 'permanent_delete_announcement') {
        $id = (int) ($_POST['announcement_id'] ?? 0);
        if ($id > 0) {
            $stmt = $db->prepare('DELETE FROM announcements WHERE id = ?');
            $stmt->execute([$id]);
        }
        redirect('/admin/dashboard.php?toast=' . urlencode('Announcement deleted permanently.'));
    }
}

$stats = [
    'students' => (int) $db->query('SELECT COUNT(*) FROM students')->fetchColumn(),
    'pending' => (int) $db->query("SELECT COUNT(*) FROM sit_in_records WHERE status = 'Reserved'")->fetchColumn(),
    'ongoing' => (int) $db->query("SELECT COUNT(*) FROM sit_in_records WHERE status = 'On Going'")->fetchColumn(),
    'today_hours' => (int) $db->query("SELECT COALESCE(SUM(duration_minutes),0) FROM sit_in_records WHERE status='Completed' AND DATE(time_out)=CURDATE()")->fetchColumn(),
];

$labs = $db->query('SELECT * FROM laboratories WHERE is_active = 1 ORDER BY lab_name')->fetchAll();
$selectedLabId = (int) ($_GET['lab_id'] ?? 0);
$selectedLab = null;
foreach ($labs as $lab) {
    if ($lab['id'] === $selectedLabId) {
        $selectedLab = $lab;
        break;
    }
}

$from = trim($_GET['from'] ?? '');
$to = trim($_GET['to'] ?? '');
$dateFilter = '';
$dateParams = [];
if ($from !== '') {
    $dateFilter .= ' AND DATE(COALESCE(booked_on, scheduled_date)) >= ?';
    $dateParams[] = $from;
}
if ($to !== '') {
    $dateFilter .= ' AND DATE(COALESCE(booked_on, scheduled_date)) <= ?';
    $dateParams[] = $to;
}

$labFilterSql = "WHERE status = 'On Going'";
$labParams = [];
if ($selectedLab) {
    $labFilterSql .= ' AND laboratory_id = ?';
    $labParams[] = $selectedLab['id'];
}
$activeStmt = $db->prepare("SELECT COUNT(*) FROM sit_in_records $labFilterSql");
$activeStmt->execute($labParams);
$activeOnGoing = (int) $activeStmt->fetchColumn();
$totalPcs = $selectedLab ? (int)$selectedLab['pc_count'] : (int) $db->query('SELECT COALESCE(SUM(pc_count),0) FROM laboratories WHERE is_active = 1')->fetchColumn();
$busyPercent = $totalPcs > 0 ? min(100, round($activeOnGoing / $totalPcs * 100)) : 0;
$busyColor = $busyPercent >= 80 ? 'danger' : ($busyPercent >= 50 ? 'warning' : 'success');
$occupancyLabel = $selectedLab ? $selectedLab['lab_name'] : 'All Labs';

$topLabRoomsStmt = $db->prepare("SELECT l.lab_name, COUNT(r.id) AS cnt FROM laboratories l LEFT JOIN sit_in_records r ON r.laboratory_id = l.id AND r.status IN ('Reserved','Approved','On Going','Completed')" . $dateFilter . " GROUP BY l.id ORDER BY cnt DESC LIMIT 5");
$topLabRoomsStmt->execute($dateParams);
$topLabRooms = $topLabRoomsStmt->fetchAll();

$topPurposeFilter = '';
$topPurposeParams = [];
if ($from !== '') {
    $topPurposeFilter .= ' AND DATE(COALESCE(booked_on, scheduled_date)) >= ?';
    $topPurposeParams[] = $from;
}
if ($to !== '') {
    $topPurposeFilter .= ' AND DATE(COALESCE(booked_on, scheduled_date)) <= ?';
    $topPurposeParams[] = $to;
}

$topPurposesStmt = $db->prepare(
    "SELECT
        SUM(CASE WHEN purpose LIKE '%C#%' THEN 1 ELSE 0 END) AS csharp,
        SUM(CASE WHEN purpose LIKE '%TypeScript%' THEN 1 ELSE 0 END) AS typescript,
        SUM(CASE WHEN purpose LIKE '%Python%' THEN 1 ELSE 0 END) AS python,
        SUM(CASE WHEN purpose LIKE '%PHP%' THEN 1 ELSE 0 END) AS php,
        SUM(CASE WHEN purpose LIKE '%JavaScript%' THEN 1 ELSE 0 END) AS javascript,
        SUM(CASE WHEN purpose LIKE '%Java%' AND purpose NOT LIKE '%JavaScript%' THEN 1 ELSE 0 END) AS java,
        SUM(CASE WHEN purpose LIKE '%C++%' THEN 1 ELSE 0 END) AS cpp,
        SUM(CASE WHEN purpose IS NOT NULL AND TRIM(purpose) != ''
            AND purpose NOT LIKE '%C#%'
            AND purpose NOT LIKE '%TypeScript%'
            AND purpose NOT LIKE '%Python%'
            AND purpose NOT LIKE '%PHP%'
            AND purpose NOT LIKE '%JavaScript%'
            AND purpose NOT LIKE '%Java%'
            AND purpose NOT LIKE '%C++%'
        THEN 1 ELSE 0 END) AS others
    FROM sit_in_records
    WHERE status IN ('On Going','Completed')" . $topPurposeFilter
);
$topPurposesStmt->execute($topPurposeParams);
$topPurposeCounts = $topPurposesStmt->fetch(PDO::FETCH_ASSOC);
$topPurposes = [
    ['purpose' => 'C#', 'cnt' => (int) ($topPurposeCounts['csharp'] ?? 0)],
    ['purpose' => 'TypeScript', 'cnt' => (int) ($topPurposeCounts['typescript'] ?? 0)],
    ['purpose' => 'Python', 'cnt' => (int) ($topPurposeCounts['python'] ?? 0)],
    ['purpose' => 'PHP', 'cnt' => (int) ($topPurposeCounts['php'] ?? 0)],
    ['purpose' => 'JavaScript', 'cnt' => (int) ($topPurposeCounts['javascript'] ?? 0)],
    ['purpose' => 'Java', 'cnt' => (int) ($topPurposeCounts['java'] ?? 0)],
    ['purpose' => 'C++', 'cnt' => (int) ($topPurposeCounts['cpp'] ?? 0)],
    ['purpose' => 'others', 'cnt' => (int) ($topPurposeCounts['others'] ?? 0)],
];

$announcements = $db->query('SELECT a.*, COALESCE(ad.full_name, "Admin") AS admin_name FROM announcements a LEFT JOIN admins ad ON ad.id = a.created_by ORDER BY a.created_at DESC')->fetchAll();

$pageTitle = 'Admin Dashboard';
require __DIR__ . '/../includes/head.php';
require __DIR__ . '/../includes/admin_navbar.php';
?>

<div class="container-fluid py-4 px-4">
    
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card main-card p-4 text-center border-0 shadow-sm">
                <h2 class="text-primary fw-bold mb-1"><?= $stats['students'] ?></h2>
                <p class="mb-0 text-muted small fw-semibold text-uppercase tracking-wider">Total Students</p>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card main-card p-4 text-center border-0 shadow-sm">
                <h2 class="text-warning fw-bold mb-1"><?= $stats['pending'] ?></h2>
                <p class="mb-0 text-muted small fw-semibold text-uppercase tracking-wider">Pending Reservations</p>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card main-card p-4 text-center border-0 shadow-sm">
                <h2 class="text-success fw-bold mb-1"><?= $stats['ongoing'] ?></h2>
                <p class="mb-0 text-muted small fw-semibold text-uppercase tracking-wider">Sessions On Going</p>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card main-card p-4 text-center border-0 shadow-sm">
                <h2 class="fw-bold mb-1" style="color:#6f42c1"><?= round($stats['today_hours']/60,1) ?>h</h2>
                <p class="mb-0 text-muted small fw-semibold text-uppercase tracking-wider">Sit-In Hours Today</p>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        
        <div class="col-lg-7">
            <div class="card main-card p-4 border-0 shadow-sm h-100">
                <div class="d-flex flex-sm-row flex-column align-items-sm-center justify-content-between gap-3 mb-4">
                    <div>
                        <h5 class="fw-bold mb-1">Lab Occupancy & Analytics</h5>
                        <p class="small text-muted mb-0">Current laboratory occupancy across all active workstations.</p>
                    </div>
                    <form method="get" class="row gx-2 gy-2 align-items-end">
                        <div class="col-auto">
                            <label class="form-label-custom">Laboratory</label>
                            <select name="lab_id" class="form-select form-select-sm shadow-sm" style="min-width: 140px;">
                                <option value="">All Laboratories</option>
                                <?php foreach ($labs as $lab): ?>
                                    <option value="<?= $lab['id'] ?>" <?= $selectedLab && $selectedLab['id'] === $lab['id'] ? 'selected' : '' ?>><?= htmlspecialchars($lab['lab_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <input type="hidden" name="from" value="<?= htmlspecialchars($from) ?>">
                        <input type="hidden" name="to" value="<?= htmlspecialchars($to) ?>">
                        <div class="col-auto">
                            <button class="btn btn-sm btn-primary-purple shadow-sm px-3">Filter</button>
                        </div>
                    </form>
                </div>

                <div class="bg-light p-3 rounded-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-bold text-secondary"><?= htmlspecialchars($occupancyLabel) ?> Status</span>
                        <span class="badge bg-<?= $busyColor ?> rounded-pill fw-bold"><?= $busyPercent ?>% Capacity</span>
                    </div>
                    <div class="progress mb-2" style="height: 14px; border-radius: 20px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?= $busyColor ?>" role="progressbar" style="width: <?= $busyPercent ?>%" aria-valuenow="<?= $busyPercent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="small text-muted mb-0"><i class="bi bi-info-circle me-1"></i><?= $activeOnGoing ?> workstations active out of <?= $totalPcs ?> registered PCs.</p>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-light shadow-none p-3 h-100 bg-white" style="border: 1px solid #f1eeff;">
                            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center"><i class="bi bi-building me-2 text-primary"></i>Laboratory Leaderboard</h6>
                            <form method="get" class="row gx-2 gy-2 align-items-end mb-3">
                                <input type="hidden" name="lab_id" value="<?= htmlspecialchars($selectedLabId) ?>">
                                <div class="col-6">
                                    <label class="form-label-custom">From</label>
                                    <input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars($from) ?>">
                                </div>
                                <div class="col-6">
                                    <label class="form-label-custom">To</label>
                                    <input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars($to) ?>">
                                </div>
                                <div class="col-12 text-end">
                                    <button class="btn btn-sm btn-primary-purple shadow-sm px-3">Apply</button>
                                </div>
                            </form>
                            <?php if (empty($topLabRooms)): ?>
                                <div class="text-muted small py-3 text-center">No structural traffic logged yet.</div>
                            <?php else: ?>
                                <?php foreach ($topLabRooms as $lab): ?>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1 small">
                                            <span class="text-secondary"><?= htmlspecialchars($lab['lab_name']) ?></span>
                                            <strong class="text-dark"><?= (int)$lab['cnt'] ?></strong>
                                        </div>
                                        <div class="progress" style="height: 6px; border-radius: 10px;">
                                            <div class="progress-bar bg-primary" style="width: <?= min(100, max(5, round($lab['cnt'] / max(1, $topLabRooms[0]['cnt']) * 100))) ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-light shadow-none p-3 h-100 bg-white" style="border: 1px solid #f1eeff;">
                            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center"><i class="bi bi-journal-bookmark me-2 text-warning"></i>Top Purposes</h6>
                            <form method="get" class="row gx-2 gy-2 align-items-end mb-3">
                                <input type="hidden" name="lab_id" value="<?= htmlspecialchars($selectedLabId) ?>">
                                <div class="col-6">
                                    <label class="form-label-custom">From</label>
                                    <input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars($from) ?>">
                                </div>
                                <div class="col-6">
                                    <label class="form-label-custom">To</label>
                                    <input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars($to) ?>">
                                </div>
                                <div class="col-12 text-end">
                                    <button class="btn btn-sm btn-primary-purple shadow-sm px-3">Apply</button>
                                </div>
                            </form>
                            <?php if (empty($topPurposes)): ?>
                                <div class="text-muted small py-3 text-center">No purpose metrics registered.</div>
                            <?php else: ?>
                                <?php foreach ($topPurposes as $purpose): ?>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1 small">
                                            <span class="text-secondary"><?= htmlspecialchars($purpose['purpose']) ?></span>
                                            <strong class="text-dark"><?= (int)$purpose['cnt'] ?></strong>
                                        </div>
                                        <div class="progress" style="height: 6px; border-radius: 10px;">
                                            <div class="progress-bar bg-warning" style="width: <?= min(100, max(5, round($purpose['cnt'] / max(1, $topPurposes[0]['cnt']) * 100))) ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card main-card p-4 border-0 shadow-sm h-100">
                <div class="mb-3">
                    <h5 class="fw-bold mb-1">Active Notices</h5>
                    <p class="small text-muted mb-0">Live context shown directly to students on login panels.</p>
                </div>
                
                <div class="announcement-scroll-areape" style="max-height: 420px; overflow-y: auto; padding-right: 4px;">
                    <?php if (empty($announcements)): ?>
                        <div class="alert alert-light border text-center py-4 text-muted small"><i class="bi bi-chat-left-dots d-block fs-3 mb-2 opacity-50"></i>No live announcements found.</div>
                    <?php else: foreach ($announcements as $a): ?>
                        <div class="announcement-item position-relative shadow-sm border-start border-4 bg-white p-3 mb-3" style="border-radius: 4px 12px 12px 4px;">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($a['title']) ?></h6>
                                    <span class="announcement-dates badge bg-light text-dark border"><i class="bi bi-calendar2-range me-1 text-primary"></i><?= date('M d, Y', strtotime($a['start_date'])) ?> — <?= date('M d, Y', strtotime($a['end_date'])) ?></span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <form method="post" onsubmit="return confirm('Archive this publication entry?');">
                                        <input type="hidden" name="action" value="delete_announcement">
                                        <input type="hidden" name="announcement_id" value="<?= (int)$a['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0 border-0 shadow-none text-decoration-none small fw-semibold">Archive</button>
                                    </form>
                                    <form method="post" onsubmit="return confirm('Permanently delete this announcement? This cannot be undone.');" class="ms-2">
                                        <input type="hidden" name="action" value="permanent_delete_announcement">
                                        <input type="hidden" name="announcement_id" value="<?= (int)$a['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </div>
                            <p class="small text-secondary mb-2" style="white-space: pre-line; line-height: 1.5;"><?= nl2br(htmlspecialchars($a['content'])) ?></p>
                            <div class="text-end border-top pt-2 mt-2" style="font-size: 0.75rem; color:#a196bd;">
                                <i class="bi bi-person-circle me-1"></i>Issued by: <strong><?= htmlspecialchars($a['admin_name']) ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card main-card p-4 border-0 shadow-sm">
                <h5 class="fw-bold text-dark mb-1 d-flex align-items-center"><i class="bi bi-megaphone me-2 text-primary"></i>Publish New Academic Broadcaster</h5>
                <p class="small text-muted mb-4">Draft global messages, schedules adjustments, or server outages to system terminals.</p>
                
                <form method="post">
                    <input type="hidden" name="action" value="create_announcement">
                    <div class="row g-3">
                        <div class="col-lg-4 col-md-6">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Announcement Title</label>
                                <input type="text" name="title" class="form-control" placeholder="e.g., Midterm Laboratory Schedule Maintenance" required>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-secondary">Start Displaying</label>
                                    <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-secondary">End Expiration</label>
                                    <input type="date" name="end_date" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8 col-md-6 d-flex flex-column justify-content-between">
                            <div class="mb-3 h-100 d-flex flex-column">
                                <label class="form-label small fw-bold text-secondary">Contextual Content Body</label>
                                <textarea name="content" class="form-control flex-grow-1" rows="3" placeholder="Write out explicit terms, rules, changes or policies clearly for target computer stations..." required></textarea>
                            </div>
                            <div class="text-end">
                                <button class="btn btn-primary-purple px-4 py-2 mt-2 shadow-sm fw-bold"><i class="bi bi-send-check me-2"></i>Deploy Live Notice</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>