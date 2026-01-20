<?php
session_start();
require_once '../database/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = trim($_POST['login_input']);
    $password = $_POST['password'];

    if (empty($login_input) || empty($password)) {
        $error = "Please enter both Login ID and Password.";
    } else {
        try {
            // Allow login via Email OR Center Code
            $stmt = $pdo->prepare("SELECT * FROM centers WHERE (email = ? OR center_code = ?)");
            $stmt->execute([$login_input, $login_input]);
            $center = $stmt->fetch();

            if ($center) {
                if ($center['is_active'] == 0) {
                    $error = "Your account is inactive. Please contact Admin.";
                } elseif (password_verify($password, $center['password'])) {
                    // Success
                    $_SESSION['center_id'] = $center['id'];
                    $_SESSION['center_name'] = $center['center_name'];
                    $_SESSION['center_code'] = $center['center_code'];
                    
                    header("Location: index.php");
                    exit;
                } else {
                    $error = "Invalid Password.";
                }
            } else {
                $error = "Invalid Email or Center Code.";
            }
        } catch (PDOException $e) {
            $error = "System Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Center Login - PACE Foundation</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #fff;
            height: 100vh;
            overflow: hidden;
        }
        .login-wrapper {
            height: 100%;
            display: flex;
        }
        /* Left Side - Image/Brand */
        .brand-section {
            flex: 1;
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }
        .brand-section::before {
            content: '';
            position: absolute;
            width: 150%;
            height: 150%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            top: -25%;
            left: -25%;
        }
        .brand-content {
            z-index: 2;
            text-align: center;
        }
        .brand-content h1 { font-weight: 800; font-size: 3.5rem; letter-spacing: -1px; margin-bottom: 1rem; }
        .brand-content p { font-size: 1.2rem; opacity: 0.9; max-width: 400px; line-height: 1.6; }
        
        /* Right Side - Form */
        .form-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 80px;
            background-color: #fff;
            max-width: 600px;
        }
        .login-header { margin-bottom: 40px; }
        .login-header h2 { font-weight: 700; color: #1f2937; margin-bottom: 10px; }
        .login-header p { color: #6b7280; }

        .form-control {
            padding: 14px 20px;
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            font-size: 1rem;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
        }
        .input-group-text {
            background: transparent;
            border: 2px solid #e5e7eb;
            border-left: none;
            border-radius: 0 12px 12px 0;
            cursor: pointer;
            color: #6b7280;
        }
        .password-field { border-right: none; border-radius: 12px 0 0 12px; }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            width: 100%;
            margin-top: 20px;
            transition: transform 0.2s;
        }
        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .brand-section { display: none; }
            .form-section { flex: 1; max-width: 100%; padding: 40px; }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <!-- Brand Section (Left) -->
    <div class="brand-section">
        <div class="brand-content">
            <h1>PACE FOUNDATION</h1>
            <p>Empowering education through technology. Manage your center, students, and courses efficiently from one unified dashboard.</p>
        </div>
    </div>

    <!-- Form Section (Right) -->
    <div class="form-section">
        <div class="login-header">
            <h2>Center Login</h2>
            <p>Welcome back! Please login to access your dashboard.</p>
        </div>

        <?php if($error): ?>
            <div class="alert alert-danger rounded-3 border-0 d-flex align-items-center mb-4">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="form-label fw-bold text-secondary small text-uppercase">Email or Center Code</label>
                <div class="input-group">
                    <span class="input-group-text border-end-0 bg-light rounded-start-3 ps-3"><i class="fas fa-user text-muted"></i></span>
                    <input type="text" name="login_input" class="form-control border-start-0 rounded-end-3" placeholder="Enter your email or ID" required>
                </div>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between">
                    <label class="form-label fw-bold text-secondary small text-uppercase">Password</label>
                    <a href="#" class="small text-primary text-decoration-none">Forgot password?</a>
                </div>
                <div class="input-group">
                    <span class="input-group-text border-end-0 bg-light rounded-start-3 ps-3"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" name="password" id="password" class="form-control border-start-0 border-end-0" placeholder="Enter your password" required>
                    <span class="input-group-text bg-white border-start-0 user-select-none rounded-end-3 pe-3" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </span>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Sign In <i class="fas fa-arrow-right ms-2"></i></button>

            <div class="text-center mt-4">
                <p class="text-muted small">Don't have an account? <span class="text-dark fw-bold">Contact Admin</span></p>
                <a href="../index.php" class="text-secondary small text-decoration-none"><i class="fas fa-long-arrow-alt-left"></i> Back to Home</a>
            </div>
        </form>
    </div>
</div>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }
</script>

</body>
</html>
