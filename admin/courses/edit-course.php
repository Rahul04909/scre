<?php
require_once '../../database/config.php';

if (!isset($_GET['id'])) { header("Location: manage-courses.php"); exit; }
$id = intval($_GET['id']);
$message = '';
$messageType = '';

// Fetch Course Data
try {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $course = $stmt->fetch();
    if (!$course) die("Course not found.");
    
    // Fetch Categories
    $stmt = $pdo->query("SELECT id, category_name FROM course_categories ORDER BY category_name ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) { die("Database Error: " . $e->getMessage()); }

// Handle Update
if (isset($_POST['update_course'])) {
    // Inputs
    $category_id = intval($_POST['category_id']);
    $course_name = trim($_POST['course_name']);
    $course_code = trim($_POST['course_code']);
    $description = $_POST['description'];
    $course_type = $_POST['course_type'];
    $duration_type = $_POST['duration_type'];
    $duration_value = intval($_POST['duration_value']);
    
    $course_fees = floatval($_POST['course_fees']);
    $admission_fees = floatval($_POST['admission_fees']);
    $exam_fees_enabled = isset($_POST['exam_fees_enabled']) ? 1 : 0;
    $exam_fees = $exam_fees_enabled ? floatval($_POST['exam_fees']) : 0.00;
    $backlog_fees_enabled = isset($_POST['backlog_fees_enabled']) ? 1 : 0;
    $backlog_fees = $backlog_fees_enabled ? floatval($_POST['backlog_fees']) : 0.00;
    
    $has_units = isset($_POST['has_units']) && $_POST['has_units'] == 'yes' ? 1 : 0;
    $unit_type = $has_units ? $_POST['unit_type'] : null;
    $unit_count = $has_units ? intval($_POST['unit_count']) : 0;
    
    // SEO
    $m_title = $_POST['meta_title']; $m_desc = $_POST['meta_description']; $m_keys = $_POST['meta_keywords'];
    $schema = $_POST['schema_markup']; $og_title = $_POST['og_title']; $og_desc = $_POST['og_description'];

    // Images
    $uploadDir = '../../assets/uploads/courses/';
    $course_image = $course['course_image'];
    if (isset($_FILES['course_image']) && $_FILES['course_image']['error'] == 0) {
        $fileName = time() . '_' . basename($_FILES['course_image']['name']);
        if (move_uploaded_file($_FILES['course_image']['tmp_name'], $uploadDir . $fileName)) {
            $course_image = 'assets/uploads/courses/' . $fileName;
        }
    }
    $og_image = $course['og_image'];
    if (isset($_FILES['og_image']) && $_FILES['og_image']['error'] == 0) {
        $fileName = time() . '_og_' . basename($_FILES['og_image']['name']);
        if (move_uploaded_file($_FILES['og_image']['tmp_name'], $uploadDir . $fileName)) {
            $og_image = 'assets/uploads/courses/' . $fileName;
        }
    }

    try {
        $sql = "UPDATE courses SET 
            category_id=:cat, course_name=:name, course_code=:code, course_image=:img, description=:desc,
            course_type=:type, duration_type=:dtype, duration_value=:dval,
            course_fees=:cfees, admission_fees=:afees,
            exam_fees_enabled=:een, exam_fees=:efees, backlog_fees_enabled=:ben, backlog_fees=:bfees,
            has_units=:hu, unit_type=:ut, unit_count=:uc,
            meta_title=:mt, meta_description=:md, meta_keywords=:mk, schema_markup=:sch,
            og_title=:ot, og_description=:od, og_image=:oi
            WHERE id=:id";
            
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':cat'=>$category_id, ':name'=>$course_name, ':code'=>$course_code, ':img'=>$course_image, ':desc'=>$description,
            ':type'=>$course_type, ':dtype'=>$duration_type, ':dval'=>$duration_value,
            ':cfees'=>$course_fees, ':afees'=>$admission_fees,
            ':een'=>$exam_fees_enabled, ':efees'=>$exam_fees, ':ben'=>$backlog_fees_enabled, ':bfees'=>$backlog_fees,
            ':hu'=>$has_units, ':ut'=>$unit_type, ':uc'=>$unit_count,
            ':mt'=>$m_title, ':md'=>$m_desc, ':mk'=>$m_keys, ':sch'=>$schema,
            ':ot'=>$og_title, ':od'=>$og_desc, ':oi'=>$og_image,
            ':id'=>$id
        ]);
        
        $message = "Course updated successfully!";
        $messageType = "success";
        
        // Refresh
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $course = $stmt->fetch();
        
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Course - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                    <h2 class="mb-0">Edit Course</h2>
                    <a href="manage-courses.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i> Back to List</a>
                </div>
                
                <?php if ($message): ?><div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show"><?php echo $message; ?><button class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-lg-8">
                            <!-- Basic -->
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-body">
                                    <h5 class="section-header">Basic Information</h5>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Category</label>
                                            <select name="category_id" class="form-select" required>
                                                <?php foreach ($categories as $cat): ?>
                                                    <option value="<?php echo $cat['id']; ?>" <?php if($course['category_id'] == $cat['id']) echo 'selected'; ?>>
                                                        <?php echo htmlspecialchars($cat['category_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Type</label>
                                            <select name="course_type" class="form-select">
                                                <option value="degree" <?php if($course['course_type']=='degree') echo 'selected'; ?>>Degree</option>
                                                <option value="diploma" <?php if($course['course_type']=='diploma') echo 'selected'; ?>>Diploma</option>
                                                <option value="crash_course" <?php if($course['course_type']=='crash_course') echo 'selected'; ?>>Crash Course</option>
                                                <option value="certification" <?php if($course['course_type']=='certification') echo 'selected'; ?>>Certification</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6"><label>Name</label><input type="text" name="course_name" class="form-control" value="<?php echo htmlspecialchars($course['course_name']); ?>" required></div>
                                        <div class="col-md-6"><label>Code</label><input type="text" name="course_code" class="form-control" value="<?php echo htmlspecialchars($course['course_code']); ?>" required></div>
                                    </div>
                                    <div class="mb-3">
                                        <label>Duration</label>
                                        <div class="input-group">
                                            <input type="number" name="duration_value" class="form-control" value="<?php echo $course['duration_value']; ?>" required>
                                            <select name="duration_type" class="form-select" style="max-width: 150px;">
                                                <option value="months" <?php if($course['duration_type']=='months') echo 'selected'; ?>>Months</option>
                                                <option value="years" <?php if($course['duration_type']=='years') echo 'selected'; ?>>Years</option>
                                                <option value="days" <?php if($course['duration_type']=='days') echo 'selected'; ?>>Days</option>
                                                <option value="hours" <?php if($course['duration_type']=='hours') echo 'selected'; ?>>Hours</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3"><label>Description</label><textarea id="summernote" name="description"><?php echo $course['description']; ?></textarea></div>
                                </div>
                            </div>
                            
                            <!-- Fees & Units -->
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-body">
                                    <h5 class="section-header">Fees & Structure</h5>
                                    <div class="row mb-3">
                                        <div class="col-md-6"><label>Course Fees</label><input type="number" step="0.01" name="course_fees" class="form-control" value="<?php echo $course['course_fees']; ?>"></div>
                                        <div class="col-md-6"><label>Admission Fees</label><input type="number" step="0.01" name="admission_fees" class="form-control" value="<?php echo $course['admission_fees']; ?>"></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" id="examFeesToggle" name="exam_fees_enabled" <?php if($course['exam_fees_enabled']) echo 'checked'; ?>>
                                                <label class="form-check-label">Exam Fees Applicable?</label>
                                            </div>
                                            <input type="number" step="0.01" name="exam_fees" id="examFeesInput" class="form-control" value="<?php echo $course['exam_fees']; ?>" <?php if(!$course['exam_fees_enabled']) echo 'disabled'; ?>>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" id="backlogFeesToggle" name="backlog_fees_enabled" <?php if($course['backlog_fees_enabled']) echo 'checked'; ?>>
                                                <label class="form-check-label">Backlog Fees Applicable?</label>
                                            </div>
                                            <input type="number" step="0.01" name="backlog_fees" id="backlogFeesInput" class="form-control" value="<?php echo $course['backlog_fees']; ?>" <?php if(!$course['backlog_fees_enabled']) echo 'disabled'; ?>>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <label class="form-label d-block">Has Units?</label>
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="has_units" value="yes" <?php if($course['has_units']) echo 'checked'; ?>> Yes</div>
                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="has_units" value="no" <?php if(!$course['has_units']) echo 'checked'; ?>> No</div>
                                        </div>
                                    </div>
                                    <div class="row mt-3 <?php if(!$course['has_units']) echo 'd-none'; ?>" id="unitsDetails">
                                        <div class="col-md-6"><label>Type</label><select name="unit_type" class="form-select"><option value="semester" <?php if($course['unit_type']=='semester') echo 'selected'; ?>>Semester</option><option value="year" <?php if($course['unit_type']=='year') echo 'selected'; ?>>Yearly</option></select></div>
                                        <div class="col-md-6"><label>Count</label><input type="number" name="unit_count" class="form-control" value="<?php echo $course['unit_count']; ?>"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- SEO -->
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-body">
                                    <h5 class="section-header">SEO</h5>
                                    <div class="mb-3"><label>Meta Title</label><input type="text" name="meta_title" class="form-control" value="<?php echo htmlspecialchars($course['meta_title']); ?>"></div>
                                    <div class="mb-3"><label>Meta Desc</label><textarea name="meta_description" class="form-control" rows="2"><?php echo htmlspecialchars($course['meta_description']); ?></textarea></div>
                                    <div class="mb-3"><label>Keywords</label><input type="text" name="meta_keywords" class="form-control" value="<?php echo htmlspecialchars($course['meta_keywords']); ?>"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card shadow-sm border-0 mb-4"><div class="card-body text-center"><h6 class="fw-bold">Course Image</h6><label for="c_img" class="image-preview-container"><img src="../../<?php echo htmlspecialchars($course['course_image']); ?>" id="prev_c"></label><input type="file" name="course_image" id="c_img" class="d-none" onchange="previewImage(this,'prev_c')"></div></div>
                            <div class="card shadow-sm border-0 mb-4"><div class="card-body text-center"><h6 class="fw-bold">SEO Image</h6><label for="og_img" class="image-preview-container"><img src="../../<?php echo htmlspecialchars($course['og_image']); ?>" id="prev_og"></label><input type="file" name="og_image" id="og_img" class="d-none" onchange="previewImage(this,'prev_og')"></div></div>
                            <button type="submit" name="update_course" class="btn btn-primary btn-lg w-100">Update Course</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
    <script src="../assets/js/sidebar.js"></script>
    <script>
        $(document).ready(function() {
            $('#summernote').summernote({ height: 250 });
            $('#examFeesToggle').change(function() { $('#examFeesInput').prop('disabled', !this.checked); });
            $('#backlogFeesToggle').change(function() { $('#backlogFeesInput').prop('disabled', !this.checked); });
            $('input[name="has_units"]').change(function() {
                if(this.value === 'yes') $('#unitsDetails').removeClass('d-none'); else $('#unitsDetails').addClass('d-none');
            });
        });
        function previewImage(i,id){ if(i.files && i.files[0]){ var r=new FileReader(); r.onload=function(e){ $('#'+id).attr('src',e.target.result); }; r.readAsDataURL(i.files[0]); } }
    </script>
</body>
</html>
