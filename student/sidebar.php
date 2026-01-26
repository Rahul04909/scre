<div class="border-end" id="sidebar-wrapper">
    <div class="sidebar-heading text-center py-4 fs-4 fw-bold text-uppercase border-bottom">
        <i class="fas fa-user-graduate me-2 text-primary"></i><span style="color: #0F172A;">SCRE Student</span>
    </div>
    
    <!-- Profile Section -->
    <div class="text-center py-4 border-bottom profile-section">
        <?php 
            $s_img = !empty($_SESSION['student_image']) ? '../'.$_SESSION['student_image'] : 'https://ui-avatars.com/api/?name='.urlencode($_SESSION['student_name'] ?? 'Student');
        ?>
        <img src="<?php echo $s_img; ?>" class="rounded-circle shadow-sm mb-2" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #fff;">
        <div class="fw-bold" style="color: #0F172A;"><?php echo htmlspecialchars($_SESSION['student_name'] ?? 'Student'); ?></div>
        <div class="small" style="color: #64748B;"><?php echo htmlspecialchars($_SESSION['enrollment_no'] ?? ''); ?></div>
    </div>
    <div class="list-group list-group-flush my-3 px-3">
        <?php 
        $current_page = basename($_SERVER['PHP_SELF']); 
        ?>
        
        <a href="../../student/index.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </a>
        <a href="../../student/profile.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
            <i class="fas fa-user me-2"></i>My Profile
        </a>
        <a href="../../student/fees.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'fees.php') ? 'active' : ''; ?>">
            <i class="fas fa-wallet me-2"></i>My Fees
        </a>
        <a href="../../student/id-card.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'id-card.php') ? 'active' : ''; ?>">
            <i class="fas fa-id-card me-2"></i>ID Card
        </a>
        <a href="../../student/results.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'results.php') ? 'active' : ''; ?>">
            <i class="fas fa-poll me-2"></i>Results
        </a>
        <a href="../../student/hall-ticket/hall-ticket.php" class="list-group-item list-group-item-action <?php echo (strpos($current_page, 'hall-ticket') !== false) ? 'active' : ''; ?>">
            <i class="fas fa-id-card me-2"></i>Hall Ticket
        </a>
        <a href="../../student/marksheet.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'marksheet.php') ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check me-2"></i>Marksheet
        </a>
        <a href="../../student/logout.php" class="list-group-item list-group-item-action text-danger fw-bold mt-4">
            <i class="fas fa-power-off me-2"></i>Logout
        </a>
    </div>
</div>
