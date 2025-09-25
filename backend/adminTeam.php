<?php

session_start();
require __DIR__ . '/db.php'; 

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
$userName = $_SESSION['name'] ?? 'Admin';

$reqPath   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$self      = basename($reqPath);
$tab       = $_GET['tab'] ?? '';

function alFile($url, $label, $icon){
  global $self, $tab;
  $urlPath = basename(parse_url($url, PHP_URL_PATH));
  $isActive = ($self === $urlPath);
  if ($url === 'admin.php?tab=settings') {
    $isActive = ($self === 'admin.php' && ($tab === 'settings'));
  }
  $cls = $isActive ? 'aside-link active' : 'aside-link';
  echo '<a class="'.$cls.'" href="'.$url.'"><i class="bi '.$icon.'"></i><span>'.$label.'</span></a>';
}

function ensureUploadDir(): string {
  $docroot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? dirname(__FILE__), '/\\');
  $dir = $docroot . '/uploads';
  if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
  return $dir;
}

function saveUploadedPhoto(?array $file): ?string {
  if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
  if ($file['error'] !== UPLOAD_ERR_OK) return null;

  $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  $allowed = ['jpg','jpeg','png','webp'];
  if (!in_array($ext, $allowed, true)) return null;
  if (($file['size'] ?? 0) > 10 * 1024 * 1024) return null; // 10MB guard

  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime  = $finfo ? finfo_file($finfo, $file['tmp_name']) : null;
  if ($finfo) finfo_close($finfo);
  $okMimes = ['image/jpeg','image/png','image/webp'];
  if ($mime && !in_array($mime, $okMimes, true)) return null;

  $dir = ensureUploadDir();
  $name = bin2hex(random_bytes(8)) . '.' . $ext;
  $abs  = $dir . '/' . $name;
  if (!move_uploaded_file($file['tmp_name'], $abs)) return null;

  return '/uploads/' . $name; 
}

$action = $_GET['action'] ?? 'index';
$notice = null;
$error  = null;

if ($action === 'save_header' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $sub   = trim($_POST['subheader'] ?? '');
  if ($title === '' || $sub === '') {
    $error = 'Please complete the header title and subheader.';
  } else {
    $pdo->exec("INSERT INTO team_header (id, title, subheader) VALUES (1,'','') ON DUPLICATE KEY UPDATE id=id");
    $stmt = $pdo->prepare("UPDATE team_header SET title=?, subheader=? WHERE id=1");
    $stmt->execute([$title, $sub]);
    $notice = 'Team header updated.';
  }
  $action = 'index';
}

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $name  = trim($_POST['name'] ?? '');
  $role  = trim($_POST['role'] ?? '');
  $bio   = trim($_POST['bio'] ?? '');
  $active= isset($_POST['is_active']) ? 1 : 0;

  if ($name === '' || $role === '' || $bio === '') {
    $error = 'Name, Role, and Bio are required.';
  } else {
    $photo = saveUploadedPhoto($_FILES['photo'] ?? null);
    $stmt = $pdo->prepare("INSERT INTO team_members (name, role, bio, photo_path, is_active) VALUES (?,?,?,?,?)");
    $stmt->execute([$name, $role, $bio, $photo, $active]);
    $notice = 'Team member added.';
  }
  $action = 'index';
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $id    = (int)($_POST['id'] ?? 0);
  $name  = trim($_POST['name'] ?? '');
  $role  = trim($_POST['role'] ?? '');
  $bio   = trim($_POST['bio'] ?? '');
  $active= isset($_POST['is_active']) ? 1 : 0;

  if ($id <= 0) {
    $error = 'Invalid member.';
  } elseif ($name === '' || $role === '' || $bio === '') {
    $error = 'Name, Role, and Bio are required.';
  } else {
    $photo = saveUploadedPhoto($_FILES['photo'] ?? null);
    if ($photo) {
      $stmt = $pdo->prepare("UPDATE team_members SET name=?, role=?, bio=?, photo_path=?, is_active=? WHERE id=?");
      $stmt->execute([$name, $role, $bio, $photo, $active, $id]);
    } else {
      $stmt = $pdo->prepare("UPDATE team_members SET name=?, role=?, bio=?, is_active=? WHERE id=?");
      $stmt->execute([$name, $role, $bio, $active, $id]);
    }
    $notice = 'Team member updated.';
  }
  $action = 'index';
}

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM team_members WHERE id=?");
    $stmt->execute([$id]);
    $notice = 'Team member deleted.';
  } else {
    $error = 'Invalid member.';
  }
  $action = 'index';
}

