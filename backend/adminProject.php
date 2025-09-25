<?php

session_start();
require __DIR__ . '/db.php'; 

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if ($pdo instanceof PDO) {
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
$userName = $_SESSION['name'] ?? 'Admin';


function flash_set($type, $msg){ $_SESSION['flash_'.$type] = $msg; }
function flash_get($type){
  if (!empty($_SESSION['flash_'.$type])) {
    $m = $_SESSION['flash_'.$type];
    unset($_SESSION['flash_'.$type]);
    return $m;
  }
  return null;
}

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

function slugify($text){
  $text = preg_replace('~[^\pL\d]+~u', '-', $text);
  if (function_exists('iconv')) $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
  $text = preg_replace('~[^-\w]+~', '', $text);
  $text = trim($text, '-');
  $text = preg_replace('~-+~', '-', $text);
  $text = strtolower($text);
  return $text ?: substr(sha1(uniqid('',true)),0,8);
}

function ensure_unique_slug(PDO $pdo, string $base, ?int $ignoreId = null): string {
  $slug = $base; $i = 2;
  $stmt = $ignoreId
    ? $pdo->prepare("SELECT COUNT(*) FROM `projects` WHERE `slug`=? AND `id`<>?")
    : $pdo->prepare("SELECT COUNT(*) FROM `projects` WHERE `slug`=?");
  while (true) {
    $ignoreId ? $stmt->execute([$slug, $ignoreId]) : $stmt->execute([$slug]);
    if ($stmt->fetchColumn() == 0) return $slug;
    $slug = $base . '-' . $i++;
  }
}

function save_upload($field){
  if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;
  if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;

  $okExt = ['jpg','jpeg','png','webp'];
  $name  = $_FILES[$field]['name'] ?? '';
  $tmp   = $_FILES[$field]['tmp_name'] ?? '';
  if (!$name || !$tmp) return null;

  $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
  if (!in_array($ext, $okExt, true)) return null;

  $docRoot    = rtrim($_SERVER['DOCUMENT_ROOT'] ?? __DIR__, DIRECTORY_SEPARATOR);
  $uploadsUrl = '/uploads';
  $uploadsFs  = $docRoot . $uploadsUrl;
  if (!is_dir($uploadsFs)) { @mkdir($uploadsFs, 0775, true); }

  $basename = date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
  $fsPath   = $uploadsFs . DIRECTORY_SEPARATOR . $basename;
  if (!@move_uploaded_file($tmp, $fsPath)) return null;
  @chmod($fsPath, 0644);

  return $uploadsUrl . '/' . $basename;
}

function fs_path_from_url(string $urlPath): ?string {
  if ($urlPath === '' || $urlPath[0] !== '/') return null;
  $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? __DIR__, DIRECTORY_SEPARATOR);
  return $docRoot . $urlPath;
}

$TYPES = ['All','Residential','Commercial','Mixed-Use','Multifamily'];
$MAX_GALLERY = 7;

$errors = [];
$notice = flash_get('ok') ?: null;
$warn   = flash_get('warn') ?: null;

if (($_POST['action'] ?? '') === 'save_hero') {
  $raw_title     = $_POST['hero_title'] ?? '';
  $hero_title    = trim(preg_replace('/\s+/', ' ', strip_tags($raw_title)));
  $hero_subtitle = trim($_POST['hero_subtitle'] ?? '');
  try {
    $pdo->prepare("INSERT INTO `project_content`(`key`,`value`) VALUES('projects_hero_title',?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)")->execute([$hero_title]);
    $pdo->prepare("INSERT INTO `project_content`(`key`,`value`) VALUES('projects_hero_subtitle',?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)")->execute([$hero_subtitle]);
    flash_set('ok','Projects hero updated.');
    header("Location: adminProject.php"); exit;
  } catch (Throwable $e) { $errors[] = "Database error (hero): " . $e->getMessage(); }
}

