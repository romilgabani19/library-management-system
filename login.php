<?php
require_once __DIR__ . '/includes/functions.php';
if (current_user()) redirect('/dashboard.php');
$pageTitle='Log in'; $error=''; $email='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 verify_csrf(); $email=trim($_POST['email']??''); $password=$_POST['password']??'';
 if (!filter_var($email,FILTER_VALIDATE_EMAIL) || $password==='') $error='Enter a valid email and password.';
 else {
  $stmt=db()->prepare("SELECT id,name,email,password_hash,role,status FROM users WHERE email=? LIMIT 1"); $stmt->execute([$email]); $u=$stmt->fetch();
  if (!$u || !password_verify($password,$u['password_hash']) || $u['status']!=='active') $error='Email or password is incorrect, or the account is inactive.';
  else { session_regenerate_id(true); $_SESSION['user']=['id'=>(int)$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role']]; $_SESSION['last_activity']=time(); flash('success','Welcome back, '.$u['name'].'!'); redirect('/dashboard.php'); }
 }
}
require __DIR__.'/includes/header.php';
?>
<div class="auth-wrap"><div class="auth-card"><div class="auth-icon mb-3"><i class="bi bi-shield-lock"></i></div><h1 class="h3 fw-bold">Welcome back</h1><p class="muted mb-4">Log in to your LibraSpace account.</p>
<?php if($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="post" novalidate><?= csrf_field() ?><div class="mb-3"><label class="form-label" for="email">Email address</label><input class="form-control" id="email" name="email" type="email" required autocomplete="username" value="<?= e($email) ?>" placeholder="you@college.edu"></div>
<div class="mb-3"><label class="form-label" for="password">Password</label><input class="form-control" id="password" name="password" type="password" required autocomplete="current-password" placeholder="Your password"></div>
<button class="btn btn-primary w-100 mt-2" type="submit">Log in <i class="bi bi-arrow-right ms-1"></i></button></form>
<div class="mt-4 pt-3 border-top"><p class="small muted mb-2">Choose the right account type automatically after sign-in:</p><div class="d-flex gap-2 flex-wrap"><span class="badge-soft">Admin</span><span class="badge-soft">Librarian</span><span class="badge-soft">Student</span></div><p class="small mt-3 mb-0">New student? <a href="register.php" class="fw-bold text-decoration-none">Create an account</a></p></div>
</div></div>
<?php require __DIR__.'/includes/footer.php'; ?>
