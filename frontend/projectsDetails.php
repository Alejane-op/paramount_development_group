<?php 

$active = 'projects.php';
require_once __DIR__ . '/../backend/db.php';
if ($pdo instanceof PDO) { $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); }

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function asset_url($path, $fallback = '/asset/project-placeholder.jpg'){
  $path = trim((string)$path);
  if ($path === '') return $fallback;
  if (preg_match('#^https?://#i', $path)) return $path;
  if ($path[0] === '/') return $path;
  return '/' . ltrim($path, '/');
}

$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug = $_GET['slug'] ?? '';
$TYPES = ['All','Residential','Commercial','Mixed-Use','Multifamily'];
$type  = $_GET['type'] ?? 'All';
if (!in_array($type, $TYPES, true)) $type = 'All';

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
  $gallery = [];
  if (!empty($project['cover_image'])) $gallery[] = $project['cover_image'];
  $g = $pdo->prepare("SELECT `img_path` FROM `project_images` WHERE `project_id`=? ORDER BY `sort_order` ASC, `id` ASC");
  $g->execute([$project['id']]);
  $imgs = $g->fetchAll(PDO::FETCH_COLUMN);
  foreach ($imgs as $p) { $gallery[] = $p; }
  if (!$gallery) $gallery[] = '/asset/project-placeholder.jpg';
}
$gallery = array_map(fn($p) => asset_url($p), $gallery);

$seq = $gallery;
while (count($seq) < 8) { $seq = array_merge($seq, $gallery); }
$seq = array_slice($seq, 0, 8);

$meta = trim((string)($project['meta'] ?? ''));

$curId        = (int)($project['id'] ?? 0);
$curFeatured  = (int)($project['is_featured'] ?? 0);
$curCreatedAt = (string)($project['created_at'] ?? '1970-01-01 00:00:00');
$typeWhere = ''; $typeParam = [];
if ($type !== 'All') { $typeWhere = " AND FIND_IN_SET(?, p.type) > 0 "; $typeParam = [$type]; }
$sqlNext = "
  SELECT p.* FROM projects p
  WHERE (
    p.is_featured < ?
    OR (p.is_featured = ? AND p.created_at < ?)
    OR (p.is_featured = ? AND p.created_at = ? AND p.id < ?)
  )
  {$typeWhere}
  ORDER BY p.is_featured DESC, p.created_at DESC, p.id DESC
  LIMIT 1
";
$paramsNext = [$curFeatured,$curFeatured,$curCreatedAt,$curFeatured,$curCreatedAt,$curId, ...$typeParam];
$stNext = $pdo->prepare($sqlNext);
$stNext->execute($paramsNext);
$nextRow = $stNext->fetch(PDO::FETCH_ASSOC);
if (!$nextRow) {
  $sqlFirst = "SELECT p.* FROM projects p WHERE 1=1 {$typeWhere} ORDER BY p.is_featured DESC, p.created_at DESC, p.id DESC LIMIT 1";
  $stFirst = $pdo->prepare($sqlFirst);
  $stFirst->execute($typeParam);
  $nextRow = $stFirst->fetch(PDO::FETCH_ASSOC) ?: null;
}
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

<body class="has-fade">
  <?php include __DIR__ . '/navbar.php'; ?>

  <main class="project-details" data-fade-container>
    <div class="pd-head container" data-fade data-fade-delay="0">
      <a class="pd-back xfade" href="projects.php<?= $type!=='All' ? '?type='.urlencode($type) : '' ?>" aria-label="Back to Projects">‹</a>
      <h1><?= h($project['title']) ?></h1>
      <?php 
        $loc = $project['location'] ?: '—';
        $typesList = implode(', ', array_filter(array_map('trim', explode(',', (string)($project['type'] ?? ''))))) ?: '—';
        $metaLine = trim((string)($project['meta'] ?? ''));
        $metaFull = "Location: {$loc} · Type: {$typesList}";
        if ($metaLine !== '') $metaFull .= " · {$metaLine}";
      ?>
      <p class="pd-meta"><?= h($metaFull) ?></p>
      <?php if (!empty($project['short_desc'])): ?>
        <p class="pd-desc"><?= nl2br(h($project['short_desc'])) ?></p>
      <?php endif; ?>
    </div>

    <section class="pd-gallery container">
      <figure class="pd-item span-3" data-fade data-fade-delay="80">
        <img src="<?= h($seq[0]) ?>" alt="" loading="lazy">
      </figure>

      <div class="pd-row">
        <figure class="pd-item" data-fade data-fade-delay="120"><img src="<?= h($seq[1]) ?>" alt="" loading="lazy"></figure>
        <figure class="pd-item" data-fade data-fade-delay="160"><img src="<?= h($seq[2]) ?>" alt="" loading="lazy"></figure>
        <figure class="pd-item" data-fade data-fade-delay="200"><img src="<?= h($seq[3]) ?>" alt="" loading="lazy"></figure>
      </div>

      <figure class="pd-item span-3" data-fade data-fade-delay="240">
        <img src="<?= h($seq[4]) ?>" alt="" loading="lazy">
      </figure>

      <div class="pd-row">
        <figure class="pd-item" data-fade data-fade-delay="280"><img src="<?= h($seq[5]) ?>" alt="" loading="lazy"></figure>
        <figure class="pd-item" data-fade data-fade-delay="320"><img src="<?= h($seq[6]) ?>" alt="" loading="lazy"></figure>
        <figure class="pd-item" data-fade data-fade-delay="360"><img src="<?= h($seq[7]) ?>" alt="" loading="lazy"></figure>
      </div>
    </section>

    <?php if ($nextHref): ?>
      <div class="pd-next container" style="margin-block:24px" data-fade data-fade-delay="380">
        <a class="xfade" href="<?= h($nextHref) ?>" aria-label="Next project">
          <span><?= h($nextRow['title'] ?? 'Next') ?></span> <span>›</span>
        </a>
      </div>
    <?php endif; ?>
  </main>

  <?php include __DIR__ . '/footer.php'; ?>

  <script>
  (function(){
    var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!prefersReduced) {
      document.documentElement.classList.add('page-fade-ready');
      requestAnimationFrame(function(){
        document.body.classList.add('fade-enter');

        requestAnimationFrame(function(){
          document.body.classList.add('fade-enter-active');
        });
      });
    }

    var els = [].slice.call(document.querySelectorAll('[data-fade]'));
    if (!prefersReduced && 'IntersectionObserver' in window) {
      var io = new IntersectionObserver(function(entries){
        entries.forEach(function(entry){
          if (entry.isIntersecting) {
            var delay = parseInt(entry.target.getAttribute('data-fade-delay') || '0', 10);
            setTimeout(function(){
              entry.target.classList.add('is-in');
            }, Math.max(0, delay));
            io.unobserve(entry.target);
          }
        });
      }, { root: null, rootMargin: '0px 0px -10% 0px', threshold: 0.1 });
      els.forEach(function(el){ el.classList.add('will-fade'); io.observe(el); });
    } else {

      els.forEach(function(el){ el.classList.add('is-in'); });
    }


    var xlinks = document.querySelectorAll('a.xfade');
    xlinks.forEach(function(a){
      a.addEventListener('click', function(e){
        if (prefersReduced) return; 
        var href = a.getAttribute('href');
        if (!href || href.indexOf('#') === 0) return;
        e.preventDefault();
        document.body.classList.add('fade-leave');
        setTimeout(function(){ window.location.href = href; }, 220); 
      });
    });
  })();
  </script>
</body>
</html>
