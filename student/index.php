<?php
session_start();
require_once '../database/config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// Fetch Student Stats (Fees)
// Calculate Total Fee (Course + Admission)
$stmtFee = $pdo->prepare("
    SELECT 
        (c.course_fees + c.admission_fees) as total_fee,
        c.course_name,
        s.first_name, s.last_name, s.enrollment_no, s.student_image, s.dob, s.email
    FROM students s
    JOIN courses c ON s.course_id = c.id
    WHERE s.id = ?
");
$stmtFee->execute([$student_id]);
$student = $stmtFee->fetch();

// Check if review already submitted
$stmtReviewCheck = $pdo->prepare("SELECT COUNT(*) FROM student_reviews WHERE student_id = ?");
$stmtReviewCheck->execute([$student_id]);
$review_exists = $stmtReviewCheck->fetchColumn() > 0;

// Handle Review Submission
$review_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!$review_exists) {
        $rating = intval($_POST['rating'] ?? 0);
        $message = trim($_POST['review_message'] ?? '');
        
        if ($rating >= 1 && $rating <= 5 && !empty($message)) {
            try {
                $stmtInsert = $pdo->prepare("INSERT INTO student_reviews (student_id, rating, review_message) VALUES (?, ?, ?)");
                $stmtInsert->execute([$student_id, $rating, $message]);
                $review_exists = true; // Hide form immediately
                $review_msg = "<div class='alert alert-success'>Thank you! Your review has been submitted.</div>";
            } catch (PDOException $e) {
                $review_msg = "<div class='alert alert-danger'>Error submitting review. Please try again.</div>";
            }
        } else {
            $review_msg = "<div class='alert alert-warning'>Please select a rating and write a message.</div>";
        }
    }
}

// Calculate Paid
$stmtPaid = $pdo->prepare("SELECT SUM(amount) FROM student_fees WHERE student_id = ?");
$stmtPaid->execute([$student_id]);
$paid = $stmtPaid->fetchColumn() ?: 0;

$pending = $student['total_fee'] - $paid;

// Recent Transactions
$stmtTxn = $pdo->prepare("SELECT * FROM student_fees WHERE student_id = ? ORDER BY payment_date DESC LIMIT 5");
$stmtTxn->execute([$student_id]);
$recent_txns = $stmtTxn->fetchAll();

