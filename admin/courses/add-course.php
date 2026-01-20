<?php
require_once '../../database/config.php';

$message = '';
$messageType = '';

// Fetch Categories for Dropdown
try {
    $stmt = $pdo->query("SELECT id, category_name FROM course_categories ORDER BY category_name ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

if (isset($_POST['add_course'])) {
    // Basic
    $category_id = intval($_POST['category_id']);
    $course_name = trim($_POST['course_name']);
    $course_code = trim($_POST['course_code']);
    $description = $_POST['description']; // HTML Content
    $course_type = $_POST['course_type'];
    $duration_type = $_POST['duration_type'];
    $duration_value = intval($_POST['duration_value']);
    
    // Fees
    $course_fees = floatval($_POST['course_fees']);
    $admission_fees = floatval($_POST['admission_fees']);
    
    $exam_fees_enabled = isset($_POST['exam_fees_enabled']) ? 1 : 0;
    $exam_fees = $exam_fees_enabled ? floatval($_POST['exam_fees']) : 0.00;
    
    $backlog_fees_enabled = isset($_POST['backlog_fees_enabled']) ? 1 : 0;
    $backlog_fees = $backlog_fees_enabled ? floatval($_POST['backlog_fees']) : 0.00;
    
    // Units
    $has_units = isset($_POST['has_units']) && $_POST['has_units'] == 'yes' ? 1 : 0;
    $unit_type = $has_units ? $_POST['unit_type'] : null;
    $unit_count = $has_units ? intval($_POST['unit_count']) : 0;
    
    // SEO
    $meta_title = trim($_POST['meta_title']);
    $meta_description = trim($_POST['meta_description']);
    $meta_keywords = trim($_POST['meta_keywords']);
    $schema_markup = trim($_POST['schema_markup']);
    $og_title = trim($_POST['og_title']);
    $og_description = trim($_POST['og_description']);

    // Image Upload
    $uploadDir = '../../assets/uploads/courses/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $course_image = '';
    if (isset($_FILES['course_image']) && $_FILES['course_image']['error'] == 0) {
        $fileName = time() . '_' . basename($_FILES['course_image']['name']);
        if (move_uploaded_file($_FILES['course_image']['tmp_name'], $uploadDir . $fileName)) {
            $course_image = 'assets/uploads/courses/' . $fileName;
        }
    }

    $og_image = '';
    if (isset($_FILES['og_image']) && $_FILES['og_image']['error'] == 0) {
        $fileName = time() . '_og_' . basename($_FILES['og_image']['name']);
        if (move_uploaded_file($_FILES['og_image']['tmp_name'], $uploadDir . $fileName)) {
            $og_image = 'assets/uploads/courses/' . $fileName;
        }
    }

    try {
        $sql = "INSERT INTO courses (
            category_id, course_name, course_code, course_image, description,
            course_type, duration_type, duration_value,
            course_fees, admission_fees,
            exam_fees_enabled, exam_fees, backlog_fees_enabled, backlog_fees,
            has_units, unit_type, unit_count,
            meta_title, meta_description, meta_keywords, schema_markup,
            og_title, og_description, og_image
        ) VALUES (
            :cat_id, :name, :code, :image, :desc,
            :type, :d_type, :d_val,
            :c_fees, :a_fees,
            :e_enabled, :e_fees, :b_enabled, :b_fees,
            :has_u, :u_type, :u_count,
            :m_title, :m_desc, :m_keys, :schema,
            :og_title, :og_desc, :og_img
        )";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':cat_id' => $category_id, ':name' => $course_name, ':code' => $course_code, ':image' => $course_image, ':desc' => $description,
            ':type' => $course_type, ':d_type' => $duration_type, ':d_val' => $duration_value,
            ':c_fees' => $course_fees, ':a_fees' => $admission_fees,
            ':e_enabled' => $exam_fees_enabled, ':e_fees' => $exam_fees, ':b_enabled' => $backlog_fees_enabled, ':b_fees' => $backlog_fees,
            ':has_u' => $has_units, ':u_type' => $unit_type, ':u_count' => $unit_count,
            ':m_title' => $meta_title, ':m_desc' => $meta_description, ':m_keys' => $meta_keywords, ':schema' => $schema_markup,
            ':og_title' => $og_title, ':og_desc' => $og_description, ':og_img' => $og_image
        ]);

        header("Location: manage-courses.php?msg=added");
        exit;
    } catch (PDOException $e) {
        $message = "Database Error: " . $e->getMessage();
        $messageType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Course - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Summernote CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <style>
        .image-preview-container {
            width: 100%; height: 180px;
            border: 2px dashed #ddd; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden; background: #f8f9fa; cursor: pointer;
        }
        .image-preview-container img { max-width: 100%; max-height: 100%; object-fit: cover; }
        .section-header { border-left: 4px solid #0d6efd; padding-left: 10px; margin-bottom: 20px; color: #333; font-weight: 600; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>

        <div id="page-content-wrapper" style="margin-left: 280px;">
            <div class="container-fluid py-5 px-lg-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Add New Course</h2>
                    <a href="manage-courses.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i> Back to List</a>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show"><?php echo $message; ?> <button class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <!-- Left Panel -->
                        <div class="col-lg-8">
                            
                            <!-- Basic Info -->
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-body">
                                    <h5 class="section-header">Basic Information</h5>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Course Category <span class="text-danger">*</span></label>
                                            <select name="category_id" class="form-select" required>
                                                <option value="">Select Category</option>
                                                <?php foreach ($categories as $cat): ?>
                                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Course Type <span class="text-danger">*</span></label>
                                            <select name="course_type" class="form-select" required>
                                                <option value="degree">Degree</option>
                                                <option value="diploma">Diploma</option>
                                                <option value="crash_course">Crash Course</option>
                                                <option value="certification">Certification</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Course Name <span class="text-danger">*</span></label>
                                            <input type="text" name="course_name" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Course Code <span class="text-danger">*</span></label>
                                            <input type="text" name="course_code" class="form-control" required placeholder="e.g. CS101">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Duration <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" name="duration_value" class="form-control" placeholder="Value (e.g. 6)" required>
                                            <select name="duration_type" class="form-select" style="max-width: 150px;">
                                                <option value="months">Months</option>
                                                <option value="years">Years</option>
                                                <option value="days">Days</option>
                                                <option value="hours">Hours</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Course Description</label>
                                        <textarea id="summernote" name="description"></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Fees & Units -->
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-body">
                                    <h5 class="section-header">Fees & Structure</h5>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Course Fees (INR)</label>
                                            <input type="number" step="0.01" name="course_fees" class="form-control" required placeholder="0.00">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Admission Fees (INR)</label>
                                            <input type="number" step="0.01" name="admission_fees" class="form-control" placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" id="examFeesToggle" name="exam_fees_enabled">
                                                <label class="form-check-label" for="examFeesToggle">Exam Fees Applicable?</label>
                                            </div>
                                            <input type="number" step="0.01" name="exam_fees" id="examFeesInput" class="form-control" placeholder="Exam Fee Amount" disabled>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" id="backlogFeesToggle" name="backlog_fees_enabled">
                                                <label class="form-check-label" for="backlogFeesToggle">Backlog Fees Applicable?</label>
                                            </div>
                                            <input type="number" step="0.01" name="backlog_fees" id="backlogFeesInput" class="form-control" placeholder="Backlog Fee Amount" disabled>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <label class="form-label d-block">Does this course have units (Semester/Yearly)?</label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="has_units" id="unitsYes" value="yes">
                                                <label class="form-check-label" for="unitsYes">Yes</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="has_units" id="unitsNo" value="no" checked>
                                                <label class="form-check-label" for="unitsNo">No</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3 d-none" id="unitsDetails">
                                        <div class="col-md-6">
                                            <label class="form-label">Unit Type</label>
                                            <select name="unit_type" class="form-select">
                                                <option value="semester">Semester</option>
                                                <option value="year">Yearly</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Unit Count (Total)</label>
                                            <input type="number" name="unit_count" class="form-control" placeholder="Example: 4 for 4 Semesters">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- SEO Section -->
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-body">
                                    <h5 class="section-header">SEO Configuration</h5>
                                    
                                    <ul class="nav nav-tabs mb-3" id="seoTab" role="tablist">
                                        <li class="nav-item"><button class="nav-link active" id="meta-tab" data-bs-toggle="tab" data-bs-target="#meta" type="button">Meta Tags</button></li>
                                        <li class="nav-item"><button class="nav-link" id="og-tab" data-bs-toggle="tab" data-bs-target="#og" type="button">Open Graph</button></li>
                                    </ul>
                                    
                                    <div class="tab-content" id="seoTabContent">
                                        <div class="tab-pane fade show active" id="meta">
                                            <div class="mb-3">
                                                <label class="form-label">Meta Title</label>
                                                <input type="text" name="meta_title" class="form-control">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Meta Description</label>
                                                <textarea name="meta_description" class="form-control" rows="2"></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Keywords</label>
                                                <input type="text" name="meta_keywords" class="form-control" placeholder="comma, separated">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Schema Markup</label>
                                                <textarea name="schema_markup" class="form-control" rows="3"></textarea>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="og">
                                            <div class="mb-3">
                                                <label class="form-label">OG Title</label>
                                                <input type="text" name="og_title" class="form-control">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">OG Description</label>
                                                <textarea name="og_description" class="form-control" rows="2"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Right Panel -->
                        <div class="col-lg-4">
                            <!-- Course Image -->
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-body text-center">
                                    <h6 class="fw-bold mb-3">Course Image</h6>
                                    <label for="course_img" class="image-preview-container" id="course_preview_box">
                                        <span>Select Image</span>
                                        <img src="" style="display:none;" id="course_preview">
                                    </label>
                                    <input type="file" name="course_image" id="course_img" class="d-none" accept="image/*" onchange="previewImage(this, 'course_preview')">
                                </div>
                            </div>

                            <!-- OG Image -->
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-body text-center">
                                    <h6 class="fw-bold mb-3">Search Social Image</h6>
                                    <label for="og_img" class="image-preview-container" id="og_preview_box">
                                        <span>Select Image</span>
                                        <img src="" style="display:none;" id="og_preview_img">
                                    </label>
                                    <input type="file" name="og_image" id="og_img" class="d-none" accept="image/*" onchange="previewImage(this, 'og_preview_img')">
                                </div>
                            </div>

                            <button type="submit" name="add_course" class="btn btn-primary btn-lg w-100"><i class="fas fa-save me-2"></i> Save Course</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#summernote').summernote({
                placeholder: 'Detailed course description...',
                tabsize: 2,
                height: 250,
                toolbar: [
                  ['style', ['style']],
                  ['font', ['bold', 'underline', 'clear']],
                  ['color', ['color']],
                  ['para', ['ul', 'ol', 'paragraph']],
                  ['table', ['table']],
                  ['insert', ['link', 'picture', 'video']],
                  ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });

            // Fees Toggles
            $('#examFeesToggle').change(function() {
                $('#examFeesInput').prop('disabled', !this.checked);
            });
            $('#backlogFeesToggle').change(function() {
                $('#backlogFeesInput').prop('disabled', !this.checked);
            });

            // Units Toggle
            $('input[name="has_units"]').change(function() {
                if(this.value === 'yes') {
                    $('#unitsDetails').removeClass('d-none');
                } else {
                    $('#unitsDetails').addClass('d-none');
                }
            });
        });

        function previewImage(input, imgId) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#' + imgId).attr('src', e.target.result).show();
                    $('#' + imgId).prev('span').hide();
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
