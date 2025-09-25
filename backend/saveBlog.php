<?php
session_start();
require __DIR__ . '/db.php';
if ($pdo instanceof PDO) $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$uploadDirFs = rtrim($_SERVER['DOCUMENT_ROOT'], '/').'/uploads';
$uploadDirUrl = '/uploads';

if (!is_dir($uploadDirFs)) {
  @mkdir($uploadDirFs, 0775, true);
}

$title   = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$imagePath = null;

if ($title === '' || $content === '') {
  $_SESSION['flash'] = 'Title and Content are required.';
  header('Location: adminBlog.php');
  exit;
}

if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
  $tmp   = $_FILES['image']['tmp_name'];
  $size  = (int)$_FILES['image']['size'];

  if ($size > 5 * 1024 * 1024) {
    $_SESSION['flash'] = 'Image too large (max 5MB).';
    header('Location: adminBlog.php');
    exit;
  }

  $finfo = @getimagesize($tmp);
  if ($finfo === false) {
    $_SESSION['flash'] = 'Invalid image.';
    header('Location: adminBlog.php');
    exit;
  }

  $ext = image_type_to_extension($finfo[2], false); 
  if (!in_array(strtolower($ext), ['jpg','jpeg','png','webp'], true)) {
    $_SESSION['flash'] = 'Only JPG, PNG, or WebP allowed.';
    header('Location: adminBlog.php');
    exit;
  }

  $basename = 'blog_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
  $destFs   = $uploadDirFs.'/'.$basename;
  if (!move_uploaded_file($tmp, $destFs)) {
    $_SESSION['flash'] = 'Failed to save image.';
    header('Location: adminBlog.php');
    exit;
  }
  $imagePath = $uploadDirUrl.'/'.$basename;
}

$stmt = $pdo->prepare("INSERT INTO blogs (title, content, image_path) VALUES (?,?,?)");
$stmt->execute([$title, $content, $imagePath]);

$_SESSION['flash'] = 'Blog post created.';
header('Location: adminBlog.php');
