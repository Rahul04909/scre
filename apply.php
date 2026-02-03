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
    :root {
        --warm-brown: #6d4c41;
        --deep-brown: #4e342e;
        --charcoal-brown: #3e2723;
        --soft-ivory: #fff9f3;
        --muted-gold: #c5a059;
        --border-beige: #e0d0b8;
        --row-hover: #fcfbf9;
        --pure-white: #ffffff;
    }

    body {
        background-color: var(--soft-ivory); /* Soft ivory background matching verification */
        font-family: 'Poppins', sans-serif;
    }
    
    .apply-wrapper {
        padding: 60px 0;
    }
    
    .apply-card {
        background: var(--pure-white);
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(109, 76, 65, 0.08); /* Warm shadow */
        overflow: hidden;
        border: 1px solid var(--border-beige);
    }
    
    .apply-header {
        background: linear-gradient(135deg, var(--deep-brown), var(--charcoal-brown));
        padding: 40px;
        text-align: center;
        color: var(--soft-ivory);
        border-bottom: 3px solid var(--muted-gold);
        position: relative;
    }
    
    .apply-title {
        font-weight: 700;
        margin-bottom: 10px;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .apply-subtitle {
        opacity: 0.9;
        font-size: 0.95rem;
        color: #e0d0b8;
    }
    
    .apply-body {
        padding: 40px;
    }
    
    /* Typography */
    .form-label {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--deep-brown);
        margin-bottom: 8px;
    }
    
    /* Uniform Input Styles */
    .form-control, .form-select {
        height: 50px;
        padding: 10px 15px;
        border-radius: 8px;
        border: 1px solid var(--border-beige);
        font-size: 0.95rem;
        background-color: #fff;
        transition: all 0.2s;
        color: var(--deep-brown);
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--muted-gold);
        box-shadow: 0 0 0 4px rgba(197, 160, 89, 0.1); /* Gold glow */
        background-color: #fff;
    }
    
    /* Section Divider */
    .section-divider {
        height: 1px;
        background: var(--border-beige);
        margin: 30px 0;
        position: relative;
    }
    .section-divider span {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: var(--pure-white);
        padding: 0 15px;
        color: var(--warm-brown);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }
    
    /* Submit Button */
    .btn-submit {
        background: linear-gradient(to right, var(--warm-brown), #5d4037);
        color: var(--pure-white);
        padding: 14px 30px;
        font-weight: 600;
        border-radius: 8px; /* Slightly more rounded */
        font-size: 1.1rem;
        transition: all 0.3s;
        border: none;
        box-shadow: 0 4px 10px rgba(109, 76, 65, 0.2);
    }
    .btn-submit:hover {
        background: linear-gradient(to right, #8d6e63, #6d4c41);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(109, 76, 65, 0.3);
        color: #fff;
    }
    
    /* Select2 Tweaks to Match Inputs */
    .select2-container .select2-selection--single {
        height: 50px !important;
        border-radius: 8px !important;
        border: 1px solid var(--border-beige) !important; 
        display: flex !important;
        align-items: center !important;
    }
    
    /* Focus state for Select2 */
    .select2-container--bootstrap-5 .select2-selection--single:focus-within,
    .select2-container--bootstrap-5.select2-container--focus .select2-selection--single {
        border-color: var(--muted-gold) !important;
        box-shadow: 0 0 0 4px rgba(197, 160, 89, 0.1) !important;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: normal !important;
        padding-left: 15px !important;
        color: var(--deep-brown) !important;
        font-size: 0.95rem;
    }
    
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        position: absolute !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        right: 15px !important;
        width: auto !important;
        height: auto !important;
        color: var(--muted-gold); /* Gold arrow */
    }
    
    /* Dropdown Options */
    .select2-results__option--highlighted[aria-selected] {
        background-color: var(--warm-brown) !important;
        color: white !important;
    }
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
