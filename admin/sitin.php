<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$db = getDB();
$labs = $db->query('SELECT * FROM laboratories ORDER BY lab_name')->fetchAll();
$labId = (int) ($_GET['lab_id'] ?? ($labs[0]['id'] ?? 1));
$students = $db->query('SELECT id, id_number, first_name, middle_name, last_name, remaining_sessions FROM students ORDER BY last_name')->fetchAll();
$pageTitle = 'Sit-In Console';
require __DIR__ . '/../includes/head.php';
require __DIR__ . '/../includes/admin_navbar.php';
$d = 'div';
?>
<<?= $d ?> class="container-fluid py-4 px-4">
<<?= $d ?> class="row g-4">
<<?= $d ?> class="col-lg-5">
<<?= $d ?> class="card main-card border-0 shadow-sm p-4">

<h4 class="fw-bold pt-3">Sit-In</h4>
<h5 class="fw-bold mb-3">Assisted Walk-In</h5>
<p class="small text-muted">For students with low battery—auto-approved, no reservation approval needed.</p>
<form method="post" action="<?= BASE_URL ?>/api/admin/walkin.php" class="mb-3">
    <input type="hidden" name="student_id" id="selectedStudentId" required>
    <label class="form-label">Student</label>
    <input id="studentSearch" type="text" class="form-control mb-2" placeholder="Type ID number or name" autocomplete="off" required>
    <div id="studentSuggestions" class="list-group mb-2" style="display:none; max-height:260px; overflow:auto;"></div>
    <div class="mb-3"><small class="text-muted">Select a student from the suggestions.</small></div>
    <label class="form-label">Purpose</label>
    <select name="purpose" class="form-select mb-2" required><?php foreach ([
        'C# Programming',
        'TypeScript Programming',
        'Python Programming',
        'PHP Programming',
        'JavaScript Programming',
        'Java Programming',
        'C++ Programming',
        'Others'
    ] as $p): ?><option><?= $p ?></option><?php endforeach; ?></select>
    <label class="form-label">Laboratory</label>
    <select name="laboratory_id" class="form-select mb-2" required><?php foreach ($labs as $l): ?><option value="<?= $l['id'] ?>" <?= $l['id']==$labId?'selected':'' ?>><?= htmlspecialchars($l['lab_name']) ?></option><?php endforeach; ?></select>
    <label class="form-label">PC Number</label>
    <select name="pc_number" class="form-select mb-4" required><?php for($i=1;$i<=50;$i++): ?><option value="<?= $i ?>">PC <?= $i ?></option><?php endfor; ?></select>
    <button class="btn btn-primary-purple w-100">Start Sit-In Now</button>
</form>
</<?= $d ?>></<?= $d ?>>
<<?= $d ?> class="col-lg-7">
<<?= $d ?> class="card main-card border-0 shadow-sm p-4">
<<?= $d ?> class="d-flex justify-content-between align-items-center mb-3">
<h5 class="fw-bold mb-0">Lab <select id="labPicker" class="form-select form-select-sm d-inline-block w-auto ms-2"><?php foreach ($labs as $l): ?><option value="<?= $l['id'] ?>" <?= $l['id']==$labId?'selected':'' ?>><?= htmlspecialchars($l['lab_name']) ?></option><?php endforeach; ?></select></h5>
<<?= $d ?>><span class="badge bg-success me-1">Green</span> Available <span class="badge bg-warning text-dark ms-2">Orange</span> In Use <span class="badge bg-primary ms-2">Blue</span> Reserved Today</<?= $d ?>>
</<?= $d ?>>
<<?= $d ?> id="pcGrid" class="pc-grid"></<?= $d ?>>
<<?= $d ?> class="mt-3 small text-muted" id="ongoingList"></<?= $d ?>>
</<?= $d ?>></<?= $d ?>></<?= $d ?>></<?= $d ?>>

<div class="modal fade" id="pcDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pcDetailTitle">PC Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="pcDetailBody">Loading...</div>
        </div>
    </div>
</div>

<script>
const baseUrl = '<?= BASE_URL ?>';
let durationIntervals = {};

async function loadPcGrid() {
    const labId = document.getElementById('labPicker').value;
    const res = await fetch(baseUrl + '/api/pc_status.php?lab_id=' + labId);
    const data = await res.json();
    const grid = document.getElementById('pcGrid');
    grid.innerHTML = data.pcs.map(p => {
        const clickable = p.status === 'occupied' || p.status === 'reserved' ? ' style="cursor:pointer;"' : '';
        return `<div class="pc-box ${p.status}" data-pc="${p.pc}" data-lab="${labId}"${clickable}>${p.pc}</div>`;
    }).join('');
    
    grid.querySelectorAll('[data-pc]').forEach(box => {
        const status = box.className.match(/occupied|reserved/)?.[0];
        if (status) {
            box.addEventListener('click', () => showPcDetail(labId, box.dataset.pc));
        }
    });
}

