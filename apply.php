<?php
// apply.php
require_once 'database/config.php';
require_once 'includes/header.php';

// Fetch Courses for Dropdown
try {
    $stmt = $pdo->query("SELECT id, course_name FROM courses ORDER BY course_name ASC");
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $courses = [];
}

// Fetch Countries
try {
    $stmt = $pdo->query("SELECT id, name FROM countries ORDER BY name ASC");
    $countries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $countries = [];
}

$selected_course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : '';

// Handle Form Submission
$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_application'])) {
    $course_id = intval($_POST['course_id']);
    $center_id = intval($_POST['center_id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $qualification = trim($_POST['qualification']);
    $country = intval($_POST['country']);
    $state = intval($_POST['state']);
    $city = intval($_POST['city']);

    try {
        $sql = "INSERT INTO applications (course_id, center_id, name, email, mobile, qualification, country_id, state_id, city_id) 
                VALUES (:cid, :cnid, :nm, :em, :mob, :qual, :ctr, :st, :ct)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':cid' => $course_id,
            ':cnid' => $center_id,
            ':nm' => $name,
            ':em' => $email,
            ':mob' => $mobile,
            ':qual' => $qualification,
            ':ctr' => $country,
            ':st' => $state,
            ':ct' => $city
        ]);
        $msg = "Application submitted successfully! We will contact you soon.";
        $msgType = "success";
    } catch (PDOException $e) {
        $msg = "Error submitting application. Please try again.";
        $msgType = "danger";
    }
}
?>

