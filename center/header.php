<!-- Header -->
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top px-4 py-2 shadow-sm" style="transition: margin-left 0.3s; z-index: 999;" id="main-header">
    <div class="d-flex w-100 justify-content-between align-items-center">
        <!-- Mobile Toggle -->
        <button class="btn border-0 text-primary d-md-none" id="sidebar-toggle">
            <i class="fas fa-bars fs-4"></i>
        </button>

        <!-- Search / Breadcrumb Placeholder -->
        <div class="d-none d-md-flex align-items-center">
            <!-- Desktop Menu Toggle -->
            <button class="btn btn-light shadow-sm me-3 border" id="menu-toggle" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s;">
                <i class="fas fa-outdent text-primary"></i>
            </button>

            <form class="d-flex">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                    <input class="form-control border-0 bg-light" type="search" placeholder="Search..." aria-label="Search">
                </div>
            </form>
        </div>

        <!-- Right Side Icons -->
        <div class="d-flex align-items-center gap-3">
            <!-- Notifications -->
            <div class="dropdown">
                <a href="#" class="text-secondary position-relative" id="notifDropdown" data-bs-toggle="dropdown">
                    <i class="fas fa-bell fs-5"></i>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                        <span class="visually-hidden">New alerts</span>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="notifDropdown">
                    <li class="dropdown-header fw-bold">Notifications</li>
                    <li><a class="dropdown-item small" href="#">No new notifications</a></li>
                </ul>
            </div>

            <!-- User Profile -->
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center gap-2 text-decoration-none dropdown-toggle hide-arrow" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="text-end d-none d-sm-block">
                        <p class="mb-0 fw-bold small text-dark"><?php echo $_SESSION['center_name'] ?? 'Center Admin'; ?></p>
                        <p class="mb-0 text-muted" style="font-size: 0.75rem;">Administrator</p>
                    </div>
                    <?php 
                        $header_img = $_SESSION['owner_image'] ?? 'https://ui-avatars.com/api/?name='.urlencode($_SESSION['center_name'] ?? 'Admin').'&background=4361ee&color=fff';
                    ?>
                    <img src="<?php echo $header_img; ?>" class="rounded-circle border" width="40" height="40" alt="Avatar">
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 animate slideIn" aria-labelledby="profileDropdown">
                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle me-2"></i>My Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<style>
    /* Mobile Sidebar Toggle Logic */
    @media (max-width: 768px) {
        #sidebar { margin-left: -280px; transition: margin 0.25s ease-out; }
        #sidebar.active { margin-left: 0; box-shadow: 10px 0 30px rgba(0,0,0,0.5); }
        #main-header, #page-content-wrapper { margin-left: 0 !important; width: 100% !important; }
    }
    
    /* Desktop Toggle Logic */
    @media (min-width: 769px) {
        #sidebar { transition: margin 0.25s ease-out; }
        #page-content-wrapper { transition: margin 0.25s ease-out; }
        
        .toggled #sidebar { margin-left: -280px; }
        .toggled #page-content-wrapper { margin-left: 0 !important; }
        .toggled #main-header { margin-left: 0 !important; }
    }

    .form-control:focus { box-shadow: none; background-color: #f8f9fa; }
</style>

    <!-- Toggle Script -->
    <script src="../assets/js/sidebar-interactive.js"></script>
