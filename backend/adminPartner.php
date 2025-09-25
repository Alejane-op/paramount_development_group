<?php

session_start();
require __DIR__ . '/db.php';
if ($pdo instanceof PDO) {
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('short_text')) {
  function short_text($s, $len = 80){
    $s = (string)$s;
    if (function_exists('mb_strimwidth')) return mb_strimwidth($s, 0, $len, '…', 'UTF-8');
    return (strlen($s) > $len) ? substr($s, 0, $len-3).'...' : $s;
  }
}
if (!function_exists('alFile')) {
  function alFile($url, $label, $icon){
    $self    = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '');
    $urlPath = basename(parse_url($url, PHP_URL_PATH));
    $isActive = ($self === $urlPath);
    $cls = $isActive ? 'aside-link active' : 'aside-link';
    echo '<a class="'.$cls.'" href="'.$url.'"><i class="bi '.$icon.'"></i><span>'.$label.'</span></a>';
  }
}

$userName = $_SESSION['name']  ?? '';
$userMail = $_SESSION['email'] ?? '';

function ensure_uploads_dir(): string {
  $root = rtrim($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__), '/\\');
  $dir  = $root.'/uploads';
  if (!is_dir($dir)) mkdir($dir, 0775, true);
  return $dir;
}
function save_image_upload(array $file): ?string {
  if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
  if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) return null;
  if (($file['size'] ?? 0) > 8*1024*1024) return null; 

  $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, ['png','jpg','jpeg','webp','gif'], true)) return null;

  $dir = ensure_uploads_dir();
  $name = 'market_'.bin2hex(random_bytes(6)).'.'.$ext;
  $dest = $dir.'/'.$name;

  if (!move_uploaded_file($file['tmp_name'], $dest)) return null;
  return '/uploads/'.$name; 
}

$flash = ['ok'=>[], 'err'=>[]];
$action = $_POST['action'] ?? '';

