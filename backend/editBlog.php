<?php

session_start();
require __DIR__ . '/db.php';
if ($pdo instanceof PDO) $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  $_SESSION['flash'] = 'Invalid post.';
  header('Location: adminBlog.php');
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM blogs WHERE id=?");
$stmt->execute([$id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$post) {
  $_SESSION['flash'] = 'Post not found.';
  header('Location: adminBlog.php');
  exit;
}

$userName = $_SESSION['name'] ?? 'Admin';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Blog — <?= h($post['title']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="asset/css/adminBlog.css">
</head>
<body>
  <div class="admin-shell">
    <aside class="admin-aside">
      <div class="aside-user">
        <div>
          <div class="name"><?= h($userName) ?></div>
          <small style="color:#8a8a8a"><?= h($_SESSION['email'] ?? '') ?></small>
        </div>
      </div>

      <nav class="aside-nav">
        <a class="aside-link" href="adminBlog.php"><i class="bi bi-journal-text"></i><span>Blogs</span></a>
      </nav>

      <div class="aside-footer">
        <div class="brand-pill">
          <strong style="font-size:12px">PARAMOUNT<br>DEVELOPMENT GROUP</strong>
        </div>
      </div>
    </aside>

    <main class="admin-main">
      <div class="main-card">
        <h2>View Blog</h2>
        <?php if ($flash): ?>
          <div class="alert success"><i class="bi bi-check-circle"></i> <?= h($flash) ?></div>
        <?php endif; ?>
      </div>

      <div class="main-card">
        <h3>Stats</h3>
        <div style="display:flex;gap:18px;flex-wrap:wrap">
          <div class="stat-pill"><i class="bi bi-hand-thumbs-up"></i> Likes: <strong><?= (int)$post['likes'] ?></strong></div>
          <div class="stat-pill"><i class="bi bi-eye"></i> Views: <strong><?= (int)$post['views'] ?></strong></div>
          <div class="stat-pill"><i class="bi bi-calendar-event"></i> Created: <strong><?= h(date('Y-m-d H:i', strtotime($post['created_at']))) ?></strong></div>
          <div class="stat-pill"><i class="bi bi-clock-history"></i> Updated: <strong><?= h(date('Y-m-d H:i', strtotime($post['updated_at']))) ?></strong></div>
        </div>
      </div>

      <div class="main-card">
        <h3>Current Image</h3>
        <?php if (!empty($post['image_path'])): ?>
          <img src="<?= h($post['image_path']) ?>" alt="" style="max-width:360px;border-radius:12px;box-shadow:0 1px 2px rgba(0,0,0,.06),0 8px 24px rgba(0,0,0,.05)">
        <?php else: ?>
          <div class="muted">No image uploaded.</div>
        <?php endif; ?>
      </div>

      <div class="main-card">
        <h3>Update Post</h3>
        <form action="updateBlog.php" method="post" enctype="multipart/form-data" autocomplete="off" novalidate>
          <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
          <div class="field">
            <label for="title">Title</label>
            <input type="text" name="title" id="title" value="<?= h($post['title']) ?>" required>
          </div>
          <div class="field">
            <label for="content">Content</label>
            <textarea name="content" id="content" rows="8" required><?= h($post['content']) ?></textarea>
          </div>
          <div class="field">
            <label for="image">Replace Image (optional)</label>
            <input type="file" name="image" id="image" accept="image/*">
            <small>Leave empty to keep current image.</small>
          </div>

          <div class="actions">
            <button type="submit" class="btn primary"><i class="bi bi-save"></i> Save Changes</button>

            <a class="btn danger" href="deleteBlog.php?id=<?= (int)$post['id'] ?>"
               onclick="return confirm('Delete this post permanently? This cannot be undone.');">
               <i class="bi bi-trash"></i> Delete
            </a>
          </div>
        </form>
      </div>
    </main>
  </div>

<script src="https://cdn.ckeditor.com/ckeditor5/41.2.1/classic/ckeditor.js"></script>
<script>
  ClassicEditor
    .create(document.querySelector('#content'))
    .catch(error => {
        console.error(error);
    });
</script>
</body>
</html>
