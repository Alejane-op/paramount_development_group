<?php
// Must be first thing, before session_start or any output
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');
ini_set('memory_limit', '128M');
ini_set('max_execution_time', '300');
ini_set('max_input_time', '300');

// backend/admin.php
session_start();
require __DIR__ . '/db.php';

// Safe HTML escape (define once)
if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
// Safe short text (works even if mbstring is missing)
if (!function_exists('short_text')) {
  function short_text($s, $len = 60){
    $s = (string)$s;
    if (function_exists('mb_strimwidth')) {
      return mb_strimwidth($s, 0, $len, '…', 'UTF-8');
    }
    return (strlen($s) > $len) ? substr($s, 0, $len - 3) . '...' : $s;
  }
}

// Ensure these are always defined to avoid notices:
$allBlocks   = $allBlocks   ?? [];
$editBlock   = $editBlock   ?? null;   // null or array
$editBullets = $editBullets ?? [];

const REMEMBER_COOKIE = 'remember_me';

/* ------------------ AUTH / SESSION ------------------ */
function hydrateUserSession(PDO $pdo, int $userId): void {
    $stmt = $pdo->prepare('SELECT id, name, email FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION['user_id'] = (int)$row['id'];
        $_SESSION['name']    = $row['name'] ?? null;
        $_SESSION['email']   = $row['email'] ?? null;
    }
}

