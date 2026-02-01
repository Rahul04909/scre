<?php
// pages/verification.php

// Adjust path to config based on location
if (file_exists('../database/config.php')) {
    require_once '../database/config.php';
} elseif (file_exists('../../database/config.php')) {
    require_once '../../database/config.php';
} else {
    // Fallback if in root (though user said it's in pages/)
    require_once 'database/config.php'; 
    // This logic handles if file is in root or subfolder, but we assume pages/verification.php
}

// Fetch Documents
$documents = [];
if (isset($pdo)) {
    try {
        // Query to join students and qualifications
        $sql = "SELECT 
                    sq.id, 
                    sq.doc_name, 
                    sq.file_path, 
                    sq.created_at, 
                    s.first_name, 
                    s.last_name, 
                    s.father_name
                FROM student_qualifications sq
                JOIN students s ON sq.student_id = s.id
                ORDER BY sq.created_at DESC";
                
        $stmt = $pdo->query($sql);
        $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $documents = [];
    }
}
?>

<!-- Header -->
<?php 
// Include header adjust for path
if (file_exists('../includes/header.php')) {
    include '../includes/header.php';
} else {
    echo "<!-- Header not found -->"; 
}
?>

<style>
    .verification-page-wrapper {
        background-color: #f9fafb;
        min-height: 80vh;
        padding: 50px 0;
        font-family: 'Poppins', sans-serif;
    }
    
    .verification-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
    }

    .page-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 30px;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .verification-table-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }

    .custom-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }

    .custom-table thead {
        background-color: #111827; /* Dark header */
        color: #ffffff;
    }

    .custom-table th, 
    .custom-table td {
        padding: 15px 20px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }

    /* Vertical borders for solid look */
    .custom-table th,
    .custom-table td {
        border-right: 1px solid #e5e7eb;
    }
    
    .custom-table th:last-child,
    .custom-table td:last-child {
        border-right: none;
    }

    .custom-table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border-right-color: #374151; /* Lighter border for dark header */
    }

    .custom-table tbody tr:hover {
        background-color: #f3f4f6;
    }

    .doc-date {
        font-size: 0.9rem;
        color: #6b7280;
    }

    .btn-download {
        display: inline-flex;
        align-items: center;
        padding: 8px 16px;
        background-color: #2563eb;
        color: #fff;
        font-size: 0.85rem;
        font-weight: 500;
        border-radius: 6px;
        text-decoration: none;
        transition: background-color 0.2s;
        border: none;
        cursor: pointer;
    }

    .btn-download:hover {
        background-color: #1d4ed8;
        color: #fff;
    }
    
    .btn-download i {
        margin-right: 6px;
    }
    
    @media (max-width: 768px) {
        .custom-table thead {
            display: none;
        }
        .custom-table, .custom-table tbody, .custom-table tr, .custom-table td {
            display: block;
            width: 100%;
        }
        .custom-table tr {
            margin-bottom: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #fff;
            overflow: hidden;
        }
        .custom-table td {
            text-align: right;
            padding-left: 50%;
            position: relative;
            border-right: none;
        }
        .custom-table td::before {
            content: attr(data-label);
            position: absolute;
            left: 15px;
            width: 45%;
            padding-right: 10px;
            white-space: nowrap;
            text-align: left;
            font-weight: 600;
            color: #4b5563;
        }
    }
</style>

<div class="verification-page-wrapper">
    <div class="verification-container">
        <h1 class="page-title">Document Verification</h1>
        
        <div class="verification-table-card">
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th width="80">Sr. No.</th>
                            <th>Student Name</th>
                            <th>Father's Name</th>
                            <th>Document Title</th>
                            <th>Uploaded On</th>
                            <th width="150" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($documents)): ?>
                            <?php 
                            $sr = 1; 
                            foreach ($documents as $doc): 
                                // Logic to adjust path relative to this file
                                // Uploads are in assets/uploads/students/
                                // This file is in pages/
                                // So path from here: ../assets/uploads/students/FILENAME
                                
                                // But stored in DB as 'assets/uploads/students/...' (relative to root)
                                // If we are in pages/verification.php, we need to go up one level
                                $relativePath = '../' . $doc['file_path'];
                            ?>
                                <tr>
                                    <td data-label="Sr. No."><?php echo $sr++; ?></td>
                                    <td data-label="Student Name" class="fw-bold text-dark">
                                        <?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?>
                                    </td>
                                    <td data-label="Father's Name">
                                        <?php echo htmlspecialchars($doc['father_name']); ?>
                                    </td>
                                    <td data-label="Document Title">
                                        <i class="far fa-file-alt text-muted me-2"></i>
                                        <?php echo htmlspecialchars($doc['doc_name']); ?>
                                    </td>
                                    <td data-label="Uploaded On">
                                        <span class="doc-date"><?php echo date('d M, Y', strtotime($doc['created_at'])); ?></span>
                                    </td>
                                    <td data-label="Action" class="text-center">
                                        <a href="<?php echo htmlspecialchars($relativePath); ?>" class="btn-download" download target="_blank">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-search fa-2x mb-3 text-secondary"></i><br>
                                    No documents found for verification.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<?php 
if (file_exists('../includes/footer.php')) {
    include '../includes/footer.php';
} else {
   echo "<!-- Footer not found -->";
}
?>
