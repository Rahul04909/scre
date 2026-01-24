<nav class="navbar navbar-expand-lg navbar-dark bg-dark-theme border-bottom shadow-sm px-4 sticky-top">
    <div class="container-fluid p-0">
        <button class="btn btn-outline-light btn-sm me-3" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        
        <h5 class="navbar-brand m-0 text-white d-none d-md-block">Admin Dashboard</h5>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mt-2 mt-lg-0 align-items-center">
                <li class="nav-item position-relative mx-3">
                    <a class="nav-link text-white-50 hover-white" href="#"><i class="fas fa-bell fa-lg"></i>
                    <span class="position-absolute top-10 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                        <span class="visually-hidden">New alerts</span>
                    </span>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center text-white" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="https://i.pravatar.cc/150?img=11" alt="admin" class="rounded-circle me-2 border border-white" style="width:32px; height: 32px;">
                        <span class="fw-semibold">Saurabh Goel</span>
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
