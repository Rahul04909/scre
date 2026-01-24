<!-- Footer Section -->
<footer class="main-footer">
    <div class="footer-top">
        <div class="container">
            <div class="footer-content">
                <!-- Column 1: About Us -->
                <div class="footer-column about-column" data-aos="fade-up" data-aos-duration="1000">
                    <div class="footer-logo">
                        <img src="<?php echo $baseUrl; ?>/assets/logo/frontpage-logo.webp" alt="Sir Chhotu Ram Education Logo">
                    </div>
                    <p class="footer-about-text">
                        Sir Chhotu Ram Education Private Limited is a premier educational institution dedicated to providing quality education. We are committed to excellence in teaching, research, and community service.
                    </p>
                    <div class="footer-certification">
                        <span><i class="fas fa-certificate"></i> ISO 9001:2015 Certified</span>
                        <span><i class="fas fa-award"></i> Ministry of Corporate Affairs Registered</span>
                    </div>
                </div>

                <!-- Column 2: Quick Links -->
                <div class="footer-column links-column" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="100">
                    <h3 class="footer-heading">Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="index.php"><i class="bi bi-chevron-right"></i> Home</a></li>
                        <li><a href="courses.php"><i class="bi bi-chevron-right"></i> Courses</a></li>
                        <li><a href="admission.php"><i class="bi bi-chevron-right"></i> Admission</a></li>
                        <li><a href="registration.php"><i class="bi bi-chevron-right"></i> Registration</a></li>
                        <li><a href="gallery.php"><i class="bi bi-chevron-right"></i> Gallery</a></li>
                        <li><a href="downloads.php"><i class="bi bi-chevron-right"></i> Downloads</a></li>
                    </ul>
                </div>

                <!-- Column 3: Contact Info -->
                <div class="footer-column contact-column" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
                    <h3 class="footer-heading">Contact Us</h3>
                    <ul class="footer-contact-info">
                        <li>
                            <i class="bi bi-geo-alt-fill"></i>
                            <span>Main Bazar Jeweler Market Market Street, opposite N.R, Jeweler, Jind, Haryana 126102</span>
                        </li>
                        <li>
                            <i class="bi bi-telephone-fill"></i>
                            <span>+91 9466317100</span>
                        </li>
                        <li>
                            <i class="bi bi-envelope-fill"></i>
                            <span>info@screduc.com</span>
                        </li>
                        <li>
                            <i class="bi bi-clock-fill"></i>
                            <span>Mon - Sat: 10.00 am - 06.00 pm</span>
                        </li>
                    </ul>
                </div>

                <!-- Column 4: Social Media & Newsletter -->
                <div class="footer-column social-column" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="300">
                    <h3 class="footer-heading">Connect With Us</h3>
                    <p class="social-text">Follow us on social media to stay updated with our latest news and events.</p>
                    <div class="social-icons">
                        <a href="#" class="social-icon facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon linkedin"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="social-icon youtube"><i class="fab fa-youtube"></i></a>
                    </div>
                    <div class="newsletter">
                        <h4 class="newsletter-heading">Subscribe to Newsletter</h4>
                        <form class="newsletter-form">
                            <input type="email" placeholder="Your Email Address" required>
                            <button type="submit"><i class="fas fa-paper-plane"></i></button>
                        </form>
                    </div>
                </div>

                <!-- Column 5: Map -->
                <div class="footer-column map-column" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="400">
                    <h3 class="footer-heading">Find Us</h3>
                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3443.5970534026996!2d76.31127707534237!3d29.316111375807665!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3912045356339b11%3A0x5fa77098ee7355f0!2sPACE%20FOUNDATION%20COMPUTER%20COACHING%20CENTRE%20JIND!5e0!3m2!1sen!2sin!4v1718272791650!5m2!1sen!2sin" 
                                width="100%" height="200" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Bottom with Enhanced Copyright -->
    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-content">
                <div class="copyright">
                    <p class="copyright-text">
                        <i class="far fa-copyright"></i> <span id="current-year">2025</span> Sir Chhotu Ram Education Private Limited. All Rights Reserved.
                    </p>
                    <p class="copyright-info">
                        Designed with <i class="fas fa-heart"></i> by SCRE Web Team | Last Updated: June 2023
                    </p>
                </div>
                <div class="footer-bottom-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Sitemap</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top" id="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </a>
</footer>