async function showPcDetail(labId, pcNumber) {
    const res = await fetch(`${baseUrl}/api/admin/pc_details.php?lab_id=${labId}&pc_number=${pcNumber}`);
    const data = await res.json();
    const bodyDiv = document.getElementById('pcDetailBody');
    
    if (data.occupied) {
        const occ = data.occupied;
        document.getElementById('pcDetailTitle').textContent = `PC ${pcNumber} - Currently In Use`;
        let html = `<h6>Student: <strong>${occ.student_name}</strong></h6>`;
        html += `<p class="small mb-2"><strong>ID:</strong> ${occ.student_id}</p>`;
        html += `<p class="small mb-2"><strong>Purpose:</strong> ${occ.purpose}</p>`;
        html += `<p class="small mb-2"><strong>Time In:</strong> ${new Date(occ.time_in).toLocaleString()}</p>`;
        html += `<p class="small mb-3"><strong>Duration:</strong> <span id="liveDuration">${formatMinutes(occ.duration_minutes)}</span></p>`;
        html += `<span class="badge bg-primary mb-3">On Going</span>`;
        html += `<form method="post" action="${baseUrl}/api/admin/checkout.php" class="mt-3"><input type="hidden" name="id" value="${occ.id}"><button type="submit" class="btn btn-danger w-100">Check Out</button></form>`;
        bodyDiv.innerHTML = html;
        
        clearInterval(durationIntervals[occ.id]);
        durationIntervals[occ.id] = setInterval(() => {
            const dur = Math.floor((Date.now() - new Date(occ.time_in).getTime()) / 60000);
            document.getElementById('liveDuration').textContent = formatMinutes(dur);
        }, 1000);
    } else if (data.reserved && data.reserved.length) {
        document.getElementById('pcDetailTitle').textContent = `PC ${pcNumber} - Reservations`;
        let html = '<h6 class="mb-3">Approved Reservations</h6>';
        html += '<div style="max-height:400px; overflow-y:auto;">';
        data.reserved.forEach(r => {
            html += `<div class="border p-2 mb-2 rounded">
                <p class="small mb-1"><strong>${r.student_name}</strong> (${r.student_id})</p>
                <p class="small mb-1"><strong>Purpose:</strong> ${r.purpose}</p>
                <p class="small mb-2"><strong>Time:</strong> ${r.scheduled_time_in}</p>
                <span class="badge bg-${r.status === 'Approved' ? 'success' : 'info'} me-2">${r.status}</span>
                <form method="post" action="${baseUrl}/api/admin/reservation_action.php" class="d-inline" style="margin-right:5px;">
                    <input type="hidden" name="id" value="${r.id}"><input type="hidden" name="action" value="reject">
                    <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                </form>`;
            if (r.status === 'Approved') {
                html += `<form method="post" action="${baseUrl}/api/admin/checkin.php" class="d-inline">
                    <input type="hidden" name="id" value="${r.id}">
                    <button type="submit" class="btn btn-sm btn-primary">Check In</button>
                </form>`;
            }
            html += '</div>';
        });
        html += '</div>';
        bodyDiv.innerHTML = html;
    } else {
        document.getElementById('pcDetailTitle').textContent = `PC ${pcNumber}`;
        bodyDiv.innerHTML = '<p class="text-muted">No occupancy or reservations.</p>';
    }
    
    new bootstrap.Modal(document.getElementById('pcDetailModal')).show();
}

function formatMinutes(minutes) {
    const h = Math.floor(minutes / 60);
    const m = minutes % 60;
    return h > 0 ? `${h}h ${m}m` : `${m}m`;
}

document.getElementById('labPicker').addEventListener('change', () => {
    loadPcGrid();
    history.replaceState({}, '', '?lab_id=' + document.getElementById('labPicker').value);
});

loadPcGrid();
setInterval(loadPcGrid, 8000);

const studentSearch = document.getElementById('studentSearch');
const suggestionBox = document.getElementById('studentSuggestions');
const selectedStudentId = document.getElementById('selectedStudentId');
let suggestionTimer = null;

function renderSuggestions(items) {
    if (!items.length) {
        suggestionBox.style.display = 'none';
        suggestionBox.innerHTML = '';
        return;
    }
    suggestionBox.innerHTML = items.map(item => `
        <button type="button" class="list-group-item list-group-item-action text-start" data-id="${item.id}" data-label="${item.label}">
            <strong>${item.id_number}</strong> – ${item.label}
        </button>
    `).join('');
    suggestionBox.style.display = 'block';
}

studentSearch.addEventListener('input', () => {
    selectedStudentId.value = '';
    const q = studentSearch.value.trim();
    if (q.length < 1) {
        renderSuggestions([]);
        return;
    }
    if (suggestionTimer) {
        clearTimeout(suggestionTimer);
    }
    suggestionTimer = setTimeout(async () => {
        const res = await fetch(`${baseUrl}/api/admin/student_suggest.php?q=` + encodeURIComponent(q));
        const data = await res.json();
        renderSuggestions(data);
    }, 200);
});

suggestionBox.addEventListener('click', (event) => {
    const button = event.target.closest('button[data-id]');
    if (!button) return;
    const id = button.dataset.id;
    const label = button.dataset.label;
    selectedStudentId.value = id;
    studentSearch.value = label;
    renderSuggestions([]);
});

document.addEventListener('click', (event) => {
    if (!studentSearch.contains(event.target) && !suggestionBox.contains(event.target)) {
        renderSuggestions([]);
    }
});

document.getElementById('pcDetailModal').addEventListener('hidden.bs.modal', () => {
    Object.values(durationIntervals).forEach(interval => clearInterval(interval));
});
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
