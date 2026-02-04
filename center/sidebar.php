<!-- Sidebar -->
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$current_page = basename($_SERVER['PHP_SELF']);

// Helper to check if a specific page or any page in a folder is active
function isActive($page_url) {
    global $current_page;
    // For Dashboard
    if ($page_url == 'index.php' && $current_page == 'index.php') return true;
    // For sections (e.g. 'students/') - simplified check
    if ($page_url != 'index.php' && strpos($_SERVER['REQUEST_URI'], $page_url) !== false) return true;
    return false;
}

// Function to check if a main category should be expanded based on current URL
function isExpanded($keywords = []) {
    foreach($keywords as $k) {
        if (strpos($_SERVER['REQUEST_URI'], $k) !== false) return true;
    }
    return false;
}

$profile_img = $_SESSION['owner_image'] ?? 'https://ui-avatars.com/api/?name='.urlencode($_SESSION['center_name'] ?? 'Center').'&background=random';
$center_name = $_SESSION['center_name'] ?? 'Demo Center';
$center_code = $_SESSION['center_code'] ?? 'CODE123';
?>

<div class="sidebar d-flex flex-column" id="sidebar-wrapper">
    
    <!-- 1. Header -->
    <div class="sidebar-header p-3 d-flex align-items-center mb-2">
        <div class="me-2 text-danger fw-bold" style="line-height: 1; font-size: 0.8rem; border-left: 3px solid #dc3545; padding-left: 5px;">
            PACE<br>EDU
        </div>
        <div>
            <h5 class="mb-0 text-warning fw-bold">PACE Center</h5>
            <small class="text-white-50" style="font-size: 0.75rem;">Franchise Portal</small>
        </div>
    </div>

    <!-- 2. Profile -->
    <div class="profile-section text-center mb-4 position-relative">
        <div class="d-inline-block position-relative mb-2">
            <img src="<?php echo $profile_img; ?>" class="profile-img rounded-circle" alt="Center Profile">
            <span class="status-dot"></span>
        </div>
        <h6 class="text-white fw-bold mb-1"><?php echo htmlspecialchars($center_name); ?></h6>
        <div class="badge-pill">Center ID: <?php echo htmlspecialchars($center_code); ?></div>
    </div>

    <!-- 3. Navigation -->
    <ul class="nav flex-column mb-auto w-100" id="sidebarMenu">
        
        <!-- Dashboard (No Collapse) -->
        <li class="nav-item">
            <a href="../../center/index.php" class="nav-link <?php echo isActive('index.php') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- Students (Collapsible) -->
        <li class="nav-item">
            <a href="#menuStudents" data-bs-toggle="collapse" class="nav-link <?php echo isExpanded(['students']) ? 'active' : ''; ?> d-flex justify-content-between align-items-center" aria-expanded="<?php echo isExpanded(['students']) ? 'true' : 'false'; ?>">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-graduate"></i>
                    <span>Students</span>
                </div>
                <i class="fas fa-chevron-right small-arrow transition-icon"></i>
            </a>
            <div class="collapse <?php echo isExpanded(['students']) ? 'show' : ''; ?>" id="menuStudents" data-bs-parent="#sidebarMenu">
                <ul class="nav flex-column ms-3 ps-2 border-start border-white-50">
                    <li class="nav-item">
                        <a href="../../center/students/add-student.php" class="nav-link sub-link <?php echo isActive('add-student.php') ? 'active-sub' : ''; ?>">
                            <span>Add New Student</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../../center/students/index.php" class="nav-link sub-link <?php echo isActive('manage-students.php') ? 'active-sub' : ''; ?>">
                            <span>Manage Students</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../../center/students/manage-fees.php" class="nav-link sub-link <?php echo isActive('fees.php') ? 'active-sub' : ''; ?>">
                            <span>Student Fees</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- Live Classes (Collapsible) -->
        <li class="nav-item">
            <a href="#menuLive" data-bs-toggle="collapse" class="nav-link <?php echo isExpanded(['live-class']) ? 'active' : ''; ?> d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fas fa-video"></i>
                    <span>Live Classes</span>
                </div>
                <i class="fas fa-chevron-right small-arrow transition-icon"></i>
            </a>
            <div class="collapse <?php echo isExpanded(['live-class']) ? 'show' : ''; ?>" id="menuLive" data-bs-parent="#sidebarMenu">
                <ul class="nav flex-column ms-3 ps-2 border-start border-white-50">
                     <li class="nav-item">
                        <a href="../live-class/manage-live-class.php" class="nav-link sub-link <?php echo isActive('manage-live-class.php') ? 'active-sub' : ''; ?>">
                            <span>Schedule Class</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../live-class/history.php" class="nav-link sub-link">
                            <span>Class History</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- Courses (Collapsible) -->
        <li class="nav-item">
            <a href="#menuCourses" data-bs-toggle="collapse" class="nav-link <?php echo isExpanded(['courses']) ? 'active' : ''; ?> d-flex justify-content-between align-items-center">
                 <div class="d-flex align-items-center">
                    <i class="fas fa-book"></i>
                    <span>Courses</span>
                </div>
                <i class="fas fa-chevron-right small-arrow transition-icon"></i>
            </a>
            <div class="collapse <?php echo isExpanded(['courses']) ? 'show' : ''; ?>" id="menuCourses" data-bs-parent="#sidebarMenu">
                <ul class="nav flex-column ms-3 ps-2 border-start border-white-50">
                    <li class="nav-item">
                        <a href="../../center/courses/index.php" class="nav-link sub-link <?php echo isActive('courses/index.php') ? 'active-sub' : ''; ?>">
                            <span>Allotted Courses</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- Wallet (Collapsible) -->
        <li class="nav-item">
            <a href="#menuWallet" data-bs-toggle="collapse" class="nav-link <?php echo isExpanded(['wallet']) ? 'active' : ''; ?> d-flex justify-content-between align-items-center">
                 <div class="d-flex align-items-center">
                    <i class="fas fa-wallet"></i>
                    <span>Wallet</span>
                </div>
                <i class="fas fa-chevron-right small-arrow transition-icon"></i>
            </a>
            <div class="collapse <?php echo isExpanded(['wallet']) ? 'show' : ''; ?>" id="menuWallet" data-bs-parent="#sidebarMenu">
                <ul class="nav flex-column ms-3 ps-2 border-start border-white-50">
                    <li class="nav-item">
                        <a href="../../center/wallet/wallet.php" class="nav-link sub-link <?php echo isActive('wallet.php') ? 'active-sub' : ''; ?>">
                            <span>My Balance</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>
         <!-- Practicals -->
        <li class="nav-item">
            <a href="#menuPracticals" data-bs-toggle="collapse" class="nav-link <?php echo isExpanded(['practicals']) ? 'active' : ''; ?> d-flex justify-content-between align-items-center">
                 <div class="d-flex align-items-center">
                    <i class="fas fa-flask"></i>
                    <span>Practicals</span>
                </div>
                <i class="fas fa-chevron-right small-arrow transition-icon"></i>
            </a>
            <div class="collapse <?php echo isExpanded(['practicals']) ? 'show' : ''; ?>" id="menuPracticals" data-bs-parent="#sidebarMenu">
                <ul class="nav flex-column ms-3 ps-2 border-start border-white-50">
                    <li class="nav-item">
                        <a href="../../center/practicals/index.php" class="nav-link sub-link <?php echo isActive('practicals.php') ? 'active-sub' : ''; ?>">
                            <span>Practicals</span>
                        </a>
                    </li>
                     <li class="nav-item">
                        <a href="../../center/practicals/create-practical.php" class="nav-link sub-link <?php echo isActive('create-practical.php') ? 'active-sub' : ''; ?>">
                            <span>Create Practical</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../../center/practicals/view-submissions.php" class="nav-link sub-link <?php echo isActive('view-submissions.php') ? 'active-sub' : ''; ?>">
                            <span>View Submissions</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

    </ul>

    <!-- 4. Footer -->
    <div class="sidebar-footer mt-auto p-3">
        <div class="d-flex justify-content-center gap-4 mb-3">
            <?php $profile_link = (basename(dirname($_SERVER['PHP_SELF'])) == 'center') ? 'profile.php' : '../profile.php'; ?>
            <?php $logout_link = (basename(dirname($_SERVER['PHP_SELF'])) == 'center') ? 'logout.php' : '../logout.php'; ?>
            <a href="<?php echo $profile_link; ?>" class="action-box" title="Profile"><i class="fas fa-user"></i></a>
            <a href="<?php echo $logout_link; ?>" class="action-box" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
        </div>
        <div class="text-center text-white-50" style="font-size: 0.7rem;">&copy; <?php echo date('Y'); ?> PACE Education</div>
    </div>
</div>


