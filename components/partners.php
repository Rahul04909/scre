<?php
// Partners Component
?>
<style>
    .partners-section-wrapper {
        padding: 50px 0;
        background-color: #fff;
        overflow: hidden; /* Hide scrollbar for ticker */
        font-family: 'Poppins', sans-serif;
    }

    .partners-container {
        max-width: 98%;
        margin: 0 auto;
    }

    .partners-title {
        text-align: center;
        margin-bottom: 40px;
        font-size: 2rem;
        font-weight: 700;
        color: #1a1a1a;
    }

    .partners-title span {
        color: #0d6efd; /* Blue highlight */
        position: relative;
    }
    
    .partners-title span::after {
        content: '';
        display: block;
        width: 100%;
        height: 3px;
        background: #0d6efd;
        position: absolute;
        bottom: -5px;
        left: 0;
        border-radius: 2px;
    }

    /* Ticker Animation */
    @keyframes ticker {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); } /* Move half the width (since we duplicate content) */
    }

    .partners-ticker-wrapper {
        width: 100%;
        overflow: hidden;
        position: relative;
        padding: 20px 0;
    }
    
    .partners-ticker-wrapper::before,
    .partners-ticker-wrapper::after {
        content: "";
        position: absolute;
        top: 0;
        width: 100px;
        height: 100%;
        z-index: 2;
        pointer-events: none;
    }
    
    .partners-ticker-wrapper::before {
        left: 0;
        background: linear-gradient(to right, white, transparent);
    }
    
    .partners-ticker-wrapper::after {
        right: 0;
        background: linear-gradient(to left, white, transparent);
    }

    .partners-track {
        display: flex;
        width: max-content; /* Allow track to be as wide as needed */
        animation: ticker 30s linear infinite; /* Adjust speed here */
    }
    
    .partners-track:hover {
        animation-play-state: paused; /* Pause on hover */
    }

    .partner-logo-item {
        flex: 0 0 auto;
        width: 200px; /* Width of each logo slot */
        padding: 0 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .partner-logo {
        max-width: 100%;
        max-height: 80px;
        object-fit: contain;
        /* No grayscale filter as requested */
        transition: transform 0.3s ease;
    }

    .partner-logo:hover {
        transform: scale(1.1);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .partners-title { font-size: 1.5rem; }
        .partner-logo-item { width: 150px; padding: 0 20px; }
        .partners-track { animation-duration: 20s; } /* Faster on small screens visually */
    }
</style>

<div class="partners-section-wrapper">
    <div class="container-fluid partners-container">
        
        <h2 class="partners-title">Our Trusted <span>Partners</span></h2>

        <div class="partners-ticker-wrapper">
            <div class="partners-track">
                <!-- Logos (Duplicated for infinite scroll effect) -->
                <!-- Set 1 -->
                <div class="partner-logo-item"><img src="https://upload.wikimedia.org/wikipedia/commons/2/2f/Google_2015_logo.svg" class="partner-logo" alt="Google"></div>
                <div class="partner-logo-item"><img src="https://upload.wikimedia.org/wikipedia/commons/4/44/Microsoft_logo.svg" class="partner-logo" alt="Microsoft"></div>
                <div class="partner-logo-item"><img src="https://upload.wikimedia.org/wikipedia/commons/5/51/IBM_logo.svg" class="partner-logo" alt="IBM"></div>
                <div class="partner-logo-item"><img src="https://upload.wikimedia.org/wikipedia/commons/9/96/Cisco_logo_blue_2016.svg" class="partner-logo" alt="Cisco"></div>
                <div class="partner-logo-item"><img src="https://upload.wikimedia.org/wikipedia/commons/0/08/Netflix_2015_logo.svg" class="partner-logo" alt="Netflix"></div>
                <div class="partner-logo-item"><img src="https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg" class="partner-logo" alt="Amazon"></div>
                <div class="partner-logo-item"><img src="https://upload.wikimedia.org/wikipedia/commons/3/30/Red_Hat_Logo.png" class="partner-logo" alt="RedHat"></div>
                <div class="partner-logo-item"><img src="https://upload.wikimedia.org/wikipedia/commons/8/87/Arduino_Logo.svg" class="partner-logo" alt="Arduino"></div>

                <!-- Set 2 (Duplicate of Set 1) -->
                <div class="partner-logo-item"><img src="https://upload.wikimedia.org/wikipedia/commons/2/2f/Google_2015_logo.svg" class="partner-logo" alt="Google"></div>
                <div class="partner-logo-item"><img src="https://upload.wikimedia.org/wikipedia/commons/4/44/Microsoft_logo.svg" class="partner-logo" alt="Microsoft"></div>
                <div class="partner-logo-item"><img src="https://upload.wikimedia.org/wikipedia/commons/5/51/IBM_logo.svg" class="partner-logo" alt="IBM"></div>
                <div class="partner-logo-item"><img src="https://upload.wikimedia.org/wikipedia/commons/9/96/Cisco_logo_blue_2016.svg" class="partner-logo" alt="Cisco"></div>
                <div class="partner-logo-item"><img src="https://upload.wikimedia.org/wikipedia/commons/0/08/Netflix_2015_logo.svg" class="partner-logo" alt="Netflix"></div>
                <div class="partner-logo-item"><img src="https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg" class="partner-logo" alt="Amazon"></div>
                <div class="partner-logo-item"><img src="https://upload.wikimedia.org/wikipedia/commons/3/30/Red_Hat_Logo.png" class="partner-logo" alt="RedHat"></div>
                <div class="partner-logo-item"><img src="https://upload.wikimedia.org/wikipedia/commons/8/87/Arduino_Logo.svg" class="partner-logo" alt="Arduino"></div>
            </div>
        </div>

    </div>
</div>
