<?php
session_start();
require_once '../database/config.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_role'] = 'super_admin'; // Hardcoded for now
            $_SESSION['admin_image'] = $admin['image'];
            
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - PACE Panel</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Popppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f3f4f6;
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 400px;
            padding: 40px;
            overflow: hidden;
            position: relative;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header img {
            width: 80px;
            margin-bottom: 15px;
        }
        .login-header h4 {
            font-weight: 700;
            color: #115E59;
        }
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #e5e7eb;
        }
        .form-control:focus {
            border-color: #115E59;
            box-shadow: 0 0 0 4px rgba(17, 94, 89, 0.1);
        }
        .btn-login {
            background-color: #115E59;
            color: white;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            border: none;
            transition: all 0.2s;
        }
        .btn-login:hover {
            background-color: #0f5257;
            transform: translateY(-2px);
        }
        .alert {
            font-size: 0.9rem;
            padding: 10px;
            border-radius: 8px;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <img src="../assets/logo/logo.jpeg" alt="Logo">
            <h4>Admin Login</h4>
            <p class="text-muted small">Enter your credentials to access the panel.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger text-center mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                    <input type="text" name="username" class="form-control border-start-0" placeholder="Enter username" required>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label small fw-bold text-muted">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control border-start-0" placeholder="Enter password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-login">
                Login <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </form>
        
        <div class="text-center mt-4">
            <a href="../index.php" class="small text-muted text-decoration-none"><i class="fas fa-home me-1"></i> Back to Website</a>
        </div>
    </div>

</body>
</html>
