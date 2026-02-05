<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm border-bottom" style="margin-left: 260px;">
    <div class="container-fluid px-4">
        <span class="navbar-text text-muted">
            <i class="far fa-calendar-alt me-2"></i> <?php echo date('l, d F Y'); ?>
        </span>
        
        <div class="ms-auto d-flex align-items-center">
            <!-- Notifications (Optional) -->
            <div class="me-4 position-relative">
                <i class="far fa-bell fs-5 text-secondary"></i>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                    <span class="visually-hidden">New alerts</span>
                </span>
            </div>
            
            <div class="d-flex flex-column text-end me-2">
                <span class="fw-bold small"><?php echo htmlspecialchars($_SESSION['student_name']); ?></span>
                <span class="text-muted small" style="font-size: 0.75rem;">Student</span>
            </div>
        </div>
    </div>
</nav>
