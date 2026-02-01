<?php
// News Ticker Component
// Future: Fetch from DB (news_updates table)
?>
<style>
    .news-ticker-fixed {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background-color: #1a2c3f; /* Dark Premium Blue */
        color: #fff;
        z-index: 1000; /* High z-index to stay on top */
        font-family: 'Poppins', sans-serif;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.2);
        display: flex;
        height: 50px;
        align-items: center;
    }

    .ticker-label {
        background-color: #dc3545; /* Red Attention Color */
        color: #fff;
        font-weight: 700;
        text-transform: uppercase;
        padding: 0 20px;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        width: auto;
        min-width: 140px;
        font-size: 0.9rem;
        position: relative;
        z-index: 2;
        flex-shrink: 0;
    }
    
    .ticker-label::after {
        content: '';
        position: absolute;
        top: 0;
        right: -15px;
        width: 0;
        height: 0;
        border-style: solid;
        border-width: 50px 0 0 15px; /* Angle effect */
        border-color: transparent transparent transparent #dc3545;
        z-index: 2;
    }

    .ticker-content-wrapper {
        flex: 1;
        overflow: hidden;
        position: relative;
        height: 100%;
        display: flex;
        align-items: center;
    }

    .ticker-content {
        display: inline-block;
        white-space: nowrap;
        animation: scroll-text 20s linear infinite;
        padding-left: 100%; /* Start from outside right */
    }

    @keyframes scroll-text {
        0% { transform: translateX(0); }
        100% { transform: translateX(-100%); }
    }
    
    .ticker-item {
        margin-right: 50px;
        display: inline-flex;
        align-items: center;
    }
    
    .ticker-item a {
        color: #fff;
        text-decoration: none;
        font-size: 0.95rem;
        transition: color 0.2s;
    }
    
    .ticker-item a:hover {
        color: #ffc107; /* Yellow on hover */
        text-decoration: underline;
    }
    
    .ticker-item i {
        margin-right: 8px;
        color: #ffc107;
        font-size: 0.8rem;
    }

    /* Pause on Hover */
    .ticker-content-wrapper:hover .ticker-content {
        animation-play-state: paused;
    }

    /* Responsive */
    @media (max-width: 576px) {
        .ticker-label {
            min-width: 100px;
            font-size: 0.8rem;
            padding: 0 10px;
        }
        .ticker-item a {
            font-size: 0.85rem;
        }
        .news-ticker-fixed {
            height: 40px;
        }
        .ticker-label::after {
            border-width: 40px 0 0 15px;
        }
    }
</style>

<div class="news-ticker-fixed">
    <div class="ticker-label">
        <i class="fas fa-bullhorn me-2"></i> LATEST
    </div>
    <div class="ticker-content-wrapper">
        <div class="ticker-content">
            <!-- Static Placeholder Content for Now -->
            <span class="ticker-item">
                <i class="fas fa-circle"></i> 
                <a href="#">Admissions Open for 2026 Session - Apply Now!</a>
            </span>
            <span class="ticker-item">
                <i class="fas fa-circle"></i> 
                <a href="#">Scholarship Exam Request on 15th Feb - Register Here</a>
            </span>
            <span class="ticker-item">
                <i class="fas fa-circle"></i> 
                <a href="#">New Crash Course for Competitive Exams Starting Soon</a>
            </span>
            <span class="ticker-item">
                <i class="fas fa-circle"></i> 
                <a href="download-steno-report.php">Download Steno Test Reports and Certificates</a>
            </span>
             <span class="ticker-item">
                <i class="fas fa-circle"></i> 
                <a href="#">Admissions Open for 2026 Session - Apply Now!</a>
            </span>
            <span class="ticker-item">
                <i class="fas fa-circle"></i> 
                <a href="#">Scholarship Exam Request on 15th Feb - Register Here</a>
            </span>
        </div>
    </div>
</div>
