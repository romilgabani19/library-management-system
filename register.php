<?php
require_once __DIR__.'/includes/functions.php';
if(current_user()) redirect('/dashboard.php');
$pageTitle='Student registration'; $errors=[]; $name=$email=$studentNo='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf(); $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $studentNo=trim($_POST['student_no']??''); $password=$_POST['password']??''; $confirm=$_POST['confirm_password']??'';
 if(mb_strlen($name)<2 || mb_strlen($name)>100) $errors[]='Name must be between 2 and 100 characters.';
 if(!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Enter a valid email address.';
 if($password!==$confirm) $errors[]='Passwords do not match.';
 if(strlen($password)<8) $errors[]='Password must contain at least 8 characters.';
 if($studentNo!=='' && mb_strlen($studentNo)>40) $errors[]='Student ID is too long.';
 if(!$errors){$q=db()->prepare('SELECT id FROM users WHERE email=?');$q->execute([$email]);if($q->fetch())$errors[]='An account with that email already exists.';}
 if(!$errors){$s=db()->prepare("INSERT INTO users(name,email,password_hash,role,student_no) VALUES(?,?,?,'student',?)");$s->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT),$studentNo?:null]);flash('success','Account created. You can now log in.');redirect('/login.php');}
}
require __DIR__.'/includes/header.php';
?>
<div class="auth-wrap"><div class="auth-card"><div class="auth-icon mb-3"><i class="bi bi-person-plus"></i></div><h1 class="h3 fw-bold">Join the library</h1><p class="muted mb-4">Create your student account in a minute.</p>
<?php foreach($errors as $err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endforeach; ?>
<form method="post" novalidate><?= csrf_field() ?><div class="mb-3"><label class="form-label">Full name</label><input class="form-control" name="name" required maxlength="100" value="<?=e($name)?>" autocomplete="name"></div><div class="mb-3"><label class="form-label">College email</label><input class="form-control" type="email" name="email" required value="<?=e($email)?>" autocomplete="email"></div><div class="mb-3"><label class="form-label">Student ID <span class="muted fw-normal">(optional)</span></label><input class="form-control" name="student_no" maxlength="40" value="<?=e($studentNo)?>"></div><div class="mb-3"><label class="form-label">Password</label><input class="form-control" type="password" name="password" minlength="8" required autocomplete="new-password"><div class="form-text">Use at least 8 characters.</div></div><div class="mb-3"><label class="form-label">Confirm password</label><input class="form-control" type="password" name="confirm_password" minlength="8" required autocomplete="new-password"></div><button class="btn btn-primary w-100" type="submit">Create student account</button></form><p class="small mt-4 mb-0">Already registered? <a href="login.php" class="fw-bold text-decoration-none">Log in</a></p></div></div>
<?php require __DIR__.'/includes/footer.php'; ?>