if (($_POST['action'] ?? '') === 'save_project') {
  $id        = isset($_POST['id']) ? (int)$_POST['id'] : 0;
  $title     = trim($_POST['title'] ?? '');
  $slug_in   = trim($_POST['slug'] ?? '');
  $location  = trim($_POST['location'] ?? '');

  $selectedTypes = isset($_POST['type']) && is_array($_POST['type'])
    ? array_values(array_intersect($_POST['type'], array_slice($TYPES,1)))
    : [];
  if (!$selectedTypes) $errors[] = "Select at least one Type of Development.";
  $type      = implode(',', $selectedTypes);

  $meta      = trim($_POST['meta'] ?? '');
  $shortDesc = trim($_POST['short_desc'] ?? '');
  $isFeat    = isset($_POST['is_featured']) ? 1 : 0;

  if ($title === '') $errors[] = "Title is required.";

  $slugBase = $slug_in !== '' ? slugify($slug_in) : slugify($title);
  $slug     = ensure_unique_slug($pdo, $slugBase, $id ?: null);

  try {
    if ($id > 0) {
      $st = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE LOWER(title)=LOWER(?) AND LOWER(location)=LOWER(?) AND id<>?");
      $st->execute([$title,$location,$id]);
    } else {
      $st = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE LOWER(title)=LOWER(?) AND LOWER(location)=LOWER(?)");
      $st->execute([$title,$location]);
    }
    if ($st->fetchColumn() > 0) $errors[] = "A project with the same Title and Location already exists.";
  } catch (Throwable $e) { $errors[] = "Duplicate check failed: ".$e->getMessage(); }

  $cover = save_upload('cover_image');
  $oldCover = null;
  if ($id > 0 && $cover) {
    $q = $pdo->prepare("SELECT cover_image FROM projects WHERE id=?");
    $q->execute([$id]);
    $oldCover = $q->fetchColumn() ?: null;
  }

  $incomingIdxs  = [];
  foreach ((array)($_FILES['gallery']['name'] ?? []) as $i => $n) {
    if ((string)$n !== '') $incomingIdxs[] = $i;
  }

  $existingCount = 0;
  if ($id > 0) {
    $existingCount = (int)$pdo->query("SELECT COUNT(*) FROM project_images WHERE project_id=".$id)->fetchColumn();
  }
  if ($incomingIdxs) {
    $slots = max(0, $MAX_GALLERY - $existingCount);
    if ($id > 0 && count($incomingIdxs) > $slots) {
      $incomingIdxs = array_slice($incomingIdxs, 0, $slots);
      if ($slots === 0) $warn = "Gallery is already full (max {$MAX_GALLERY}). New images ignored.";
      else $warn = "Only {$slots} new gallery image(s) saved (max {$MAX_GALLERY}). Extras ignored.";
    } elseif ($id === 0 && count($incomingIdxs) > $MAX_GALLERY) {
      $incomingIdxs = array_slice($incomingIdxs, 0, $MAX_GALLERY);
      $warn = "Only the first {$MAX_GALLERY} gallery images were saved. Extras ignored.";
    }
  }

  if (!$errors && $id > 0) {
    $cur = $pdo->prepare("SELECT title, slug, location, type, meta, short_desc, is_featured, cover_image FROM projects WHERE id=?");
    $cur->execute([$id]);
    $cur = $cur->fetch(PDO::FETCH_ASSOC);

    $changed = false;
    if ($cur) {
      $cmp = function($a){ return trim((string)$a); };
      if ($cmp($cur['title'])      !== $cmp($title))      $changed = true;
      if ($cmp($cur['slug'])       !== $cmp($slug))       $changed = true;
      if ($cmp($cur['location'])   !== $cmp($location))   $changed = true;
      if ($cmp($cur['type'])       !== $cmp($type))       $changed = true;
      if ($cmp($cur['meta'])       !== $cmp($meta))       $changed = true;
      if ($cmp($cur['short_desc']) !== $cmp($shortDesc))  $changed = true;
      if ((int)$cur['is_featured'] !== (int)$isFeat)       $changed = true;
      if ($cover)                                           $changed = true; 
      if (!empty($incomingIdxs))                            $changed = true; 
    }

    if (!$changed) {
      flash_set('warn','No changes to save.');
      header("Location: adminProject.php?edit=".$id);
      exit;
    }
  }

  if (!$errors) {
    try {
      if ($id > 0) {
        $sql = "UPDATE projects SET title=?, slug=?, location=?, type=?, meta=?, short_desc=?, is_featured=?";
        $params = [$title,$slug,$location,$type,$meta,$shortDesc,$isFeat];
        if ($cover) { $sql.=", cover_image=?"; $params[]=$cover; }
        $sql.=" WHERE id=?"; $params[]=$id;
        $pdo->prepare($sql)->execute($params);

        if ($cover && $oldCover && ($p = fs_path_from_url($oldCover))) @unlink($p);

        $projId = $id; $okMsg = "Project updated.";
      } else {
        $pdo->prepare("INSERT INTO projects (title,slug,location,type,meta,short_desc,cover_image,is_featured) VALUES (?,?,?,?,?,?,?,?)")
            ->execute([$title,$slug,$location,$type,$meta,$shortDesc,$cover,$isFeat]);
        $projId = (int)$pdo->lastInsertId(); $okMsg = "Project created.";
      }

      if (!empty($incomingIdxs)) {
        $ins = $pdo->prepare("INSERT INTO project_images(project_id,img_path,sort_order) VALUES (?,?,?)");
        $order = (int)$pdo->query("SELECT COALESCE(MAX(sort_order),-1) FROM project_images WHERE project_id=".$projId)->fetchColumn() + 1;
        foreach ($incomingIdxs as $idx) {
          $_FILES['__one'] = [
            'name'     => $_FILES['gallery']['name'][$idx] ?? '',
            'type'     => $_FILES['gallery']['type'][$idx] ?? '',
            'tmp_name' => $_FILES['gallery']['tmp_name'][$idx] ?? '',
            'error'    => $_FILES['gallery']['error'][$idx] ?? UPLOAD_ERR_NO_FILE,
            'size'     => $_FILES['gallery']['size'][$idx] ?? 0,
          ];
          $gurl = save_upload('__one'); unset($_FILES['__one']);
          if ($gurl) { $ins->execute([$projId,$gurl,$order++]); }
        }
      }

      if ($warn) flash_set('warn',$warn);
      flash_set('ok',$okMsg);
      $redir = ($id > 0) ? "adminProject.php?edit=".$projId : "adminProject.php";
      header("Location: $redir"); exit;

    } catch (Throwable $e) { $errors[] = "Database error (project save): " . $e->getMessage(); }
  }
}

