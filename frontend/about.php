<?php 

  $active = 'about.php';

  require_once __DIR__ . '/../backend/db.php';

  if ($pdo instanceof PDO) {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }

  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

  function cget(PDO $pdo, string $key, string $default=''): string {
    $stmt = $pdo->prepare('SELECT cvalue FROM about_content WHERE ckey=?');
    $stmt->execute([$key]);
    $v = $stmt->fetchColumn();
    return ($v !== false) ? (string)$v : $default;
  }

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

  $milestones = $pdo->query('SELECT * FROM about_milestones ORDER BY sort ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
  $goals      = $pdo->query('SELECT * FROM about_goals      ORDER BY sort ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
  $values     = $pdo->query('SELECT * FROM about_values    ORDER BY sort ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
  $strategy   = $pdo->query('SELECT * FROM about_strategy  ORDER BY sort ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);

  $vision      = cget($pdo, 'vision_body', '');
  $commitment  = cget($pdo, 'commitment_body', '');
  $teamTitle   = cget($pdo, 'team_title', 'At Paramount Development');
  $teamSubtitle= cget($pdo, 'team_subtitle', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" type="image/png" href="asset/paramount.png">
  <title>Paramount Development Group — About</title>

  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="asset/css/about.css">
</head>
<body>

  <?php include 'navbar.php'; ?>

  <main class="about-wrap">

    <section class="hero-about">
      <video class="hero-about-video" autoplay muted loop playsinline preload="auto" poster="asset/hero-poster.jpg">
        <source src="asset/bg-vid6.mp4" type="video/mp4" />
        Your browser does not support the video tag.
      </video>

      <div class="hero-content-about">
        <div class="hero-copy-about">
          <h1 style="color:#fff !important;"><?= $heroTitle ?></h1>
          <?php if (trim($heroPara)!==''): ?>
            <p class="muted"><?= $heroPara ?></p> 
          <?php endif; ?>
        </div>
      </div>
    </section>

    <section class="section history">
      <div class="container">
        <h2>Company History &amp; Experience</h2>

        <div class="twocol mt-16">
          <div>
            <h3><?= h($h20t) ?></h3>
            <?php if (trim($h20b)!==''): ?>
              <p class="lede muted"><?= $h20b ?></p>
            <?php endif; ?>

            <h3 class="mt-24"><?= h($h15t) ?></h3>
            <?php if (trim($h15b)!==''): ?>
              <p class="lede muted"><?= $h15b ?></p>
            <?php endif; ?>
          </div>

          <div>
            <h3><?= h($htrt) ?></h3>
            <?php if (trim($htrb)!==''): ?>
              <p class="lede muted"><?= $htrb ?></p>
            <?php endif; ?>

            <h3 class="mt-24"><?= h($hct) ?></h3>
            <?php if (trim($hcb)!==''): ?>
              <p class="lede muted"><?= $hcb ?></p>
            <?php endif; ?>
          </div>
        </div>

        <h2 class="mt-32">Milestones</h2>
        <div class="milestones mt-12" style="padding-left: 50px;">
          <?php if (!$milestones): ?>
            <div class="muted">No milestones yet.</div>
          <?php else: ?>
            <?php foreach ($milestones as $m): ?>
              <div class="milestone">
                <div class="dot" aria-hidden="true"></div>
                <p class="muted"><?= nl2br(h($m['body'])) ?></p>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <section class="section" id="goals">
      <div class="container">
        <h2>Our Goals</h2>

        <div class="goals mt-16">
          <?php if (!$goals): ?>
            <p class="muted">No goals yet.</p>
          <?php else: ?>
            <?php foreach ($goals as $g): ?>
              <article class="goal-card">
                <div class="goal-head">
                  <div class="pill" aria-hidden="true"></div>
                  <h3><?= h($g['title']) ?></h3>
                </div>
                <p class="muted"><?= nl2br(h($g['body'])) ?></p>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <section class="section" id="vision">
      <div class="container">
        <h2>Our Vision</h2>
        <?php if (trim($vision)!==''): ?>
          <p class="vision muted mt-12"><?= $vision ?></p> 
        <?php else: ?>
          <p class="vision muted mt-12"> </p>
        <?php endif; ?>
      </div>
    </section>

    <section class="invest" style="margin-top: -60px;">
      <div class="container">
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
                <p><?= nl2br(h($v['body'])) ?></p>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <section class="section strategy" id="strategy">
      <div class="container">
        <h2 class="h2">Our Strategy</h2>

        <ol class="timeline">
          <?php if (!$strategy): ?>
            <li class="tl-item"><div class="tl-content"><p class="muted">No strategy items yet.</p></div></li>
          <?php else: ?>
            <?php foreach ($strategy as $s): ?>
              <li class="tl-item">
                <span class="dot"></span>
                <div class="tl-content">
                  <h3><?= h($s['title']) ?></h3>
                  <p><?= nl2br(h($s['body'])) ?></p>
                </div>
              </li>
            <?php endforeach; ?>
          <?php endif; ?>
        </ol>

        <h2 class="h2 mt-64" id="commit">Our Commitment</h2>
        <?php if (trim($commitment)!==''): ?>
          <p class="commitment"><?= $commitment ?></p> 
        <?php else: ?>
          <p class="commitment"></p>
        <?php endif; ?>
      </div>
    </section>

    <section class="section team"  id="team">
      <div class="container">
        <h2 class="team-title"><?= h($teamTitle) ?></h2>
        <?php if (trim($teamSubtitle)!==''): ?>
          <p class="team-subtitle"><?= nl2br(h($teamSubtitle)) ?></p>
        <?php endif; ?>

        <a href="team.php" class="btn btn-olive2">Meet the Team</a>
      </div>
    </section>
  </main>

  <?php include 'footer.php'; ?>

  <script src="https://kit.fontawesome.com/b698486cb7.js" crossorigin="anonymous"></script>
<script>
(function () {
  const scope = document.querySelector('.about-wrap');
  if (!scope) return;

  const targets = scope.querySelectorAll([
    '.hero-content-about .hero-copy-about > *',

    '.container h1, .container h2, .container h3, .container p',

    '.milestones .milestone',
    '.goals .goal-card',
    '.values-title',
    '.values-subtitle',
    '.values-grid .value-item',
    '.timeline .tl-item',
    '.team-title',
    '.team-subtitle'
  ].join(','));

  targets.forEach(el => {
    el.classList.add('reveal');
  });

  const io = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.intersectionRatio >= 0.25) {
        e.target.classList.add('in');
      } else {
        e.target.classList.remove('in');
      }
    });
  }, {
    threshold: [0, 0.25, 1],
    rootMargin: '0px 0px -5% 0px'
  });

  scope.querySelectorAll('.reveal').forEach(el => io.observe(el));
})();
</script>
</body>
</html>
