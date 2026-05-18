<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

$sql = "SELECT f.*, s.id_number, s.first_name, s.middle_name, s.last_name, l.lab_name
        FROM feedback f
        JOIN students s ON s.id = f.student_id
        JOIN laboratories l ON l.id = f.laboratory_id WHERE 1=1";
$params = [];
if ($from !== '') { $sql .= ' AND DATE(f.created_at) >= ?'; $params[] = $from; }
if ($to !== '') { $sql .= ' AND DATE(f.created_at) <= ?'; $params[] = $to; }
$sql .= ' ORDER BY f.created_at DESC';
$stmt = getDB()->prepare($sql);
$stmt->execute($params);
$feedbacks = $stmt->fetchAll();

$labSql = "SELECT l.lab_name, AVG(f.rating) AS avg_rating, COUNT(*) AS cnt FROM feedback f JOIN laboratories l ON l.id=f.laboratory_id WHERE 1=1";
if ($from !== '') { $labSql .= ' AND DATE(f.created_at) >= ?'; }
if ($to !== '') { $labSql .= ' AND DATE(f.created_at) <= ?'; }
$labSql .= ' GROUP BY l.id ORDER BY avg_rating DESC';
$lstmt = getDB()->prepare($labSql);
$lstmt->execute($params);
$labRanks = $lstmt->fetchAll();

$monthly = getDB()->prepare("SELECT DATE_FORMAT(created_at,'%Y-%m') ym, AVG(rating) avg_r, COUNT(*) cnt FROM feedback WHERE 1=1"
    . ($from ? ' AND DATE(created_at)>=?' : '') . ($to ? ' AND DATE(created_at)<=?' : '') . ' GROUP BY ym ORDER BY ym');
$monthly->execute($params);
$chartData = $monthly->fetchAll();

$pageTitle = 'Feedback Reports';
require __DIR__ . '/../includes/head.php';
require __DIR__ . '/../includes/admin_navbar.php';
$d = 'div';
?>
<h4 class="fw-bold pt-3 ms-3">Feedback Reports</h4>
<<?= $d ?> class="container-fluid py-4 px-4">
<form method="get" class="row g-2 mb-4 align-items-end">
<div class="col-md-3"><label class="form-label-custom">From</label><input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>"></div>
<div class="col-md-3"><label class="form-label-custom">To</label><input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>"></div>
<div class="col-md-3"><button class="btn btn-primary-purple">Filter</button></div>
<div class="col-md-3 text-end"><a href="<?= BASE_URL ?>/api/admin/export_feedback.php?from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>" class="btn btn-success">Download CSV</a></div>
</form>
<<?= $d ?> class="row g-4 mb-4">
<<?= $d ?> class="col-md-4"><<?= $d ?> class="card p-3 border-0 shadow-sm"><h6 class="fw-bold">Lab Leaderboard (avg stars)</h6>
<?php foreach ($labRanks as $i=>$l): ?><p class="mb-1 small"><strong>#<?= $i+1 ?></strong> <?= htmlspecialchars($l['lab_name']) ?> — <?= number_format($l['avg_rating'],1) ?> ★ (<?= (int)$l['cnt'] ?>)</p><?php endforeach; ?>
</<?= $d ?>></<?= $d ?>>
<<?= $d ?> class="col-md-8"><<?= $d ?> class="card p-3 border-0 shadow-sm"><h6 class="fw-bold">Ratings</h6><canvas id="fbChart" height="100"></canvas></<?= $d ?>></<?= $d ?>>
</<?= $d ?>>
<table class="table table-hover bg-white shadow-sm"><thead class="table-light"><tr><th>Date & Time</th><th>Student</th><th>Lab</th><th>Rating</th><th>Feedback</th></tr></thead>
<tbody><?php foreach ($feedbacks as $f): ?>
<tr>
<td><?= date('M d, Y h:i A', strtotime($f['created_at'])) ?></td>
<td><?= htmlspecialchars(getStudentFullName($f)) ?> (<?= htmlspecialchars($f['id_number']) ?>)</td>
<td><?= htmlspecialchars($f['lab_name']) ?></td>
<td><?= str_repeat('★', (int)$f['rating']) ?></td>
<td><?= htmlspecialchars($f['comments']) ?></td>
</tr><?php endforeach; ?></tbody></table>
</<?= $d ?>>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('fbChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($chartData,'ym')) ?>,
        datasets: [
            { label: 'Avg Rating', data: <?= json_encode(array_map(fn($r)=>round($r['avg_r'],2), $chartData)) ?>, borderColor: '#6f42c1', tension: 0.3 },
            { label: 'Count', data: <?= json_encode(array_column($chartData,'cnt')) ?>, borderColor: '#fd7e14', tension: 0.3 }
        ]
    }
});
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