if (($_POST['action'] ?? '') === 'delete_project' && isset($_POST['id'])) {
  $id = (int)$_POST['id'];
  try {
    $imgs = $pdo->prepare("SELECT img_path FROM project_images WHERE project_id=?");
    $imgs->execute([$id]);
    foreach ($imgs as $row) { if ($p = fs_path_from_url($row['img_path'])) @unlink($p); }
    $cov = $pdo->prepare("SELECT cover_image FROM projects WHERE id=?");
    $cov->execute([$id]); if ($c = $cov->fetchColumn()) { if ($p=fs_path_from_url($c)) @unlink($p); }

    $pdo->prepare("DELETE FROM project_images WHERE project_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM projects WHERE id=?")->execute([$id]);

    flash_set('ok','Project deleted.');
    header("Location: adminProject.php"); exit;
  } catch (Throwable $e) {
    $errors[] = "Database error (delete): " . $e->getMessage();
  }
}

if (($_POST['action'] ?? '') === 'replace_image') {
  $imgId = (int)($_POST['image_id'] ?? 0);
  $pid   = (int)($_POST['project_id'] ?? 0);
  if ($imgId && $pid) {
    try {
      $row = $pdo->prepare("SELECT img_path FROM project_images WHERE id=? AND project_id=?");
      $row->execute([$imgId,$pid]);
      if ($cur = $row->fetch(PDO::FETCH_ASSOC)) {
        $new = save_upload('new_image');
        if ($new) {
          if ($p = fs_path_from_url($cur['img_path'])) @unlink($p);
          $pdo->prepare("UPDATE project_images SET img_path=? WHERE id=?")->execute([$new,$imgId]);
          flash_set('ok','Image replaced.');
        } else {
          flash_set('warn','No valid file selected for replacement.');
        }
      }
    } catch (Throwable $e) {
      flash_set('warn','Replace failed: '.$e->getMessage());
    }
  }
  header("Location: adminProject.php?edit=".$pid); exit;
}

