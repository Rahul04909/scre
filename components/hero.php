<?php
// Hero Section Component
?>
<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Custom Styles for Hero -->
<style>
    .hero-section-wrapper {
        margin-top: 20px;
        margin-bottom: 40px;
        font-family: 'Poppins', sans-serif;
        max-width: 95%; /* Increased width as requested */
    }

    .hero-container {
        position: relative;
        overflow: hidden;
        border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.2);
    }

    /* Carousel Items */
    .hero-slider .carousel-item {
        height: 550px;
        background-color: #000;
        position: relative;
    }

    .hero-slider .carousel-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.85;
        transition: transform 7s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    .hero-slider .carousel-item.active img {
        transform: scale(1.05); /* Subtle zoom effect */
    }

    /* Overlay Gradient & Text */
    .hero-caption {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 60px 20px 40px;
        background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.6) 50%, rgba(0,0,0,0) 100%);
        text-align: center;
        z-index: 10;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
        height: 100%;
        pointer-events: none; /* Let clicks pass through if needed, but button needs pointer-events: auto */
    }

    .hero-content {
        pointer-events: auto;
        max-width: 800px;
        margin-bottom: 30px;
        transform: translateY(20px);
        opacity: 0;
        animation: fadeInUp 1s ease forwards 0.5s;
    }

    /* Animations */
    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .hero-title {
        font-size: 3rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 15px;
        text-shadow: 2px 2px 10px rgba(0,0,0,0.5);
        letter-spacing: 1px;
    }

    .hero-subtitle {
        font-size: 1.2rem;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 25px;
        font-weight: 300;
    }

    /* Action Button */
    .hero-btn {
        background: linear-gradient(135deg, #ffb74d 0%, #ff9800 100%);
        color: #fff;
        padding: 12px 40px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: none;
        box-shadow: 0 5px 15px rgba(255, 152, 0, 0.4);
        display: inline-block;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .hero-btn:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 8px 25px rgba(255, 152, 0, 0.6);
        color: #fff;
    }

    /* Carousel Indicators */
    .carousel-indicators {
        bottom: 20px;
    }
    
    .carousel-indicators button {
        width: 12px !important;
        height: 12px !important;
        border-radius: 50%;
        background-color: rgba(255,255,255,0.5) !important;
        border: 2px solid transparent !important;
        margin: 0 6px !important;
        transition: all 0.3s ease;
        opacity: 1 !important;
    }

    .carousel-indicators button.active {
        background-color: #ff9800 !important;
        transform: scale(1.2);
        box-shadow: 0 0 10px rgba(255, 152, 0, 0.5);
    }

    /* Navigation Arrows */
    .carousel-control-prev, .carousel-control-next {
        width: 5%;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .hero-container:hover .carousel-control-prev,
    .hero-container:hover .carousel-control-next {
        opacity: 1;
    }

    .carousel-control-prev-icon, .carousel-control-next-icon {
        background-color: rgba(0,0,0,0.5);
        border-radius: 50%;
        padding: 25px;
        background-size: 50% 50%;
    }

    /* Responsive */
    @media (max-width: 991px) {
        .hero-slider .carousel-item { height: 450px; }
        .hero-title { font-size: 2.2rem; }
    }
    
    @media (max-width: 768px) {
        .hero-slider .carousel-item { height: 400px; }
        .hero-title { font-size: 1.8rem; }
        .hero-subtitle { font-size: 1rem; }
        .hero-btn { padding: 10px 30px; font-size: 1rem; }
        .hero-caption { padding-bottom: 30px; }
    }
</style>

<div class="container hero-section-wrapper">
    <div class="hero-container">
        <div id="heroCarousel" class="carousel slide hero-slider carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
            
            <!-- Indicators -->
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
            </div>

            <!-- Slides -->
            <div class="carousel-inner">
                <!-- Slide 1 -->
                <div class="carousel-item active">
                    <img src="assets/images/slider/1.jpg" alt="Education Excellence">
                    <div class="hero-caption">
                        <div class="hero-content">
                            <h2 class="hero-title">Empowering Future Leaders</h2>
                            <p class="hero-subtitle">Quality education that transforms lives and builds careers.</p>
                            <a href="courses.php" class="hero-btn">Explore Courses</a>
                        </div>
                    </div>
                </div>

                <!-- Slide 2 -->
                <div class="carousel-item">
                    <img src="assets/images/slider/2.jpg" alt="Skill Development">
                    <div class="hero-caption">
                        <div class="hero-content">
                            <h2 class="hero-title">Advanced Skill Development</h2>
                            <p class="hero-subtitle">Master the latest technologies with our expert-led programs.</p>
                            <a href="registration.php" class="hero-btn">Join Now</a>
                        </div>
                    </div>
                </div>

                <!-- Slide 3 -->
                <div class="carousel-item">
                    <img src="assets/images/slider/3.jpg" alt="Certified Programs">
                    <div class="hero-caption">
                        <div class="hero-content">
                            <h2 class="hero-title">ISO Certified Programs</h2>
                            <p class="hero-subtitle">Recognized certifications to boost your professional portfolio.</p>
                            <a href="contact.php" class="hero-btn">Contact Us</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controls -->
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
