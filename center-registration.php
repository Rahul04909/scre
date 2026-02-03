<?php
// center-registration.php
require_once 'database/config.php';
require_once 'includes/header.php';

// Fetch Countries
try {
    $stmt = $pdo->query("SELECT id, name FROM countries ORDER BY name ASC");
    $countries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $countries = [];
}

// Handle Form Submission
$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_center_request'])) {
    $center_name = trim($_POST['center_name']);
    $owner_name = trim($_POST['owner_name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    
    $country = intval($_POST['country']);
    $state = intval($_POST['state']);
    $city = intval($_POST['city']);
    
    $pincode = trim($_POST['pincode']);
    $address = trim($_POST['address']);
    $message = trim($_POST['message']); // Optional

    try {
        $sql = "INSERT INTO center_requests (center_name, owner_name, email, mobile, country_id, state_id, city_id, pincode, address, message) 
                VALUES (:cn, :on, :em, :mob, :ctr, :st, :ct, :pin, :addr, :msg)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':cn' => $center_name,
            ':on' => $owner_name,
            ':em' => $email,
            ':mob' => $mobile,
            ':ctr' => $country,
            ':st' => $state,
            ':ct' => $city,
            ':pin' => $pincode,
            ':addr' => $address,
            ':msg' => $message
        ]);
        $msg = "Registration request submitted successfully! Our team will verify your details and contact you.";
        $msgType = "success";
    } catch (PDOException $e) {
        $msg = "Error submitting request. Please try again.";
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
    /* Premium Color Scheme (Matching Verification & Apply Page) */
    :root {
        --warm-brown: #6d4c41;
        --deep-brown: #4e342e;
        --charcoal-brown: #3e2723;
        --soft-ivory: #fff9f3;
        --muted-gold: #c5a059;
        --border-beige: #e0d0b8;
        --pure-white: #ffffff;
    }

    body {
        background-color: var(--soft-ivory);
        font-family: 'Poppins', sans-serif;
    }
    
    .apply-wrapper {
        padding: 60px 0;
    }
    
    .apply-card {
        background: var(--pure-white);
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(109, 76, 65, 0.08);
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
    
    /* Typography & Inputs */
    .form-label {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--deep-brown);
        margin-bottom: 8px;
    }
    
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
        box-shadow: 0 0 0 4px rgba(197, 160, 89, 0.1);
        background-color: #fff;
    }
    
    /* Textarea height exception */
    textarea.form-control {
        height: auto !important;
    }

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
    
    .btn-submit {
        background: linear-gradient(to right, var(--warm-brown), #5d4037);
        color: var(--pure-white);
        padding: 14px 30px;
        font-weight: 600;
        border-radius: 8px;
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
    
    /* Select2 Tweaks */
    .select2-container .select2-selection--single {
        height: 50px !important;
        border-radius: 8px !important;
        border: 1px solid var(--border-beige) !important;
        display: flex !important;
        align-items: center !important;
    }
    
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
        color: var(--muted-gold);
    }
    
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
                        <h2 class="apply-title">Center Registration</h2>
                        <p class="apply-subtitle">Partner with us. Fill in the details to register your center.</p>
                    </div>
                    <div class="apply-body">
                        <form method="POST">
                            
                            <!-- Basic Info -->
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Center Name <span class="text-danger">*</span></label>
                                    <input type="text" name="center_name" class="form-control" placeholder="e.g. Pace Computer Institute" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Owner Name <span class="text-danger">*</span></label>
                                    <input type="text" name="owner_name" class="form-control" placeholder="Full Name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" placeholder="official@center.com" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                    <input type="tel" name="mobile" class="form-control" placeholder="10-digit mobile" required>
                                </div>
                            </div>
                            
                            <div class="section-divider"><span>Center Location</span></div>
                            
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
                                        <option value="">-- Select Country First --</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">City <span class="text-danger">*</span></label>
                                    <select name="city" id="city" class="form-select select2" required>
                                        <option value="">-- Select State First --</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Pincode <span class="text-danger">*</span></label>
                                    <input type="text" name="pincode" class="form-control" required placeholder="6-digit PIN">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Full Address <span class="text-danger">*</span></label>
                                    <input type="text" name="address" class="form-control" required placeholder="Building, Street, Area">
                                </div>
                            </div>

                            <div class="section-divider"><span>Additional Info</span></div>
                            
                            <div class="mb-4">
                                <label class="form-label">Message / Inquiry (Optional)</label>
                                <textarea name="message" class="form-control" rows="3" placeholder="Tell us about your infrastructure or query..."></textarea>
                            </div>

                            <div class="mt-4 text-center">
                                <button type="submit" name="submit_center_request" class="btn btn-submit w-100">
                                    Submit Registration Request <i class="fas fa-paper-plane ms-2"></i>
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

    // --- Location Logic (Reuse from Admin) ---
    var locationApiUrl = 'admin/locations/get-location-data.php';

    $('#country').change(function() {
        var countryId = $(this).val();
        $('#state').html('<option value="">Loading...</option>');
        $('#city').html('<option value="">-- Select State First --</option>');
        
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
            $('#state').html('<option value="">-- Select Country First --</option>');
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
            $('#city').html('<option value="">-- Select State First --</option>');
        }
    });
});
</script>
