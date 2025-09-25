<?php

session_start();
require __DIR__ . '/db.php';
if ($pdo instanceof PDO) $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$id      = (int)($_POST['id'] ?? 0);
$title   = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');

if ($id <= 0) {
  $_SESSION['flash'] = 'Invalid post.';
  header('Location: adminBlog.php');
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM blogs WHERE id=?");
$stmt->execute([$id]);
$orig = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$orig) {
  $_SESSION['flash'] = 'Post not found.';
  header('Location: adminBlog.php');
  exit;
}

$imagePath = $orig['image_path'];

if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
  $uploadDirFs = rtrim($_SERVER['DOCUMENT_ROOT'], '/').'/uploads';
  $uploadDirUrl = '/uploads';
  if (!is_dir($uploadDirFs)) { @mkdir($uploadDirFs, 0775, true); }

  $tmp   = $_FILES['image']['tmp_name'];
  $size  = (int)$_FILES['image']['size'];

  if ($size > 5 * 1024 * 1024) {
    $_SESSION['flash'] = 'Image too large (max 5MB).';
    header('Location: editBlog.php?id='.$id);
    exit;
  }
  $finfo = @getimagesize($tmp);
  if ($finfo === false) {
    $_SESSION['flash'] = 'Invalid image.';
    header('Location: editBlog.php?id='.$id);
    exit;
  }
  $ext = image_type_to_extension($finfo[2], false);
  if (!in_array(strtolower($ext), ['jpg','jpeg','png','webp'], true)) {
    $_SESSION['flash'] = 'Only JPG, PNG, or WebP allowed.';
    header('Location: editBlog.php?id='.$id);
    exit;
  }
  $basename = 'blog_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
  $destFs   = $uploadDirFs.'/'.$basename;
  if (!move_uploaded_file($tmp, $destFs)) {
    $_SESSION['flash'] = 'Failed to save image.';
    header('Location: editBlog.php?id='.$id);
    exit;
  }
  $imagePath = $uploadDirUrl.'/'.$basename;

  if (!empty($orig['image_path']) && str_starts_with($orig['image_path'], '/uploads/')) {
    $oldFs = rtrim($_SERVER['DOCUMENT_ROOT'], '/').$orig['image_path'];
    if (is_file($oldFs)) @unlink($oldFs);
  }
}

if ($title === '' || $content === '') {
  $_SESSION['flash'] = 'Title and Content are required.';
  header('Location: editBlog.php?id='.$id);
  exit;
}

$u = $pdo->prepare("UPDATE blogs SET title=?, content=?, image_path=? WHERE id=?");
$u->execute([$title, $content, $imagePath, $id]);

$_SESSION['flash'] = 'Post updated.';
header('Location: editBlog.php?id='.$id);