if (empty($_SESSION['user_id']) && !empty($_COOKIE[REMEMBER_COOKIE])) {
    $cookie    = $_COOKIE[REMEMBER_COOKIE];
    [$selector, $validator] = explode(':', $cookie, 2) + [null, null];

    if ($selector && $validator && ctype_digit($selector)) {
        $stmt = $pdo->prepare('SELECT id, name, email, remember_token_hash, remember_token_expires 
                               FROM users WHERE id = ?');
        $stmt->execute([(int)$selector]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user
            && !empty($user['remember_token_hash'])
            && !empty($user['remember_token_expires'])
            && (new DateTime($user['remember_token_expires'])) > new DateTime()
            && hash_equals($user['remember_token_hash'], hash('sha256', $validator))) {

            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['name']    = $user['name'] ?? null;
            $_SESSION['email']   = $user['email'] ?? null;
        }
    }
}

if (!empty($_SESSION['user_id']) && (empty($_SESSION['name']) || empty($_SESSION['email']))) {
    hydrateUserSession($pdo, (int)$_SESSION['user_id']);
}

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userName = h($_SESSION['name'] ?? 'Admin');

/* ------------------ HERO INNER HELPERS (home_hero_inner) ------------------ */
/* Expected table:
CREATE TABLE IF NOT EXISTS home_hero_inner (
  id INT AUTO_INCREMENT PRIMARY KEY,
  upper_title VARCHAR(255) NOT NULL,
  down_title  VARCHAR(255) NOT NULL,
  subtitle    TEXT,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
*/
function get_home_hero_inner(PDO $pdo): array {
    $stmt = $pdo->query("SELECT id, upper_title, down_title, subtitle FROM home_hero_inner ORDER BY id ASC LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return ['id'=>null, 'upper_title'=>'', 'down_title'=>'', 'subtitle'=>''];
    }
    return $row;
}
function upsert_home_hero_inner(PDO $pdo, ?int $id, string $upper, string $down, string $subtitle): bool {
    if ($id === null) {
        $stmt = $pdo->prepare("INSERT INTO home_hero_inner (upper_title, down_title, subtitle) VALUES (:u, :d, :s)");
        return $stmt->execute([':u'=>$upper, ':d'=>$down, ':s'=>$subtitle]);
    } else {
        $stmt = $pdo->prepare("UPDATE home_hero_inner SET upper_title=:u, down_title=:d, subtitle=:s WHERE id=:id");
        return $stmt->execute([':u'=>$upper, ':d'=>$down, ':s'=>$subtitle, ':id'=>$id]);
    }
}

/* ------------------ HOME ABOUT HELPERS (home_about_content) ------------------ */
/* Expected table:
CREATE TABLE IF NOT EXISTS home_about_content (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  body  TEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
*/
function get_home_about(PDO $pdo): array {
    $stmt = $pdo->query("SELECT id, title, body FROM home_about_content ORDER BY id ASC LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return ['id'=>null, 'title'=>'', 'body'=>''];
    }
    return $row;
}
function upsert_home_about(PDO $pdo, ?int $id, string $title, string $body): bool {
    if ($id === null) {
        $stmt = $pdo->prepare("INSERT INTO home_about_content (title, body) VALUES (:t, :b)");
        return $stmt->execute([':t'=>$title, ':b'=>$body]);
    } else {
        $stmt = $pdo->prepare("UPDATE home_about_content SET title=:t, body=:b WHERE id=:id");
        return $stmt->execute([':t'=>$title, ':b'=>$body, ':id'=>$id]);
    }
}

/* ------------------ Handle POST (text sections only) ------------------ */
$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Save hero inner text
    if ($action === 'save_hero_inner') {
        $id        = isset($_POST['id']) && ctype_digit($_POST['id']) ? (int)$_POST['id'] : null;
        $upper     = trim((string)($_POST['upper_title'] ?? ''));
        $down      = trim((string)($_POST['down_title'] ?? ''));
        $subtitle  = trim((string)($_POST['subtitle'] ?? ''));

        if ($upper === '' || $down === '') {
            $msg = 'Please fill in both Upper Title and Down Title.';
        } elseif (mb_strlen($upper) > 255 || mb_strlen($down) > 255) {
            $msg = 'Titles must be 255 characters or fewer.';
        } else {
            $msg = upsert_home_hero_inner($pdo, $id, $upper, $down, $subtitle)
                ? 'Hero text saved.' : 'Database error: could not save hero text.';
        }
    }

    // Save home about text
    if ($action === 'save_home_about') {
        $id    = isset($_POST['id']) && ctype_digit($_POST['id']) ? (int)$_POST['id'] : null;
        $title = trim((string)($_POST['title'] ?? ''));
        $body  = trim((string)($_POST['body'] ?? ''));

        if ($title === '') {
            $msg = 'Please enter a title.';
        } elseif (mb_strlen($title) > 255) {
            $msg = 'Title must be 255 characters or fewer.';
        } else {
            $msg = upsert_home_about($pdo, $id, $title, $body)
                ? 'Home About content saved.' : 'Database error: could not save Home About content.';
        }
    }
}

// Fetch data for forms + previews (after POST handling)
$heroInner = get_home_hero_inner($pdo);
$homeAbout = get_home_about($pdo);

