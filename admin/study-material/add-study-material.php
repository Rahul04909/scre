<?php
session_start();
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: ../login.php");
//     exit;
// }
require_once '../../database/config.php';

$message = "";

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_material'])) {
    $course_id = $_POST['course_id'];
    $unit_no = !empty($_POST['unit_no']) ? $_POST['unit_no'] : NULL;
    $subject_id = !empty($_POST['subject_id']) ? $_POST['subject_id'] : NULL;
    $title = trim($_POST['title']);

    // File Upload
    $uploadDir = '../../assets/uploads/study-material/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $file_path = "";
    if (isset($_FILES['material_file']) && $_FILES['material_file']['error'] == 0) {
        $fileName = time() . '_' . basename($_FILES['material_file']['name']);
        if (move_uploaded_file($_FILES['material_file']['tmp_name'], $uploadDir . $fileName)) {
            $file_path = 'assets/uploads/study-material/' . $fileName;
        } else {
            $message = "<div class='alert alert-danger'>Error uploading file.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Please select a valid file.</div>";
    }

    if ($file_path && $course_id && $title) {
        try {
            $stmt = $pdo->prepare("INSERT INTO study_materials (course_id, unit_no, subject_id, title, file_path) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$course_id, $unit_no, $subject_id, $title, $file_path]);
            header("Location: index.php?msg=added");
            exit;
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

// Fetch Courses
$courses = $pdo->query("SELECT id, course_name FROM courses ORDER BY course_name")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Study Material - Admin</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/sidebar.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        #page-content-wrapper { margin-left: 280px; transition: margin 0.3s; }
        .content-card { background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); padding: 30px; }
        @media (max-width: 768px) { #page-content-wrapper { margin-left: 0; } }
    </style>
</head>
<body>

    <div class="d-flex" id="wrapper">
        <?php include '../sidebar.php'; ?>

        <div id="page-content-wrapper">
            <div class="container-fluid px-4 py-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold" style="color: #115E59;">Add Study Material</h2>
                    <a href="index.php" class="btn btn-secondary">Back to List</a>
                </div>

                <?php echo $message; ?>

                <div class="content-card">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Select Course <span class="text-danger">*</span></label>
                                <select name="course_id" id="course_id" class="form-select" required>
                                    <option value="">Choose Course</option>
                                    <?php foreach($courses as $id => $name): ?>
                                        <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3 d-none" id="unit_wrapper">
                                <label class="form-label">Select Unit/Semester</label>
                                <select name="unit_no" id="unit_no" class="form-select">
                                    <option value="">All Units</option>
                                    <!-- Populated via JS -->
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Select Subject</label>
                                <select name="subject_id" id="subject_id" class="form-select">
                                    <option value="">General / All Subjects</option>
                                    <!-- Populated via JS -->
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Material Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" placeholder="e.g. Chapter 1 Notes" required>
                            </div>

                            <div class="col-12 mb-4">
                                <label class="form-label">Upload PDF/File <span class="text-danger">*</span></label>
                                <input type="file" name="material_file" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx" required>
                                <div class="form-text">Allowed formats: PDF, DOC, PPT</div>
                            </div>

                            <div class="col-12">
                                <button type="submit" name="add_material" class="btn btn-primary px-5" style="background-color: #115E59; border-color: #115E59;">Upload Material</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            let subjectsData = [];

            $('#course_id').change(function() {
                const courseId = $(this).val();
                
                // Reset fields
                $('#unit_wrapper').addClass('d-none');
                $('#unit_no').html('<option value="">All Units</option>');
                $('#subject_id').html('<option value="">General / All Subjects</option>');
                subjectsData = [];

                if (courseId) {
                    $.getJSON('get-course-meta.php', { course_id: courseId }, function(data) {
                        if (data.error) {
                            alert(data.error);
                            return;
                        }

                        // Handle Units
                        if (data.has_units) {
                            $('#unit_wrapper').removeClass('d-none');
                            let options = '<option value="">All Units</option>';
                            for (let i = 1; i <= data.unit_count; i++) {
                                options += `<option value="${i}">${data.unit_type.charAt(0).toUpperCase() + data.unit_type.slice(1)} ${i}</option>`;
                            }
                            $('#unit_no').html(options);
                        }

                        // Handle Subjects (Store locally to filter by unit later)
                        subjectsData = data.subjects;
                        populateSubjects(null); // Show all subjects initially
                    });
                }
            });

            $('#unit_no').change(function() {
                const unitNo = $(this).val();
                populateSubjects(unitNo);
            });

            function populateSubjects(filterUnit) {
                let options = '<option value="">General / All Subjects</option>';
                subjectsData.forEach(sub => {
                    // Filter logic: If unit selected, show only subjects of that unit.
                    // If no unit selected, show all.
                    if (!filterUnit || sub.unit_no == filterUnit) {
                        options += `<option value="${sub.id}">${sub.subject_name} ${sub.unit_no ? '(Unit ' + sub.unit_no + ')' : ''}</option>`;
                    }
                });
                $('#subject_id').html(options);
            }
        });
    </script>
</body>
</html>
