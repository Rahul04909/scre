<?php
session_start();
require_once '../database/config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['online_exam_student_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enrollment_no = trim($_POST['enrollment_no']);
    $password = $_POST['password'];

    if (!empty($enrollment_no) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT id, password, first_name, last_name, course_id, session_id FROM students WHERE enrollment_no = ?");
            $stmt->execute([$enrollment_no]);
            $student = $stmt->fetch();

            if ($student && password_verify($password, $student['password'])) {
                // Set Session
                $_SESSION['online_exam_student_id'] = $student['id'];
                $_SESSION['student_name'] = $student['first_name'] . ' ' . $student['last_name'];
                $_SESSION['course_id'] = $student['course_id'];
                $_SESSION['session_id'] = $student['session_id'];
                
                header("Location: index.php");
                exit;
            } else {
                $error = "Invalid Enrollment Number or Password.";
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Online Exam Portal - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f3f4f6;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .login-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
            overflow: hidden;
            width: 1000px;
            max-width: 95%;
            display: flex;
            min-height: 600px;
        }
        
        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #115E59 0%, #0f766e 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #fff;
            padding: 40px;
            position: relative;
        }
        
        .login-left::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: url('https://img.freepik.com/free-vector/online-test-concept-illustration_114360-5473.jpg?t=st=1737357000~exp=1737360600~hmac=xxxxx');
            background-size: cover;
            background-position: center;
            opacity: 0.1;
            mix-blend-mode: overlay;
        }
        
        .login-right {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .form-control {
            padding: 12px 15px;
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #115E59;
            box-shadow: 0 0 0 4px rgba(17, 94, 89, 0.1);
        }
        
        .btn-primary {
            background-color: #115E59;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.05rem;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: #0f766e;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(17, 94, 89, 0.2);
        }
        
        .brand-logo {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 20px;
            letter-spacing: -1px;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
            margin-top: 30px;
            z-index: 2;
        }
        
        .feature-list li {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            font-size: 1.1rem;
        }
        
        .feature-list li i {
            margin-right: 15px;
            background: rgba(255,255,255,0.2);
            padding: 8px;
            border-radius: 50%;
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .login-card { flex-direction: column; min-height: auto; width: 100%; height: 100%; border-radius: 0; }
            .login-left { padding: 30px; }
            .login-right { padding: 30px; }
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-left">
            <div class="brand-logo"><i class="fas fa-graduation-cap me-2"></i>PACE EXAMS</div>
            <h2 class="fw-bold mb-3 z-2">Student Assessment Portal</h2>
            <p class="text-white-50 text-center z-2">Secure, reliable, and easy-to-use platform for your online examinations.</p>
            
            <ul class="feature-list">
                <li><i class="fas fa-check"></i> Secure Login System</li>
                <li><i class="fas fa-clock"></i> Real-time Exam Timer</li>
                <li><i class="fas fa-file-alt"></i> Instant Result Processing</li>
                <li><i class="fas fa-shield-alt"></i> Anti-Cheating Measures</li>
            </ul>
        </div>
        
        <div class="login-right">
            <div class="mb-5">
                <h3 class="fw-bold text-dark">Welcome Back!</h3>
                <p class="text-secondary">Please login with your enrollment details.</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger rounded-3 mb-4">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-4">
                    <label class="form-label text-secondary fw-semibold small text-uppercase">Enrollment Number</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 border-2 rounded-start-3 ms-100" style="border-color: #e5e7eb;"><i class="fas fa-id-card text-muted"></i></span>
                        <input type="text" name="enrollment_no" class="form-control border-start-0 ps-0" placeholder="Enter Enrollment No" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label text-secondary fw-semibold small text-uppercase">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 border-2 rounded-start-3" style="border-color: #e5e7eb;"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" name="password" id="password" class="form-control border-start-0 ps-0" placeholder="Enter Password" required>
                        <button type="button" class="btn btn-light border border-start-0 border-2 rounded-end-3" onclick="togglePassword()" style="border-color: #e5e7eb !important;"><i class="fas fa-eye text-muted" id="eyeIcon"></i></button>
                    </div>
                </div>
                
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary">
                        Login to Dashboard <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
                
                <div class="text-center mt-4">
                    <a href="#" class="text-decoration-none text-muted small">Forgot Credentials? Contact Center</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            var pass = document.getElementById("password");
            var icon = document.getElementById("eyeIcon");
            if (pass.type === "password") {
                pass.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                pass.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html>