/* ------------------ DEV BLOCK HELPERS ------------------ */
function get_all_blocks(PDO $pdo): array {
    return $pdo->query("SELECT * FROM dev_blocks ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
}
function get_block(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM dev_blocks WHERE id=?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}
function get_bullets(PDO $pdo, int $blockId): array {
    $stmt = $pdo->prepare("SELECT * FROM dev_bullets WHERE block_id=? ORDER BY id ASC");
    $stmt->execute([$blockId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* where to store files (filesystem + relative web path) */
$WEBROOT_FS   = realpath(__DIR__ . '/..');   // one level up from /backend
$UPLOADS_FS   = $WEBROOT_FS . '/uploads';    // /.../uploads
$UPLOADS_REL  = 'uploads';                   // stored in DB
if (!is_dir($UPLOADS_FS)) { @mkdir($UPLOADS_FS, 0775, true); }

/* ---------- action router (Dev Blocks only) ---------- */
$action = $_POST['action'] ?? $_GET['action'] ?? null;

/* Create/Update block (with image upload) */
if ($action === 'save_block') {
    $id     = isset($_POST['id']) && ctype_digit((string)$_POST['id']) ? (int)$_POST['id'] : 0;
    $title  = trim($_POST['title'] ?? '');
    $desc   = trim($_POST['description'] ?? '');
    $layout = $_POST['layout'] ?? 'image-left';
    $layout = in_array($layout, ['image-left','image-right'], true) ? $layout : 'image-left';

    // keep existing image unless replaced
    $imagePath = trim($_POST['existing_image'] ?? '');

    // ----- handle image upload if provided -----
    if (!empty($_FILES['image']['name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($_FILES['image']['tmp_name']) ?: '';
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            'image/avif' => 'avif',
        ];
        if (!isset($allowed[$mime])) {
            die('Invalid image type. Allowed: JPG/PNG/WEBP/GIF/AVIF.');
        }
        if ((int)$_FILES['image']['size'] > 5 * 1024 * 1024) {
            die('Image too large (max 5MB).');
        }

        $ext    = $allowed[$mime];
        $fname  = 'dev_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $target = $UPLOADS_FS . '/' . $fname;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            die('Failed to store uploaded file.');
        }
        $imagePath = $UPLOADS_REL . '/' . $fname;
    }

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE dev_blocks SET title=?, description=?, image=?, layout=? WHERE id=?");
        $stmt->execute([$title, $desc, $imagePath, $layout, $id]);
    } else {
        $maxOrder = (int)$pdo->query("SELECT COALESCE(MAX(sort_order),0) FROM dev_blocks")->fetchColumn();
        $stmt = $pdo->prepare("INSERT INTO dev_blocks (title, description, image, layout, sort_order) VALUES (?,?,?,?,?)");
        $stmt->execute([$title, $desc, $imagePath, $layout, $maxOrder + 1]);
        $id = (int)$pdo->lastInsertId();
    }
    header("Location: admin.php?edit_block={$id}#dev-blocks");
    exit;
}

/* Delete block (bullets cascade via FK) */
if ($action === 'delete_block') {
    $id = isset($_POST['id']) && ctype_digit((string)$_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM dev_blocks WHERE id=?");
        $stmt->execute([$id]);
    }
    header("Location: admin.php#dev-blocks");
    exit;
}

/* OPTIONAL: Reorder blocks (swap sort_order) */
if ($action === 'move') {
    $id  = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : 0;
    $dir = $_GET['dir'] ?? 'up'; // 'up' or 'down'
    if ($id > 0) {
        $cur = $pdo->prepare("SELECT id, sort_order FROM dev_blocks WHERE id=?");
        $cur->execute([$id]);
        if ($row = $cur->fetch(PDO::FETCH_ASSOC)) {
            $curOrder = (int)$row['sort_order'];
            if ($dir === 'up') {
                $other = $pdo->prepare("SELECT id, sort_order FROM dev_blocks WHERE sort_order < ? ORDER BY sort_order DESC LIMIT 1");
                $other->execute([$curOrder]);
            } else {
                $other = $pdo->prepare("SELECT id, sort_order FROM dev_blocks WHERE sort_order > ? ORDER BY sort_order ASC LIMIT 1");
                $other->execute([$curOrder]);
            }
            if ($o = $other->fetch(PDO::FETCH_ASSOC)) {
                $pdo->beginTransaction();
                $u1 = $pdo->prepare("UPDATE dev_blocks SET sort_order=? WHERE id=?");
                $u2 = $pdo->prepare("UPDATE dev_blocks SET sort_order=? WHERE id=?");
                $u1->execute([$o['sort_order'], $row['id']]);
                $u2->execute([$curOrder, $o['id']]);
                $pdo->commit();
            }
        }
    }
    header("Location: admin.php#dev-blocks");
    exit;
}

/* Add bullet */
if ($action === 'add_bullet') {
    $blockId = isset($_POST['block_id']) && ctype_digit((string)$_POST['block_id']) ? (int)$_POST['block_id'] : 0;
    $label   = trim($_POST['label'] ?? '');
    $desc    = trim($_POST['description'] ?? '');
    if ($blockId > 0 && $label !== '' && $desc !== '') {
        $stmt = $pdo->prepare("INSERT INTO dev_bullets (block_id, label, description) VALUES (?,?,?)");
        $stmt->execute([$blockId, $label, $desc]);
    }
    header("Location: admin.php?edit_block={$blockId}#dev-blocks");
    exit;
}

/* Update bullet */
if ($action === 'update_bullet') {
    $id      = isset($_POST['id']) && ctype_digit((string)$_POST['id']) ? (int)$_POST['id'] : 0;
    $blockId = isset($_POST['block_id']) && ctype_digit((string)$_POST['block_id']) ? (int)$_POST['block_id'] : 0;
    $label   = trim($_POST['label'] ?? '');
    $desc    = trim($_POST['description'] ?? '');
    if ($id > 0 && $label !== '' && $desc !== '') {
        $stmt = $pdo->prepare("UPDATE dev_bullets SET label=?, description=? WHERE id=?");
        $stmt->execute([$label, $desc, $id]);
    }
    header("Location: admin.php?edit_block={$blockId}#dev-blocks");
    exit;
}

/* Delete bullet */
if ($action === 'delete_bullet') {
    $id      = isset($_POST['id']) && ctype_digit((string)$_POST['id']) ? (int)$_POST['id'] : 0;
    $blockId = isset($_POST['block_id']) && ctype_digit((string)$_POST['block_id']) ? (int)$_POST['block_id'] : 0;
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM dev_bullets WHERE id=?");
        $stmt->execute([$id]);
    }
    header("Location: admin.php?edit_block={$blockId}#dev-blocks");
    exit;
}

/* fetch for UI */
$editBlockId = isset($_GET['edit_block']) && ctype_digit($_GET['edit_block']) ? (int)$_GET['edit_block'] : 0;
$editBlock   = $editBlockId ? get_block($pdo, $editBlockId)     : null;
$editBullets = $editBlockId ? get_bullets($pdo, $editBlockId)   : [];
$allBlocks   = get_all_blocks($pdo);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin — Home</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="asset/css/admin.css">
</head>
<body>
  <div class="admin-layout">
    <div class="admin-shell">
      <!-- ===== Sidebar ===== -->
      <aside class="admin-aside">
        <div class="aside-user">
          <div>
            <div class="name"><?= $userName ?></div>
            <small style="color:#8a8a8a"><?= h($_SESSION['email'] ?? '') ?></small>
          </div>
        </div>

        <nav class="aside-nav">
          <?php
            $self = basename($_SERVER['PHP_SELF']);
            function alFile($file,$label,$icon){
              global $self;
              $cls = $self===$file ? 'aside-link active' : 'aside-link';
              echo '<a class="'.$cls.'" href="'.$file.'"><i class="bi '.$icon.'"></i><span>'.$label.'</span></a>';
            }
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

      <!-- ===== Main content ===== -->
      <main class="content">
        <!-- ==================== HERO INNER TEXT MANAGER ==================== -->
        <section id="hero-inner" class="card">
          <h2 style="margin-top:0">Homepage Hero Text</h2>

          <?php if (!empty($msg) && ($_POST['action'] ?? '') === 'save_hero_inner'): ?>
            <div class="msg"><?= h($msg) ?></div>
          <?php endif; ?>

          <form method="post" class="card" style="margin-top:12px;">
            <input type="hidden" name="action" value="save_hero_inner">
            <input type="hidden" name="id" value="<?= h((string)($heroInner['id'] ?? '')) ?>">

            <div class="row" style="gap:12px">
              <div style="flex:1; min-width:260px">
                <label style="display:block; font-weight:600; margin-bottom:6px">Upper Title</label>
                <input type="text" name="upper_title" required
                       value="<?= h((string)$heroInner['upper_title']) ?>"
                       style="width:100%;">
              </div>
              <div style="flex:1; min-width:260px">
                <label style="display:block; font-weight:600; margin-bottom:6px">Down Title</label>
                <input type="text" name="down_title" required
                       value="<?= h((string)$heroInner['down_title']) ?>"
                       style="width:100%;">
              </div>
            </div>

            <div style="margin-top:12px">
              <label style="display:block; font-weight:600; margin-bottom:6px">Subtitle</label>
              <textarea name="subtitle" rows="3" style="width:100%; resize:vertical"><?= h((string)$heroInner['subtitle']) ?></textarea>
            </div>

            <div style="margin-top:12px">
              <button class="btn primary" type="submit">Save Text</button>
            </div>
          </form>

          <div class="card" style="margin-top:12px;">
            <h3 style="margin-top:0;">Preview</h3>
            <div class="hero-inner" style="padding:12px; border:1px dashed #ddd; border-radius:10px;">
              <h1 style="margin:0 0 8px 0; font-weight:700;">
                <?= h((string)$heroInner['upper_title']) ?> <br>
                <?= h((string)$heroInner['down_title']) ?>
              </h1>
              <p style="margin:0; color:#555;">
                <?= nl2br(h((string)$heroInner['subtitle'])) ?>
              </p>
            </div>
            <p style="color:#666; font-size:13px; margin-top:8px;">
              Frontend keeps exact HTML: <code>&lt;h1&gt;Upper &lt;br&gt; Down&lt;/h1&gt;</code> (no <code>&lt;br&gt;</code> stored in DB).
            </p>
          </div>
        </section>

        <!-- ==================== HOME ABOUT TEXT MANAGER ==================== -->
        <section id="home-about" class="card">
          <h2 style="margin-top:0">Homepage — Our Purpose</h2>

          <?php if (!empty($msg) && ($_POST['action'] ?? '') === 'save_home_about'): ?>
            <div class="msg"><?= h($msg) ?></div>
          <?php endif; ?>

          <form method="post" class="card" style="margin-top:12px;">
            <input type="hidden" name="action" value="save_home_about">
            <input type="hidden" name="id" value="<?= h((string)($homeAbout['id'] ?? '')) ?>">

            <div style="margin-bottom:12px">
              <label style="display:block; font-weight:600; margin-bottom:6px">Our Purpose</label>
              <input type="text" name="title" required
                     value="<?= h((string)$homeAbout['title']) ?>"
                     style="width:100%;">
            </div>

            <div>
              <label style="display:block; font-weight:600; margin-bottom:6px">Paragraph</label>
              <textarea name="body" rows="5" style="width:100%; resize:vertical"><?= h((string)$homeAbout['body']) ?></textarea>
            </div>

            <div style="margin-top:12px">
              <button class="btn primary" type="submit">Save</button>
            </div>
          </form>

          <div class="card" style="margin-top:12px;">
            <h3 style="margin-top:0;">Preview</h3>
            <section class="home_about_content" style="padding:12px; border:1px dashed #ddd; border-radius:10px;">
              <div class="container">
                <h1><?= h((string)$homeAbout['title']) ?></h1>
                <p><?= nl2br(h((string)$homeAbout['body'])) ?></p>
              </div>
            </section>
          </div>
        </section>

        <!-- ==================== DEVELOPMENT BLOCKS ==================== -->
        <section id="dev-blocks" class="card">
          <h2>Development Blocks</h2>

          <!-- list of existing blocks -->
          <?php if (!empty($allBlocks)): ?>
            <?php foreach ($allBlocks as $b): ?>
              <div class="dev-row">
                <img src="<?= h($b['image'] ?? '') ?>" class="dev-thumb" alt="<?= h($b['title'] ?? 'Block') ?>">
                <div style="flex:1">
                  <strong><?= h($b['title'] ?? '') ?></strong><br>
                  <small><?= h(short_text($b['description'] ?? '', 60)) ?></small>
                </div>
                <a href="admin.php?edit_block=<?= (int)($b['id'] ?? 0) ?>#dev-blocks">Edit</a>
                <form method="post" style="display:inline" onsubmit="return confirm('Delete this block?')">
                  <input type="hidden" name="action" value="delete_block">
                  <input type="hidden" name="id" value="<?= (int)($b['id'] ?? 0) ?>">
                  <button type="submit">Delete</button>
                </form>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p style="color:#666">No blocks yet. Create one below.</p>
          <?php endif; ?>

          <!-- create / edit form -->
          <h3><?= !empty($editBlock) ? 'Edit Block' : 'Create New Block' ?></h3>
          <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save_block">
            <input type="hidden" name="id" value="<?= !empty($editBlock)?(int)$editBlock['id']:'' ?>">
            <input type="hidden" name="existing_image" value="<?= !empty($editBlock)?h($editBlock['image']):'' ?>">

            <p><label>Title
              <input type="text" name="title" required value="<?= !empty($editBlock)?h($editBlock['title']):'' ?>">
            </label></p>

            <p><label>Description
              <textarea name="description" required><?= !empty($editBlock)?h($editBlock['description']):'' ?></textarea>
            </label></p>

            <p><label>Layout
              <select name="layout">
                <?php $layout = !empty($editBlock)?($editBlock['layout'] ?? 'image-left'):'image-left'; ?>
                <option value="image-left"  <?= $layout==='image-left'?'selected':'' ?>>Image Left</option>
                <option value="image-right" <?= $layout==='image-right'?'selected':'' ?>>Image Right</option>
              </select>
            </label></p>

            <p><label>Image (upload to replace/add)
              <input type="file" name="image" accept="image/*">
            </label>
            <?php if (!empty($editBlock['image'])): ?>
              <br>Current: <code><?= h($editBlock['image']) ?></code>
            <?php endif; ?>
            </p>

            <button type="submit"><?= !empty($editBlock)?'Update':'Create' ?></button>
          </form>

          <?php if (!empty($editBlock)): ?>
            <h4>Bullets for <?= h($editBlock['title'] ?? '') ?></h4>

            <!-- add bullet -->
            <form method="post" style="margin-bottom:10px">
              <input type="hidden" name="action" value="add_bullet">
              <input type="hidden" name="block_id" value="<?= (int)($editBlock['id'] ?? 0) ?>">
              <p>
                <input type="text" name="label" placeholder="Label (bold)" required>
                <input type="text" name="description" placeholder="Description" required>
                <button type="submit">Add Bullet</button>
              </p>
            </form>

            <!-- existing bullets -->
            <?php if (!empty($editBullets)): ?>
              <ul class="bullets" style="list-style:none; padding-left:0">
                <?php foreach ($editBullets as $bl): ?>
                  <li style="margin:6px 0">
                    <form method="post" style="display:inline">
                      <input type="hidden" name="action" value="update_bullet">
                      <input type="hidden" name="id" value="<?= (int)($bl['id'] ?? 0) ?>">
                      <input type="hidden" name="block_id" value="<?= (int)($editBlock['id'] ?? 0) ?>">
                      <input type="text" name="label" value="<?= h($bl['label'] ?? '') ?>" required>
                      <input type="text" name="description" value="<?= h($bl['description'] ?? '') ?>" required>
                      <button type="submit">Save</button>
                    </form>

                    <form method="post" style="display:inline" onsubmit="return confirm('Delete bullet?')">
                      <input type="hidden" name="action" value="delete_bullet">
                      <input type="hidden" name="id" value="<?= (int)($bl['id'] ?? 0) ?>">
                      <input type="hidden" name="block_id" value="<?= (int)($editBlock['id'] ?? 0) ?>">
                      <button type="submit">Delete</button>
                    </form>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <p style="color:#666">No bullets yet.</p>
            <?php endif; ?>
          <?php endif; ?>
        </section>
      </main>
    </div>
  </div>
</body>
</html>
