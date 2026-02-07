<!-- Sidebar -->
<?php $sidebarPrefix = isset($sidebarPrefix) ? $sidebarPrefix : '../../'; ?>
<div id="sidebar-wrapper">
    <div class="sidebar-heading">
        <div class="d-flex align-items-center justify-content-center w-100">
            <img src="<?php echo $sidebarPrefix; ?>assets/logo/logo.jpeg" alt="Logo" class="sidebar-logo">
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
        <a href="<?php echo $sidebarPrefix; ?>admin/index.php" class="list-group-item list-group-item-action bg-transparent active">
            <i class="fas fa-tachometer-alt menu-icon"></i> Dashboard
        </a>

        <!-- Gallery Menu -->
        <a href="#gallerySubmenu" data-bs-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action bg-transparent second-text fw-bold">
            <i class="fas fa-images me-2"></i>Gallery <i class="fas fa-chevron-down ms-auto"></i>
        </a>
        <div class="collapse" id="gallerySubmenu">
            <div class="sub-menu">
                <a href="<?php echo $sidebarPrefix; ?>admin/gallery/manage-categories.php" class="list-group-item list-group-item-action bg-transparent">Categories</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/gallery/manage-images.php" class="list-group-item list-group-item-action bg-transparent">Images</a>
            </div>
        </div>

        <a href="#" class="list-group-item list-group-item-action bg-transparent second-text fw-bold">
            <i class="fas fa-cog me-2"></i>Settings
        </a>

        <!-- Centers (Added) -->
        <a href="#centersSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action bg-transparent">
            <i class="fas fa-building menu-icon"></i> Centers <i class="fas fa-chevron-right menu-arrow ms-auto"></i>
        </a>
        <div class="collapse" id="centersSubmenu">
            <div class="sub-menu">
                <a href="<?php echo $sidebarPrefix; ?>admin/centers/manage-centers.php" class="list-group-item list-group-item-action bg-transparent">Manage Centers</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/centers/add-center.php" class="list-group-item list-group-item-action bg-transparent">Add Center</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/centers/manage-center-wallet.php" class="list-group-item list-group-item-action bg-transparent">Wallet Management</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/centers/manage-center-franchise-fees.php" class="list-group-item list-group-item-action bg-transparent">Franchise Fees</a>
            </div>
        </div>

        <!-- Students -->
        <a href="#studentsSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action bg-transparent">
            <i class="fas fa-user-graduate menu-icon"></i> Students <i class="fas fa-chevron-right menu-arrow ms-auto"></i>
        </a>
        <div class="collapse" id="studentsSubmenu">
            <div class="sub-menu">
                <a href="<?php echo $sidebarPrefix; ?>admin/students/index.php" class="list-group-item list-group-item-action bg-transparent">Manage Students</a>
            </div>
        </div>
        

        <!-- Fees Collection Example with dropdown -->
        <a href="#feesSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action bg-transparent">
            <i class="fas fa-book menu-icon"></i>Courses<i class="fas fa-chevron-right menu-arrow ms-auto"></i>
        </a>
        <div class="collapse" id="feesSubmenu">
            <div class="sub-menu">
                <a href="<?php echo $sidebarPrefix; ?>admin/courses/manage-categories.php" class="list-group-item list-group-item-action bg-transparent">Manage Course Categories</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/courses/add-category.php" class="list-group-item list-group-item-action bg-transparent">Add Category</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/courses/manage-courses.php" class="list-group-item list-group-item-action bg-transparent">Manage Courses</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/courses/add-course.php" class="list-group-item list-group-item-action bg-transparent">Add Course</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/courses/manage-subjects.php" class="list-group-item list-group-item-action bg-transparent">Manage Subjects</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/courses/add-subject.php" class="list-group-item list-group-item-action bg-transparent">Add Subject</a>
            </div>
        </div>
        <!-- sessions -->
        <a href="#sessionsSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action bg-transparent">
            <i class="fas fa-calendar-alt menu-icon"></i>Sessions<i class="fas fa-chevron-right menu-arrow ms-auto"></i>
        </a>
        <div class="collapse" id="sessionsSubmenu">
            <div class="sub-menu">
                <a href="<?php echo $sidebarPrefix; ?>admin/sessions/manage-sessions.php" class="list-group-item list-group-item-action bg-transparent">Manage Sessions</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/sessions/add-session.php" class="list-group-item list-group-item-action bg-transparent">Add Session</a>
            </div>
        </div>
        <!-- examinations -->
        <a href="#examinationsSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action bg-transparent">
            <i class="fas fa-file-alt menu-icon"></i>Examinations<i class="fas fa-chevron-right menu-arrow ms-auto"></i>
        </a>
        <div class="collapse" id="examinationsSubmenu">
            <div class="sub-menu">
                <a href="<?php echo $sidebarPrefix; ?>admin/examination/exam-schedule.php" class="list-group-item list-group-item-action bg-transparent">Exam Schedule</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/examination/index.php" class="list-group-item list-group-item-action bg-transparent">Schedule List</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/examination/create-exam.php" class="list-group-item list-group-item-action bg-transparent">Question Paper</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/examination/manage-question-paper.php" class="list-group-item list-group-item-action bg-transparent">Manage Question Papers</a>
            </div>
        </div>
        <!-- locations -->
        <a href="#locationsSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action bg-transparent">
            <i class="fas fa-map-marker-alt menu-icon"></i>Locations<i class="fas fa-chevron-right menu-arrow ms-auto"></i>
        </a>
        <div class="collapse" id="locationsSubmenu">
            <div class="sub-menu">
                <a href="<?php echo $sidebarPrefix; ?>admin/locations/manage-countries.php" class="list-group-item list-group-item-action bg-transparent">Manage Country</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/locations/add-country.php" class="list-group-item list-group-item-action bg-transparent">Add Country</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/locations/manage-states.php" class="list-group-item list-group-item-action bg-transparent">Manage State</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/locations/add-state.php" class="list-group-item list-group-item-action bg-transparent">Add State</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/locations/manage-cities.php" class="list-group-item list-group-item-action bg-transparent">Manage City</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/locations/add-city.php" class="list-group-item list-group-item-action bg-transparent">Add City</a>
            </div>
        </div>

        <!-- Marksheet -->
        <a href="#marksheetSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action bg-transparent">
            <i class="fas fa-file-invoice menu-icon"></i>Marksheet<i class="fas fa-chevron-right menu-arrow ms-auto"></i>
        </a>
        <div class="collapse" id="marksheetSubmenu">
            <div class="sub-menu">
                <a href="<?php echo $sidebarPrefix; ?>admin/marksheet/index.php" class="list-group-item list-group-item-action bg-transparent">Manage Marksheet</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/marksheet/generate-marksheet.php" class="list-group-item list-group-item-action bg-transparent">Generate Marksheet</a>
            </div>
        </div>

        <!-- Typing Master -->
        <a href="#typingMasterSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action bg-transparent">
            <i class="fas fa-keyboard menu-icon"></i>Typing Master<i class="fas fa-chevron-right menu-arrow ms-auto"></i>
        </a>
        <div class="collapse" id="typingMasterSubmenu">
            <div class="sub-menu">
                <a href="<?php echo $sidebarPrefix; ?>admin/typing-master/manage-languages.php" class="list-group-item list-group-item-action bg-transparent">Manage Languages</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/typing-master/add-language.php" class="list-group-item list-group-item-action bg-transparent">Add Language</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/typing-master/manage-lessons.php" class="list-group-item list-group-item-action bg-transparent">Manage Lessons</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/typing-master/add-lesson.php" class="list-group-item list-group-item-action bg-transparent">Add Lesson</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/typing-master/manage-practice-tests.php" class="list-group-item list-group-item-action bg-transparent">Manage Practice Tests</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/typing-master/add-practice-test.php" class="list-group-item list-group-item-action bg-transparent">Add Practice Test</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/typing-master/allot-typing-master.php" class="list-group-item list-group-item-action bg-transparent">Allot to Course</a>
            </div>
        </div>

        <!-- Pages -->
        <a href="#pagesSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action bg-transparent">
            <i class="fas fa-columns menu-icon"></i>Pages<i class="fas fa-chevron-right menu-arrow ms-auto"></i>
        </a>
        <div class="collapse" id="pagesSubmenu">
            <div class="sub-menu">
                <a href="<?php echo $sidebarPrefix; ?>admin/pages/manage-verification.php" class="list-group-item list-group-item-action bg-transparent">Manage Verification</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/pages/manage-downloads.php" class="list-group-item list-group-item-action bg-transparent">Manage Downloads</a>
            </div>
        </div>
        
        <!-- Frontend Components -->
        <a href="#frontendSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action bg-transparent">
            <i class="fas fa-laptop-code menu-icon"></i>Frontend<i class="fas fa-chevron-right menu-arrow ms-auto"></i>
        </a>
        <div class="collapse" id="frontendSubmenu">
            <div class="sub-menu">
                <a href="<?php echo $sidebarPrefix; ?>admin/components/manage-hero.php" class="list-group-item list-group-item-action bg-transparent">Hero Slider</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/components/manage-news.php" class="list-group-item list-group-item-action bg-transparent">Latest News</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/components/manage-partners.php" class="list-group-item list-group-item-action bg-transparent">Our Partners</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/components/manage-teachers.php" class="list-group-item list-group-item-action bg-transparent">Teachers</a>
            </div>
        </div>

        <!-- Blogs Menu -->
        <a href="#blogSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="list-group-item list-group-item-action bg-transparent second-text fw-bold">
            <i class="fas fa-blog me-2"></i>Blogs <i class="fas fa-chevron-down ms-auto"></i>
        </a>
        <div class="collapse" id="blogSubmenu">
            <div class="sub-menu">
                <a href="<?php echo $sidebarPrefix; ?>admin/blog/manage-categories.php" class="list-group-item list-group-item-action bg-transparent">Categories</a>
                <a href="<?php echo $sidebarPrefix; ?>admin/blog/manage-blogs.php" class="list-group-item list-group-item-action bg-transparent">Manage Blogs</a>
            </div>
        </div>
    </div>
</div>
<!-- /#sidebar-wrapper -->