// Check for Birthday
$is_birthday = false;
if (!empty($student['dob'])) {
    $today_md = date('m-d');
    $dob_md = date('m-d', strtotime($student['dob']));
    if ($today_md === $dob_md) {
        $is_birthday = true;
    }
}
// For testing purposes, uncomment to force show:
$is_birthday = true; 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - PACE Student</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/sidebar.css" rel="stylesheet">
    <link href="assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div id="page-content-wrapper">
            <?php include 'header.php'; ?>
            
            <div class="container-fluid px-4 py-4">
                
                <!-- Welcome Section -->
                <div class="welcome-card p-4 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h2 class="welcome-heading mb-1">Hello, <?php echo htmlspecialchars($student['first_name']); ?> 👋</h2>
                        <p class="welcome-subtext mb-0">Welcome to your student portal. Here's your quick overview.</p>
                    </div>
                    <div>
                        <span class="student-badge">
                            <i class="fas fa-id-badge me-2"></i><?php echo htmlspecialchars($student['enrollment_no']); ?>
                        </span>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="row g-4 mb-4">
                    <!-- Fees Paid -->
                    <div class="col-md-4">
                        <div class="stat-card stat-green p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-label">Fees Paid</div>
                                    <h3 class="stat-value">₹<?php echo number_format($paid); ?></h3>
                                </div>
                                <div class="stat-icon-circle">
                                    <i class="fas fa-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Fees Pending -->
                    <div class="col-md-4">
                        <div class="stat-card stat-amber p-4">
                             <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-label">Fees Pending</div>
                                    <h3 class="stat-value">₹<?php echo number_format($pending); ?></h3>
                                </div>
                                <div class="stat-icon-circle">
                                    <i class="fas fa-hourglass-half"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Course Info -->
                    <div class="col-md-4">
                        <div class="stat-card stat-indigo p-4">
                             <div class="d-flex justify-content-between align-items-start">
                                <div style="overflow: hidden;">
                                    <div class="stat-label">Enrolled Course</div>
                                    <h4 class="fw-bold mb-0 text-truncate text-dark" title="<?php echo htmlspecialchars($student['course_name']); ?>">
                                        <?php echo htmlspecialchars($student['course_name']); ?>
                                    </h4>
                                </div>
                                <div class="stat-icon-circle flex-shrink-0 ms-2">
                                    <i class="fas fa-book-reader"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Row -->
                <div class="row g-4">
                    
                    <!-- Recent Payments -->
                    <div class="<?php echo $is_birthday ? 'col-lg-8' : 'col-lg-12'; ?>">
                        <div class="content-card h-100">
                            <div class="card-header-clean">
                                <h5 class="card-title-clean">
                                    <i class="fas fa-history text-muted"></i> Recent Payments
                                </h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-clean mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">Date</th>
                                            <th>Mode</th>
                                            <th>Transaction ID</th>
                                            <th class="text-end pe-4">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(count($recent_txns) > 0): ?>
                                            <?php foreach($recent_txns as $txn): ?>
                                            <tr>
                                                <td class="ps-4 text-secondary fw-500"><?php echo date('d M Y', strtotime($txn['payment_date'])); ?></td>
                                                <td><span class="badge bg-light text-dark border fw-normal"><?php echo htmlspecialchars($txn['payment_mode']); ?></span></td>
                                                <td class="small font-monospace text-muted"><?php echo htmlspecialchars($txn['transaction_id'] ?? '-'); ?></td>
                                                <td class="text-end pe-4 fw-bold text-success">+₹<?php echo number_format($txn['amount']); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="4" class="text-center py-5 text-muted">No recent payments found.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Birthday Wish Section (Only if Birthday) -->
                    <?php if ($is_birthday): ?>
                    <div class="col-lg-4">
                         <div class="content-card h-100 border-0" style="background: linear-gradient(135deg, #FFD700 0%, #FDB931 100%); color: #fff;">
                            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
                                <div class="mb-3" style="font-size: 3rem;">
                                    🎂
                                </div>
                                <h3 class="fw-bold mb-2 text-dark">Happy Birthday!</h3>
                                <h5 class="mb-3 text-dark"><?php echo htmlspecialchars($student['first_name']); ?></h5>
                                <p class="mb-0 text-dark opacity-75">
                                    Wishing you a fantastic day filled with joy and a year ahead full of success and learning!
                                </p>
                                <div class="mt-4">
                                    <i class="fas fa-gift fa-2x text-dark opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Review Section (One-time only) -->
                <?php if (!$review_exists): ?>
                <div class="row g-4 mt-2">
                    <div class="col-12">
                        <?php if (!empty($review_msg)) echo $review_msg; ?>
                        <div class="content-card">
                            <div class="card-header-clean">
                                <h5 class="card-title-clean">
                                    <i class="fas fa-star text-warning"></i> Write a Review
                                </h5>
                            </div>
                            <div class="p-4">
                                <form method="POST" action="">
                                    <div class="row">
                                        <!-- Readonly Info -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small">Full Name</label>
                                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label text-muted small">Email Address</label>
                                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($student['email']); ?>" readonly>
                                        </div>

                                        <!-- Rating -->
                                        <div class="col-12 mb-3">
                                            <label class="form-label">Rate your experience</label>
                                            <div class="rating-stars">
                                                <input type="radio" name="rating" id="star5" value="5" required> <label for="star5" title="5 stars">★</label>
                                                <input type="radio" name="rating" id="star4" value="4"> <label for="star4" title="4 stars">★</label>
                                                <input type="radio" name="rating" id="star3" value="3"> <label for="star3" title="3 stars">★</label>
                                                <input type="radio" name="rating" id="star2" value="2"> <label for="star2" title="2 stars">★</label>
                                                <input type="radio" name="rating" id="star1" value="1"> <label for="star1" title="1 star">★</label>
                                            </div>
                                        </div>

                                        <!-- Message -->
                                        <div class="col-12 mb-3">
                                            <label class="form-label">Your Review</label>
                                            <textarea name="review_message" class="form-control" rows="4" placeholder="Share your feedback..." required></textarea>
                                        </div>

                                        <div class="col-12 text-end">
                                            <button type="submit" name="submit_review" class="btn btn-primary px-5">Submit Review</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <style>
                    /* Simple Star Rating CSS */
                    .rating-stars {
                        display: flex;
                        flex-direction: row-reverse;
                        justify-content: flex-end;
                    }
                    .rating-stars input {
                        display: none;
                    }
                    .rating-stars label {
                        font-size: 2rem;
                        color: #ddd;
                        cursor: pointer;
                        padding: 0 5px;
                    }
                    .rating-stars input:checked ~ label,
                    .rating-stars input:hover ~ label,
                    .rating-stars label:hover ~ input:checked ~ label {
                         color: #ffc107;
                    }
                    /* Ensure hover fills up to the hovered star */
                    .rating-stars label:hover,
                    .rating-stars label:hover ~ label {
                        color: #ffc107;
                    }
                </style>
                <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
