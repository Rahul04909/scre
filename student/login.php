<?php
session_start();
require_once '../database/config.php';

if (isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if (isset($_POST['login'])) {
    $enrollment_no = trim($_POST['enrollment_no']);
    $password = $_POST['password'];

    if (empty($enrollment_no) || empty($password)) {
        $error = "Please enter both Enrollment No and Password.";
    } else {
        $stmt = $pdo->prepare("SELECT id, first_name, last_name, password, student_image FROM students WHERE enrollment_no = ?");
        $stmt->execute([$enrollment_no]);
        $student = $stmt->fetch();

        if ($student) {
            
            if (password_verify($password, $student['password'])) {
                $_SESSION['student_id'] = $student['id'];
                $_SESSION['student_name'] = $student['first_name'] . ' ' . $student['last_name'];
                $_SESSION['enrollment_no'] = $enrollment_no;
                $_SESSION['student_image'] = $student['student_image'];
                
                header("Location: index.php");
                exit;
            } else {
                $error = "Invalid Password.";
            }
        } else {
            $error = "Student with this Enrollment No not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - PACE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f3f4f6; height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; }
        .login-card { width: 100%; max-width: 400px; border: none; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); overflow: hidden; }
        .login-header { background: #115E59; padding: 30px 20px; text-align: center; color: white; }
        .login-body { padding: 40px 30px; background: white; }
        .form-control { border-radius: 8px; padding: 12px; border: 1px solid #e5e7eb; background: #f9fafb; }
        .form-control:focus { border-color: #115E59; box-shadow: 0 0 0 3px rgba(17, 94, 89, 0.1); }
        .btn-login { background: #115E59; color: white; border: none; padding: 12px; border-radius: 8px; width: 100%; font-weight: 600; font-size: 1rem; transition: all 0.3s; }
        .btn-login:hover { background: #0f524d; transform: translateY(-1px); }
        .input-group-text { background: transparent; border: none; position: absolute; right: 10px; top: 12px; z-index: 10; cursor: pointer; color: #6b7280; }
        .brand-logo { width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <div class="brand-logo">
                <i class="fas fa-user-graduate fa-2x"></i>
            </div>
            <h4 class="fw-bold mb-0">Student Portal</h4>
            <p class="mb-0 opacity-75 small">Login to access your dashboard</p>
        </div>
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger text-center small py-2"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Enrollment Number</label>
                    <input type="text" name="enrollment_no" class="form-control" placeholder="Enter Enrollment No" required>
                </div>
                
                <div class="mb-4 position-relative">
                    <label class="form-label small fw-bold text-muted text-uppercase">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter Password" required>
                    <span class="input-group-text" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </span>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" name="login" class="btn btn-login">Login</button>
                </div>
                
                <div class="text-center">
                    <a href="#" class="text-muted small text-decoration-none">Forgot Password?</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
