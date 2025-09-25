<?php
// projects.php
$active = 'projects.php';
require_once __DIR__ . '/../backend/db.php';

if ($pdo instanceof PDO) {
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

/* ---------- filters ---------- */
$TYPES = ['All','Residential','Commercial','Mixed-Use','Multifamily'];
$type = $_GET['type'] ?? 'All';
if (!in_array($type, $TYPES, true)) $type = 'All';

/* ---------- hero content ---------- */
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
  if (preg_match('#^https?://#i', $path)) return $path;
  if ($path[0] === '/') return $path;
  return '/' . ltrim($path, '/');
}

/* ---------- fetch projects ---------- */
if ($type === 'All') {
  $stmt = $pdo->query("SELECT * FROM `projects` ORDER BY `is_featured` DESC, `created_at` DESC, `id` DESC");
} else {
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
  <link rel="icon" type="image/png" href="asset/paramount.png">
  <title>Paramount Development Group — Projects</title>

  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="asset/css/projects.css">
</head>

<body>
  <?php include __DIR__ . '/navbar.php'; ?>

  <main class="projects">
    <section class="projects-hero container">
      <h1 class="reveal" style="--d:.0s"><?= title_with_break($hero_title) ?></h1>

      <?php if (!empty($hero_sub)): ?>
        <p class="lead reveal" style="--d:.05s"><?= h($hero_sub) ?></p>
      <?php endif; ?>

      <!-- Filter chips -->
      <div class="chip-row reveal" style="--d:.1s">
        <?php foreach ($TYPES as $t): ?>
          <a class="chip <?= $t===$type ? 'is-active' : '' ?>" href="projects.php?type=<?= urlencode($t) ?>">
            <?= h($t) ?>
          </a>
        <?php endforeach; ?>
      </div>

      <section class="projects-list container-wide">
        <?php if (!$projects): ?>
          <p class="reveal" style="--d:.15s; text-align:center; margin-top:12px;">No projects under “<?= h($type) ?>” yet.</p>
        <?php endif; ?>

        <?php
          $i = 0;
          foreach ($projects as $p):
            $wide  = ($i % 5 === 0); 
            $id    = (int)($p['id'] ?? 0);
            $slug  = $p['slug'] ?? '';
            $title = $p['title'] ?? '';
            $img   = asset_url($p['cover_image'] ?? '');
            $loc   = $p['location'] ?? '';
            $typesCsv = $p['type'] ?? '';

            $href = "projectsDetails.php?id={$id}"
                  . ($slug !== '' ? "&slug=".urlencode($slug) : "")
                  . "&type=" . urlencode($type);

            $delay = 0.06 * ($i % 12);
            $i++;
        ?>
          <a class="project-card reveal <?= $wide ? 'span-2' : '' ?>"
             href="<?= h($href) ?>"
             aria-label="View details for <?= h($title) ?>"
             style="--d: <?= number_format($delay,2) ?>s">
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

  <!-- Fade in/out on scroll -->
  <script>
    (function(){
      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.querySelectorAll('.reveal').forEach(el => el.classList.add('show'));
        return;
      }
      const io = new IntersectionObserver((entries) => {
        entries.forEach(({target, isIntersecting}) => {
          if (isIntersecting) {
            target.classList.add('show');
          } else {
            
            target.classList.remove('show');
          }
        });
      }, { threshold: 0.2, rootMargin: '0px 0px -5% 0px' });

      document.querySelectorAll('.reveal').forEach((el) => io.observe(el));
    })();
  </script>
</body>
</html>
