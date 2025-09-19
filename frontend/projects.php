<?php
// projects.php
// highlight Projects tab if your navbar supports $active
$active = 'projects.php';

require_once __DIR__ . '/../backend/db.php';

// DEV ONLY: show DB errors (safe in dev, remove in prod)
if ($pdo instanceof PDO) {
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

/* ---------- filters ---------- */
$TYPES = ['All','Residential','Commercial','Mixed-Use','Multifamily'];
$type = $_GET['type'] ?? 'All';
if (!in_array($type, $TYPES, true)) $type = 'All';

/* ---------- hero content (from project_content) ---------- */
$hero_title = $pdo->query("SELECT `value` FROM `project_content` WHERE `key`='projects_hero_title'")
                 ->fetchColumn() ?: 'Find Your Dream. Home Here.';
$hero_sub   = $pdo->query("SELECT `value` FROM `project_content` WHERE `key`='projects_hero_subtitle'")
                 ->fetchColumn() ?: 'You can see for yourself how the Paramount Development offers beautiful and comfortable housing for you and your family. See photos of the house, environment and facilities we provide here.';

// display helper: insert <br> after 1st period
function title_with_break($s){
  $s = (string)$s;
  $pos = strpos($s, '.');
  if ($pos !== false && $pos < strlen($s)-1) {
    return h(substr($s,0,$pos+1)) . '<br>' . h(substr($s,$pos+1));
  }
  return h($s);
}

/* ---------- helper: normalize uploaded image paths ---------- */
function asset_url($path, $fallback = '/asset/project-placeholder.jpg'){
  $path = trim((string)$path);
  if ($path === '') return $fallback;
  if (preg_match('#^https?://#i', $path)) return $path; // absolute URL
  if ($path[0] === '/') return $path;                   // root-relative
  return '/' . ltrim($path, '/');                       // relative upload
}

/* ---------- fetch projects ---------- */
if ($type === 'All') {
  $stmt = $pdo->query("SELECT * FROM `projects` ORDER BY `is_featured` DESC, `created_at` DESC, `id` DESC");
} else {
  // type column is CSV (e.g., "Residential,Commercial"), so use FIND_IN_SET
  $stmt = $pdo->prepare("SELECT * FROM `projects` WHERE FIND_IN_SET(?, `type`) > 0 ORDER BY `is_featured` DESC, `created_at` DESC, `id` DESC");
  $stmt->execute([$type]);
}
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Paramount Development Group — Projects</title>

  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="asset/css/projects.css">
</head>

<body>
  <?php include __DIR__ . '/navbar.php'; ?>

  <main class="projects">
    <section class="projects-hero container">
      <h1><?= title_with_break($hero_title) ?></h1>

      <?php if (!empty($hero_sub)): ?>
        <p class="lead"><?= h($hero_sub) ?></p>
      <?php endif; ?>

      <!-- Filter chips -->
      <div class="chip-row">
        <?php foreach ($TYPES as $t): ?>
          <a class="chip <?= $t===$type ? 'is-active' : '' ?>" href="projects.php?type=<?= urlencode($t) ?>">
            <?= h($t) ?>
          </a>
        <?php endforeach; ?>
      </div>

      <section class="projects-list container-wide">
        <?php if (!$projects): ?>
          <p style="margin-top:12px; text-align:center;">No projects under “<?= h($type) ?>” yet.</p>
        <?php endif; ?>

        <?php
          $i = 0;
          foreach ($projects as $p):
            $wide  = ($i % 5 === 0); // keep your cadence
            $i++;

            $id    = (int)($p['id'] ?? 0);
            $slug  = $p['slug'] ?? '';
            $title = $p['title'] ?? '';
            $img   = asset_url($p['cover_image'] ?? '');
            $loc   = $p['location'] ?? '';
            $typesCsv = $p['type'] ?? '';

            // PASS ID (and slug if present) + filter type to details page
            $href = "projectsDetails.php?id={$id}"
                  . ($slug !== '' ? "&slug=".urlencode($slug) : "")
                  . "&type=" . urlencode($type);
        ?>
          <a class="project-card <?= $wide ? 'span-2' : '' ?>"
            href="<?= h($href) ?>"
            aria-label="View details for <?= h($title) ?>">
            <img src="<?= h($img) ?>" alt="<?= h($title) ?>">
            <div class="overlay">
              <h3><?= h($title) ?></h3>
              <?php
                $typesList = implode(', ', array_filter(array_map('trim', explode(',', $typesCsv))));
                $metaLine  = trim($p['meta'] ?? '');
              ?>
              <?php if ($metaLine !== ''): ?>
                <p class="meta pd-meta"><?= h($metaLine) ?></p>
              <?php endif; ?>
              <p>
                Location: <?= h($loc !== '' ? $loc : '—') ?> ·
                Type: <?= h($typesList !== '' ? $typesList : '—') ?>
              </p>
            </div>
          </a>
        <?php endforeach; ?>
      </section>
    </section>
  </main>

  <?php include __DIR__ . '/footer.php'; ?>
</body>
</html>
