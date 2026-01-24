<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EduDash</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Dashboard Widget CSS -->
    <link href="assets/css/dashboard.css" rel="stylesheet">
</head>
<body>

    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Page Content -->
        <div id="page-content-wrapper" style="margin-left: 280px; transition: margin 0.25s ease-out;">
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm px-4">
                <div class="container-fluid p-0">
                    <button class="btn btn-outline-secondary btn-sm" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                    
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav ms-auto mt-2 mt-lg-0 align-items-center">
                            <li class="nav-item position-relative mx-3">
                                <a class="nav-link text-secondary" href="#"><i class="fas fa-bell fa-lg"></i>
                                <span class="position-absolute top-10 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                                    <span class="visually-hidden">New alerts</span>
                                </span>
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <img src="https://i.pravatar.cc/150?img=11" alt="admin" class="rounded-circle me-2" style="width:32px; height: 32px;">
                                    <span class="fw-semibold text-dark">Saurabh Goel</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end shadow-lg border-0" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="#"><i class="fas fa-user me-2 text-muted"></i> Profile</a>
                                    <a class="dropdown-item" href="#"><i class="fas fa-cog me-2 text-muted"></i> Settings</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="#"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="container-fluid py-5 px-lg-5">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="fw-bold text-dark">Dashboard Overview</h2>
                        <p class="text-muted">Welcome back, here's what's happening at your center today.</p>
                    </div>
                </div>

                <!-- Stat Cards Row -->
                <div class="row">
                    <!-- Total Students -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card card-vibrant-blue h-100">
                            <div class="card-body">
                                <div class="stat-content">
                                    <h3>3,520</h3>
                                    <h6>Total Students</h6>
                                </div>
                                <i class="fas fa-users stat-icon-bg"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Assigned Courses (Teachers/Courses) -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card card-vibrant-green h-100">
                            <div class="card-body">
                                <div class="stat-content">
                                    <h3>42</h3>
                                    <h6>Assigned Courses</h6>
                                </div>
                                <i class="fas fa-book stat-icon-bg"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Revenue -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card card-vibrant-cyan h-100">
                            <div class="card-body">
                                <div class="stat-content">
                                    <h3>$52k</h3>
                                    <h6>Monthly Revenue</h6>
                                </div>
                                <i class="fas fa-dollar-sign stat-icon-bg"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Exams -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card card-vibrant-orange h-100">
                            <div class="card-body">
                                <div class="stat-content">
                                    <h3>12</h3>
                                    <h6>Upcoming Exams</h6>
                                </div>
                                <i class="fas fa-calendar-alt stat-icon-bg"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Fees Table -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold text-dark">Recent Fee Collections</h5>
                                <a href="#" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4">Transaction ID</th>
                                                <th>Student Name</th>
                                                <th>Class</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th class="text-end pe-4">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="ps-4 fw-bold">#TXN-7859</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="https://i.pravatar.cc/150?img=3" class="rounded-circle me-2" width="30">
                                                        <span>John Doe</span>
                                                    </div>
                                                </td>
                                                <td>Class 10-A</td>
                                                <td class="fw-bold">$1,200</td>
                                                <td><span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Completed</span></td>
                                                <td class="text-muted">Oct 24, 2025</td>
                                                <td class="text-end pe-4">
                                                    <button class="btn btn-sm btn-light text-primary"><i class="fas fa-eye"></i></button>
                                                    <button class="btn btn-sm btn-light text-danger"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="ps-4 fw-bold">#TXN-7860</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="https://i.pravatar.cc/150?img=5" class="rounded-circle me-2" width="30">
                                                        <span>Sarah Smith</span>
                                                    </div>
                                                </td>
                                                <td>Class 8-B</td>
                                                <td class="fw-bold">$850</td>
                                                <td><span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">Pending</span></td>
                                                <td class="text-muted">Oct 24, 2025</td>
                                                <td class="text-end pe-4">
                                                    <button class="btn btn-sm btn-light text-primary"><i class="fas fa-eye"></i></button>
                                                    <button class="btn btn-sm btn-light text-danger"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="ps-4 fw-bold">#TXN-7861</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="https://i.pravatar.cc/150?img=8" class="rounded-circle me-2" width="30">
                                                        <span>Michael Johnson</span>
                                                    </div>
                                                </td>
                                                <td>Class 12-C</td>
                                                <td class="fw-bold">$1,500</td>
                                                <td><span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Completed</span></td>
                                                <td class="text-muted">Oct 23, 2025</td>
                                                <td class="text-end pe-4">
                                                    <button class="btn btn-sm btn-light text-primary"><i class="fas fa-eye"></i></button>
                                                    <button class="btn btn-sm btn-light text-danger"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="ps-4 fw-bold">#TXN-7862</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="https://i.pravatar.cc/150?img=9" class="rounded-circle me-2" width="30">
                                                        <span>Emily Davis</span>
                                                    </div>
                                                </td>
                                                <td>Class 9-A</td>
                                                <td class="fw-bold">$920</td>
                                                <td><span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Failed</span></td>
                                                <td class="text-muted">Oct 23, 2025</td>
                                                <td class="text-end pe-4">
                                                    <button class="btn btn-sm btn-light text-primary"><i class="fas fa-eye"></i></button>
                                                    <button class="btn btn-sm btn-light text-danger"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- /#wrapper -->

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Scripts -->
    <script src="assets/js/sidebar.js"></script>
    <script>
        // Simple toggle for mobile testing/demo
        document.getElementById("sidebarToggle").addEventListener("click", function(){
            const wrapper = document.getElementById('wrapper');
            const sidebar = document.getElementById('sidebar-wrapper');
            const content = document.getElementById('page-content-wrapper');
            
            if (sidebar.style.marginLeft === '-280px') {
                sidebar.style.marginLeft = '0px';
                content.style.marginLeft = '280px';
            } else {
                sidebar.style.marginLeft = '-280px';
                content.style.marginLeft = '0px';
            }
        });
    </script>
</body>
</html>
