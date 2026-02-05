<?php
session_start();
require_once '../database/config.php';

$error = '';

if (isset($_POST['login'])) {
    $enrollment_no = trim($_POST['enrollment_no']);
    $password = $_POST['password'];

    if (empty($enrollment_no) || empty($password)) {
        $error = "Please enter both Enrollment No and Password.";
    } else {
        try {
            // 1. Check Student Credentials
            $stmt = $pdo->prepare("SELECT id, course_id, password, first_name, last_name, student_image FROM students WHERE enrollment_no = :enrollment_no");
            $stmt->execute([':enrollment_no' => $enrollment_no]);
            $student = $stmt->fetch();

            if ($student && password_verify($password, $student['password'])) {
                // 2. Check if Course is Allotted Typing Master Permission
                $course_id = $student['course_id'];
                $checkStmt = $pdo->prepare("SELECT id FROM typing_course_allocations WHERE course_id = :course_id");
                $checkStmt->execute([':course_id' => $course_id]);
                
                if ($checkStmt->rowCount() > 0) {
                    // Success
                    $_SESSION['student_id'] = $student['id'];
                    $_SESSION['student_name'] = $student['first_name'] . ' ' . $student['last_name'];
                    $_SESSION['student_image'] = $student['student_image'];
                    $_SESSION['typing_master_access'] = true;
                    
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error = "Access Denied. Your course does not have detailed Typing Master privileges.";
                }
            } else {
                $error = "Invalid Enrollment No or Password.";
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
    <title>Login - Typing Master</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(17, 94, 89, 0.1);
        }
        .brand-logo {
            width: 60px;
            height: 60px;
            background: #0f766e;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin: 0 auto 1.5rem;
        }
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
        }
        .form-control:focus {
            border-color: #0f766e;
            box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1);
            background-color: white;
        }
        .btn-primary {
            background-color: #0f766e;
            border-color: #0f766e;
            padding: 0.75rem;
            border-radius: 10px;
            font-weight: 600;
            width: 100%;
        }
        .btn-primary:hover {
            background-color: #115e59;
            border-color: #115e59;
        }
        .login-title {
            color: #0f766e;
            font-weight: 700;
            text-align: center;
            margin-bottom: 0.5rem;
        }
        .login-subtitle {
            text-align: center;
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="brand-logo">
            <i class="fas fa-keyboard"></i>
        </div>
        <h4 class="login-title">Typing Master</h4>
        <p class="login-subtitle">Student Portal Login</p>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 text-center" role="alert">
                <small><?php echo htmlspecialchars($error); ?></small>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label text-secondary small fw-bold">Enrollment No</label>
                <input type="text" name="enrollment_no" class="form-control" placeholder="Enter Enrollment No" required>
            </div>
            <div class="mb-4">
                <label class="form-label text-secondary small fw-bold">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter Password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary">Sign In</button>
        </form>
    </div>

    <!-- FontAwesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
