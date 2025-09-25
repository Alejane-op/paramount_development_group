<?php

session_start();
require __DIR__ . '/db.php'; 

if ($pdo instanceof PDO) {
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

if (!isset($_SESSION['_flash'])) $_SESSION['_flash'] = ['ok'=>[], 'err'=>[]];
function flash_ok($m){ $_SESSION['_flash']['ok'][] = $m; }
function flash_err($m){ $_SESSION['_flash']['err'][] = $m; }
function take_flash(){ $f = $_SESSION['_flash']; $_SESSION['_flash'] = ['ok'=>[], 'err'=>[]]; return $f; }

$uid = $_SESSION['user_id'] ?? 0;
if (!$uid) { header('Location: login.php'); exit; }

$stmt = $pdo->prepare("SELECT id, name, email, password, avatar, updated_at FROM users WHERE id=?");
$stmt->execute([$uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC) ?: die('User not found.');

$userName   = $user['name'];
$userEmail  = $user['email'];
$userAvatar = $user['avatar'] ?: 'asset/img/avatar.jpg';
$avatarVer  = $user['updated_at'] ? strtotime($user['updated_at']) : time();
$avatarSrc  = $userAvatar.'?v='.$avatarVer;

if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];

$reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$self    = basename($reqPath);

if (!function_exists('alFile')) {
  function alFile($url, $label, $icon){
    global $self;
    $urlPath  = basename(parse_url($url, PHP_URL_PATH));
    $isActive = ($self === $urlPath);
    $cls = $isActive ? 'aside-link active' : 'aside-link';
    echo '<a class="'.$cls.'" href="'.$url.'"><i class="bi '.$icon.'"></i><span>'.$label.'</span></a>';
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action  = $_POST['action'] ?? '';
  if (!hash_equals($csrf, $_POST['csrf'] ?? '')) {
    flash_err('Invalid request.'); header('Location: adminSettings.php'); exit;
  }

  try {
    if ($action === 'update_profile') {
      $name  = trim($_POST['name'] ?? '');
      $email = trim($_POST['email'] ?? '');
      if ($name === '') flash_err('Name is required.');
      if ($email==='' || !filter_var($email,FILTER_VALIDATE_EMAIL)) flash_err('Valid email required.');

      $chk = $pdo->prepare("SELECT id FROM users WHERE email=? AND id<>?");
      $chk->execute([$email, $uid]);
      if ($chk->fetch()) flash_err('Email already in use.');

      $avatarPath = $userAvatar;
      if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $info = @getimagesize($_FILES['avatar']['tmp_name']);
        if ($info) {
          $ext = match($info[2]) {
            IMAGETYPE_JPEG=>'.jpg', IMAGETYPE_PNG=>'.png', IMAGETYPE_WEBP=>'.webp', IMAGETYPE_GIF=>'.gif', default=>'.png'
          };
          $dir = rtrim($_SERVER['DOCUMENT_ROOT'],'/').'/uploads';
          if (!is_dir($dir)) @mkdir($dir,0775,true);
          $fname='user'.$uid.$ext; $dest=$dir.'/'.$fname;
          if (move_uploaded_file($_FILES['avatar']['tmp_name'],$dest)) {
            $avatarPath='/uploads/'.$fname;
          }
        }
      }
      if (empty($_SESSION['_flash']['err'])) {
        $pdo->prepare("UPDATE users SET name=?,email=?,avatar=?,updated_at=NOW() WHERE id=?")->execute([$name,$email,$avatarPath,$uid]);
        $_SESSION['name']=$name; $_SESSION['email']=$email;
        flash_ok('Profile updated.');
      }
      header('Location: adminSettings.php'); exit;
    }

    if ($action === 'change_password') {
      $cur=$_POST['current_password']??''; $new=$_POST['new_password']??''; $conf=$_POST['confirm_password']??'';
      if ($new===''||$conf==='') flash_err('New password required.');
      if ($new!==$conf) flash_err('Passwords do not match.');
      if (empty($_SESSION['_flash']['err'])) {
        if (!password_verify($cur,$user['password'])) flash_err('Current password incorrect.');
        else {
          $pdo->prepare("UPDATE users SET password=?,updated_at=NOW() WHERE id=?")->execute([password_hash($new,PASSWORD_DEFAULT),$uid]);
          flash_ok('Password changed successfully.');
        }
      }
      header('Location: adminSettings.php'); exit;
    }
  } catch(Throwable $e){ flash_err($e->getMessage()); header('Location: adminSettings.php'); exit; }
}

$flash = take_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin — Settings</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="asset/css/adminSettings.css">
</head>
<body>
  <div class="admin-shell">
    <aside class="admin-aside">
      <div class="aside-user">
        <div>
          <div class="name"><?= h($userName) ?></div>
          <small style="color:#8a8a8a"><?= h($_SESSION['email'] ?? 'paramount_admin@example.com') ?></small>
        </div>
      </div>

      <nav class="aside-nav">
        <?php
          alFile('admin.php','Dashboard','bi-grid');
          alFile('adminAbout.php','About','bi-info-circle');
          alFile('adminTeam.php','Team','bi-person-badge');
          alFile('adminProject.php','Projects','bi-kanban');
          alFile('adminBlog.php','Blogs','bi-journal-text');
          alFile('adminContact.php','Contacts','bi-envelope');
          alFile('adminPartner.php','Partners','bi-people');
          alFile('adminSettings.php','Settings','bi-gear');
          alFile('logout.php','Logout','bi-box-arrow-right');
        ?>
      </nav>

      <div class="aside-footer">
        <div class="brand-pill">
          <strong style="font-size:12px">PARAMOUNT<br>DEVELOPMENT GROUP</strong>
        </div>
      </div>
    </aside>

    <main class="admin-main">
      <?php foreach($flash['ok'] as $m): ?>
        <div class="alert alert-success d-flex align-items-center"><i class="bi bi-check-circle me-2"></i><?=h($m)?></div>
      <?php endforeach; ?>
      <?php foreach($flash['err'] as $m): ?>
        <div class="alert alert-danger d-flex align-items-center"><i class="bi bi-exclamation-triangle me-2"></i><?=h($m)?></div>
      <?php endforeach; ?>

      <div class="main-card">
        <form method="post" enctype="multipart/form-data" class="row g-3">
          <input type="hidden" name="csrf" value="<?=h($csrf)?>">
          <input type="hidden" name="action" value="update_profile">

          <div class="col-12 avatar-box">
            <img src="<?=h($avatarSrc)?>" alt="avatar">
            <div class="flex-grow-1">
              <label class="form-label">Avatar</label>
              <input class="form-control" type="file" name="avatar" accept="image/*">
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Name</label>
            <input class="form-control" name="name" value="<?=h($userName)?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email" value="<?=h($userEmail)?>">
          </div>
          <div class="col-12">
            <button class="btn btn-dark"><i class="bi bi-save me-1"></i>Save</button>
            <button class="btn btn-outline-secondary" type="reset"><i class="bi bi-x-circle me-1"></i>Cancel</button>
          </div>
        </form>
      </div>

      <div class="main-card">
        <h5 class="mb-3"><i class="bi bi-shield-lock"></i> Change Password</h5>
        <form method="post" class="row g-3" autocomplete="off">
          <input type="hidden" name="csrf" value="<?=h($csrf)?>">
          <input type="hidden" name="action" value="change_password">
          <div class="col-md-4">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
          </div>
          <div class="col-12">
            <button class="btn btn-dark"><i class="bi bi-key me-1"></i>Update Password</button>
            <button class="btn btn-outline-secondary" type="reset"><i class="bi bi-x-circle me-1"></i>Cancel</button>
          </div>
        </form>
      </div>
  </main>
  </div>
</body>
</html>