if (($_POST['action'] ?? '') === 'delete_image') {
  $imgId = (int)($_POST['image_id'] ?? 0);
  $pid   = (int)($_POST['project_id'] ?? 0);
  if ($imgId && $pid) {
    try {
      $row = $pdo->prepare("SELECT img_path FROM project_images WHERE id=? AND project_id=?");
      $row->execute([$imgId,$pid]);
      if ($cur = $row->fetch(PDO::FETCH_ASSOC)) {
        if ($p = fs_path_from_url($cur['img_path'])) @unlink($p);
        $pdo->prepare("DELETE FROM project_images WHERE id=?")->execute([$imgId]);
        flash_set('ok','Image deleted.');
      }
    } catch (Throwable $e) {
      flash_set('warn','Delete failed: '.$e->getMessage());
    }
  }
  header("Location: adminProject.php?edit=".$pid); exit;
}

if (($_POST['action'] ?? '') === 'add_images') {
  $pid = (int)($_POST['project_id'] ?? 0);
  if ($pid > 0) {
    try {
      $chk = $pdo->prepare("SELECT id FROM projects WHERE id=?");
      $chk->execute([$pid]);
      if (!$chk->fetchColumn()) {
        flash_set('warn','Project not found.');
        header("Location: adminProject.php"); exit;
      }

      $existingCount = (int)$pdo->query("SELECT COUNT(*) FROM project_images WHERE project_id=".$pid)->fetchColumn();
      $slots = max(0, $MAX_GALLERY - $existingCount);

      $incomingIdxs = [];
      foreach ((array)($_FILES['add_gallery']['name'] ?? []) as $i => $n) {
        if ((string)$n !== '') $incomingIdxs[] = $i;
      }

      if (!$incomingIdxs) {
        flash_set('warn','No files selected.');
        header("Location: adminProject.php?edit=".$pid); exit;
      }

      if ($slots === 0) {
        flash_set('warn','Gallery is already full (max '.$MAX_GALLERY.').');
        header("Location: adminProject.php?edit=".$pid); exit;
      }

      if (count($incomingIdxs) > $slots) {
        $incomingIdxs = array_slice($incomingIdxs, 0, $slots);
        flash_set('warn','Only '.$slots.' image(s) added (max '.$MAX_GALLERY.'). Extras ignored.');
      }

      $ins = $pdo->prepare("INSERT INTO project_images(project_id,img_path,sort_order) VALUES (?,?,?)");
      $order = (int)$pdo->query("SELECT COALESCE(MAX(sort_order),-1) FROM project_images WHERE project_id=".$pid)->fetchColumn() + 1;

      foreach ($incomingIdxs as $idx) {
        $_FILES['__one'] = [
          'name'     => $_FILES['add_gallery']['name'][$idx] ?? '',
          'type'     => $_FILES['add_gallery']['type'][$idx] ?? '',
          'tmp_name' => $_FILES['add_gallery']['tmp_name'][$idx] ?? '',
          'error'    => $_FILES['add_gallery']['error'][$idx] ?? UPLOAD_ERR_NO_FILE,
          'size'     => $_FILES['add_gallery']['size'][$idx] ?? 0,
        ];
        $gurl = save_upload('__one'); unset($_FILES['__one']);
        if ($gurl) { $ins->execute([$pid, $gurl, $order++]); }
      }

      flash_set('ok','Image(s) added.');
      header("Location: adminProject.php?edit=".$pid); exit;

    } catch (Throwable $e) {
      flash_set('warn','Add images failed: '.$e->getMessage());
      header("Location: adminProject.php?edit=".$pid); exit;
    }
  } else {
    flash_set('warn','Invalid project.');
    header("Location: adminProject.php"); exit;
  }
}


$hero_title = $pdo->query("SELECT `value` FROM `project_content` WHERE `key`='projects_hero_title'")->fetchColumn();
$hero_sub   = $pdo->query("SELECT `value` FROM `project_content` WHERE `key`='projects_hero_subtitle'")->fetchColumn();

