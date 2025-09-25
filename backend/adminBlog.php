<?php

session_start();

require __DIR__ . '/db.php'; 
if ($pdo instanceof PDO) {
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$userName = $_SESSION['name'] ?? 'Admin';

$reqPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';
$self    = basename($reqPath);
$qstr    = $_SERVER['QUERY_STRING'] ?? '';
$tab     = $_GET['tab'] ?? '';


function alFile(string $href, string $label, string $icon): void {
  $self = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');
  $tab  = $_GET['tab'] ?? '';

  $isActive = false;
  $fileOnly = basename(parse_url($href, PHP_URL_PATH) ?? '');
  if ($fileOnly === $self) {
    if ($fileOnly === 'admin.php') {
      $wantSettings = (strpos($href, 'tab=settings') !== false);
      $isActive = $wantSettings ? ($tab === 'settings') : ($tab !== 'settings');
    } else {
      $isActive = true;
    }
  }
  $cls = $isActive ? 'aside-link active' : 'aside-link';
  echo '<a class="'.$cls.'" href="'.$href.'"><i class="bi '.$icon.'"></i><span>'.$label.'</span></a>';
}

$pdo->exec("
  CREATE TABLE IF NOT EXISTS blog_content (
    `key`   VARCHAR(64) PRIMARY KEY,
    `value` MEDIUMTEXT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

function get_content(PDO $pdo, string $key, string $default=''): string {
  $stmt = $pdo->prepare("SELECT `value` FROM blog_content WHERE `key`=?");
  $stmt->execute([$key]);
  $v = $stmt->fetchColumn();
  return $v !== false ? (string)$v : $default;
}
function set_content(PDO $pdo, string $key, ?string $value): void {
  $stmt = $pdo->prepare("
    INSERT INTO blog_content(`key`,`value`)
    VALUES(?,?)
    ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)
  ");
  $stmt->execute([$key, $value]);
}

$default_h1 = 'Insights, Updates & Stories';
$default_p  = 'Explore articles about our developments, community impact, and perspectives from the Paramount Development Group team.';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_hero') {
  $hero_h1 = trim($_POST['hero_h1'] ?? '');
  $hero_p  = trim($_POST['hero_p']  ?? '');

  set_content($pdo, 'blogs_hero_h1', $hero_h1);
  set_content($pdo, 'blogs_hero_p',  $hero_p);

  $_SESSION['flash'] = 'Blogs header updated.';
  header('Location: adminBlog.php');
  exit;
}

$cur_h1 = get_content($pdo, 'blogs_hero_h1', $default_h1);
$cur_p  = get_content($pdo, 'blogs_hero_p',  $default_p);

$posts = $pdo->query("SELECT id, title, image_path, likes, views, created_at, updated_at
                      FROM blogs ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);


$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin — Blogs</title>

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
      <div class="main-card">
        <h2>Manage Blogs</h2>
        <p>Edit the Blogs page header, add posts, and manage existing posts.</p>
        <?php if ($flash): ?>
          <div class="alert success"><i class="bi bi-check-circle"></i> <?= h($flash) ?></div>
        <?php endif; ?>
      </div>

      <div class="main-card">
        <h3>Blogs Header Content</h3>
        <form method="post" action="adminBlog.php" autocomplete="off" novalidate>
          <input type="hidden" name="action" value="save_hero">
          <div class="field">
            <label for="hero_h1">Heading</label>
            <input type="text" id="hero_h1" name="hero_h1" value="<?= h($cur_h1) ?>" required>
          </div>

          <div class="field">
            <label for="hero_p">Intro Paragraph</label>
            <textarea id="hero_p" name="hero_p" rows="4" required><?= h($cur_p) ?></textarea>
          </div>

          <div class="actions">
            <button type="submit" class="btn primary"><i class="bi bi-save"></i> Save Header</button>
            <a href="adminBlog.php" class="btn"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
          </div>
        </form>
      </div>

      <div class="main-card">
        <h3>Add New Blog</h3>
        <form action="saveBlog.php" method="post" enctype="multipart/form-data" autocomplete="off" novalidate>
          <div class="field">
            <label for="title">Title</label>
            <input type="text" name="title" id="title" required>
          </div>
          <div class="field">
            <label for="content">Content</label>
            <textarea name="content" id="content" rows="6" required></textarea>
          </div>
          <div class="field">
            <label for="image">Feature Image</label>
            <input type="file" name="image" id="image" accept="image/*">
            <small>Optional. JPG/PNG/WebP up to ~5MB.</small>
          </div>
          <div class="actions">
            <button type="submit" class="btn primary"><i class="bi bi-save"></i> Save</button>
            <a href="adminBlog.php" class="btn"><i class="bi bi-x-circle"></i> Cancel</a>
          </div>
        </form>
      </div>

      <div class="main-card">
        <h3>Existing Posts</h3>
        <?php if (!$posts): ?>
          <div class="muted">No posts yet.</div>
        <?php else: ?>
          <div class="table-wrap">
            <table class="table">
              <thead>
                <tr>
                  <th style="width:64px">Image</th>
                  <th>Title</th>
                  <th style="width:120px">Likes</th>
                  <th style="width:120px">Views</th>
                  <th style="width:180px">Created</th>
                  <th style="width:120px"></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($posts as $p): ?>
                  <tr>
                    <td>
                      <?php if (!empty($p['image_path'])): ?>
                        <img src="<?= h($p['image_path']) ?>" alt="" style="width:56px;height:42px;object-fit:cover;border-radius:6px">
                      <?php else: ?>
                        <div style="width:56px;height:42px;background:#eee;border-radius:6px"></div>
                      <?php endif; ?>
                    </td>
                    <td><?= h($p['title']) ?></td>
                    <td><?= (int)$p['likes'] ?></td>
                    <td><?= (int)$p['views'] ?></td>
                    <td><?= h(date('Y-m-d H:i', strtotime($p['created_at']))) ?></td>
                    <td>
                      <a class="btn small" href="editBlog.php?id=<?= (int)$p['id'] ?>"><i class="bi bi-pencil-square"></i> Edit</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
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