<style>
    /* Footer Styles */
    :root {
        --cream-light: #fff9f3;
        --cream-medium: #f8f1e4;
        --warm-brown: #6d4c41;
        --dark-grey: #333;
        --pastel-orange: #ffb74d;
        width: 100%;
    }

    .main-footer {
        background: linear-gradient(135deg, var(--warm-brown) 0%, var(--pastel-orange) 100%);
        color: #fff;
        position: relative;
        overflow: hidden;
        font-family: 'Poppins', sans-serif;
        border: 1px solid black;
        border-radius: 30px;
    }

    .main-footer::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23f8f1e4" fill-opacity="0.1" d="M0,192L48,197.3C96,203,192,213,288,229.3C384,245,480,267,576,250.7C672,235,768,181,864,181.3C960,181,1056,235,1152,234.7C1248,235,1344,181,1392,154.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
        background-size: cover;
        background-position: center;
        opacity: 0.2;
        z-index: 0;
    }

    .footer-top {
        padding: 0;
        position: relative;
        z-index: 1;
    }

    .container {
        width: 100%;
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px 16px;
        box-sizing: border-box;
        overflow-x: clip;
    }

    @media (min-width: 768px) {
        .container {
            padding: 40px 80px;
        }
    }

    .footer-content {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 30px;
    }

    .footer-column {
        flex: 1;
        min-width: 200px;
    }

    /* About Column */
    .about-column {
        flex: 1.5;
    }

    .footer-logo {
        margin-bottom: 15px;
    }

    .footer-logo img {
        max-width: 120px;
        border-radius: 5px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        background: white;
        padding: 5px;
    }

    .footer-about-text {
        margin-bottom: 15px;
        line-height: 1.6;
        font-size: 14px;
        color: var(--dark-grey);
    }

    .footer-certification {
        display: flex;
        flex-direction: column;
        gap: 5px;
        font-size: 13px;
    }

    .footer-certification span {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .footer-certification i {
        color: var(--pastel-orange);
    }

    /* Headings */
    .footer-heading {
        color: var(--warm-brown);
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        position: relative;
    }

    .footer-heading::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        height: 2px;
        width: 50px;
        background: var(--pastel-orange);
    }

    /* Links Column */
    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links li {
        margin-bottom: 10px;
    }

    .footer-links a {
        color: var(--dark-grey);
        text-decoration: none;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        font-size: 14px;
    }

    .footer-links a i {
        margin-right: 8px;
        font-size: 12px;
        transition: transform 0.3s ease;
    }

    .footer-links a:hover {
        color: var(--pastel-orange);
        transform: translateX(5px);
    }

    .footer-links a:hover i {
        transform: translateX(3px);
        color: var(--warm-brown);
    }

    /* Contact Column */
    .footer-contact-info {
        list-style: none;
        padding: 0;
        margin-left: -30px;
    }

    .footer-contact-info li {
        display: flex;
        margin-bottom: 15px;
        align-items: flex-start;
        margin-left: -27px;
    }

    .footer-contact-info i {
        color: var(--pastel-orange);
        margin-right: 10px;
        font-size: 16px;
        margin-top: 3px;
    }

    .footer-contact-info span {
        font-size: 14px;
        line-height: 1.5;
        color: var(--dark-grey);
    }

    /* Social Column */
    .social-text {
        margin-bottom: 15px;
        font-size: 14px;
        color: var(--dark-grey);
    }

    .social-icons {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .social-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--cream-medium);
        color: var(--warm-brown);
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .social-icon:hover {
        transform: translateY(-5px);
    }

    .social-icon.facebook:hover {
        background: #3b5998;
        box-shadow: 0 5px 15px rgba(59, 89, 152, 0.4);
    }

    .social-icon.twitter:hover {
        background: #1da1f2;
        box-shadow: 0 5px 15px rgba(29, 161, 242, 0.4);
    }

    .social-icon.instagram:hover {
        background: linear-gradient(45deg, #405de6, #5851db, #833ab4, #c13584, #e1306c, #fd1d1d);
        box-shadow: 0 5px 15px rgba(225, 48, 108, 0.4);
    }

    .social-icon.linkedin:hover {
        background: #0077b5;
        box-shadow: 0 5px 15px rgba(0, 119, 181, 0.4);
    }

    .social-icon.youtube:hover {
        background: #ff0000;
        box-shadow: 0 5px 15px rgba(255, 0, 0, 0.4);
    }

    /* Newsletter */
    .newsletter {
        margin-top: 20px;
    }

    .newsletter-heading {
        font-size: 16px;
        margin-bottom: 10px;
        color: var(--warm-brown);
    }

    .newsletter-form {
        display: flex;
        position: relative;
    }

    .newsletter-form input {
        width: 100%;
        padding: 10px 15px;
        border: none;
        border-radius: 50px;
        font-size: 14px;
        outline: none;
    }

    .newsletter-form button {
        position: absolute;
        right: 5px;
        top: 5px;
        background: var(--warm-brown);
        border: none;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .newsletter-form button:hover {
        background: var(--pastel-orange);
        transform: scale(1.1);
    }

    /* Map Column */
    .map-container {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        border: 3px solid rgba(255, 255, 255, 0.1);
    }

    /* Footer Bottom */
    .footer-bottom {
        background: var(--cream-medium);
        color: var(--dark-grey);
        padding: 0;
        position: relative;
        z-index: 1;
    }

    .footer-bottom-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .copyright {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .copyright-text {
        margin: 0;
        font-size: 14px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 5px;
    }

    .copyright-text i {
        color: var(--warm-brown);
        font-size: 16px;
    }

    .copyright-info {
        margin: 0;
        font-size: 12px;
        color: var(--dark-grey);
        font-style: italic;
    }

    .copyright-info i {
        color: var(--pastel-orange);
        animation: heartbeat 1.5s infinite;
    }

    @keyframes heartbeat {
        0% { transform: scale(1); }
        25% { transform: scale(1.1); }
        50% { transform: scale(1); }
        75% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    .footer-bottom-links {
        display: flex;
        gap: 20px;
    }

    .footer-bottom-links a {
        color: var(--warm-brown);
        text-decoration: none;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .footer-bottom-links a:hover {
        color: var(--pastel-orange);
    }

    /* Back to Top Button */
    .back-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 40px;
        height: 40px;
        background: var(--warm-brown);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 999;
    }

    .back-to-top.active {
        opacity: 1;
        visibility: visible;
    }

    .back-to-top:hover {
        background: var(--pastel-orange);
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    /* Responsive Styles */
    @media (max-width: 992px) {
        .footer-content {
            gap: 20px;
        }

        .footer-column {
            flex: 1 1 calc(50% - 20px);
        }

        .about-column {
            flex: 1 1 100%;
        }

        .footer-logo {
            text-align: center;
            margin: 0 auto 15px;
        }

        .footer-about-text {
            text-align: center;
        }

        .footer-certification {
            align-items: center;
        }
    }

    @media (max-width: 768px) {
        .container {
            padding: 0 4px;
        }

        .footer-column {
            flex: 1 1 100%;
            margin-bottom: 30px;
        }

        .footer-heading {
            text-align: center;
        }

        .footer-heading::after {
            left: 50%;
            transform: translateX(-50%);
        }

        .footer-links {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .footer-contact-info {
            align-items: center;
            margin-left: 0;
        }

        .footer-contact-info li {
            justify-content: center;
            margin-left: 0;
            text-align: center;
        }

        .social-text {
            text-align: center;
        }

        .social-icons {
            justify-content: center;
        }

        .newsletter {
            max-width: 300px;
            margin: 20px auto 0;
        }

        .footer-bottom-content {
            flex-direction: column;
            text-align: center;
        }

        .footer-bottom-links {
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .container {
            padding: 20px 16px;
        }

        .footer-column {
            margin-bottom: 25px;
        }

        .newsletter-form {
            max-width: 100%;
        }

        .back-to-top {
            bottom: 20px;
            right: 20px;
            width: 35px;
            height: 35px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Back to top button functionality
        const backToTopButton = document.getElementById('back-to-top');

        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('active');
            } else {
                backToTopButton.classList.remove('active');
            }
        });

        backToTopButton.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Update copyright year dynamically
        const currentYearElement = document.getElementById('current-year');
        if (currentYearElement) {
            currentYearElement.textContent = new Date().getFullYear();
        }

        // WhatsApp Chat Functionality
        const whatsappButton = document.getElementById('whatsapp-chat-btn');
        const whatsappPopup = document.getElementById('whatsapp-chat-popup');
        const closePopupBtn = document.getElementById('close-whatsapp-popup');
        const whatsappForm = document.getElementById('whatsapp-chat-form');
        const whatsappInput = document.getElementById('whatsapp-chat-input');

        // Open WhatsApp popup when button is clicked
        whatsappButton.addEventListener('click', function() {
            whatsappPopup.classList.add('active');
        });

        // Close WhatsApp popup when close button is clicked
        closePopupBtn.addEventListener('click', function() {
            whatsappPopup.classList.remove('active');
        });

        // Handle form submission
        whatsappForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const message = whatsappInput.value.trim();
            if (message) {
                // Redirect to WhatsApp with the message
                const whatsappUrl = `https://wa.me/919466317100?text=${encodeURIComponent(message)}`;
                window.open(whatsappUrl, '_blank');
                whatsappInput.value = '';
                whatsappPopup.classList.remove('active');
            }
        });
    });
</script>

<!-- WhatsApp Chat Button and Popup -->
<div class="whatsapp-chat-container">
    <!-- WhatsApp Button -->
    <button id="whatsapp-chat-btn" class="whatsapp-chat-btn">
        <i class="fab fa-whatsapp"></i>
    </button>

    <!-- WhatsApp Popup -->
    <div id="whatsapp-chat-popup" class="whatsapp-chat-popup">
        <!-- Popup Header -->
        <div class="whatsapp-popup-header">
            <div class="whatsapp-popup-header-info">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/6b/WhatsApp.svg/1200px-WhatsApp.svg.png" alt="WhatsApp Logo" class="whatsapp-logo">
                <div class="whatsapp-popup-title">
                    <h3>Chat with Us</h3>
                    <p>Online | Typically replies instantly</p>
                </div>
            </div>
            <button id="close-whatsapp-popup" class="close-whatsapp-popup">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Popup Body -->
        <div class="whatsapp-popup-body">
            <div class="whatsapp-message received">
                <p>👋 Hello! How can we help you today?</p>
                <span class="whatsapp-message-time">Just now</span>
            </div>
        </div>

        <!-- Popup Footer -->
        <div class="whatsapp-popup-footer">
            <form id="whatsapp-chat-form">
                <input type="text" id="whatsapp-chat-input" placeholder="Type a message..." required>
                <button type="submit" class="whatsapp-send-btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    /* WhatsApp Chat Button and Popup Styles */
    :root {
        --whatsapp-green: #25D366;
        --whatsapp-dark-green: #128C7E;
        --whatsapp-light-green: #DCF8C6;
        --whatsapp-header-green: #075E54;
    }

    /* WhatsApp Button */
    .whatsapp-chat-container {
        position: fixed;
        bottom: 30px;
        left: 30px;
        z-index: 9999;
    }

    .whatsapp-chat-btn {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background-color: var(--whatsapp-green);
        color: white;
        border: none;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        transition: all 0.3s ease;
    }

    .whatsapp-chat-btn:hover {
        transform: scale(1.1);
        background-color: var(--whatsapp-dark-green);
    }

    /* WhatsApp Popup */
    .whatsapp-chat-popup {
        position: fixed;
        bottom: 100px;
        left: 30px;
        width: 350px;
        background-color: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        display: flex;
        flex-direction: column;
        opacity: 0;
        visibility: hidden;
        transform: translateY(20px);
        transition: all 0.3s ease;
        z-index: 9998;
    }

    .whatsapp-chat-popup.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    /* Popup Header */
    .whatsapp-popup-header {
        background-color: var(--whatsapp-header-green);
        color: white;
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .whatsapp-popup-header-info {
        display: flex;
        align-items: center;
    }

    .whatsapp-logo {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-right: 10px;
        background-color: white;
        padding: 5px;
    }

    .whatsapp-popup-title h3 {
        margin: 0;
        font-size: 16px;
    }

    .whatsapp-popup-title p {
        margin: 5px 0 0;
        font-size: 12px;
        opacity: 0.8;
    }

    .close-whatsapp-popup {
        background: none;
        border: none;
        color: white;
        font-size: 18px;
        cursor: pointer;
        padding: 5px;
    }

    /* Popup Body */
    .whatsapp-popup-body {
        padding: 15px;
        flex-grow: 1;
        max-height: 300px;
        overflow-y: auto;
        background-color: #E5DDD5;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Cg fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.4'%3E%3Cpath opacity='.5' d='M96 95h4v1h-4v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9zm-1 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm9-10v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm9-10v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm9-10v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .whatsapp-message {
        max-width: 80%;
        padding: 10px 15px;
        border-radius: 10px;
        margin-bottom: 10px;
        position: relative;
    }

    .whatsapp-message.received {
        background-color: white;
        align-self: flex-start;
        border-top-left-radius: 0;
    }

    .whatsapp-message.sent {
        background-color: var(--whatsapp-light-green);
        align-self: flex-end;
        margin-left: auto;
        border-top-right-radius: 0;
    }

    .whatsapp-message p {
        margin: 0;
        font-size: 14px;
    }

    .whatsapp-message-time {
        display: block;
        font-size: 10px;
        color: #999;
        text-align: right;
        margin-top: 5px;
    }

    /* Popup Footer */
    .whatsapp-popup-footer {
        padding: 10px;
        background-color: #F0F0F0;
    }

    #whatsapp-chat-form {
        display: flex;
        align-items: center;
    }

    #whatsapp-chat-input {
        flex-grow: 1;
        padding: 10px 15px;
        border: none;
        border-radius: 20px;
        outline: none;
        font-size: 14px;
    }

    .whatsapp-send-btn {
        background-color: var(--whatsapp-green);
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-left: 10px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.3s ease;
    }

    .whatsapp-send-btn:hover {
        background-color: var(--whatsapp-dark-green);
    }

    /* Responsive Styles */
    @media (max-width: 480px) {
        .whatsapp-chat-popup {
            width: 90%;
            right: 5%;
            left: 5%;
            bottom: 80px;
        }

        .whatsapp-chat-btn {
            width: 50px;
            height: 50px;
            font-size: 24px;
            left: 20px;
            bottom: 20px;
        }
    }
</style>

</body>
</html>
