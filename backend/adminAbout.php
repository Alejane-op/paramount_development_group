<?php

session_start();
require __DIR__ . '/db.php'; 

if ($pdo instanceof PDO) {
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function post($k, $d=null){ return $_POST[$k] ?? $d; }
function csrf_token(): string {
  if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
  return $_SESSION['csrf'];
}
function csrf_check(): void {
  if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) {
    http_response_code(400);
    exit('Invalid CSRF token');
  }
}
function cget(PDO $pdo, string $key, string $default=''): string {
  $stmt = $pdo->prepare('SELECT cvalue FROM about_content WHERE ckey=?');
  $stmt->execute([$key]);
  $v = $stmt->fetchColumn();
  return ($v!==false) ? (string)$v : $default;
}
function cset(PDO $pdo, string $key, string $val): void {
  $stmt = $pdo->prepare('INSERT INTO about_content (ckey, cvalue) VALUES (?,?)
                         ON DUPLICATE KEY UPDATE cvalue=VALUES(cvalue)');
  $stmt->execute([$key,$val]);
}

$userName = $_SESSION['name'] ?? 'Admin';
$reqPath  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '';
$self     = basename($reqPath);
$tab      = $_GET['tab'] ?? 'hero';

function alFile($url, $label, $icon){
  global $self, $tab;
  $urlPath  = basename(parse_url($url, PHP_URL_PATH));
  $isActive = ($self === $urlPath);
  if ($url === 'admin.php?tab=settings') $isActive = ($self==='admin.php' && ($tab==='settings'));
  $cls = $isActive ? 'aside-link active' : 'aside-link';
  echo '<a class="'.$cls.'" href="'.$url.'"><i class="bi '.$icon.'"></i><span>'.$label.'</span></a>';
}

$flash = ['ok'=>[], 'err'=>[]];

if ($_SERVER['REQUEST_METHOD']==='POST') {
  csrf_check();
  $action = post('action','');

  try {
    if ($action === 'save_hero_texts') {
      cset($pdo, 'hero_title', post('hero_title',''));
      cset($pdo, 'hero_paragraph', post('hero_paragraph',''));
      $flash['ok'][] = 'Hero texts updated.';
    }
    elseif ($action === 'upload_hero_video') {
      if (!empty($_FILES['hero_video']['name']) && is_uploaded_file($_FILES['hero_video']['tmp_name'])) {
        $ext = strtolower(pathinfo($_FILES['hero_video']['name'], PATHINFO_EXTENSION));
        $safe = 'hero_'.date('Ymd_His').'.'.$ext;
        $destDir = $_SERVER['DOCUMENT_ROOT'].'/uploads';
        if (!is_dir($destDir)) mkdir($destDir, 0775, true);
        $dest = $destDir.'/'.$safe;
        if (!move_uploaded_file($_FILES['hero_video']['tmp_name'], $dest)) {
          throw new RuntimeException('Upload failed.');
        }
        cset($pdo, 'hero_video', '/uploads/'.$safe);
        $flash['ok'][] = 'Hero video uploaded.';
      } else {
        $flash['err'][] = 'No video selected.';
      }
    }
    elseif ($action === 'save_company_history') {
      cset($pdo, 'history_20y_title', post('h20t',''));
      cset($pdo, 'history_20y_body',  post('h20b',''));
      cset($pdo, 'history_15y_title', post('h15t',''));
      cset($pdo, 'history_15y_body',  post('h15b',''));
      cset($pdo, 'history_trade_title', post('htrt',''));
      cset($pdo, 'history_trade_body',  post('htrb',''));
      cset($pdo, 'history_comm_title', post('hct',''));
      cset($pdo, 'history_comm_body',  post('hcb',''));
      $flash['ok'][] = 'Company history updated.';
    }

    elseif ($action === 'milestone_add') {
      $stmt = $pdo->prepare('INSERT INTO about_milestones (body,sort) VALUES (?,?)');
      $stmt->execute([post('body',''), (int)post('sort',0)]);
      $flash['ok'][] = 'Milestone added.';
    } elseif ($action === 'milestone_update') {
      $stmt = $pdo->prepare('UPDATE about_milestones SET body=?, sort=? WHERE id=?');
      $stmt->execute([post('body',''), (int)post('sort',0), (int)post('id',0)]);
      $flash['ok'][] = 'Milestone updated.';
    } elseif ($action === 'milestone_delete') {
      $stmt = $pdo->prepare('DELETE FROM about_milestones WHERE id=?');
      $stmt->execute([(int)post('id',0)]);
      $flash['ok'][] = 'Milestone deleted.';
    }

    elseif ($action === 'goal_add') {
      $stmt = $pdo->prepare('INSERT INTO about_goals (title,body,sort) VALUES (?,?,?)');
      $stmt->execute([post('title',''), post('body',''), (int)post('sort',0)]);
      $flash['ok'][] = 'Goal added.';
    } elseif ($action === 'goal_update') {
      $stmt = $pdo->prepare('UPDATE about_goals SET title=?, body=?, sort=? WHERE id=?');
      $stmt->execute([post('title',''), post('body',''), (int)post('sort',0), (int)post('id',0)]);
      $flash['ok'][] = 'Goal updated.';
    } elseif ($action === 'goal_delete') {
      $stmt = $pdo->prepare('DELETE FROM about_goals WHERE id=?');
      $stmt->execute([(int)post('id',0)]);
      $flash['ok'][] = 'Goal deleted.';
    }

    elseif ($action === 'save_vision') {
      cset($pdo, 'vision_body', post('vision_body',''));
      $flash['ok'][] = 'Vision updated.';
    }

    elseif ($action === 'value_add') {
      $stmt = $pdo->prepare('INSERT INTO about_values (icon,title,body,sort) VALUES (?,?,?,?)');
      $stmt->execute([post('icon',''), post('title',''), post('body',''), (int)post('sort',0)]);
      $flash['ok'][] = 'Value added.';
    } elseif ($action === 'value_update') {
      $stmt = $pdo->prepare('UPDATE about_values SET icon=?, title=?, body=?, sort=? WHERE id=?');
      $stmt->execute([post('icon',''), post('title',''), post('body',''), (int)post('sort',0), (int)post('id',0)]);
      $flash['ok'][] = 'Value updated.';
    } elseif ($action === 'value_delete') {
      $stmt = $pdo->prepare('DELETE FROM about_values WHERE id=?');
      $stmt->execute([(int)post('id',0)]);
      $flash['ok'][] = 'Value deleted.';
    }

    elseif ($action === 'strategy_add') {
      $stmt = $pdo->prepare('INSERT INTO about_strategy (title,body,sort) VALUES (?,?,?)');
      $stmt->execute([post('title',''), post('body',''), (int)post('sort',0)]);
      $flash['ok'][] = 'Strategy item added.';
    } elseif ($action === 'strategy_update') {
      $stmt = $pdo->prepare('UPDATE about_strategy SET title=?, body=?, sort=? WHERE id=?');
      $stmt->execute([post('title',''), post('body',''), (int)post('sort',0), (int)post('id',0)]);
      $flash['ok'][] = 'Strategy item updated.';
    } elseif ($action === 'strategy_delete') {
      $stmt = $pdo->prepare('DELETE FROM about_strategy WHERE id=?');
      $stmt->execute([(int)post('id',0)]);
      $flash['ok'][] = 'Strategy item deleted.';
    }

    elseif ($action === 'save_commitment') {
      cset($pdo, 'commitment_body', post('commitment_body',''));
      $flash['ok'][] = 'Commitment updated.';
    } elseif ($action === 'save_team_intro') {
      cset($pdo, 'team_title', post('team_title',''));
      cset($pdo, 'team_subtitle', post('team_subtitle',''));
      $flash['ok'][] = 'Team intro updated.';
    }
  } catch (Throwable $e) {
    $flash['err'][] = $e->getMessage();
  }
}


$heroVideo = cget($pdo, 'hero_video', 'asset/bg-vid.mp4');
$heroTitle = cget($pdo, 'hero_title', 'Our Paramount<br>Development Background');
$heroPara  = cget($pdo, 'hero_paragraph', '');

$h20t = cget($pdo,'history_20y_title','20+ Years in Construction');
$h20b = cget($pdo,'history_20y_body','');
$h15t = cget($pdo,'history_15y_title','15+ Years of Real Estate Investments');
$h15b = cget($pdo,'history_15y_body','');
$htrt = cget($pdo,'history_trade_title','Specialized Trade Expertise');
$htrb = cget($pdo,'history_trade_body','');
$hct  = cget($pdo,'history_comm_title','Community Ties');
$hcb  = cget($pdo,'history_comm_body','');

$vision = cget($pdo,'vision_body','');
$commitment = cget($pdo,'commitment_body','');
$teamTitle = cget($pdo,'team_title','At Paramount Development');
$teamSub   = cget($pdo,'team_subtitle','');

$milestones = $pdo->query('SELECT * FROM about_milestones ORDER BY sort ASC, id ASC')->fetchAll();
$goals      = $pdo->query('SELECT * FROM about_goals ORDER BY sort ASC, id ASC')->fetchAll();
$values     = $pdo->query('SELECT * FROM about_values ORDER BY sort ASC, id ASC')->fetchAll();
$strategy   = $pdo->query('SELECT * FROM about_strategy ORDER BY sort ASC, id ASC')->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin — About</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="asset/css/adminAbout.css">
<style>
.tabs{display:flex;gap:8px;flex-wrap:wrap;margin:0 0 14px}
.tab{padding:8px 12px;border:1px solid #ddd;border-radius:8px;background:#fff;text-decoration:none;color:#222}
.tab.active{background:#ecf0e7;border-color:#dfe7d6}
.table{width:100%;border-collapse:collapse;margin-top:10px}
.table th,.table td{border:1px solid #eee;padding:8px;text-align:left;vertical-align:top}
.small{font-size:12px;color:#777}
.alert{padding:10px 12px;border-radius:8px;margin:10px 0}
.alert-success{border:1px solid #dfe7d6;background:#f3f7ef}
.alert-danger{border:1px solid #f0d6d6;background:#fbeeee}
textarea{min-height:110px}
</style>
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
      <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?=h($m)?></div>
    <?php endforeach; ?>
    <?php foreach($flash['err'] as $m): ?>
      <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?=h($m)?></div>
    <?php endforeach; ?>

    <div class="tabs">
      <?php
        $tabs = [
          'hero' => 'Hero',
          'history' => 'Company History',
          'milestones' => 'Milestones',
          'goals' => 'Goals',
          'vision' => 'Vision',
          'values' => 'Core Values',
          'strategy' => 'Strategy',
          'commitment' => 'Commitment',
          'team' => 'Team Intro'
        ];
        foreach($tabs as $k=>$label){
          $cls = ($tab===$k)?'tab active':'tab';
          echo '<a class="'.$cls.'" href="?tab='.$k.'">'.$label.'</a>';
        }
      ?>
    </div>

    <?php if ($tab==='hero'): ?>
      <div class="main-card">
        <h2>Overlay Title</h2>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
          <input type="hidden" name="action" value="save_hero_texts">
          <div class="field">
            <label>Title</label>
            <input type="text" name="hero_title" value="<?= h($heroTitle) ?>">
          </div>
          <div class="field">
            <label>Paragraph</label>
            <textarea name="hero_paragraph"><?= h($heroPara) ?></textarea>
          </div>
          <button class="btn primary"><i class="bi bi-save"></i> Save</button>
        </form>
      </div>
    <?php endif; ?>

    <?php if ($tab==='history'): ?>
      <div class="main-card">
        <h2>Company History & Experience</h2>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
          <input type="hidden" name="action" value="save_company_history">

          <div class="field">
            <label>Years in Construction</label>
            <input name="h20t" value="<?= h($h20t) ?>">
          </div>
          <div class="field">
            <label>Years in Construction Content</label>
            <textarea name="h20b"><?= h($h20b) ?></textarea>
          </div>

          <div class="field">
            <label>Years of Real Estate Investments</label>
            <input name="h15t" value="<?= h($h15t) ?>">
          </div>
          <div class="field">
            <label> Years of Real Estate Investments Content</label>
            <textarea name="h15b"><?= h($h15b) ?></textarea>
          </div>

          <div class="field">
            <label>Trade</label>
            <input name="htrt" value="<?= h($htrt) ?>">
          </div>
          <div class="field">
            <label>Trade Content</label>
            <textarea name="htrb"><?= h($htrb) ?></textarea>
          </div>

          <div class="field">
            <label>Community</label>
            <input name="hct" value="<?= h($hct) ?>">
          </div>
          <div class="field">
            <label>Community Content</label>
            <textarea name="hcb"><?= h($hcb) ?></textarea>
          </div>

          <button class="btn primary"><i class="bi bi-save"></i> Save</button>
        </form>
      </div>
    <?php endif; ?>

    <?php if ($tab==='milestones'): ?>
      <div class="main-card">
        <h2>Milestones</h2>
        <table class="table">
          <thead><tr><th style="width:60px">Sort</th><th>Text</th><th style="width:180px">Actions</th></tr></thead>
          <tbody>
          <?php foreach($milestones as $m): ?>
            <tr>
              <td><?= (int)$m['sort'] ?></td>
              <td><?= nl2br(h($m['body'])) ?></td>
              <td>
                <form method="post" style="display:inline-block">
                  <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                  <input type="hidden" name="action" value="milestone_delete">
                  <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                  <button class="btn" onclick="return confirm('Delete this milestone?')"><i class="bi bi-trash"></i> Delete</button>
                </form>
                <details style="display:inline-block;margin-left:6px">
                  <summary class="btn"><i class="bi bi-pencil-square"></i> Edit</summary>
                  <form method="post" style="margin-top:8px">
                    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="action" value="milestone_update">
                    <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                    <div class="field"><label>Sort</label><input type="number" name="sort" value="<?= (int)$m['sort'] ?>"></div>
                    <div class="field"><label>Body</label><textarea name="body"><?= h($m['body']) ?></textarea></div>
                    <button class="btn primary"><i class="bi bi-save"></i> Save</button>
                  </form>
                </details>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>

        <h3 style="margin-top:16px">Add Milestone</h3>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
          <input type="hidden" name="action" value="milestone_add">
          <div class="field"><label>Sort</label><input type="number" name="sort" value="0"></div>
          <div class="field"><label>Body</label><textarea name="body"></textarea></div>
          <button class="btn primary"><i class="bi bi-plus-circle"></i> Add</button>
        </form>
      </div>
    <?php endif; ?>

    <?php if ($tab==='goals'): ?>
      <div class="main-card">
        <h2>Goals</h2>
        <table class="table">
          <thead><tr><th style="width:60px">Sort</th><th>Title</th><th>Body</th><th style="width:200px">Actions</th></tr></thead>
          <tbody>
          <?php foreach($goals as $g): ?>
            <tr>
              <td><?= (int)$g['sort'] ?></td>
              <td><?= h($g['title']) ?></td>
              <td><?= nl2br(h($g['body'])) ?></td>
              <td>
                <form method="post" style="display:inline-block">
                  <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                  <input type="hidden" name="action" value="goal_delete">
                  <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
                  <button class="btn" onclick="return confirm('Delete this goal?')"><i class="bi bi-trash"></i> Delete</button>
                </form>
                <details style="display:inline-block;margin-left:6px">
                  <summary class="btn"><i class="bi bi-pencil-square"></i> Edit</summary>
                  <form method="post" style="margin-top:8px">
                    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="action" value="goal_update">
                    <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
                    <div class="field"><label>Sort</label><input type="number" name="sort" value="<?= (int)$g['sort'] ?>"></div>
                    <div class="field"><label>Title</label><input name="title" value="<?= h($g['title']) ?>"></div>
                    <div class="field"><label>Body</label><textarea name="body"><?= h($g['body']) ?></textarea></div>
                    <button class="btn primary"><i class="bi bi-save"></i> Save</button>
                  </form>
                </details>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>

        <h3 style="margin-top:16px">Add Goal</h3>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
          <input type="hidden" name="action" value="goal_add">
          <div class="field"><label>Sort</label><input type="number" name="sort" value="0"></div>
          <div class="field"><label>Title</label><input name="title"></div>
          <div class="field"><label>Body</label><textarea name="body"></textarea></div>
          <button class="btn primary"><i class="bi bi-plus-circle"></i> Add</button>
        </form>
      </div>
    <?php endif; ?>

    <?php if ($tab==='vision'): ?>
      <div class="main-card">
        <h2>Vision</h2>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
          <input type="hidden" name="action" value="save_vision">
          <div class="field"><label>Vision (paragraph)</label><textarea name="vision_body"><?= h($vision) ?></textarea></div>
          <button class="btn primary"><i class="bi bi-save"></i> Save</button>
        </form>
      </div>
    <?php endif; ?>

    <?php if ($tab==='values'): ?>
      <div class="main-card">
        <h2>Core Values</h2>
        <table class="table">
          <thead><tr><th style="width:60px">Sort</th><th>Icon class</th><th>Title</th><th>Body</th><th style="width:220px">Actions</th></tr></thead>
          <tbody>
          <?php foreach($values as $v): ?>
            <tr>
              <td><?= (int)$v['sort'] ?></td>
              <td><code><?= h($v['icon']) ?></code></td>
              <td><?= h($v['title']) ?></td>
              <td><?= nl2br(h($v['body'])) ?></td>
              <td>
                <form method="post" style="display:inline-block">
                  <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                  <input type="hidden" name="action" value="value_delete">
                  <input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
                  <button class="btn" onclick="return confirm('Delete this value?')"><i class="bi bi-trash"></i> Delete</button>
                </form>
                <details style="display:inline-block;margin-left:6px">
                  <summary class="btn"><i class="bi bi-pencil-square"></i> Edit</summary>
                  <form method="post" style="margin-top:8px">
                    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="action" value="value_update">
                    <input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
                    <div class="field"><label>Sort</label><input type="number" name="sort" value="<?= (int)$v['sort'] ?>"></div>
                    <div class="field"><label>Icon class</label><input name="icon" value="<?= h($v['icon']) ?>"></div>
                    <div class="field"><label>Title</label><input name="title" value="<?= h($v['title']) ?>"></div>
                    <div class="field"><label>Body</label><textarea name="body"><?= h($v['body']) ?></textarea></div>
                    <button class="btn primary"><i class="bi bi-save"></i> Save</button>
                  </form>
                </details>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>

        <h3 style="margin-top:16px">Add Core Value</h3>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
          <input type="hidden" name="action" value="value_add">
          <div class="field"><label>Sort</label><input type="number" name="sort" value="0"></div>
          <div class="field"><label>Icon class (FontAwesome) [Note: Do Not Edit]</label><input name="icon" placeholder="fa-solid fa-user-shield"></div>
          <div class="field"><label>Title</label><input name="title"></div>
          <div class="field"><label>Body</label><textarea name="body"></textarea></div>
          <button class="btn primary"><i class="bi bi-plus-circle"></i> Add</button>
        </form>
      </div>
    <?php endif; ?>

    <?php if ($tab==='strategy'): ?>
      <div class="main-card">
        <h2>Strategy Timeline</h2>
        <table class="table">
          <thead><tr><th style="width:60px">Sort</th><th>Title</th><th>Body</th><th style="width:220px">Actions</th></tr></thead>
          <tbody>
          <?php foreach($strategy as $s): ?>
            <tr>
              <td><?= (int)$s['sort'] ?></td>
              <td><?= h($s['title']) ?></td>
              <td><?= nl2br(h($s['body'])) ?></td>
              <td>
                <form method="post" style="display:inline-block">
                  <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                  <input type="hidden" name="action" value="strategy_delete">
                  <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                  <button class="btn" onclick="return confirm('Delete this item?')"><i class="bi bi-trash"></i> Delete</button>
                </form>
                <details style="display:inline-block;margin-left:6px">
                  <summary class="btn"><i class="bi bi-pencil-square"></i> Edit</summary>
                  <form method="post" style="margin-top:8px">
                    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="action" value="strategy_update">
                    <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                    <div class="field"><label>Sort</label><input type="number" name="sort" value="<?= (int)$s['sort'] ?>"></div>
                    <div class="field"><label>Title</label><input name="title" value="<?= h($s['title']) ?>"></div>
                    <div class="field"><label>Body</label><textarea name="body"><?= h($s['body']) ?></textarea></div>
                    <button class="btn primary"><i class="bi bi-save"></i> Save</button>
                  </form>
                </details>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>

        <h3 style="margin-top:16px">Add Strategy Item</h3>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
          <input type="hidden" name="action" value="strategy_add">
          <div class="field"><label>Sort</label><input type="number" name="sort" value="0"></div>
          <div class="field"><label>Title</label><input name="title"></div>
          <div class="field"><label>Body</label><textarea name="body"></textarea></div>
          <button class="btn primary"><i class="bi bi-plus-circle"></i> Add</button>
        </form>
      </div>
    <?php endif; ?>

    <?php if ($tab==='commitment'): ?>
      <div class="main-card">
        <h2>Our Commitment</h2>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
          <input type="hidden" name="action" value="save_commitment">
          <div class="field"><label>Commitment (paragraph)</label><textarea name="commitment_body"><?= h($commitment) ?></textarea></div>
          <button class="btn primary"><i class="bi bi-save"></i> Save</button>
        </form>
      </div>
    <?php endif; ?>

    <?php if ($tab==='team'): ?>
      <div class="main-card">
        <h2>Our Team</h2>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
          <input type="hidden" name="action" value="save_team_intro">
          <div class="field"><label>Title</label><input name="team_title" value="<?= h($teamTitle) ?>"></div>
          <div class="field"><label>Paragraph</label><textarea name="team_subtitle"><?= h($teamSub) ?></textarea></div>
          <div class="actions">
            <button class="btn primary"><i class="bi bi-save"></i> Save</button>
          </div>
        </form>
      </div>
    <?php endif; ?>

  </main>
</div>
</body>
</html>
