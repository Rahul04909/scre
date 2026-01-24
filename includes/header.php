<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    // Get SEO settings from database
    try {
        require_once dirname(__DIR__) . '/database/config.php';
        $conn = getDbConnection();
        
        // Check if general_settings table exists
        $tableExists = false;
        $tables = $conn->query("SHOW TABLES LIKE 'general_settings'")->fetchAll();
        if (count($tables) > 0) {
            $tableExists = true;
        }
        
        if ($tableExists) {
            $settingsSql = "SELECT setting_key, setting_value FROM general_settings";
            $stmt = $conn->query($settingsSql);
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            $website_title = $settings['website_title'] ?? 'SIR CHHOTU RAM EDUCATION';
            $meta_title = $settings['meta_title'] ?? 'SIR CHHOTU RAM EDUCATION - Quality Education Portal';
            $meta_description = $settings['meta_description'] ?? '';
            $meta_keywords = $settings['meta_keywords'] ?? '';
            $seo_schema = $settings['seo_schema'] ?? '';
        } else {
            $website_title = 'SIR CHHOTU RAM EDUCATION';
            $meta_title = 'SIR CHHOTU RAM EDUCATION - Quality Education Portal';
        }
    } catch (Exception $e) {
        $website_title = 'SIR CHHOTU RAM EDUCATION';
        $meta_title = 'SIR CHHOTU RAM EDUCATION - Quality Education Portal';
    }
    ?>
    <title><?php echo htmlspecialchars($website_title); ?></title>
    <meta name="title" content="<?php echo htmlspecialchars($meta_title); ?>">
    <?php if (!empty($meta_description)): ?>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <?php endif; ?>
    <?php if (!empty($meta_keywords)): ?>
    <meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords); ?>">
    <?php endif; ?>
    <?php if (!empty($seo_schema)): ?>
    <script type="application/ld+json">
        <?php echo $seo_schema; ?>
    </script>
    <?php endif; ?>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --cream-light: #fff9f3;
            --cream-medium: #f8f1e4;
            --warm-brown: #6d4c41;
            --dark-grey: #333;
            --pastel-orange: #ffb74d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        html, body {
            background-color: var(--cream-light);
            overflow-x: hidden;
            width: 100%;
            max-width: 100vw;
            margin: 0;
            padding: 0;
        }

        /* Custom Scrollbar for Webkit Browsers */
        ::-webkit-scrollbar {
            width: 10px;
            background: var(--cream-light);
        }
        ::-webkit-scrollbar-track {
            background: var(--cream-medium);
        }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--pastel-orange), var(--warm-brown));
            border-radius: 10px;
            min-height: 40px;
            box-shadow: 0 2px 6px rgba(255,183,77,0.07);
            border: 2px solid var(--cream-medium);
        }
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #e09a3e, #4e342e);
            box-shadow: 0 0 8px 2px #ffb74d88;
        }

        /* Firefox Scrollbar */
        html {
            scrollbar-width: thin;
            scrollbar-color: #ffb74d #f8f1e4;
        }

        /* Top Bar Styles */
        .top-bar {
            background-color: var(--cream-medium);
            padding: 10px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--dark-grey);
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 100vw;
            box-sizing: border-box;
            overflow-x: clip;
        }

        .top-bar-section {
            padding: 0 20px;
            display: flex;
            align-items: center;
        }

        .top-bar-center {
            text-align: center;
        }

        .top-bar-right {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 15px;
            border-radius: 20px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .btn-primary {
            background-color: var(--warm-brown);
            color: white;
        }

        .btn-secondary {
            background-color: var(--pastel-orange);
            color: var(--dark-grey);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        /* Main Navigation Styles */
        .main-nav {
            background-color: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
            width: 100%;
            max-width: 100%; /* Changed from 120vw */
            box-sizing: border-box;
            /* Removed overflow-x: clip; */
        }

        .nav-container {
            display: flex;
            align-items: center;
            max-width: 100%; /* Changed from 1400px */
            width: 100%;
            margin: 0 auto;
            padding: 0 20px;
            box-sizing: border-box;
            /* Removed overflow-x: clip; */
        }

        .hamburger {
            display: none;
            cursor: pointer;
            width: 30px;
            height: 20px;
            position: relative;
        }

        .hamburger span {
            display: block;
            position: absolute;
            height: 3px;
            width: 100%;
            background: var(--warm-brown);
            border-radius: 3px;
            opacity: 1;
            left: 0;
            transform: rotate(0deg);
            transition: .25s ease-in-out;
        }

        .hamburger span:nth-child(1) {
            top: 0px;
        }

        .hamburger span:nth-child(2) {
            top: 8px;
        }

        .hamburger span:nth-child(3) {
            top: 16px;
        }

        .menu {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            flex-direction: row;
            list-style: none;
            justify-content: center;
            align-items: center;
            gap: 0;
            width: 100%;
            min-height: 48px;
            max-height: none;
            height: auto;
            padding: 0 5px;
            background: #fff9f3;
            overflow: visible;
            border-radius: 10px;
            box-shadow: 0 1px 6px 0 #f8f1e4;
            transition: max-height 0.3s ease;
            white-space: nowrap; /* Added to prevent wrapping */
        }
        @media (max-width: 768px) {
            .main-nav {
                padding: 8px 0;
                box-shadow: none
            }
            .nav-container {
                flex-direction: row;
                padding: 0 15px;
                justify-content: space-between;
            }
            .hamburger {
                display: block;
                margin-right: 0;
                z-index: 1000;
            }
            .hamburger span {
                transition: .25s ease-in-out;
            }
            .hamburger.active span:nth-child(1) {
                top: 8px;
                transform: rotate(45deg);
            }
            .hamburger.active span:nth-child(2) {
                opacity: 0;
            }
            .hamburger.active span:nth-child(3) {
                top: 8px;
                transform: rotate(-45deg);
            }
            .menu {
                flex-direction: column;
                align-items: flex-start;
                width: 100%;
                max-width: 100%;
                position: absolute;
                top: 100%;
                left: 0;
                background: #fff9f3;
                border-radius: 0 0 12px 12px;
                box-shadow: 0 8px 24px rgba(0,0,0,0.08);
                z-index: 999;
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.35s ease-in-out;
                padding: 0;
            }
            .menu.active {
                max-height: 500px;
                overflow-y: auto;
                border-top: 1px solid #f3e7d6;
                padding: 10px 0;
            }
            .menu-item {
                width: 100%;
                border-left: none;
                border-bottom: 1px solid #f0e7d9;
                height: auto;
                display: flex;
                flex-direction: column;
                align-items: flex-start;
            }
            .menu-item:last-child {
                border-bottom: none;
            }
            .menu-link {
                width: 100%;
                padding: 12px 20px;
                font-size: 1rem;
                justify-content: flex-start;
                border-radius: 0;
                gap: 10px;
            }
            .dropdown {
                position: static;
                width: 100%;
                box-shadow: none;
                border-radius: 0;
                border: none;
                background: #f8f1e4;
                padding: 0;
                margin: 0;
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.3s ease-in-out;
                opacity: 1;
                visibility: visible;
                transform: none;
            }
            .menu-item.active .dropdown {
                max-height: 300px;
                padding: 5px 0;
            }
            .dropdown-item {
                width: 100%;
            }
            .dropdown-link {
                width: 100%;
                padding: 10px 30px;
                font-size: 0.95rem;
                border-radius: 0;
                text-align: left;
            }
        }
        .menu::-webkit-scrollbar {
            height: 8px;
            background: var(--cream-medium);
        }
        .menu::-webkit-scrollbar-thumb {
            background: linear-gradient(90deg, var(--pastel-orange), var(--warm-brown));
            border-radius: 10px;
        }
        .menu::-webkit-scrollbar-track {
            background: var(--cream-medium);
        }
        .menu::-webkit-scrollbar-corner {
            background: var(--cream-medium);
        }

        .menu-item {
            position: relative;
            display: flex;
            align-items: center;
            background: none;
            border-radius: 0;
            margin: 0;
            padding: 0;
            height: 44px;
            border-left: 1px solid #f0e7d9;
        }
        .menu-item:first-child {
            border-left: none;
        }

        .menu-link {
            text-decoration: none;
            color: var(--warm-brown);
            font-weight: 500;
            padding: 7px 13px;
            transition: background 0.2s, color 0.2s;
            position: relative;
            border-radius: 7px;
            background: none;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 15px;
            height: 100%;
        }
        .menu-link i, .menu-link svg {
            color: var(--warm-brown);
            font-size: 15px;
            vertical-align: middle;
            margin-right: 4px;
        }

        .menu-link:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--pastel-orange);
            transition: width 0.3s ease;
        }

        .menu-link:hover, .menu-link:focus {
            color: #fff;
            background: linear-gradient(90deg, var(--pastel-orange), var(--warm-brown));
            transition: background 0.2s, color 0.2s;
            outline: none;
        }
        .menu-link:hover i, .menu-link:focus i,
        .menu-link:hover svg, .menu-link:focus svg {
            color: #fff;
        }

        .menu-link:hover:after {
            width: 100%;
        }

        /* Dropdown Menu */
        .dropdown {
            position: absolute;
            top: calc(100% + 6px);
            left: 50%;
            transform: translateX(-50%) translateY(0);
            background-color: #fff;
            min-width: 200px;
            width: max-content;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            border-radius: 8px;
            padding: 8px 0;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s, top 0.3s;
            z-index: 9999;
            border: 1px solid #f0f0f0;
            overflow: visible;
        }
        .menu-item:last-child {
            position: relative;
        }
        .menu-item:hover .dropdown,
        .menu-item:focus-within .dropdown {
            opacity: 1;
            visibility: visible;
            top: calc(100% + 6px);
            pointer-events: auto;
        }

        .menu-item:hover .dropdown {
            opacity: 1;
            visibility: visible;
            top: calc(100% + 8px);
            pointer-events: auto;
        }

        .dropdown-item {
            list-style: none;
        }

        .dropdown-link {
            display: block;
            padding: 10px 24px;
            color: var(--dark-grey);
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
            font-size: 15px;
            white-space: nowrap;
        }

        .dropdown-link:hover {
            background-color: var(--cream-medium);
            color: var(--warm-brown);
            border-radius: 5px;
        }

        /* Responsive Styles */
        @media (max-width: 992px) and (min-width: 769px) {
            .menu {
                flex-wrap: wrap;
                justify-content: center;
                align-items: center;
                gap: 0;
                min-height: 44px;
                height: auto;
                padding: 0 2px;
                background: #fff9f3;
                overflow: visible;
                box-shadow: 0 1px 6px 0 #f8f1e4;
                border-radius: 8px;
            }
            .menu-item {
                height: 40px;
                border-left: 1px solid #f0e7d9;
            }
            .menu-item:first-child {
                border-left: none;
            }
            .menu-link {
                font-size: 14px;
                padding: 6px 9px;
                gap: 6px;
            }
            .menu-link i, .menu-link svg {
                font-size: 13px;
                margin-right: 3px;
            }
            .top-bar {
                flex-direction: column;
                gap: 10px;
                padding: 10px;
            }

            .top-bar-section {
                padding: 5px 0;
            }

            .top-bar-right {
                flex-wrap: wrap;
                justify-content: center;
            }
        }

        /* Mobile Styles for Top Bar */
        @media (max-width: 768px) {
            .top-bar {
                flex-direction: column;
                gap: 12px;
                padding: 12px 8px;
            }

            .top-bar-section {
                width: 100%;
                justify-content: center;
                text-align: center;
                padding: 5px 0;
            }

            .top-bar-right {
                flex-wrap: wrap;
                justify-content: center;
                gap: 8px;
            }

            .top-bar-right .btn {
                padding: 6px 12px;
                font-size: 12px;
                width: calc(50% - 8px);
                margin: 2px 0;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .top-bar-right .btn i {
                margin-right: 5px;
            }

            .modal-content {
                width: 95%;
                margin: 30% auto;
                padding: 15px;
            }
        }
        /* Institute Header Row */
        .institute-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f1e4;
            padding: 12px 24px 10px 24px;
            border-bottom: 1px solid #f3e7d6;
            flex-wrap: wrap;
            gap: 16px;
        }
        .header-logo {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            margin-left: 100px;
            height: 78px;
            width: 80px;
            justify-content: center;
            box-sizing: border-box;
            padding: 0 10px;
            border-radius: 8px;
            box-shadow: 0 1px 4px #e5d4c0;
        }
        .main-logo {
            height: 88px;
            width: 140px;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 1px 4px #e5d4c0;
            border: 1px solid #8a6143;
        }
        .header-center {
            flex: 1 1 200px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .header-center-animated {
            opacity: 0;
            transform: translateY(32px);
        }
        .header-center-animated.visible {
            animation: fadeSlideUp 0.7s cubic-bezier(.4,0,.2,1) forwards;
        }
        @keyframes fadeSlideUp {
            from {
                opacity: 0;
                transform: translateY(32px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .inst-name {
            font-size: 1.25rem;
            font-weight: bold;
            color: var(--warm-brown);
            letter-spacing: 1px;
        }
        .inst-hindi {
            font-size: 0.7rem;
            color: #6d4c41bb;
            margin-top: 2px;
        }
        .header-badges {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .badge-logo {
            height: 36px;
            width: auto;
            border-radius: 6px;
            background: #fff;
            box-shadow: 0 1px 3px #e5d4c0;
            padding: 2px 6px;
        }
        @media (max-width: 800px) {
            .institute-header-row {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
                padding: 10px 6vw 8px 6vw;
            }
            .header-logo, .header-badges {
                justify-content: center;
                margin-bottom: 0;
            }
            .header-center {
                margin: 6px 0 4px 0;
            }
        }
        /* Modal Styles */
        .custom-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from {opacity: 0}
            to {opacity: 1}
        }

        .modal-content {
            background-color: var(--cream-light);
            margin: 10% auto;
            padding: 20px;
            border: 1px solid var(--cream-medium);
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            position: relative;
            animation: slideDown 0.4s;
        }

        @keyframes slideDown {
            from {transform: translateY(-50px); opacity: 0;}
            to {transform: translateY(0); opacity: 1;}
        }

        .close-modal {
            color: var(--warm-brown);
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s;
        }

        .close-modal:hover {
            color: var(--pastel-orange);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            background-color: white;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -10px;
            margin-left: -10px;
        }

        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding: 0 10px;
            box-sizing: border-box;
        }

        .mt-3 {
            margin-top: 15px;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .alert-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }

        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 20% auto;
                padding: 15px;
            }

            .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<?php
// Dynamically get the base URL (works for both root and subfolder installs)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$baseUrl = $protocol . $domain . ($basePath === '/' ? '' : $basePath);
?>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="top-bar-section top-bar-left">
            <i class="far fa-clock"></i>&nbsp; Opening Hours: Mon - Fri: 10.00 am - 06.00 pm
        </div>
        <div class="top-bar-section top-bar-center">
            <i class="fas fa-phone-alt"></i>&nbsp; Call Us: +91 9466317100
        </div>
        <div class="top-bar-section top-bar-right">
            <!-- Google Translator -->
            <div id="google_translate_element"></div>
            <script type="text/javascript">
                function googleTranslateElementInit() {
                    new google.translate.TranslateElement({pageLanguage: 'en', layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element');
                }
            </script>
            <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
            <style>
                .goog-te-gadget-simple {
                    background-color: transparent !important;
                    border: 1px solid rgba(0,0,0,0.1) !important;
                    padding: 4px 8px !important;
                    border-radius: 4px !important;
                    font-family: inherit !important;
                    font-size: 13px !important;
                }
                .goog-te-gadget-simple img {
                    display: none !important;
                }
                .goog-te-menu-value {
                    color: var(--dark-grey) !important;
                }
                .goog-te-menu-value span {
                    color: var(--dark-grey) !important;
                    border-left: none !important;
                }
                .goog-te-menu-value span:last-child {
                    display: none !important;
                }
            </style>

            <button id="enquiry-btn" class="btn btn-primary">ENQUIRY NOW</button>
            <button class="btn btn-secondary">VERIFICATION LETTER</button>
        </div>


        <!-- Enquiry Form Modal -->
        <div id="enquiry-modal" class="custom-modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h2><i class="fas fa-envelope"></i> Submit an Enquiry</h2>
                <form id="enquiry-form">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="enquiry-name">Full Name:</label>
                            <input type="text" id="enquiry-name" name="name" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="enquiry-email">Email Address:</label>
                            <input type="email" id="enquiry-email" name="email" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="enquiry-phone">Phone Number:</label>
                            <input type="tel" id="enquiry-phone" name="phone" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="enquiry-message">Your Message:</label>
                        <textarea id="enquiry-message" name="message" rows="4" class="form-control" required></textarea>
                    </div>
                    <button type="submit" id="enquiry-submit" class="btn btn-primary">Submit Enquiry</button>
                    <div id="enquiry-result" class="mt-3" style="display: none;"></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Institute Header Row -->
    <div class="institute-header-row">
        <div class="header-logo">
            <img src="<?php echo $baseUrl; ?>/logo/logo.png" alt="Institute Logo" class="main-logo" />
        </div>
        <div class="header-center">
            <div class="inst-name header-center-animated">SIR CHHOTU RAM EDUCATION PVT. LTD.</div>
            <div class="inst-hindi header-center-animated"><strong>AN ISO 9001-2015 Certified Organization</strong></div>
            <div class="inst-hindi header-center-animated"><strong>Registred Under The Company ACT 2013 By The</strong></div>
            <div class="inst-hindi header-center-animated"><strong>Ministry Of Corporate Affairs,</strong></div>
            <div class="inst-hindi header-center-animated"><strong>Ministry of Micro, Small & Medium Enterprises</strong></div>
            <div class="inst-name header-center-animated"><strong>Goverment Of India</strong></div>
        </div> 
        <div class="header-badges">
            <img src="<?php echo $baseUrl; ?>/logo/iso.png" alt="ISO Certified" class="main-logo" />
        </div>
    </div>

    <!-- Main Navigation -->
    <nav class="main-nav">
        <div class="nav-container">
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <ul class="menu">
                <li class="menu-item"><a href="https://screduc.com" class="menu-link"><i class="fas fa-home"></i>Home</a></li>
                <li class="menu-item"><a href="<?php echo $baseUrl; ?>/courses.php" class="menu-link"><i class="fas fa-book"></i>Courses</a></li>
                <li class="menu-item"><a href="#" class="menu-link"><i class="fas fa-user-graduate"></i>Admission</a></li>
                <li class="menu-item"><a href="#" class="menu-link"><i class="fas fa-edit"></i>Registration</a></li>
                <li class="menu-item"><a href="#" class="menu-link"><i class="fas fa-university"></i>Center Registration</a></li>
                <li class="menu-item"><a href="<?php echo $baseUrl; ?>/gallery.php" class="menu-link"><i class="fas fa-images"></i>Gallery</a></li>
                <li class="menu-item"><a href="<?php echo $baseUrl; ?>/our-partners.php" class="menu-link"><i class="fas fa-handshake"></i>Partners</a></li>
                <li class="menu-item">
                    <a href="#" class="menu-link"><i class="fas fa-sign-in-alt"></i>Login</a>
                    <ul class="dropdown">
                        <li class="dropdown-item"><a href="<?php echo $baseUrl; ?>/student/index.php" class="dropdown-link">Student Dashboard</a></li>
                        <li class="dropdown-item"><a href="<?php echo $baseUrl; ?>/center/index.php" class="dropdown-link">Center Dashboard</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <script>
        // Toggle hamburger menu
        const hamburger = document.querySelector('.hamburger');
        const menu = document.querySelector('.menu');

        hamburger.addEventListener('click', function() {
            this.classList.toggle('active');
            menu.classList.toggle('active');
        });

        // Handle mobile menu dropdowns
        function setupMobileMenu() {
            const menuItems = document.querySelectorAll('.menu-item');

            // Remove existing event listeners first
            menuItems.forEach(item => {
                const menuLink = item.querySelector('.menu-link');
                if (menuLink) {
                    const newMenuLink = menuLink.cloneNode(true);
                    menuLink.parentNode.replaceChild(newMenuLink, menuLink);
                }
            });

            // Add new event listeners based on window width
            if (window.innerWidth <= 768) {
                menuItems.forEach(item => {
                    if (item.querySelector('.dropdown')) {
                        const menuLink = item.querySelector('.menu-link');
                        menuLink.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();

                            // Close other open dropdowns
                            menuItems.forEach(otherItem => {
                                if (otherItem !== item && otherItem.classList.contains('active')) {
                                    otherItem.classList.remove('active');
                                }
                            });

                            item.classList.toggle('active');
                        });
                    }
                });
            }
        }

        // Setup mobile menu on page load
        setupMobileMenu();

        // Update mobile menu on window resize
        window.addEventListener('resize', function() {
            setupMobileMenu();
        });
        // Header center staggered animation
        window.addEventListener('DOMContentLoaded', function() {
            const lines = document.querySelectorAll('.header-center-animated');
            lines.forEach((line, i) => {
                setTimeout(() => {
                    line.classList.add('visible');
                }, 200 + i * 180); // 0.2s, 0.38s, 0.56s, etc, total ~1.2s for all
            });

            // Modal functionality
            const baseUrl = '<?php echo $baseUrl; ?>';

            // Get modal elements
            const enquiryModal = document.getElementById('enquiry-modal');
            const enquiryBtn = document.getElementById('enquiry-btn');
            const closeButtons = document.querySelectorAll('.close-modal');

            // Open enquiry modal
            enquiryBtn.addEventListener('click', function() {
                enquiryModal.style.display = 'block';
            });

            // Close modals when clicking on X
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    enquiryModal.style.display = 'none';
                });
            });

            // Close modals when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === enquiryModal) {
                    enquiryModal.style.display = 'none';
                }
            });


            // Enquiry form functionality
            const enquiryForm = document.getElementById('enquiry-form');
            const enquiryResult = document.getElementById('enquiry-result');

            enquiryForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // Get form data
                const formData = new FormData(enquiryForm);
                const enquirySubmit = document.getElementById('enquiry-submit');

                // Show loading state
                enquiryResult.style.display = 'block';
                enquiryResult.className = 'alert alert-info mt-3';
                enquiryResult.textContent = 'Submitting your enquiry...';
                enquirySubmit.disabled = true;

                // Make API request
                fetch(`${baseUrl}/includes/submit-enquiry.php`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    // Check if the response is ok (status in the range 200-299)
                    if (!response.ok) {
                        throw new Error(`Server responded with status: ${response.status}`);
                    }

                    // Check if the response has content
                    if (response.headers.get('content-length') === '0') {
                        throw new Error('Empty response received from server');
                    }

                    // Try to parse the response as JSON
                    return response.json().catch(err => {
                        throw new Error('Failed to parse server response as JSON: ' + err.message);
                    });
                })
                .then(data => {
                    if (data.success) {
                        enquiryResult.className = 'alert alert-success mt-3';

                        // Create a more detailed success message
                        let successMessage = 'Thank you for your enquiry! ';

                        if (data.email_sent) {
                            successMessage += `A confirmation email has been sent to ${document.getElementById('enquiry-email').value}. `;
                        } else if (data.email_error) {
                            console.warn('Email sending failed:', data.email_error);
                        }

                        successMessage += 'We will get back to you soon.';
                        enquiryResult.textContent = successMessage;

                        // Reset the form
                        enquiryForm.reset();
                    } else {
                        enquiryResult.className = 'alert alert-danger mt-3';
                        enquiryResult.textContent = 'Error: ' + data.error;
                    }
                })
                .catch(error => {
                    console.error('Enquiry submission error:', error);
                    enquiryResult.className = 'alert alert-danger mt-3';
                    enquiryResult.textContent = 'Error: ' + error.message;
                })
                .finally(() => {
                    enquirySubmit.disabled = false;
                });
            });
        });
    </script>
<!-- Body and HTML tags will be closed in footer.php -->
<!-- Do not close body or html tags here as content will be added after this include -->
