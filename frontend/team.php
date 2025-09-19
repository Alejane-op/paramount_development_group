<?php
// Team.php (public)
require_once __DIR__ . '/../backend/db.php';

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

/**
 * Convert a stored DB path into a safe, working <img src>.
 * - Keeps http(s) URLs as-is.
 * - For local paths, makes them absolute-from-root and verifies existence on disk.
 * - If the saved path doesn't exist, tries a legacy fallback: /asset/team/<basename>.
 * - Returns a placeholder if nothing is found.
 */
function resolve_img_src(?string $p): string {
  $placeholder = '/asset/img/placeholder-team.png';
  if (!$p) return $placeholder;

  $p = trim($p);

  // 1) Already a full URL? (e.g., CDN)
  if (preg_match('~^https?://~i', $p)) return $p;

  // 2) Clean common prefixes (./, public/, backslashes)
  $p = preg_replace('~^(\./|\.\\\\)+~', '', $p);
  $p = preg_replace('~^public/~i', '', $p);
  $p = str_replace('\\', '/', $p);

  // 3) Ensure absolute-from-root for local web path
  if ($p === '' || $p[0] !== '/') $p = '/'.$p;

  // 4) If we can, verify the file is really there
  $docroot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
  if ($docroot) {
    $abs = $docroot . $p;
    if (is_file($abs)) return $p;

    // 5) Not found? Try legacy folder by basename (helps when old rows still have relative/odd paths)
    $base = basename($p);
    if ($base) {
      $legacy = '/asset/team/' . $base;
      if (is_file($docroot . $legacy)) return $legacy;
      $uploads = '/uploads/' . $base;
      if (is_file($docroot . $uploads)) return $uploads;
    }

    // 6) Still nothing—use placeholder
    if (is_file($docroot . $placeholder)) return $placeholder;
  }

  // If DOCUMENT_ROOT not set (rare), just return cleaned path
  return $p ?: $placeholder;
}

// Fetch header + members
$stmt = $pdo->query("SELECT title, subheader FROM team_header WHERE id=1");
$header = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['title'=>'Meet Our Team','subheader'=>''];

$members = $pdo->query("SELECT * FROM team_members WHERE is_active=1 ORDER BY sort_order ASC, id ASC")
               ->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Paramount Development Group — Meet Our Team</title>

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="asset/css/team.css">
</head>
<body>

<?php include __DIR__ . '/navbar.php'; ?>

<section class="team-section">
  <h1 class="team-header"><?= h($header['title']) ?></h1>
  <p class="team-subheader"><?= nl2br(h($header['subheader'])) ?></p>

  <?php foreach ($members as $i => $m): ?>
    <?php $src = resolve_img_src($m['photo_path'] ?? null); ?>
    <div class="team-card">
      <?php if ($i % 2 === 0): // even = text left, image right ?>
        <div class="team-info">
          <h2><?= h($m['name']) ?></h2>
          <h3><?= h($m['role']) ?></h3>
          <p><?= nl2br(h($m['bio'])) ?></p>
        </div>
        <img src="<?= h($src) ?>" alt="<?= h($m['name']) ?>" loading="lazy">
      <?php else: // odd = image left, text right ?>
        <img src="<?= h($src) ?>" alt="<?= h($m['name']) ?>" loading="lazy">
        <div class="team-info">
          <h2><?= h($m['name']) ?></h2>
          <h3><?= h($m['role']) ?></h3>
          <p><?= nl2br(h($m['bio'])) ?></p>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</section>
<?php include __DIR__ . '/footer.php'; ?>
</body>
<script>
(function () {
  // pick targets on the team page
  const targets = document.querySelectorAll([
    '.team-header',
    '.team-subheader',
    '.team-card',
    '.team-card img',
    '.team-card .team-info h2',
    '.team-card .team-info h3',
    '.team-card .team-info p'
  ].join(','));

  // seed .reveal + optional stagger for each .team-card
  const cards = document.querySelectorAll('.team-card');
  cards.forEach((card, i) => card.style.setProperty('--i', i)); // 0,1,2...

  targets.forEach(el => el.classList.add('reveal'));

  // IO: add 'in' when >=25% visible; remove when below -> fade in/out
  const io = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.intersectionRatio >= 0.25) e.target.classList.add('in');
      else e.target.classList.remove('in');
    });
  }, { threshold: [0, 0.25, 1], rootMargin: '0px 0px -5% 0px' });

  document.querySelectorAll('.reveal').forEach(el => io.observe(el));
})();
</script>
</html>
