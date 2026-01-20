<nav class="navbar navbar-expand-lg px-4 py-2" id="main-header">
    <div class="d-flex align-items-center w-100 justify-content-between">
        
        <!-- Left: Toggle & Welcome -->
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-light d-md-none" id="sidebar-toggle">
                <i class="fas fa-bars text-secondary"></i>
            </button>
            <button class="nav-icon-btn d-none d-md-flex" id="menu-toggle">
                <i class="fas fa-outdent fs-5"></i> 
            </button>
            
            <div class="d-none d-sm-block">
                <div class="header-welcome">Welcome Back</div>
            </div>
        </div>

        <!-- Right: Actions & Profile -->
        <div class="d-flex align-items-center gap-3">
            
            <!-- Notification Bell -->
            <button class="nav-icon-btn position-relative">
                <i class="far fa-bell fs-5"></i>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="width: 10px; height: 10px;"></span>
            </button>

            <!-- Profile Dropdown -->
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle hide-arrow" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="d-flex align-items-center gap-2">
                        <div class="text-end d-none d-md-block">
                            <div class="fw-bold small text-dark"><?php echo htmlspecialchars($_SESSION['student_name'] ?? 'Student'); ?></div>
                            <div class="text-muted" style="font-size: 0.75rem;">Student</div>
                        </div>
                        <?php 
                            $s_img = !empty($_SESSION['student_image']) ? '../'.$_SESSION['student_image'] : 'https://ui-avatars.com/api/?name='.urlencode($_SESSION['student_name'] ?? 'Student');
                        ?>
                        <img src="<?php echo $s_img; ?>" class="rounded-circle border" width="40" height="40" style="object-fit: cover;">
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm animate slideIn" aria-labelledby="profileDropdown">
                    <li><a class="dropdown-item" href="profile.php"><i class="far fa-user-circle me-2"></i>My Profile</a></li>
                    <li><a class="dropdown-item" href="fees.php"><i class="fas fa-wallet me-2"></i>Fee Status</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>

        </div>
    </div>
</nav>

<!-- Scripts -->
<script src="assets/js/sidebar.js"></script>
