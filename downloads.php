<?php
// downloads.php

require_once 'database/config.php';

// Fetch Downloads
$documents = [];
if (isset($pdo)) {
    try {
        $sql = "SELECT * FROM downloads ORDER BY created_at DESC";
        $stmt = $pdo->query($sql);
        $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $documents = [];
    }
}
?>

<!-- Header -->
<?php include 'includes/header.php'; ?>

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

    .downloads-page-wrapper {
        background-color: var(--pure-white);
        min-height: 80vh;
        padding: 60px 0;
        font-family: 'Poppins', sans-serif;
    }
    
    .downloads-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .page-title {
        font-size: 2.2rem;
        font-weight: 700;
        color: var(--deep-brown);
        margin-bottom: 40px;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        position: relative;
        padding-bottom: 15px;
    }

    .page-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background: var(--muted-gold);
        border-radius: 2px;
    }

    .downloads-table-card {
        background: var(--pure-white);
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(109, 76, 65, 0.08); /* Warm shadow */
        overflow: hidden;
        border: 1px solid var(--border-beige);
    }

    .custom-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 0;
    }

    .custom-table thead {
        background: linear-gradient(135deg, var(--deep-brown), var(--charcoal-brown));
        color: var(--soft-ivory);
    }

    .custom-table th {
        padding: 18px 24px;
        text-align: left;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 0.8px;
        border-bottom: 3px solid var(--muted-gold);
        border-right: 1px solid rgba(255,255,255,0.1);
    }

    .custom-table th:last-child {
        border-right: none;
    }

    .custom-table td {
        padding: 18px 24px;
        text-align: left;
        border-bottom: 1px solid var(--border-beige);
        border-right: 1px solid #f0e7d9;
        color: #5d4037;
        font-size: 0.95rem;
        transition: background-color 0.2s ease;
    }

    .custom-table td:last-child {
        border-right: none;
    }

    .custom-table tbody tr {
        background-color: var(--pure-white);
        transition: all 0.2s ease;
    }

    /* Zebra Rows */
    .custom-table tbody tr:nth-child(even) {
        background-color: #fffcf8; /* Very subtle warm tint */
    }

    .custom-table tbody tr:hover {
        background-color: var(--soft-ivory);
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.03);
        position: relative;
        z-index: 1;
    }

    .doc-date {
        font-size: 0.9rem;
        color: #8d6e63;
        font-style: italic;
    }

    .doc-title {
        color: var(--warm-brown);
        font-weight: 600;
    }
    
    .doc-title i {
        color: var(--muted-gold);
        margin-right: 8px;
    }

    .btn-download {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 18px;
        background: linear-gradient(to right, var(--warm-brown), #5d4037);
        color: var(--pure-white);
        font-size: 0.85rem;
        font-weight: 500;
        border-radius: 50px; /* Fully rounded */
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        box-shadow: 0 2px 5px rgba(109, 76, 65, 0.2);
    }

    .btn-download:hover {
        background: linear-gradient(to right, #8d6e63, #6d4c41);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(109, 76, 65, 0.3);
        color: var(--soft-ivory);
    }
    
    .btn-download i {
        margin-right: 8px;
        font-size: 0.9rem;
    }
    
    @media (max-width: 900px) {
        .custom-table thead {
            display: none;
        }
        .custom-table, .custom-table tbody, .custom-table tr, .custom-table td {
            display: block;
            width: 100%;
        }
        .custom-table tr {
            margin-bottom: 20px;
            border: 1px solid var(--border-beige);
            border-radius: 12px;
            background: var(--pure-white);
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .custom-table td {
            text-align: right;
            padding-left: 50%;
            position: relative;
            border-right: none;
            border-bottom: 1px solid #f0e7d9;
        }
        .custom-table td:last-child {
            border-bottom: none;
            background-color: #faf7f2;
            text-align: center;
            padding: 15px;
        }
        .custom-table td::before {
            content: attr(data-label);
            position: absolute;
            left: 20px;
            width: 45%;
            padding-right: 10px;
            white-space: nowrap;
            text-align: left;
            font-weight: 700;
            color: var(--warm-brown);
            text-transform: uppercase;
            font-size: 0.8rem;
        }
        .btn-download {
            width: 100%;
        }
    }
</style>

<div class="downloads-page-wrapper">
    <div class="downloads-container">
        <h1 class="page-title">Downloads</h1>
        
        <div class="downloads-table-card">
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th width="80">Sr. No.</th>
                            <th>Title</th>
                            <th>Uploaded On</th>
                            <th width="150" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($documents)): ?>
                            <?php 
                            $sr = 1; 
                            foreach ($documents as $doc): 
                            ?>
                                <tr>
                                    <td data-label="Sr. No."><?php echo $sr++; ?></td>
                                    <td data-label="Title" class="doc-title">
                                        <i class="far fa-file-alt"></i>
                                        <?php echo htmlspecialchars($doc['title']); ?>
                                    </td>
                                    <td data-label="Uploaded On">
                                        <span class="doc-date"><?php echo date('d M, Y', strtotime($doc['created_at'])); ?></span>
                                    </td>
                                    <td data-label="Action" class="text-center">
                                        <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" class="btn-download" download target="_blank">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-cloud-download-alt fa-2x mb-3 text-secondary"></i><br>
                                    No downloads available at the moment.
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
<?php include 'includes/footer.php'; ?>
