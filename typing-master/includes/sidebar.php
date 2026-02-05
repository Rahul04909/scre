<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar d-flex flex-column flex-shrink-0 p-3 bg-white shadow-sm" style="width: 260px; height: 100vh; position: fixed; top: 0; left: 0; z-index: 1000;">
    <a href="#" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-dark text-decoration-none">
        <div class="sidebar-logo-container bg-primary-soft rounded p-2 me-2">
            <i class="fas fa-keyboard fs-4 text-primary"></i>
        </div>
        <span class="fs-5 fw-bold text-primary">Typing Master</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?php echo ($currentPage == 'dashboard.php') ? 'active' : 'link-dark'; ?>">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
        </li>
        <li class="mt-2">
            <small class="text-uppercase text-muted fw-bold ps-3" style="font-size: 0.75rem;">Learning</small>
        </li>
        <li>
            <a href="lessons.php" class="nav-link <?php echo ($currentPage == 'lessons.php') ? 'active' : 'link-dark'; ?>">
                <i class="fas fa-book-reader me-2"></i> Typing Lessons
            </a>
        </li>
        <li>
            <a href="practice-tests.php" class="nav-link <?php echo ($currentPage == 'practice-tests.php') ? 'active' : 'link-dark'; ?>">
                <i class="fas fa-tasks me-2"></i> Practice Tests
            </a>
        </li>
        <li class="mt-2">
            <small class="text-uppercase text-muted fw-bold ps-3" style="font-size: 0.75rem;">Analysis</small>
        </li>
        <!-- <li>
            <a href="reports.php" class="nav-link <?php echo ($currentPage == 'reports.php') ? 'active' : 'link-dark'; ?>">
                <i class="fas fa-chart-line me-2"></i> Progress Report
            </a>
        </li> -->
    </ul>
    <hr>
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center link-dark text-decoration-none dropdown-toggle" id="dropdownUser2" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="<?php echo !empty($_SESSION['student_image']) ? '../'.$_SESSION['student_image'] : 'https://via.placeholder.com/32'; ?>" alt="" width="32" height="32" class="rounded-circle me-2 border">
            <strong><?php echo htmlspecialchars(explode(' ', $_SESSION['student_name'])[0]); ?></strong>
        </a>
        <ul class="dropdown-menu text-small shadow" aria-labelledby="dropdownUser2">
            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Sign out</a></li>
        </ul>
    </div>
</div>

<style>
    .nav-link { font-weight: 500; padding: 10px 15px; border-radius: 8px; margin-bottom: 5px; transition: all 0.2s; }
    .nav-link:hover { background-color: #f3f4f6; }
    .nav-link.active { background-color: #0f766e; color: white !important; box-shadow: 0 4px 6px -1px rgba(15, 118, 110, 0.4); }
    .text-primary { color: #0f766e !important; }
    .bg-primary-soft { background-color: rgba(15, 118, 110, 0.1); }
</style>
