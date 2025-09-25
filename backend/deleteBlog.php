<?php

session_start();
require __DIR__ . '/db.php';
if ($pdo instanceof PDO) $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  $_SESSION['flash'] = 'Invalid post.';
  header('Location: adminBlog.php');
  exit;
}

$stmt = $pdo->prepare("SELECT image_path FROM blogs WHERE id=?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {

  if (!empty($row['image_path']) && str_starts_with($row['image_path'], '/uploads/')) {
    $fs = rtrim($_SERVER['DOCUMENT_ROOT'], '/').$row['image_path'];
    if (is_file($fs)) @unlink($fs);
  }
  $pdo->prepare("DELETE FROM blogs WHERE id=?")->execute([$id]);
  $_SESSION['flash'] = 'Post deleted.';
}

header('Location: adminBlog.php');