<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<style>
    body {
        background-color: #f8f9fa;
    }
    .apply-wrapper {
        padding: 60px 0;
    }
    .apply-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        overflow: hidden;
        border: none;
    }
    .apply-header {
        background: linear-gradient(135deg, #1a2c3f 0%, #2c3e50 100%);
        padding: 40px;
        text-align: center;
        color: #fff;
    }
    .apply-title {
        font-weight: 700;
        margin-bottom: 10px;
    }
    .apply-subtitle {
        opacity: 0.8;
        font-size: 0.95rem;
    }
    .apply-body {
        padding: 40px;
    }
    .form-label {
        font-weight: 600;
        font-size: 0.9rem;
        color: #495057;
    }
    .form-control, .form-select {
        padding: 10px 15px;
        border-color: #dee2e6;
    }
    .form-control:focus, .form-select:focus {
        border-color: #1a2c3f;
        box-shadow: 0 0 0 0.25rem rgba(26, 44, 63, 0.15);
    }
    
    .section-divider {
        height: 1px;
        background: #eee;
        margin: 30px 0;
        position: relative;
    }
    .section-divider span {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #fff;
        padding: 0 15px;
        color: #aaa;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .btn-submit {
        background: #1a2c3f;
        color: #fff;
        padding: 12px 30px;
        font-weight: 700;
        border-radius: 8px;
        font-size: 1.1rem;
        transition: all 0.3s;
        border: none;
    }
    .btn-submit:hover {
        background: #0f1c29;
        transform: translateY(-2px);
    }
    
    /* Select2 Tweaks */
    .select2-container .select2-selection--single { height: 42px !important; }
    .select2-container--bootstrap-5 .select2-selection { border-color: #dee2e6; }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered { line-height: 40px; padding-left: 15px; }
</style>

<div class="apply-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <?php if ($msg): ?>
                    <div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show mb-4" role="alert">
                        <?php echo $msg; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="apply-card">
                    <div class="apply-header">
                        <h2 class="apply-title">Course Application</h2>
                        <p class="apply-subtitle">Fill in the details below to start your learning journey.</p>
                    </div>
                    <div class="apply-body">
                        <form method="POST">
                            
                            <!-- Course & Center -->
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Select Course <span class="text-danger">*</span></label>
                                    <select name="course_id" id="course_id" class="form-select select2" required>
                                        <option value="">-- Choose Course --</option>
                                        <?php foreach ($courses as $c): ?>
                                            <option value="<?php echo $c['id']; ?>" <?php echo ($c['id'] == $selected_course_id) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($c['course_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Select Center <span class="text-danger">*</span></label>
                                    <select name="center_id" id="center_id" class="form-select select2" required>
                                        <option value="">-- Select Course First --</option>
                                    </select>
                                    <div class="form-text text-muted">Showing centers that offer this course.</div>
                                </div>
                            </div>
                            
                            <div class="section-divider"><span>Personal Details</span></div>
                            
                            <!-- Personal Info -->
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" placeholder="example@mail.com" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                    <input type="tel" name="mobile" class="form-control" placeholder="10-digit mobile number" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Qualification <span class="text-danger">*</span></label>
                                    <input type="text" name="qualification" class="form-control" placeholder="e.g. 12th Pass, Graduate" required>
                                </div>
                            </div>

                            <div class="section-divider"><span>Your Address</span></div>
                            
                            <!-- Location -->
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label">Country <span class="text-danger">*</span></label>
                                    <select name="country" id="country" class="form-select select2" required>
                                        <option value="">-- Select Country --</option>
                                        <?php foreach ($countries as $ctr): ?>
                                            <option value="<?php echo $ctr['id']; ?>"><?php echo htmlspecialchars($ctr['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">State <span class="text-danger">*</span></label>
                                    <select name="state" id="state" class="form-select select2" required>
                                        <option value="">-- Chooe Country First --</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">City <span class="text-danger">*</span></label>
                                    <select name="city" id="city" class="form-select select2" required>
                                        <option value="">-- Choose State First --</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-5 text-center">
                                <button type="submit" name="submit_application" class="btn btn-submit w-100">
                                    Submit Application <i class="fas fa-paper-plane ms-2"></i>
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    // --- Course Allotment Logic ---
    function fetchCenters(courseId) {
        if(!courseId) {
            $('#center_id').html('<option value="">-- Select Course First --</option>');
            return;
        }
        
        $('#center_id').html('<option value="">Loading...</option>');
        
        $.ajax({
            url: 'get-course-centers.php',
            type: 'GET',
            data: { course_id: courseId },
            success: function(response) {
                try {
                    var centers = JSON.parse(response);
                    var html = '<option value="">-- Select Center --</option>';
                    if(centers.length > 0) {
                        centers.forEach(function(center) {
                            html += '<option value="'+center.id+'">' + center.center_name + ' (' + center.city_name + ')</option>';
                        });
                    } else {
                        html = '<option value="">No centers found for this course</option>';
                    }
                    $('#center_id').html(html);
                } catch(e) {
                    $('#center_id').html('<option value="">Error loading centers</option>');
                }
            }
        });
    }

    // Trigger on load if course is selected
    var initialCourse = $('#course_id').val();
    if(initialCourse) {
        fetchCenters(initialCourse);
    }

    // Trigger on change
    $('#course_id').change(function() {
        var courseId = $(this).val();
        fetchCenters(courseId);
    });


    // --- Location Logic (Reuse from Admin) ---
    // Note: We use relative path to admin script
    var locationApiUrl = 'admin/locations/get-location-data.php';

    $('#country').change(function() {
        var countryId = $(this).val();
        $('#state').html('<option value="">Loading...</option>');
        $('#city').html('<option value="">-- Choose State First --</option>');
        
        if(countryId) {
            $.get(locationApiUrl + '?type=get_states&country_id=' + countryId, function(data) {
                var states = JSON.parse(data);
                var html = '<option value="">-- Select State --</option>';
                states.forEach(function(item) {
                    html += '<option value="'+item.id+'">'+item.name+'</option>';
                });
                $('#state').html(html);
            });
        } else {
            $('#state').html('<option value="">-- Choose Country First --</option>');
        }
    });

    $('#state').change(function() {
        var stateId = $(this).val();
        $('#city').html('<option value="">Loading...</option>');
        
        if(stateId) {
            $.get(locationApiUrl + '?type=get_cities&state_id=' + stateId, function(data) {
                var cities = JSON.parse(data);
                var html = '<option value="">-- Select City --</option>';
                cities.forEach(function(item) {
                    html += '<option value="'+item.id+'">'+item.name+'</option>';
                });
                $('#city').html(html);
            });
        } else {
            $('#city').html('<option value="">-- Choose State First --</option>');
        }
    });
});
</script>
