<?php
// projectsDetails.php
// highlight Projects tab if your navbar supports $active
$active = 'projects.php';

require_once __DIR__ . '/../backend/db.php';

// DEV ONLY: show DB errors while building (remove in prod)
if ($pdo instanceof PDO) {
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

/* ---------- helpers ---------- */
function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function asset_url($path, $fallback = '/asset/project-placeholder.jpg'){
  $path = trim((string)$path);
  if ($path === '') return $fallback;
  if (preg_match('#^https?://#i', $path)) return $path; // full URL
  if ($path[0] === '/') return $path;                   // absolute
  return '/' . ltrim($path, '/');                       // make absolute
}

/* ---------- params ---------- */
$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug = $_GET['slug'] ?? '';

$TYPES = ['All','Residential','Commercial','Mixed-Use','Multifamily'];
$type  = $_GET['type'] ?? 'All';
if (!in_array($type, $TYPES, true)) $type = 'All';

/* ---------- load current project (prefer id, fallback to slug) ---------- */
$project = null;
if ($id > 0) {
  $stmt = $pdo->prepare("SELECT * FROM `projects` WHERE `id`=? LIMIT 1");
  $stmt->execute([$id]);
  $project = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}
if (!$project && $slug !== '') {
  $stmt = $pdo->prepare("SELECT * FROM `projects` WHERE `slug`=? LIMIT 1");
  $stmt->execute([$slug]);
  $project = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

if (!$project) {
  http_response_code(404);
  $project = [
    'id' => 0,
    'title' => 'Project Not Found',
    'meta'  => '',
    'short_desc' => 'The project you are looking for does not exist or was removed.',
    'location' => '',
    'type' => '',
    'cover_image' => '/asset/project-placeholder.jpg',
  ];
  $gallery = [$project['cover_image']];
} else {
  // Build gallery: cover first, then uploaded images
  $gallery = [];
  if (!empty($project['cover_image'])) $gallery[] = $project['cover_image'];

  $g = $pdo->prepare("SELECT `img_path` FROM `project_images` WHERE `project_id`=? ORDER BY `sort_order` ASC, `id` ASC");
  $g->execute([$project['id']]);
  $imgs = $g->fetchAll(PDO::FETCH_COLUMN);

  foreach ($imgs as $p) { $gallery[] = $p; }
  if (!$gallery) $gallery[] = '/asset/project-placeholder.jpg';
}

/* normalize gallery paths */
$gallery = array_map(fn($p) => asset_url($p), $gallery);

/* ensure we have at least 8 images for the layout pattern */
$seq = $gallery;
while (count($seq) < 8) { $seq = array_merge($seq, $gallery); }
$seq = array_slice($seq, 0, 8);

/* ---------- meta line (Location · Type · meta) ---------- */
$meta = trim((string)($project['meta'] ?? ''));

/* ---------- next/prev based on SAME order as grid ---------- */
/* Grid order: ORDER BY is_featured DESC, created_at DESC, id DESC */
$curId        = (int)($project['id'] ?? 0);
$curFeatured  = (int)($project['is_featured'] ?? 0);
$curCreatedAt = (string)($project['created_at'] ?? '1970-01-01 00:00:00');

/* Optional type filter (to stay within the same category when navigating) */
$typeWhere = '';
$typeParam = [];
if ($type !== 'All') {
  $typeWhere = " AND FIND_IN_SET(?, p.type) > 0 ";
  $typeParam = [$type];
}

/* NEXT: lower featured OR same featured with older created_at OR same & older id */
$sqlNext = "
  SELECT p.*
  FROM projects p
  WHERE
    (
      p.is_featured < ?
      OR (p.is_featured = ? AND p.created_at < ?)
      OR (p.is_featured = ? AND p.created_at = ? AND p.id < ?)
    )
    {$typeWhere}
  ORDER BY p.is_featured DESC, p.created_at DESC, p.id DESC
  LIMIT 1
";
$paramsNext = [$curFeatured, $curFeatured, $curCreatedAt, $curFeatured, $curCreatedAt, $curId, ...$typeParam];
$stNext = $pdo->prepare($sqlNext);
$stNext->execute($paramsNext);
$nextRow = $stNext->fetch(PDO::FETCH_ASSOC);

/* wrap around to first if none */
if (!$nextRow) {
  $sqlFirst = "
    SELECT p.*
    FROM projects p
    WHERE 1=1 {$typeWhere}
    ORDER BY p.is_featured DESC, p.created_at DESC, p.id DESC
    LIMIT 1
  ";
  $stFirst = $pdo->prepare($sqlFirst);
  $stFirst->execute($typeParam);
  $nextRow = $stFirst->fetch(PDO::FETCH_ASSOC) ?: null;
}

/* (Optional) you can also compute PREV if you later add a ‹ Prev link
$sqlPrev = \" 
  SELECT p.* FROM projects p
  WHERE
    (
      p.is_featured > ?
      OR (p.is_featured = ? AND p.created_at > ?)
      OR (p.is_featured = ? AND p.created_at = ? AND p.id > ?)
    )
    {$typeWhere}
  ORDER BY p.is_featured ASC, p.created_at ASC, p.id ASC
  LIMIT 1
\";
$paramsPrev = [$curFeatured, $curFeatured, $curCreatedAt, $curFeatured, $curCreatedAt, $curId, ...$typeParam];
$stPrev = $pdo->prepare($sqlPrev);
$stPrev->execute($paramsPrev);
$prevRow = $stPrev->fetch(PDO::FETCH_ASSOC);
if (!$prevRow) {
  $sqlLast = \"SELECT p.* FROM projects p WHERE 1=1 {$typeWhere} ORDER BY p.is_featured ASC, p.created_at ASC, p.id ASC LIMIT 1\";
  $stLast = $pdo->prepare($sqlLast);
  $stLast->execute($typeParam);
  $prevRow = $stLast->fetch(PDO::FETCH_ASSOC) ?: null;
}
*/

/* build next link (preserve type filter; include id and slug if present) */
$nextHref = null;
if ($nextRow) {
  $qs = "id=".(int)$nextRow['id'];
  if (!empty($nextRow['slug'])) $qs .= "&slug=".urlencode($nextRow['slug']);
  $qs .= "&type=".urlencode($type);
  $nextHref = "projectsDetails.php?{$qs}";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Projects — Paramount Development</title>

  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="asset/css/projectsDetails.css">
  <style>
    .pd-next a{display:inline-flex;align-items:center;gap:6px;color:#364025;text-decoration:none}
    .pd-next a:hover{opacity:.85}
  </style>
</head>

<body>
  <?php include __DIR__ . '/navbar.php'; ?>

  <main class="project-details">
    <!-- Top header -->
    <div class="pd-head container">
      <a class="pd-back" href="projects.php<?= $type!=='All' ? '?type='.urlencode($type) : '' ?>" aria-label="Back to Projects">‹</a>
      <h1><?= h($project['title']) ?></h1>

      <?php 
        $loc = $project['location'] ?: '—';
        $typesList = implode(', ', array_filter(array_map('trim', explode(',', (string)($project['type'] ?? '')))));
        $typesList = $typesList !== '' ? $typesList : '—';
        $metaLine = trim((string)($project['meta'] ?? ''));

        $metaFull = "Location: {$loc} · Type: {$typesList}";
        if ($metaLine !== '') $metaFull .= " · {$metaLine}";
      ?>
      <p class="pd-meta"><?= h($metaFull) ?></p>

      <?php if (!empty($project['short_desc'])): ?>
        <p class="pd-desc"><?= nl2br(h($project['short_desc'])) ?></p>
      <?php endif; ?>
    </div>

    <!-- Gallery -->
    <section class="pd-gallery container">
      <!-- 1) Wide hero -->
      <figure class="pd-item span-3">
        <img src="<?= h($seq[0]) ?>" alt="" loading="lazy">
      </figure>

      <!-- 2) Three small -->
      <div class="pd-row">
        <figure class="pd-item"><img src="<?= h($seq[1]) ?>" alt="" loading="lazy"></figure>
        <figure class="pd-item"><img src="<?= h($seq[2]) ?>" alt="" loading="lazy"></figure>
        <figure class="pd-item"><img src="<?= h($seq[3]) ?>" alt="" loading="lazy"></figure>
      </div>

      <!-- 3) Wide -->
      <figure class="pd-item span-3">
        <img src="<?= h($seq[4]) ?>" alt="" loading="lazy">
      </figure>

      <!-- 4) Three small -->
      <div class="pd-row">
        <figure class="pd-item"><img src="<?= h($seq[5]) ?>" alt="" loading="lazy"></figure>
        <figure class="pd-item"><img src="<?= h($seq[6]) ?>" alt="" loading="lazy"></figure>
        <figure class="pd-item"><img src="<?= h($seq[7]) ?>" alt="" loading="lazy"></figure>
      </div>
    </section>

    <!-- Next project link -->
    <?php if ($nextHref): ?>
      <div class="pd-next container" style="margin-block:24px">
        <a href="<?= h($nextHref) ?>" aria-label="Next project">
          <span><?= h($nextRow['title'] ?? 'Next') ?></span> <span>›</span>
        </a>
      </div>
    <?php endif; ?>
  </main>

  <?php include __DIR__ . '/footer.php'; ?>
</body>
</html>