$editId  = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editing = null;
$gallery = [];
if ($editId) {
  $stmt = $pdo->prepare("SELECT * FROM projects WHERE id=?"); $stmt->execute([$editId]);
  $editing = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
  if ($editing) {
    $g = $pdo->prepare("SELECT * FROM project_images WHERE project_id=? ORDER BY sort_order, id");
    $g->execute([$editId]);
    $gallery = $g->fetchAll(PDO::FETCH_ASSOC);
  }
}
$projects = $pdo->query("SELECT * FROM projects ORDER BY is_featured DESC, created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$gallerySlotsLeft = max(0, $MAX_GALLERY - count($gallery));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin — Projects</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="asset/css/adminProject.css">
  <script>
    function confirmDelete(id){
      if (confirm("Delete this project? This cannot be undone.")) {
        document.getElementById('del-'+id).submit();
      }
      return false;
    }
    function confirmDeleteImage(formId){
      if (confirm("Remove this image from the gallery?")) {
        document.getElementById(formId).submit();
      }
      return false;
    }

    document.addEventListener('DOMContentLoaded', function(){
      const form = document.getElementById('project-form');
      if (!form) return;
      const idField = form.querySelector('input[name="id"]');
      const isEdit = idField && idField.value !== '0';
      const submitBtn = document.getElementById('updateBtn');
      if (!submitBtn) return;

      const getState = () => {
        const data = {};
        for (const el of form.elements) {
          if (!el.name || el.name === 'action') continue;
          if (el.type === 'file') continue;
          if (el.type === 'checkbox') {
            if (!data[el.name]) data[el.name] = [];
            if (el.checked) data[el.name].push(el.value || 'on');
          } else if (el.type === 'radio') {
            if (el.checked) data[el.name] = el.value;
          } else {
            data[el.name] = el.value;
          }
        }

        for (const k in data) {
          if (Array.isArray(data[k])) data[k].sort();
        }
        return JSON.stringify(data);
      };

      const setDisabled = (v) => {
        submitBtn.disabled = v;
        submitBtn.classList.toggle('disabled', v);
        submitBtn.style.opacity = v ? '0.6' : '';
        submitBtn.style.pointerEvents = v ? 'none' : 'auto';
      };

      let initial = getState();
      const updateDirty = () => {
        let dirty = (getState() !== initial);

        const files = form.querySelectorAll('input[type="file"]');
        for (const f of files) { if (f.files && f.files.length) { dirty = true; break; } }
        setDisabled(isEdit ? !dirty : false);
      };

      if (isEdit) {
        updateDirty();
        form.addEventListener('input', updateDirty);
        form.addEventListener('change', updateDirty);
      } else {
        setDisabled(false); 
      }
    });
  </script>
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
          alFile('admin.php?tab=settings','Settings','bi-gear');
          alFile('logout.php','Logout','bi-box-arrow-right');
        ?>
      </nav>
      <div class="aside-footer">
        <div class="brand-pill">
          <strong style="font-size:12px">PARAMOUNT<br>DEVELOPMENT GROUP</strong>
        </div>
      </div>
    </aside>

    <main class="admin-projects">
      <?php if (!empty($errors)): ?>
        <div class="alert error" style="margin:0 0 12px; padding:10px; border:1px solid #e7b3b3; background:#ffe9e9; color:#8a1f1f; border-radius:8px">
          <strong style="display:block; margin-bottom:6px">Couldn't save:</strong>
          <ul style="margin:0 0 0 18px; padding:0">
            <?php foreach($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if (!empty($notice)): ?>
        <div class="alert success" style="margin:0 0 12px; padding:10px; border:1px solid #b8e3c1; background:#e9fff0; color:#1f7a3a; border-radius:8px">
          <?= h($notice) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($warn = $warn ?? flash_get('warn'))): ?>
        <div class="alert" style="margin:0 0 12px; padding:10px; border:1px solid #ffe1a5; background:#fff7e0; color:#7a5b1f; border-radius:8px">
          <?= h($warn) ?>
        </div>
      <?php endif; ?>

      <section class="card" style="margin-bottom:16px">
        <h2 class="section-title" style="margin:0 0 10px">Projects Page — Hero</h2>
        <form method="post" class="grid-2">
          <input type="hidden" name="action" value="save_hero">
          <div class="grid">
            <label>Title</label>
            <input type="text" name="hero_title" value="<?= h($hero_title) ?>" placeholder="Find Your Dream. Home Here.">
          </div>
          <div class="grid">
            <label>Subtitle</label>
            <input type="text" name="hero_subtitle" value="<?= h($hero_sub) ?>" placeholder="Short description under heading">
          </div>
          <div class="actions" style="grid-column:1/-1">
            <button class="btn primary" type="submit"><i class="bi bi-save"></i> Save Hero</button>
          </div>
        </form>
      </section>

      <section class="card" style="margin-bottom:16px">
        <h2 class="section-title" style="margin:0 0 10px"><?= $editing ? 'Edit Project' : 'Add New Project' ?></h2>
        <form id="project-form" method="post" enctype="multipart/form-data" class="grid-2">
          <input type="hidden" name="action" value="save_project">
          <input type="hidden" name="id" value="<?= (int)($editing['id'] ?? 0) ?>">

          <div class="grid">
            <label>Title</label>
            <input type="text" name="title" value="<?= h($editing['title'] ?? '') ?>" required>
          </div>

          <div class="grid">
            <label>Slug (Note: Do Not Edit)</label>
            <input type="text" name="slug" value="<?= h($editing['slug'] ?? '') ?>">
          </div>

          <div class="grid">
            <label>Location</label>
            <input style="margin-top: -55px;" type="text" name="location" value="<?= h($editing['location'] ?? '') ?>" placeholder="e.g., Bismarck, North Dakota">
          </div>

          <div class="grid">
            <label>Type of Development <small style="color:#a33">(choose at least one)</small></label>
            <div style="display:flex;flex-direction:column;gap:6px;">
              <?php 
                $selectedTypes = explode(',', $editing['type'] ?? '');
                foreach (array_slice($TYPES,1) as $t): 
                  $checked = in_array($t, $selectedTypes, true) ? 'checked' : '';
              ?>
              <label style="display:flex;align-items:center;gap:6px;">
                <input type="checkbox" name="type[]" value="<?= h($t) ?>" <?= $checked ?>> <?= h($t) ?>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="grid">
            <label>Number Units</label>
            <input style="margin-top: -55px;" type="text" name="meta" value="<?= h($editing['meta'] ?? '') ?>" placeholder="Type: Residential · 36-Unit Value-Add">
          </div>

          <div class="grid">
            <label>Short Description</label>
            <textarea name="short_desc" placeholder="2–4 lines for project details page..."><?= h($editing['short_desc'] ?? '') ?></textarea>
          </div>

          <div class="grid" style="width: 95%;" >
            <label>Cover Image <?= ($editing && !empty($editing['cover_image'])) ? '(leave blank to keep current)' : '' ?></label>
            <?php if (!empty($editing['cover_image'])): ?>
              <div style="display:flex;align-items:center;gap:10px;">
                <img src="<?= h($editing['cover_image']) ?>" alt="" style="width:64px;height:48px;object-fit:cover;border-radius:6px;border:1px solid #ddd">
                <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp">
              </div>
            <?php else: ?>
              <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp">
            <?php endif; ?>
          </div>

          <?php if (!$editing): ?>
            <div class="grid" style="width: 95%;">
              <label>Add Featured Image (Note: 7 Photos Only)</label>
              <input type="file" name="gallery[]" accept=".jpg,.jpeg,.png,.webp" multiple>
            </div>
          <?php endif; ?>

          <div class="grid" style="align-items:center">
            <label><input type="checkbox" name="is_featured" <?= (!empty($editing['is_featured'])?'checked':'') ?>> Featured</label>
          </div>

          <div class="actions" style="grid-column:1/-1">
            <button id="updateBtn" class="btn primary" type="submit">
              <i class="bi bi-check2-circle"></i> <?= $editing ? 'Update Project' : 'Create Project' ?>
            </button>
            <?php if ($editing): ?>
              <a class="btn" href="adminProject.php"><i class="bi bi-x-circle"></i> Cancel</a>
            <?php endif; ?>
          </div>
        </form>
      </section>

      <?php if ($editing): ?>
      <section class="card" style="margin-bottom:16px">
        <h2 class="section-title" style="margin:0 0 10px">Gallery (<?= count($gallery) ?>/<?= $MAX_GALLERY ?>)</h2>

        <?php if (!$gallery): ?>
        <div style="color:#777;margin-bottom:12px">No images yet.</div>
            <?php else: ?>
            <div class="gallery-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;margin-bottom:16px">
                <?php foreach ($gallery as $gi): ?>
                <div class="gitem" style="border:1px solid #e5e5e5;border-radius:10px;padding:8px;background:#fafafa;display:flex;flex-direction:column;align-items:center">
                    <img src="<?= h($gi['img_path']) ?>" alt="" style="width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:6px;margin-bottom:8px">

                    <input type="file" name="new_image_<?= (int)$gi['id'] ?>" accept=".jpg,.jpeg,.png,.webp" style="width: 150px;margin-bottom:8px">

                    <div style="display:flex;gap:8px;justify-content:center">

                    <form method="post" enctype="multipart/form-data" style="margin:0">
                        <input type="hidden" name="action" value="replace_image">
                        <input type="hidden" name="project_id" value="<?= (int)$editing['id'] ?>">
                        <input type="hidden" name="image_id" value="<?= (int)$gi['id'] ?>">
                        <button class="btn" type="submit" title="Replace">
                        <i class="bi bi-arrow-repeat"></i>
                        </button>
                    </form>

                    <form id="imgdel-<?= (int)$gi['id'] ?>" method="post" style="margin:0">
                        <input type="hidden" name="action" value="delete_image">
                        <input type="hidden" name="project_id" value="<?= (int)$editing['id'] ?>">
                        <input type="hidden" name="image_id" value="<?= (int)$gi['id'] ?>">
                        <button class="btn" type="button" onclick="return confirmDeleteImage('imgdel-<?= (int)$gi['id'] ?>')" title="Delete">
                        <i class="bi bi-trash"></i>
                        </button>
                    </form>
                    </div>
                </div>
                <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php $gallerySlotsLeft = max(0, $MAX_GALLERY - count($gallery)); ?>
        <form method="post" enctype="multipart/form-data" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center">
          <input type="hidden" name="action" value="add_images">
          <input type="hidden" name="project_id" value="<?= (int)$editing['id'] ?>">
          <div style="flex:1 1 260px;min-width:260px">
            <label>Add Images (<?= $gallerySlotsLeft ?> slot<?= $gallerySlotsLeft===1?'':'s' ?> left)</label>
            <input type="file" name="add_gallery[]" accept=".jpg,.jpeg,.png,.webp" <?= $gallerySlotsLeft ? '' : 'disabled' ?> multiple style="width: 95%;">
            <?php if (!$gallerySlotsLeft): ?>
              <small style="color:#777">Gallery is full. Delete or replace an image to add more.</small>
            <?php endif; ?>
          </div>
          <button class="btn primary" type="submit" <?= $gallerySlotsLeft ? '' : 'disabled' ?>>
            <i class="bi bi-plus-circle"></i> Add to Gallery
          </button>
        </form>
      </section>
      <?php endif; ?>

      <section class="card">
        <h2 class="section-title" style="margin:0 0 10px">All Projects</h2>
        <div class="table-wrap" style="overflow:auto">
          <table>
            <thead>
              <tr>
                <th>Cover</th>
                <th>Title & Meta</th>
                <th>Type</th>
                <th>Location</th>
                <th>Slug</th>
                <th>Featured</th>
                <th style="width:170px"></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($projects as $p): ?>
              <tr>
                <td><?php if (!empty($p['cover_image'])): ?><img class="thumb" src="<?= h($p['cover_image']) ?>" alt=""><?php endif; ?></td>
                <td>
                  <strong><?= h($p['title']) ?></strong><br>
                  <span style="color:#777"><?= h($p['meta'] ?? '') ?></span>
                </td>
                <td><span class="chip"><?= h($p['type'] ?? '') ?></span></td>
                <td><?= h($p['location'] ?? '') ?></td>
                <td><code><?= h($p['slug'] ?? '') ?></code></td>
                <td><?= !empty($p['is_featured']) ? 'Yes' : '—' ?></td>
                <td style="white-space:nowrap">
                  <a class="btn" href="adminProject.php?edit=<?= (int)$p['id'] ?>"><i class="bi bi-pencil-square"></i> Edit</a>
                  <form id="del-<?= (int)$p['id'] ?>" method="post" style="display:inline">
                    <input type="hidden" name="action" value="delete_project">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <button class="btn" onclick="return confirmDelete(<?= (int)$p['id'] ?>)"><i class="bi bi-trash"></i> Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
