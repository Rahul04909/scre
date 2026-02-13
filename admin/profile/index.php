<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../../database/config.php';

$admin_id = $_SESSION['admin_id'];
$msg = '';
$msgType = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // File Upload
    $imagePath = $_SESSION['admin_image']; // Default to current
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $uploadDir = '../../assets/uploads/admins/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $fileName = 'admin_' . $admin_id . '_' . time() . '.' . $ext;
        $targetFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile)) {
            $imagePath = 'assets/uploads/admins/' . $fileName;
        }
    }

    try {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE admins SET name = ?, email = ?, password = ?, image = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $email, $hashed_password, $imagePath, $admin_id]);
        } else {
            $sql = "UPDATE admins SET name = ?, email = ?, image = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $email, $imagePath, $admin_id]);
        }

        // Update Session
        $_SESSION['admin_name'] = $name;
        $_SESSION['admin_image'] = $imagePath;

        $msg = "Profile updated successfully!";
        $msgType = "success";

    } catch (PDOException $e) {
        $msg = "Error updating profile: " . $e->getMessage();
        $msgType = "danger";
    }
}

// Fetch Current Admin Data
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Profile - PACE Admin</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
    <style>
        .profile-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            background: #fff;
            overflow: hidden;
            max-width: 600px;
            margin: 0 auto;
        }
        .profile-header {
            background-color: #115E59;
            padding: 30px;
            text-align: center;
            color: white;
        }
        .profile-pic-wrapper {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 15px;
        }
        .profile-pic {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .upload-btn {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #F59E0B;
            color: white;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2px solid white;
            transition: all 0.2s;
        }
        .upload-btn:hover { background: #d97706; transform: scale(1.1); }
    </style>
</head>
<body>

    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>

        <div id="page-content-wrapper">
            <?php include '../header.php'; ?>

            <div class="container-fluid px-4 py-5">
                
                <?php if ($msg): ?>
                    <div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show mb-4" style="max-width: 600px; margin: 0 auto;">
                        <?php echo $msg; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="profile-card">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="profile-header">
                            <div class="profile-pic-wrapper">
                                <?php 
                                    $imgSrc = !empty($admin['image']) ? '../../' . $admin['image'] : 'https://ui-avatars.com/api/?name=' . urlencode($admin['name']);
                                ?>
                                <img src="<?php echo $imgSrc; ?>" class="profile-pic" id="previewImg">
                                <label for="profile_image" class="upload-btn">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <input type="file" name="profile_image" id="profile_image" class="d-none" accept="image/*" onchange="previewFile()">
                            </div>
                            <h4 class="fw-bold"><?php echo htmlspecialchars($admin['name']); ?></h4>
                            <p class="mb-0 opacity-75"><?php echo htmlspecialchars($admin['username']); ?></p>
                        </div>

                        <div class="p-4">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">New Password <small class="text-muted">(Leave blank to keep current)</small></label>
                                <input type="password" name="password" class="form-control" placeholder="Enter new password">
                            </div>

                            <button type="submit" class="btn btn-warning w-100 fw-bold py-2 text-dark">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewFile() {
            var preview = document.getElementById('previewImg');
            var file    = document.querySelector('input[type=file]').files[0];
            var reader  = new FileReader();

            reader.onloadend = function () {
                preview.src = reader.result;
            }

            if (file) {
                reader.readAsDataURL(file);
            } else {
                preview.src = "";
            }
        }
    </script>
</body>
</html>
