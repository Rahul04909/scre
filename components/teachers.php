<?php
// Teachers Component
?>
<!-- Bootstrap 5 CSS (Ensure this is loaded in your header or here) -->
<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->

<style>
    .teachers-section-wrapper {
        margin-top: 60px;
        margin-bottom: 60px;
        font-family: 'Poppins', sans-serif;
        max-width: 98%; /* Matching Hero Section Width */
        margin-left: auto;
        margin-right: auto;
    }

    .teachers-heading-area {
        margin-bottom: 50px;
    }

    .teachers-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1a1a1a;
        line-height: 1.2;
    }

    .highlight-text {
        color: #ff9800; /* Orange highlight */
        position: relative;
        display: inline-block;
    }
    
    .highlight-text::after {
        content: '';
        position: absolute;
        bottom: 2px;
        left: 0;
        width: 100%;
        height: 8px;
        background-color: #ffcc80;
        opacity: 0.4;
        z-index: -1;
        border-radius: 4px;
    }

    /* Feature Icons Row */
    .feature-row {
        background-color: #f8f9fa;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 40px;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-around;
        align-items: center;
        gap: 20px;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 15px;
        max-width: 350px;
    }

    .feature-icon {
        width: 50px;
        height: 50px;
        background-color: #e3f2fd;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #0d47a1;
        flex-shrink: 0;
    }

    .feature-text {
        font-size: 0.9rem;
        color: #555;
        line-height: 1.4;
    }

    /* Teacher Cards */
    .teacher-card {
        border: NONE;
        background: transparent;
        position: relative;
        text-align: center;
        margin-bottom: 30px;
        transition: transform 0.3s ease;
    }

    .teacher-card:hover {
        transform: translateY(-10px);
    }

    .teacher-image-wrapper {
        position: relative;
        z-index: 1;
        margin-bottom: -40px; /* Overlap with bottom card */
        height: 250px; /* Fixed height for consistency */
        display: flex;
        align-items: flex-end;
        justify-content: center;
    }

    .teacher-img {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
        filter: drop-shadow(0 5px 10px rgba(0,0,0,0.1));
    }

    .teacher-info-card {
        background-color: #1a2c3f; /* Dark Blue/Black */
        color: #fff;
        border-radius: 20px;
        padding: 50px 20px 30px; /* Top padding for image overlap */
        position: relative;
        z-index: 0;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .exp-badge {
        position: absolute;
        top: -15px; /* Stick out top of info card */
        left: 50%;
        transform: translateX(-50%);
        background-color: #e0f2f1;
        color: #00695c;
        padding: 5px 15px;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        z-index: 2;
    }

    .teacher-name {
        font-size: 1.4rem;
        font-weight: 700;
        margin-top: 10px;
        margin-bottom: 5px;
        color: #fff;
    }

    .teacher-designation {
        font-size: 0.9rem;
        color: #cfd8dc; /* Light grey */
        margin-bottom: 5px;
    }

    .teacher-college {
        font-size: 1rem;
        font-weight: 600;
        color: #ff9800; /* Highlight color */
    }

    /* Scroll/Navigation Buttons */
    .scroll-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #1a2c3f;
        color: #fff;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        position: absolute;
        top: 50%;
        transform: translateY(-50);
        z-index: 10;
        transition: all 0.3s ease;
    }
    
    .scroll-btn:hover {
        background-color: #ff9800;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .teachers-title {
            font-size: 1.8rem;
            text-align: center;
        }
        .feature-row {
            flex-direction: column;
            align-items: flex-start;
        }
        .teacher-info-card {
            padding-top: 40px;
        }
    }
</style>

<div class="container-fluid teachers-section-wrapper">
    
    <!-- Heading -->
    <div class="row align-items-center teachers-heading-area">
        <div class="col-md-8">
            <h2 class="teachers-title">
                All teachers teach.<br>
                Greatest <span class="highlight-text">teachers</span> inspire
            </h2>
        </div>
        <div class="col-md-4 text-md-end d-none d-md-block">
            <!-- Optional View All Button or Arrows -->
        </div>
    </div>

    <!-- Features (from image top part) -->
    <div class="feature-row">
        <div class="feature-item">
            <div class="feature-icon"><i class="fas fa-graduation-cap"></i></div>
            <div class="feature-text">From Top-tier colleges with many years of experience</div>
        </div>
        <div class="feature-item">
            <div class="feature-icon"><i class="fas fa-chalkboard-teacher"></i></div>
            <div class="feature-text">Specially-trained teachers to bring out the best in every student.</div>
        </div>
        <div class="feature-item">
            <div class="feature-icon"><i class="fas fa-hourglass-half"></i></div>
            <div class="feature-text">Teaching experience of over 4.5 crore hours to 10 lakh students.</div>
        </div>
    </div>

    <!-- Teacher Cards Grid -->
    <div class="row g-4 justify-content-center">
        
        <!-- Teacher 1 -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-10">
            <div class="teacher-card">
                <div class="teacher-image-wrapper">
                    <!-- Placeholder for transparent PNG cutout -->
                    <img src="https://png.pngtree.com/png-vector/20230928/ourmid/pngtree-young-indian-man-png-image_10149659.png" class="teacher-img" alt="Teacher">
                </div>
                <div class="teacher-info-card">
                    <span class="exp-badge">10+ years exp</span>
                    <h3 class="teacher-name">Rahul Sharma</h3>
                    <p class="teacher-designation">Chemistry Master Teacher</p>
                    <p class="teacher-college">IIT Bombay</p>
                </div>
            </div>
        </div>

        <!-- Teacher 2 -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-10">
            <div class="teacher-card">
                <div class="teacher-image-wrapper">
                    <img src="https://png.pngtree.com/png-vector/20240321/ourmid/pngtree-young-man-expression-png-image_12015521.png" class="teacher-img" alt="Teacher">
                </div>
                <div class="teacher-info-card">
                    <span class="exp-badge">9+ years exp</span>
                    <h3 class="teacher-name">Harsh Priyam</h3>
                    <p class="teacher-designation">Math Master Teacher</p>
                    <p class="teacher-college">BIT Durg</p>
                </div>
            </div>
        </div>

        <!-- Teacher 3 -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-10">
            <div class="teacher-card">
                <div class="teacher-image-wrapper">
                    <img src="https://png.pngtree.com/png-vector/20230906/ourmid/pngtree-man-in-blue-shirt-png-image_10006245.png" class="teacher-img" alt="Teacher">
                </div>
                <div class="teacher-info-card">
                    <span class="exp-badge">12+ years exp</span>
                    <h3 class="teacher-name">Shreyas</h3>
                    <p class="teacher-designation">Physics Master Teacher</p>
                    <p class="teacher-college">NIT Nagpur</p>
                </div>
            </div>
        </div>

        <!-- Teacher 4 -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-10">
            <div class="teacher-card">
                <div class="teacher-image-wrapper">
                    <img src="https://png.pngtree.com/png-vector/20240205/ourmid/pngtree-indian-teacher-teaching-and-giving-lecture-png-image_11612043.png" class="teacher-img" alt="Teacher">
                </div>
                <div class="teacher-info-card">
                    <span class="exp-badge">14+ years exp</span>
                    <h3 class="teacher-name">Rama</h3>
                    <p class="teacher-designation">Physics Master Teacher</p>
                    <p class="teacher-college">M.Sc University</p>
                </div>
            </div>
        </div>

    </div>

</div>