$header = $pdo->query("SELECT * FROM team_header WHERE id=1")->fetch(PDO::FETCH_ASSOC) ?: ['title'=>'','subheader'=>''];
$members = $pdo->query("SELECT * FROM team_members ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin — Team</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="asset/css/adminTeam.css">
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
      <div class="main-card">
        <h1>Admin Team</h1>
        <p>Were you can add, edit and delete a team member of the Paramount Development Group.</p>
        <?php if ($notice): ?><div class="card" style="background:#e8f5e9;color:#1b5e20"><?=h($notice)?></div><?php endif; ?>
        <?php if ($error):  ?><div class="card" style="background:#fdecea;color:#b3261e"><?=h($error)?></div><?php endif; ?>
      </div>

      <div class="card">
        <h2>Team Header</h2>
        <form method="post" action="?action=save_header">
          <div class="field"><label>Title</label><input type="text" name="title" value="<?=h($header['title'])?>" required></div>
          <div class="field"><label>Subheader</label><textarea name="subheader" rows="4" required><?=h($header['subheader'])?></textarea></div>
          <div class="actions"><button class="btn primary" type="submit"><i class="bi bi-save"></i>Save Header</button></div>
        </form>
      </div>

      <div class="card">
        <h2>Add Team Member</h2>
        <form method="post" action="?action=create" enctype="multipart/form-data">
          <div class="grid">
            <div>
              <div class="field"><label>Name</label><input type="text" name="name" required></div>
              <div class="field"><label>Role / Title</label><input type="text" name="role" required></div>
              <div class="field"><label>Bio</label><textarea name="bio" rows="6" required></textarea></div>
            </div>
            <div>
              <div class="field"><label>Photo</label><input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp"></div>
              <div class="field"><label><input type="checkbox" name="is_active" checked> Active</label></div>
              <div class="actions"><button class="btn primary" type="submit"><i class="bi bi-plus-circle"></i>Add Member</button></div>
            </div>
          </div>
        </form>
      </div>

      <div class="card">
        <h2>Members</h2>
        <table class="table">
          <thead>
            <tr>
              <th>Photo</th>
              <th>Name & Role</th>
              <th>Bio</th>
              <th>Status</th>
              <th style="width:400px">Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($members as $m): ?>
            <tr>
              <td><?php if ($m['photo_path']): ?><img class="img-preview" src="<?=h($m['photo_path'])?>"><?php endif; ?></td>
              <td><strong><?=h($m['name'])?></strong><br><span class="muted"><?=h($m['role'])?></span></td>
              <td><div class="muted" style="font-size:13px"><?=nl2br(h($m['bio']))?></div></td>
              <td><span class="badge <?= $m['is_active'] ? 'on':'off' ?>"><?= $m['is_active']?'Active':'Hidden' ?></span></td>
              <td>
                <details>
                  <summary>Edit</summary>
                  <form method="post" action="?action=update" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                    <div class="field"><label>Name</label><input type="text" name="name" value="<?=h($m['name'])?>" required></div>
                    <div class="field"><label>Role</label><input type="text" name="role" value="<?=h($m['role'])?>" required></div>
                    <div class="field"><label>Bio</label><textarea name="bio" rows="5" required><?=h($m['bio'])?></textarea></div>
                    <div class="field"><label>Replace Photo</label><input type="file" name="photo"></div>
                    <div class="field"><label><input type="checkbox" name="is_active" <?= $m['is_active']?'checked':'' ?>> Active</label></div>
                    <div class="actions"><button class="btn primary" type="submit">Save</button></div>
                  </form>
                </details>
                    <form class="inline" method="post" action="?action=delete" 
                            onsubmit="return confirm('Are you sure you want to delete this member?')">
                        <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                        <button class="btn" type="submit" style="border-color:#b3261e;color:#b3261e">
                            <i class="bi bi-trash3"></i> Delete
                        </button>
                    </form>
                </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$members): ?>
            <tr><td colspan="5" class="muted">No members yet. Add one above.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>
</html>
