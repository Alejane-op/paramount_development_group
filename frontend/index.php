<?php

$active = 'index.php';
require_once __DIR__ . '/../backend/db.php';


if ($pdo instanceof PDO) {
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}


$BASE_URL = '';

function img_url(string $path, string $BASE_URL=''): string {
  $path = trim($path);
  if ($path === '') return '';
  if (preg_match('~^(https?:)?//|^data:~i', $path)) return $path; 
  if ($path[0] === '/') return $path;                               
  return rtrim($BASE_URL, '/') . '/' . ltrim($path, '/');          
}

$stmt = $pdo->query("SELECT upper_title, down_title, subtitle FROM home_hero_inner LIMIT 1");
$hero = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$upper    = $hero['upper_title'] ?? '';
$down     = $hero['down_title']  ?? '';
$subtitle = $hero['subtitle']    ?? '';

$stmt = $pdo->query("SELECT title, body FROM home_about_content ORDER BY id ASC LIMIT 1");
$about = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['title'=>'', 'body'=>''];

$blocks = $pdo->query("SELECT * FROM dev_blocks ORDER BY sort_order ASC, id ASC")
              ->fetchAll(PDO::FETCH_ASSOC);

$values = $pdo->query("SELECT * FROM about_values ORDER BY sort ASC, id ASC")
              ->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Paramount Development Group — Home</title>
  <link rel="icon" type="image/png" href="asset/paramount.png">
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Poppins -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="asset/css/index.css">
</head>
<body>

  <?php include 'navbar.php'; ?>

  <main>
    <!-- ================= HERO ================= -->
    <section class="hero" style="margin-top: -72px;">
      <video class="hero-video" autoplay muted loop playsinline>
        <source src="asset/bg-vid4.mp4" type="video/mp4" />
      </video>

      <!-- Overlay -->
      <div class="hero-overlay"></div>

      <!-- Content -->
      <div class="hero-inner">
        <h1><?= h($upper) ?> <br> <?= h($down) ?></h1>
        <p><?= h($subtitle) ?></p>
      </div>
    </section>

    <!-- ================= ABOUT (brief) ================= -->
    <section class="about-content">
      <div class="container">
        <h1><?= h($about['title']) ?></h1>
        <p><?= nl2br(h($about['body'])) ?></p>
        <a href="about.php" class="btn">Learn More</a>
      </div>
    </section>

    <!-- ================= DEVELOPMENT BLOCKS ================= -->
    <section class="type-development">
      <div class="dev-grid">
        <?php foreach ($blocks as $block): ?>
          <?php
            $stmt = $pdo->prepare("SELECT label, description FROM dev_bullets WHERE block_id=? ORDER BY id ASC");
            $stmt->execute([$block['id']]);
            $bullets = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $src = img_url($block['image'] ?? '', $BASE_URL);
            $layout = $block['layout'] ?? 'image-left';
            $title = $block['title'] ?? '';
            $desc  = $block['description'] ?? '';
          ?>

          <?php if ($layout === 'image-left'): ?>
            <figure class="dev-media">
              <img src="<?= h($src) ?>" alt="<?= h($title) ?>">
            </figure>

            <div class="dev-content">
              <h1><?= h($title) ?></h1>
              <p><?= nl2br(h($desc)) ?></p>
              <?php if ($bullets): ?>
                <ul class="dev-bullets">
                  <?php foreach ($bullets as $b): ?>
                    <li><strong><?= h($b['label']) ?></strong> – <?= h($b['description']) ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
              <a class="btn1" href="projects.php">View Projects</a>
            </div>

          <?php else: ?>
            <div class="dev-content" style="margin-top:30px;">
              <h1><?= h($title) ?></h1>
              <p><?= nl2br(h($desc)) ?></p>
              <?php if ($bullets): ?>
                <ul class="dev-bullets">
                  <?php foreach ($bullets as $b): ?>
                    <li><strong><?= h($b['label']) ?></strong> – <?= h($b['description']) ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
              <a class="btn2" href="projects.php">View Projects</a>
            </div>

            <figure class="dev-media" style="margin-top:30px;">
              <img src="<?= h($src) ?>" alt="<?= h($title) ?>">
            </figure>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- ================= CORE VALUES ================= -->
    <section class="values-block">
      <div class="values-container">
        <h2 class="values-title">OUR CORE VALUES</h2>
        <p class="values-subtitle">
          At Paramount Development Group, our values guide every decision and define how we build lasting impact for investors, partners, and communities.
        </p>

        <div class="values-grid">
          <?php if (!$values): ?>
            <p class="muted">No core values yet.</p>
          <?php else: ?>
            <?php foreach ($values as $v): ?>
              <div class="value-item">
                <i class="<?= h($v['icon']) ?>"></i>
                <h3><?= h($v['title']) ?></h3>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <a href="about.php" class="btn">Learn More</a>
      </div>
    </section>
  </main>

<?php include 'footer.php'; ?>

<script src="https://kit.fontawesome.com/b698486cb7.js" crossorigin="anonymous"></script>
<script>
(function () {
  const targets = document.querySelectorAll([
    
    '.hero-inner > *',

    '.about-content h1',
    '.about-content p',
    '.about-content .btn',
  
    '.type-development .dev-content h1',
    '.type-development .dev-content p',
    '.type-development .dev-bullets li',
    '.type-development .dev-media img',
    '.type-development .btn1',
    '.type-development .btn2',

    '.values-title',
    '.values-subtitle',
    '.values-grid .value-item',
    '.values-container .btn'
  ].join(','));

  targets.forEach(el => el.classList.add('reveal'));

  const io = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.intersectionRatio >= 0.25) {
        e.target.classList.add('in');
      } else {
        e.target.classList.remove('in');
      }
    });
  }, { threshold: [0, 0.25, 1], rootMargin: '0px 0px -5% 0px' });

  document.querySelectorAll('.reveal').forEach(el => io.observe(el));
})();
</script>
</body>
</html>
