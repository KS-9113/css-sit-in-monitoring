<?php $d = 'div'; ?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-purple sticky-top">
<<?= $d ?> class="container-fluid px-4">
<a class="navbar-brand navbar-brand-custom" href="<?= BASE_URL ?>/admin/dashboard.php">CCS ADMIN DASHBOARD</a>
<<?= $d ?> class="dropdown ms-auto">
<a class="nav-link dropdown-toggle text-white d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
<span class="rounded-circle bg-white text-primary d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px"><i class="bi bi-person-fill"></i></span>
<span>Administrator</span>
</a>
<ul class="dropdown-menu dropdown-menu-end shadow">
<li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/dashboard.php"><i class="bi bi-house me-2"></i>Home</a></li>
<li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/search.php"><i class="bi bi-search me-2"></i>Search</a></li>
<li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/students.php"><i class="bi bi-people me-2"></i>Students</a></li>
<li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/sitin.php"><i class="bi bi-pc-display me-2"></i>Sit-In</a></li>
<li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/records.php"><i class="bi bi-clock-history me-2"></i>Sit-In Records</a></li>
<li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/reports.php"><i class="bi bi-bar-chart me-2"></i>Sit-In Reports</a></li>
<li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/feedback.php"><i class="bi bi-star me-2"></i>Feedback Reports</a></li>
<li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/reservations.php"><i class="bi bi-calendar-check me-2"></i>Reservation</a></li>
<li><hr class="dropdown-divider"></li>
<li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/admin/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</a></li>
</ul>
</<?= $d ?>>
</<?= $d ?>>
</nav>

