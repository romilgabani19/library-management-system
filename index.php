<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Welcome';
$featured = db()->query("SELECT b.*, c.name AS category_name FROM books b LEFT JOIN categories c ON c.id=b.category_id WHERE b.is_active=1 ORDER BY b.created_at DESC LIMIT 4")->fetchAll();
$categories = db()->query("SELECT c.id,c.name,COUNT(b.id) AS book_count FROM categories c LEFT JOIN books b ON b.category_id=c.id AND b.is_active=1 GROUP BY c.id,c.name ORDER BY book_count DESC LIMIT 6")->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="hero mb-4"><div class="hero-content">
 <span class="badge rounded-pill text-bg-light mb-3 px-3 py-2"><i class="bi bi-stars me-1"></i> Your campus, your library</span>
 <h1>Welcome to Our<br>Digital Library.</h1>
 <p>Find your next great read, explore new ideas, and keep your learning journey moving — all in one place.</p>
 <form class="search-panel d-flex flex-column flex-sm-row gap-2 mt-4" action="catalog.php" method="get">
  <div class="d-flex align-items-center flex-grow-1 px-2"><i class="bi bi-search text-primary me-2"></i><input class="form-control" name="q" placeholder="Search title, author, or category..." aria-label="Search books"></div>
  <button class="btn btn-accent px-4" type="submit">Explore books <i class="bi bi-arrow-right ms-1"></i></button>
 </form>
 <div class="d-flex flex-wrap gap-3 mt-4 small text-white-50"><span><i class="bi bi-check-circle me-1"></i> Easy discovery</span><span><i class="bi bi-check-circle me-1"></i> Track your loans</span><span><i class="bi bi-check-circle me-1"></i> Learn without limits</span></div>
 </div></section>
<section class="section-heading"><div><h2>Recently added</h2><p class="muted mb-0 mt-1">A few good places to start.</p></div><a href="catalog.php" class="text-decoration-none fw-bold">Browse all <i class="bi bi-arrow-right"></i></a></section>
<div class="row g-3">
<?php if (!$featured): ?><div class="col-12"><div class="surface empty-state"><i class="bi bi-book d-block mb-2"></i><h5>No books added yet</h5><p class="mb-0">A librarian can add books from the dashboard.</p></div></div><?php endif; ?>
<?php foreach ($featured as $b): ?><div class="col-6 col-lg-3"><article class="book-card"><a href="book.php?id=<?= (int)$b['id'] ?>" class="text-decoration-none text-reset"><div class="book-cover"><img src="<?= e(cover_url($b['cover_image'])) ?>" alt="<?= e($b['title']) ?> cover"></div><div class="card-body"><span class="badge-soft"><?= e($b['category_name'] ?? 'General') ?></span><h3 class="book-title mt-2 mb-1"><?= e($b['title']) ?></h3><p class="muted small mb-3"><?= e($b['author']) ?></p><span class="<?= $b['available_quantity'] > 0 ? 'badge-available' : 'badge-unavailable' ?> badge-soft"><?= $b['available_quantity'] > 0 ? 'Available' : 'Currently out' ?></span></div></a></article></div><?php endforeach; ?>
</div>
<section class="section-heading"><div><h2>Explore by category</h2><p class="muted mb-0 mt-1">Follow your curiosity.</p></div></section>
<div class="row g-3 mb-3"><?php foreach ($categories as $i=>$c): ?><div class="col-6 col-md-4"><a class="category-card" href="catalog.php?category=<?= (int)$c['id'] ?>"><span class="category-icon"><i class="bi <?= ['bi-cpu','bi-flask','bi-globe2','bi-brush','bi-calculator','bi-journal-text'][$i%6] ?>"></i></span><span><strong class="d-block"><?= e($c['name']) ?></strong><small class="muted"><?= (int)$c['book_count'] ?> books</small></span></a></div><?php endforeach; ?></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
