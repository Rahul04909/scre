<?php
// blog-details.php
require_once 'database/config.php';

// Get Slug
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
if (!$slug) {
    header("Location: blog.php");
    exit;
}

// Fetch Blog Post
try {
    $stmt = $pdo->prepare("SELECT b.*, c.name as category_name, c.slug as category_slug 
                           FROM blogs b 
                           LEFT JOIN blog_categories c ON b.category_id = c.id 
                           WHERE b.slug = ? AND b.status = 'published'");
    $stmt->execute([$slug]);
    $blog = $stmt->fetch();

    if (!$blog) {
        header("Location: blog.php"); // Or show 404
        exit;
    }

    // Increment Views
    $pdo->prepare("UPDATE blogs SET views = views + 1 WHERE id = ?")->execute([$blog['id']]);

} catch (PDOException $e) {
    die("Database Error");
}

// Fetch Recent Posts (Sidebar)
try {
    $stmtRecent = $pdo->prepare("SELECT title, slug, image_path, created_at FROM blogs WHERE status='published' AND id != ? ORDER BY created_at DESC LIMIT 5");
    $stmtRecent->execute([$blog['id']]);
    $recentPosts = $stmtRecent->fetchAll();
} catch (PDOException $e) { $recentPosts = []; }

// Fetch Categories (Sidebar)
try {
    $stmtCat = $pdo->query("SELECT * FROM blog_categories WHERE status='active' ORDER BY name ASC");
    $categories = $stmtCat->fetchAll();
} catch (PDOException $e) { $categories = []; }
?>

<!-- Header -->
<?php include 'includes/header.php'; ?>

<style>
    :root {
        --warm-brown: #6d4c41;
        --deep-brown: #4e342e;
        --soft-ivory: #fff9f3;
        --muted-gold: #c5a059;
        --border-beige: #e0d0b8;
    }

    .blog-details-wrapper {
        background-color: #fff;
        min-height: 90vh;
        padding: 60px 0;
        font-family: 'Poppins', sans-serif;
    }

    /* Main Content */
    .blog-header-image {
        width: 100%;
        height: 400px;
        object-fit: cover;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }

    .article-container {
        max-width: 100%;
    }

    .article-meta {
        margin-bottom: 20px;
        color: #777;
        font-size: 0.9rem;
        border-bottom: 1px solid #eee;
        padding-bottom: 15px;
    }

    .article-meta span { margin-right: 15px; }
    .article-meta i { color: var(--muted-gold); margin-right: 5px; }

    .article-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--deep-brown);
        margin-bottom: 20px;
        line-height: 1.3;
    }

    .article-content {
        line-height: 1.8;
        color: #444;
        font-size: 1.05rem;
    }
    
    .article-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 20px 0;
    }
    
    .article-content h2, .article-content h3 {
        color: var(--deep-brown);
        margin-top: 30px;
        margin-bottom: 15px;
    }

    /* Sidebar */
    .blog-sidebar {
        background: var(--soft-ivory);
        border-radius: 12px;
        padding: 30px;
        border: 1px solid var(--border-beige);
        position: sticky;
        top: 20px;
    }

    .sidebar-widget { margin-bottom: 40px; }
    .sidebar-widget:last-child { margin-bottom: 0; }

    .widget-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--deep-brown);
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--muted-gold);
    }

    /* Recent Posts */
    .recent-post-item {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }
    
    .recent-post-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }

    .recent-post-thumb {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 6px;
        margin-right: 15px;
    }

    .recent-post-info h6 {
        font-size: 1rem;
        line-height: 1.4;
        margin-bottom: 5px;
    }

    .recent-post-info a {
        color: var(--deep-brown);
        text-decoration: none;
        transition: color 0.3s;
    }

    .recent-post-info a:hover { color: var(--muted-gold); }

    .recent-post-date {
        font-size: 0.8rem;
        color: #888;
    }

    /* Categories List */
    .category-list { list-style: none; padding: 0; margin: 0; }
    .category-item { margin-bottom: 10px; }
    
    .category-link {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        color: #5d4037;
        text-decoration: none;
        border-bottom: 1px dashed #ddd;
        transition: all 0.3s;
    }

    .category-link:hover {
        color: var(--muted-gold);
        padding-left: 5px;
    }

    @media (max-width: 991px) {
        .blog-sidebar { margin-top: 50px; position: static; }
        .article-title { font-size: 2rem; }
    }
</style>

<div class="blog-details-wrapper">
    <div class="container">
        <div class="row">
            <!-- Main Article -->
            <div class="col-lg-8">
                <article class="article-container">
                    <?php if($blog['image_path']): ?>
                        <img src="<?php echo htmlspecialchars($blog['image_path']); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>" class="blog-header-image">
                    <?php endif; ?>
                    
                    <h1 class="article-title"><?php echo htmlspecialchars($blog['title']); ?></h1>
                    
                    <div class="article-meta">
                        <span><i class="far fa-user"></i> <?php echo htmlspecialchars($blog['author']); ?></span>
                        <span><i class="far fa-folder"></i> <?php echo htmlspecialchars($blog['category_name']); ?></span>
                        <span><i class="far fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($blog['created_at'])); ?></span>
                        <span><i class="far fa-eye"></i> <?php echo $blog['views']; ?> Views</span>
                    </div>

                    <div class="article-content">
                        <?php echo $blog['content']; // Summernote content is HTML ?>
                    </div>
                    
                    <div class="mt-5">
                        <a href="blog.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i> Back to Blog</a>
                    </div>
                </article>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="blog-sidebar shadow-sm">
                    <!-- Categories -->
                    <div class="sidebar-widget">
                        <h5 class="widget-title">Categories</h5>
                        <ul class="category-list">
                            <?php foreach ($categories as $cat): ?>
                            <li class="category-item">
                                <a href="blog.php?category=<?php echo htmlspecialchars($cat['slug']); ?>" class="category-link">
                                    <span><?php echo htmlspecialchars($cat['name']); ?></span>
                                    <i class="fas fa-chevron-right small"></i>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Recent Posts -->
                    <div class="sidebar-widget">
                        <h5 class="widget-title">Recent Posts</h5>
                        <?php if (count($recentPosts) > 0): ?>
                            <?php foreach ($recentPosts as $recent): ?>
                            <div class="recent-post-item">
                                <?php if($recent['image_path']): ?>
                                    <img src="<?php echo htmlspecialchars($recent['image_path']); ?>" class="recent-post-thumb" alt="Thumb">
                                <?php endif; ?>
                                <div class="recent-post-info">
                                    <h6><a href="blog-details.php?slug=<?php echo $recent['slug']; ?>"><?php echo htmlspecialchars($recent['title']); ?></a></h6>
                                    <span class="recent-post-date"><?php echo date('M d, Y', strtotime($recent['created_at'])); ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No other posts found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>
