<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$db = getDB();

$from = trim($_GET['from'] ?? '');
$to = trim($_GET['to'] ?? '');

$leaderboardSql = "SELECT s.id_number, s.first_name, s.middle_name, s.last_name, s.course, s.year_level,
           COUNT(r.id) AS total_sessions,
           COALESCE(SUM(r.duration_minutes),0) AS total_minutes
    FROM students s
    LEFT JOIN sit_in_records r ON r.student_id = s.id AND r.status = 'Completed'";
$params = [];
if ($from !== '') { $leaderboardSql .= ' AND DATE(r.time_in) >= ?'; $params[] = $from; }
if ($to !== '') { $leaderboardSql .= ' AND DATE(r.time_in) <= ?'; $params[] = $to; }
$leaderboardSql .= ' GROUP BY s.id ORDER BY total_sessions DESC, total_minutes DESC LIMIT 30';
$leaderboard = $db->prepare($leaderboardSql);
$leaderboard->execute($params);
$leaderboard = $leaderboard->fetchAll();

$monthlySql = "SELECT DATE_FORMAT(time_in, '%Y-%m') AS ym, COUNT(*) AS cnt
    FROM sit_in_records WHERE status = 'Completed' AND time_in IS NOT NULL";
$filterParams = [];
if ($from !== '') { $monthlySql .= ' AND DATE(time_in) >= ?'; $filterParams[] = $from; }
if ($to !== '') { $monthlySql .= ' AND DATE(time_in) <= ?'; $filterParams[] = $to; }
$monthlySql .= ' GROUP BY ym ORDER BY ym';
$monthly = $db->prepare($monthlySql);
$monthly->execute($filterParams);
$monthly = $monthly->fetchAll();

$todayMinutes = (int) $db->query("SELECT COALESCE(SUM(duration_minutes),0) FROM sit_in_records WHERE status='Completed' AND DATE(time_out)=CURDATE()")->fetchColumn();
$pending = (int) $db->query("SELECT COUNT(*) FROM sit_in_records WHERE status='Reserved'")->fetchColumn();
$ongoing = (int) $db->query("SELECT COUNT(*) FROM sit_in_records WHERE status='On Going'")->fetchColumn();
$studentsCount = (int) $db->query('SELECT COUNT(*) FROM students')->fetchColumn();

$pageTitle = 'Sit-In Reports';
require __DIR__ . '/../includes/head.php';
require __DIR__ . '/../includes/admin_navbar.php';
$d = 'div';
$labels = json_encode(array_column($monthly, 'ym'));
$counts = json_encode(array_column($monthly, 'cnt'));
?>
<div class="container-fluid py-4 px-4">
    <h4 class="fw-bold mb-4">Sit-In Reports</h4>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card p-4 border-0 shadow-sm text-center h-100">
                <h5 class="text-muted small mb-2 text-uppercase fw-semibold">Total Students</h5>
                <h3 class="fw-bold mb-0"><?= $studentsCount ?></h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card p-4 border-0 shadow-sm text-center h-100">
                <h5 class="text-muted small mb-2 text-uppercase fw-semibold">Pending Reservations</h5>
                <h3 class="fw-bold mb-0"><?= $pending ?></h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card p-4 border-0 shadow-sm text-center h-100">
                <h5 class="text-muted small mb-2 text-uppercase fw-semibold">Sessions On Going</h5>
                <h3 class="fw-bold mb-0"><?= $ongoing ?></h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card p-4 border-0 shadow-sm text-center h-100">
                <h5 class="text-muted small mb-2 text-uppercase fw-semibold">Sit-In Hours Today</h5>
                <h3 class="fw-bold mb-0"><?= round($todayMinutes/60, 2) ?>h</h3>
            </div>
        </div>
    </div>

    <form id="reportFilter" method="get" class="row g-3 mb-4 align-items-end">
        <div class="col-12 col-md-4 col-lg-4">
            <label class="form-label fw-semibold small text-muted mb-1">From</label>
            <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>">
        </div>
        <div class="col-12 col-md-4 col-lg-4">
            <label class="form-label fw-semibold small text-muted mb-1">To</label>
            <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>">
        </div>
        <div class="col-12 col-md-4 col-lg-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100" style="background-color: #6f42c1; border-color: #6f42c1;">Filter</button>
            <a href="<?= BASE_URL ?>/api/admin/export_reports.php?from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>" class="btn btn-success px-4">CSV</a>
        </div>
    </form>

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-8">
            <div class="card main-card p-4 border-0 shadow-sm h-100">
                <h5 class="fw-bold mb-3">Sit-In Volume</h5>
                <div style="position: relative; width: 100%; height: 100%;">
                    <canvas id="monthChart" height="120"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-lg-4">
            <div class="card main-card p-4 border-0 shadow-sm h-100">
                <h5 class="fw-bold mb-4">Student Leaderboard</h5>
                <div class="d-flex flex-column gap-3">
                    <?php foreach (array_slice($leaderboard, 0, 3) as $i => $row): $rank = $i+1; ?>
                        <div class="d-flex align-items-center gap-3">
                            <span class="leaderboard-rank rank-<?= $rank ?>"><?= $rank ?></span>
                            <div>
                                <strong class="d-block"><?= htmlspecialchars(getStudentFullName($row)) ?></strong>
                                <small class="text-muted"><?= (int)$row['total_sessions'] ?> sessions</small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive bg-white shadow-sm rounded">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th width="60">Rank</th>
                    <th>ID Number</th>
                    <th>Name</th>
                    <th>Course</th>
                    <th>Year Level</th>
                    <th>Sessions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leaderboard as $i => $row): ?>
                    <tr>
                        <td>
                            <?php if ($i < 3): ?>
                                <span class="leaderboard-rank rank-<?= $i+1 ?>"><?= $i+1 ?></span>
                            <?php else: ?>
                                <span class="ps-2"><?= $i+1 ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['id_number']) ?></td>
                        <td><?= htmlspecialchars(getStudentFullName($row)) ?></td>
                        <td><?= htmlspecialchars($row['course']) ?></td>
                        <td><?= htmlspecialchars($row['year_level']) ?></td>
                        <td><span class="badge bg-light text-dark fw-semibold"><?= (int)$row['total_sessions'] ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</<?= $d ?>>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('monthChart'), {
    type: 'bar',
    data: { labels: <?= $labels ?: '[]' ?>, datasets: [{ label: 'Completed Sessions', data: <?= $counts ?: '[]' ?>, backgroundColor: '#6f42c1' }] },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
