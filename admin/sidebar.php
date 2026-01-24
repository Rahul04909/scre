<!-- Sidebar -->
<div id="sidebar-wrapper">
    <div class="sidebar-heading">
        <div class="d-flex align-items-center">
            <!-- Replace with actual logo path if available -->
            <img src="https://cdn-icons-png.flaticon.com/512/3413/3413535.png" alt="Logo"> 
            <span class="logo-text">EduDash</span>
        </div>
        <a href="#" id="toggle-sidebar" class="d-md-none"><i class="fas fa-times"></i></a>
    </div>

    <div class="sidebar-profile">
        <div class="profile-img-container">
            <img src="https://i.pravatar.cc/150?img=11" alt="Admin" class="profile-img">
            <div class="status-indicator"></div>
        </div>
        <div class="profile-info">
            <h6>Saurabh Goel</h6>
            <span class="role-text">super_admin</span>
            <a href="#" class="btn-edit-profile"><i class="fas fa-user-edit"></i> Edit Profile</a>
        </div>
    </div>

    <div class="list-group list-group-flush">
        <a href="../../admin/index.php" class="list-group-item list-group-item-action bg-transparent active">
            <i class="fas fa-tachometer-alt menu-icon"></i> Dashboard
        </a>

        <!-- Centers (Added) -->
        <a href="#centersSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action bg-transparent">
            <i class="fas fa-building menu-icon"></i> Centers <i class="fas fa-chevron-right menu-arrow ms-auto"></i>
        </a>
        <div class="collapse" id="centersSubmenu">
            <div class="sub-menu">
                <a href="../../admin/centers/manage-centers.php" class="list-group-item list-group-item-action bg-transparent">Manage Centers</a>
                <a href="../../admin/centers/add-center.php" class="list-group-item list-group-item-action bg-transparent">Add Center</a>
                <a href="../../admin/centers/manage-center-wallet.php" class="list-group-item list-group-item-action bg-transparent">Wallet Management</a>
                <a href="../../admin/centers/manage-center-franchise-fees.php" class="list-group-item list-group-item-action bg-transparent">Franchise Fees</a>
            </div>
        </div>
        

        <!-- Fees Collection Example with dropdown -->
        <a href="#feesSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action bg-transparent">
            <i class="fas fa-book menu-icon"></i>Courses<i class="fas fa-chevron-right menu-arrow ms-auto"></i>
        </a>
        <div class="collapse" id="feesSubmenu">
            <div class="sub-menu">
                <a href="../../admin/courses/manage-categories.php" class="list-group-item list-group-item-action bg-transparent">Manage Course Categories</a>
                <a href="../../admin/courses/add-category.php" class="list-group-item list-group-item-action bg-transparent">Add Category</a>
                <a href="../../admin/courses/manage-courses.php" class="list-group-item list-group-item-action bg-transparent">Manage Courses</a>
                <a href="../../admin/courses/add-course.php" class="list-group-item list-group-item-action bg-transparent">Add Course</a>
            </div>
        </div>
        <!-- sessions -->
        <a href="#sessionsSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action bg-transparent">
            <i class="fas fa-calendar-alt menu-icon"></i>Sessions<i class="fas fa-chevron-right menu-arrow ms-auto"></i>
        </a>
        <div class="collapse" id="sessionsSubmenu">
            <div class="sub-menu">
                <a href="../../admin/sessions/manage-sessions.php" class="list-group-item list-group-item-action bg-transparent">Manage Sessions</a>
                <a href="../../admin/sessions/add-session.php" class="list-group-item list-group-item-action bg-transparent">Add Session</a>
            </div>
        </div>
        <!-- examinations -->
        <a href="#examinationsSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action bg-transparent">
            <i class="fas fa-file-alt menu-icon"></i>Examinations<i class="fas fa-chevron-right menu-arrow ms-auto"></i>
        </a>
        <div class="collapse" id="examinationsSubmenu">
            <div class="sub-menu">
                <a href="../../admin/examination/exam-schedule.php" class="list-group-item list-group-item-action bg-transparent">Exam Schedule</a>
                <a href="../../admin/examination/index.php" class="list-group-item list-group-item-action bg-transparent">Schedule List</a>
                <a href="../../admin/examination/create-exam.php" class="list-group-item list-group-item-action bg-transparent">Question Paper</a>
                <a href="../../admin/examination/manage-question-paper.php" class="list-group-item list-group-item-action bg-transparent">Manage Question Papers</a>
            </div>
        </div>
        <!-- locations -->
        <a href="#locationsSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action bg-transparent">
            <i class="fas fa-map-marker-alt menu-icon"></i>Locations<i class="fas fa-chevron-right menu-arrow ms-auto"></i>
        </a>
        <div class="collapse" id="locationsSubmenu">
            <div class="sub-menu">
                <a href="../../admin/locations/manage-countries.php" class="list-group-item list-group-item-action bg-transparent">Manage Country</a>
                <a href="../../admin/locations/add-country.php" class="list-group-item list-group-item-action bg-transparent">Add Country</a>
                <a href="../../admin/locations/manage-states.php" class="list-group-item list-group-item-action bg-transparent">Manage State</a>
                <a href="../../admin/locations/add-state.php" class="list-group-item list-group-item-action bg-transparent">Add State</a>
                <a href="../../admin/locations/manage-cities.php" class="list-group-item list-group-item-action bg-transparent">Manage City</a>
                <a href="../../admin/locations/add-city.php" class="list-group-item list-group-item-action bg-transparent">Add City</a>
            </div>
        </div>
    </div>
</div>
<!-- /#sidebar-wrapper -->