try {
  switch ($action) {

    case 'save_content':
      $pairs = [
        'market_intro'    => $_POST['market_intro']    ?? '',
        'invest_intro'    => $_POST['invest_intro']    ?? '',
        'invest_result'   => $_POST['invest_result']   ?? '',
        'vision_intro'    => $_POST['vision_intro']    ?? '',
        'community_intro' => $_POST['community_intro'] ?? '',
        'why_we_do_it'    => $_POST['why_we_do_it']    ?? '',
      ];
      $stmt = $pdo->prepare("INSERT INTO partner_content(`key`,`value`)
                             VALUES(:k,:v)
                             ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
      foreach ($pairs as $k=>$v) $stmt->execute([':k'=>$k, ':v'=>$v]);
      $flash['ok'][] = 'Section copy saved.';
      break;

    case 'market_image_upload':
      $img = save_image_upload($_FILES['market_image'] ?? []);
      if (!$img) throw new RuntimeException('Please choose a valid image (png/jpg/webp/gif, up to 8MB).');
      $stmt = $pdo->prepare("INSERT INTO partner_content(`key`,`value`)
                             VALUES('market_image', :v)
                             ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
      $stmt->execute([':v'=>$img]);
      $flash['ok'][] = 'Market image updated.';
      break;

    case 'market_create':
      $stmt = $pdo->prepare("INSERT INTO partner_market_bullets(title,body,sort_order)
                             VALUES(:t,:b,:o)");
      $stmt->execute([
        ':t'=> $_POST['title'] ?? '',
        ':b'=> $_POST['body'] ?? '',
        ':o'=> (int)($_POST['sort_order'] ?? 100),
      ]);
      $flash['ok'][] = 'Market bullet added.';
      break;

    case 'market_update':
      $stmt = $pdo->prepare("UPDATE partner_market_bullets
                                SET title=:t, body=:b, sort_order=:o
                              WHERE id=:id");
      $stmt->execute([
        ':t'=> $_POST['title'] ?? '',
        ':b'=> $_POST['body'] ?? '',
        ':o'=> (int)($_POST['sort_order'] ?? 100),
        ':id'=> (int)$_POST['id'],
      ]);
      $flash['ok'][] = 'Market bullet updated.';
      break;

    case 'market_delete':
      $stmt = $pdo->prepare("DELETE FROM partner_market_bullets WHERE id=:id");
      $stmt->execute([':id'=>(int)$_POST['id']]);
      $flash['ok'][] = 'Market bullet deleted.';
      break;

    case 'invest_create':
      $stmt = $pdo->prepare("INSERT INTO partner_invest_items(heading,body,sort_order)
                             VALUES(:h,:b,:o)");
      $stmt->execute([
        ':h'=> $_POST['heading'] ?? '',
        ':b'=> $_POST['body'] ?? '',
        ':o'=> (int)($_POST['sort_order'] ?? 100),
      ]);
      $flash['ok'][] = 'Investment item added.';
      break;

    case 'invest_update':
      $stmt = $pdo->prepare("UPDATE partner_invest_items
                                SET heading=:h, body=:b, sort_order=:o
                              WHERE id=:id");
      $stmt->execute([
        ':h'=> $_POST['heading'] ?? '',
        ':b'=> $_POST['body'] ?? '',
        ':o'=> (int)($_POST['sort_order'] ?? 100),
        ':id'=> (int)$_POST['id'],
      ]);
      $flash['ok'][] = 'Investment item updated.';
      break;

    case 'invest_delete':
      $stmt = $pdo->prepare("DELETE FROM partner_invest_items WHERE id=:id");
      $stmt->execute([':id'=>(int)$_POST['id']]);
      $flash['ok'][] = 'Investment item deleted.';
      break;

    case 'partner_create':
      $logo = save_image_upload($_FILES['logo'] ?? []);
      if (!$logo) throw new RuntimeException('Logo upload failed or missing.');
      $stmt = $pdo->prepare("INSERT INTO partner_partners(name,logo_path,website,sort_order)
                             VALUES(:n,:p,:w,:o)");
      $stmt->execute([
        ':n'=> $_POST['name'] ?? '',
        ':p'=> $logo,
        ':w'=> ($_POST['website'] ?? null) ?: null,
        ':o'=> (int)($_POST['sort_order'] ?? 100),
      ]);
      $flash['ok'][] = 'Partner added.';
      break;

    case 'partner_update':
      $id = (int)$_POST['id'];
      $logo = save_image_upload($_FILES['logo'] ?? []);
      if ($logo) {
        $stmt = $pdo->prepare("UPDATE partner_partners
                                  SET name=:n, logo_path=:p, website=:w, sort_order=:o
                                WHERE id=:id");
        $stmt->execute([
          ':n'=> $_POST['name'] ?? '',
          ':p'=> $logo,
          ':w'=> ($_POST['website'] ?? null) ?: null,
          ':o'=> (int)($_POST['sort_order'] ?? 100),
          ':id'=> $id,
        ]);
      } else {
        $stmt = $pdo->prepare("UPDATE partner_partners
                                  SET name=:n, website=:w, sort_order=:o
                                WHERE id=:id");
        $stmt->execute([
          ':n'=> $_POST['name'] ?? '',
          ':w'=> ($_POST['website'] ?? null) ?: null,
          ':o'=> (int)($_POST['sort_order'] ?? 100),
          ':id'=> $id,
        ]);
      }
      $flash['ok'][] = 'Partner updated.';
      break;

    case 'partner_delete':
      $stmt = $pdo->prepare("DELETE FROM partner_partners WHERE id=:id");
      $stmt->execute([':id'=>(int)$_POST['id']]);
      $flash['ok'][] = 'Partner deleted.';
      break;
  }

} catch (Throwable $e) {
  $flash['err'][] = $e->getMessage();
}

$market_intro     = $pdo->query("SELECT `value` FROM partner_content WHERE `key`='market_intro'")->fetchColumn() ?: '';
$invest_intro     = $pdo->query("SELECT `value` FROM partner_content WHERE `key`='invest_intro'")->fetchColumn() ?: '';
$invest_result    = $pdo->query("SELECT `value` FROM partner_content WHERE `key`='invest_result'")->fetchColumn() ?: '';
$vision_intro     = $pdo->query("SELECT `value` FROM partner_content WHERE `key`='vision_intro'")->fetchColumn() ?: '';
$community_intro  = $pdo->query("SELECT `value` FROM partner_content WHERE `key`='community_intro'")->fetchColumn() ?: '';
$why_we_do_it     = $pdo->query("SELECT `value` FROM partner_content WHERE `key`='why_we_do_it'")->fetchColumn() ?: '';
$market_image     = $pdo->query("SELECT `value` FROM partner_content WHERE `key`='market_image'")->fetchColumn() ?: 'asset/Building.jpg';

$market   = $pdo->query("SELECT * FROM partner_market_bullets ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
$invest   = $pdo->query("SELECT * FROM partner_invest_items ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
$partners = $pdo->query("SELECT * FROM partner_partners ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin — Partners</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="asset/css/adminPartner.css"><!-- reuse your admin CSS -->
</head>
<body>
<div class="admin-shell">
  <aside class="admin-aside">
    <div class="aside-user">
      <div class="flex-grow-1">
        <div class="name"><?= h($userName) ?></div>
        <small class="muted"><?= h($userMail) ?></small>
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
      <h2 class="section-title"><i class="bi bi-image"></i> Market Image</h2>
      <div style="display:flex;gap:16px;align-items:flex-start;flex-wrap:wrap">
        <img class="img-prev" src="<?=h($market_image)?>" alt="Market image preview">
        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="action" value="market_image_upload">
          <label class="form-label">Replace image (png/jpg/webp/gif, ≤ 8MB)</label>
          <input class="form-control" type="file" name="market_image" accept="image/*" required>
          <div style="margin-top:10px">
            <button class="btn btn-dark"><i class="bi bi-upload"></i> Upload & Save</button>
          </div>
          <div class="muted" style="margin-top:8px">Current path: <?=h($market_image)?></div>
        </form>
      </div>
    </div>

    <div class="main-card">
      <h2 class="section-title"><i class="bi bi-text-left"></i> Section Copy</h2>
      <form method="post" class="grid-2">
        <input type="hidden" name="action" value="save_content">

        <div>
          <label class="form-label">Market Intro</label>
          <textarea name="market_intro" class="form-control" rows="5"><?=h($market_intro)?></textarea>
        </div>

        <div>
          <label class="form-label">Vision Intro</label>
          <textarea name="vision_intro" class="form-control" rows="5"><?=h($vision_intro)?></textarea>
        </div>

        <div>
          <label class="form-label">Invest Intro</label>
          <textarea name="invest_intro" class="form-control" rows="4"><?=h($invest_intro)?></textarea>
        </div>

        <div>
          <label class="form-label">Invest “Result” line</label>
          <textarea name="invest_result" class="form-control" rows="4"><?=h($invest_result)?></textarea>
        </div>

        <div>
          <label class="form-label">Community Involvement (intro)</label>
          <textarea name="community_intro" class="form-control" rows="6"><?=h($community_intro)?></textarea>
        </div>

        <div>
          <label class="form-label">Why We Do It (paragraph)</label>
          <textarea name="why_we_do_it" class="form-control" rows="6"><?=h($why_we_do_it)?></textarea>
        </div>

        <div class="col-12">
          <button class="btn btn-dark"><i class="bi bi-save"></i> Save Content</button>
        </div>
      </form>
    </div>

    <div class="main-card">
      <h2 class="section-title"><i class="bi bi-geo"></i> Why This Market? (bullets)</h2>

      <form method="post" class="row g-3" style="margin-bottom:10px">
        <input type="hidden" name="action" value="market_create">
        <div class="col-md-6">
          <label class="form-label">Title</label>
          <input class="form-control" name="title" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Order</label>
          <input class="form-control" name="sort_order" type="number" value="100">
        </div>
        <div class="col-12">
          <label class="form-label">Body</label>
          <textarea class="form-control" name="body" rows="3" required></textarea>
        </div>
        <div class="col-12">
          <button class="btn btn-dark"><i class="bi bi-plus-circle"></i> Add Bullet</button>
        </div>
      </form>

      <table class="tbl">
        <thead><tr><th style="width:24%">Title</th><th>Body</th><th style="width:12%">Order</th><th style="width:16%">Actions</th></tr></thead>
        <tbody>
        <?php foreach($market as $m): ?>
          <tr>
            <form method="post">
              <td><input class="form-control" name="title" value="<?=h($m['title'])?>"></td>
              <td><textarea class="form-control" name="body" rows="2"><?=h($m['body'])?></textarea></td>
              <td><input class="form-control" type="number" name="sort_order" value="<?=h($m['sort_order'])?>"></td>
              <td class="acts">
                <input type="hidden" name="id" value="<?=$m['id']?>">
                <button name="action" value="market_update" class="btn btn-outline-secondary"><i class="bi bi-save"></i> Save</button>
                <button name="action" value="market_delete" class="btn btn-outline-secondary" onclick="return confirm('Delete bullet?')"><i class="bi bi-trash"></i></button>
              </td>
            </form>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="main-card">
      <h2 class="section-title"><i class="bi bi-diagram-3"></i> Our Investment Plan (accordion)</h2>

      <form method="post" class="row g-3" style="margin-bottom:10px">
        <input type="hidden" name="action" value="invest_create">
        <div class="col-md-6">
          <label class="form-label">Heading</label>
          <input class="form-control" name="heading" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Order</label>
          <input class="form-control" name="sort_order" type="number" value="100">
        </div>
        <div class="col-12">
          <label class="form-label">Body</label>
          <textarea class="form-control" name="body" rows="3" required></textarea>
        </div>
        <div class="col-12">
          <button class="btn btn-dark"><i class="bi bi-plus-circle"></i> Add Item</button>
        </div>
      </form>

      <table class="tbl">
        <thead><tr><th style="width:24%">Heading</th><th>Body</th><th style="width:12%">Order</th><th style="width:16%">Actions</th></tr></thead>
        <tbody>
        <?php foreach($invest as $it): ?>
          <tr>
            <form method="post">
              <td><input class="form-control" name="heading" value="<?=h($it['heading'])?>"></td>
              <td><textarea class="form-control" name="body" rows="2"><?=h($it['body'])?></textarea></td>
              <td><input class="form-control" type="number" name="sort_order" value="<?=h($it['sort_order'])?>"></td>
              <td class="acts">
                <input type="hidden" name="id" value="<?=$it['id']?>">
                <button name="action" value="invest_update" class="btn btn-outline-secondary"><i class="bi bi-save"></i> Save</button>
                <button name="action" value="invest_delete" class="btn btn-outline-secondary" onclick="return confirm('Delete item?')"><i class="bi bi-trash"></i></button>
              </td>
            </form>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="main-card">
      <h2 class="section-title"><i class="bi bi-people"></i> Our Company Partners (logos)</h2>

      <form method="post" enctype="multipart/form-data" class="row g-3" style="margin-bottom:10px">
        <input type="hidden" name="action" value="partner_create">
        <div class="col-md-4">
          <label class="form-label">Name</label>
          <input class="form-control" name="name" required>
        </div>
        <div class="col-md-4">
          <label class="form-label ">Website (optional)</label>
          <input class="form-control" name="website" placeholder="https://…">
        </div>
        <div class="col-md-2">
          <label class="form-label">Order</label>
          <input class="form-control" type="number" name="sort_order" value="100">
        </div>
        <div class="col-md-6">
          <label class="form-label">Logo (png/jpg/webp/gif)</label>
          <input class="form-control" type="file" accept="image/*" name="logo" required>
        </div>
        <div class="col-12">
          <button class="btn btn-dark"><i class="bi bi-plus-circle"></i> Add Partner</button>
        </div>
      </form>

      <table class="tbl">
        <thead><tr><th style="width:34%">Partner</th><th>Logo</th><th style="width:14%">Order</th><th style="width:18%">Actions</th></tr></thead>
        <tbody>
        <?php foreach($partners as $p): ?>
          <tr>
            <form method="post" enctype="multipart/form-data">
              <td>
                <div style="display:flex;gap:8px;align-items:center">
                  <input class="form-control" name="name" value="<?=h($p['name'])?>">
                  <input class="form-control" name="website" value="<?=h($p['website'])?>" placeholder="https://…">
                </div>
              </td>
              <td>
                <div style="display:flex;gap:10px;align-items:center">
                  <img src="<?=h($p['logo_path'])?>" alt="" style="width:80px;height:48px;object-fit:contain;border:1px solid #eee;border-radius:8px;background:#fff">
                  <input class="form-control" type="file" accept="image/*" name="logo">
                </div>
              </td>
              <td><input class="form-control" type="number" name="sort_order" value="<?=h($p['sort_order'])?>"></td>
              <td class="acts">
                <input type="hidden" name="id" value="<?=$p['id']?>">
                <button name="action" value="partner_update" class="btn btn-outline-secondary"><i class="bi bi-save"></i> Save</button>
                <button name="action" value="partner_delete" class="btn btn-outline-secondary" onclick="return confirm('Delete partner?')"><i class="bi bi-trash"></i></button>
              </td>
            </form>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </main>
</div>
</body>
</html>
