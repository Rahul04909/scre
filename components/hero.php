<?php
// Hero Section Component
?>
<style>
    .hero-container {
        position: relative;
        overflow: hidden;
        border-radius: 20px; /* Rounded borders as requested */
        box-shadow: 0 10px 30px rgba(0,0,0,0.15); /* Professional shadow */
        margin: 20px 0; /* Vertical spacing */
    }

    .hero-slider .carousel-item {
        height: 500px; /* Fixed height for consistency */
        background-color: #000;
    }

    .hero-slider .carousel-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.8; /* Slight darken for text readability */
    }

    /* Overlay Text Centered Bottom */
    .hero-caption {
        position: absolute;
        bottom: 40px; /* Positioned at bottom */
        left: 0;
        right: 0;
        text-align: center;
        z-index: 10;
        padding: 20px;
        background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%); /* Gradient backdrop */
    }

    .hero-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 10px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    }

    .hero-btn {
        background-color: #0d6efd; /* Bootstrap Primary */
        color: #fff;
        padding: 10px 30px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        border: 2px solid #0d6efd;
        display: inline-block;
        margin-top: 15px;
    }

    .hero-btn:hover {
        background-color: transparent;
        color: #fff;
        border-color: #fff;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .hero-slider .carousel-item {
            height: 350px;
        }
        .hero-title {
            font-size: 1.8rem;
        }
    }
</style>

<div class="container"> <!-- Container ensures equal left/right margins -->
    <div class="hero-container">
        <div id="heroCarousel" class="carousel slide hero-slider" data-bs-ride="carousel">
            
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
                    <img src="assets/images/slider/1.jpg" alt="Education Excellence"> <!-- Placeholder path -->
                    <div class="hero-caption">
                        <h2 class="hero-title">Empowering Future Leaders</h2>
                        <a href="courses.php" class="hero-btn">Explore Courses</a>
                    </div>
                </div>

                <!-- Slide 2 -->
                <div class="carousel-item">
                    <img src="assets/images/slider/2.jpg" alt="Skill Development">
                    <div class="hero-caption">
                        <h2 class="hero-title">Advanced Skill Development</h2>
                        <a href="registration.php" class="hero-btn">Join Now</a>
                    </div>
                </div>

                <!-- Slide 3 -->
                <div class="carousel-item">
                    <img src="assets/images/slider/3.jpg" alt="Certified Programs">
                    <div class="hero-caption">
                        <h2 class="hero-title">ISO Certified Programs</h2>
                        <a href="contact.php" class="hero-btn">Contact Us</a>
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
