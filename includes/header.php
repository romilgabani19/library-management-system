<?php
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? APP_NAME;
$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> · <?= e(APP_NAME) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
 <div class="container">
  <a class="navbar-brand fw-bold" href="index.php"><span class="brand-mark"><i class="bi bi-book-half"></i></span> LibraSpace</a>
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
  <div class="collapse navbar-collapse" id="mainNav">
   <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
    <li class="nav-item"><a class="nav-link" href="catalog.php">Book Catalog</a></li>
    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
    <?php if ($user): ?>
      <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
      <li class="nav-item"><span class="nav-link user-pill"><i class="bi bi-person-circle me-1"></i><?= e($user['name']) ?></span></li>
      <li class="nav-item"><form action="logout.php" method="post" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-light rounded-pill px-3" type="submit">Log out</button></form></li>
    <?php else: ?>
      <li class="nav-item"><a class="nav-link" href="login.php">Log in</a></li>
      <li class="nav-item"><a class="btn btn-sm btn-accent rounded-pill px-3" href="register.php">Create account</a></li>
    <?php endif; ?>
   </ul>
  </div>
 </div>
</nav>
<main class="page-shell">
<div class="container py-4">
<?php show_flash(); ?>
