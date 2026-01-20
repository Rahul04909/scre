<?php
// Include database configuration
require_once '../../database/config.php';

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../../vendor/autoload.php'; // Ensure Composer's autoloader is available

$message = '';
$messageType = '';

// Handle Save Settings
if (isset($_POST['save_settings'])) {
    $host = trim($_POST['host']);
    $port = intval($_POST['port']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $encryption = trim($_POST['encryption']);
    $from_email = trim($_POST['from_email']);
    $from_name = trim($_POST['from_name']);

    try {
        // Update the single row in smtp_settings (assuming id=1 or just updating correct one)
        // Since we insert a default row, we can just update the first one found or specifically ID 1.
        // Using a generic update for the single row approach.
        $sql = "UPDATE smtp_settings SET host = :host, port = :port, username = :username, password = :password, encryption = :encryption, from_email = :from_email, from_name = :from_name LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':host' => $host,
            ':port' => $port,
            ':username' => $username,
            ':password' => $password,
            ':encryption' => $encryption,
            ':from_email' => $from_email,
            ':from_name' => $from_name
        ]);
        
        $message = "SMTP Settings saved successfully!";
        $messageType = "success";
    } catch (PDOException $e) {
        $message = "Database Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Handle Test Email
if (isset($_POST['send_test_email'])) {
    $test_email = trim($_POST['test_email']);
    
    // Fetch current settings (even if not saved in this request, we use what's in DB to verify)
    // Alternatively, we could use the POST data if we wanted to test before saving. 
    // Let's fetch from DB to be sure we are testing what is saved.
    try {
        $stmt = $pdo->query("SELECT * FROM smtp_settings LIMIT 1");
        $settings = $stmt->fetch();
        
        if ($settings) {
            $mail = new PHPMailer(true);

            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host       = $settings['host'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $settings['username'];
                $mail->Password   = $settings['password'];
                $mail->SMTPSecure = $settings['encryption'];
                $mail->Port       = $settings['port'];

                //Recipients
                $mail->setFrom($settings['from_email'], $settings['from_name']);
                $mail->addAddress($test_email);

                //Content
                $mail->isHTML(true);
                $mail->Subject = 'Test Email from Admin Panel';
                $mail->Body    = '<b>Success!</b> Your SMTP settings are configured correctly.';
                $mail->AltBody = 'Success! Your SMTP settings are configured correctly.';

                $mail->send();
                $message = "Test email sent successfully to $test_email";
                $messageType = "success";
            } catch (Exception $e) {
                $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                $messageType = "danger";
            }
        } else {
            $message = "No SMTP settings found. Please save settings first.";
            $messageType = "warning";
        }
    } catch (PDOException $e) {
        $message = "Database Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Fetch settings for display
try {
    $stmt = $pdo->query("SELECT * FROM smtp_settings LIMIT 1");
    $settings = $stmt->fetch();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage SMTP - Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Sidebar CSS -->
    <link href="../assets/css/sidebar.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <?php include '../sidebar.php'; ?>

        <div id="page-content-wrapper" style="margin-left: 280px;">
            <div class="container-fluid py-5 px-lg-5">
                <h2 class="mb-4">SMTP Configuration</h2>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-8">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-server me-2"></i> SMTP Server Settings</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="row mb-3">
                                        <div class="col-md-8">
                                            <label class="form-label">SMTP Host</label>
                                            <input type="text" name="host" class="form-control" value="<?php echo htmlspecialchars($settings['host'] ?? ''); ?>" required placeholder="smtp.gmail.com">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Port</label>
                                            <input type="number" name="port" class="form-control" value="<?php echo htmlspecialchars($settings['port'] ?? '587'); ?>" required placeholder="587">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Username</label>
                                            <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($settings['username'] ?? ''); ?>" required placeholder="email@example.com">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Password</label>
                                            <input type="password" name="password" class="form-control" value="<?php echo htmlspecialchars($settings['password'] ?? ''); ?>" required placeholder="App Password">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Encryption</label>
                                            <select name="encryption" class="form-select">
                                                <option value="tls" <?php if(($settings['encryption'] ?? '') == 'tls') echo 'selected'; ?>>TLS</option>
                                                <option value="ssl" <?php if(($settings['encryption'] ?? '') == 'ssl') echo 'selected'; ?>>SSL</option>
                                                <option value="" <?php if(($settings['encryption'] ?? '') == '') echo 'selected'; ?>>None</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">From Email</label>
                                            <input type="email" name="from_email" class="form-control" value="<?php echo htmlspecialchars($settings['from_email'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">From Name</label>
                                            <input type="text" name="from_name" class="form-control" value="<?php echo htmlspecialchars($settings['from_name'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <button type="submit" name="save_settings" class="btn btn-primary"><i class="fas fa-save me-2"></i> Save Settings</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 text-success fw-bold"><i class="fas fa-paper-plane me-2"></i> Test Configuration</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">Send a test email to verify your settings are correct.</p>
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label class="form-label">To Email Address</label>
                                        <input type="email" name="test_email" class="form-control" placeholder="your@email.com" required>
                                    </div>
                                    <button type="submit" name="send_test_email" class="btn btn-success w-100"><i class="fas fa-check-circle me-2"></i> Send Test Email</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>
</body>
</html>
