<?php

require_once __DIR__ . '/../backend/db.php';
if ($pdo instanceof PDO) {
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function short_text(string $s, int $len = 200): string {
  $s = trim($s);
  if (function_exists('mb_strimwidth')) {
    return mb_strimwidth($s, 0, $len, '…', 'UTF-8');
  }
  return strlen($s) > $len ? substr($s, 0, $len - 3) . '…' : $s;
}

function excerpt_text(string $html, int $len): string {
  $s = preg_replace('/<\/?p[^>]*>|<br\s*\/?>/i', ' ', (string)$html); 
  $s = strip_tags($s);                                              
  $s = preg_replace('/\s+/', ' ', trim($s));                         
  return short_text($s, $len);
}

function slugify(string $text): string {
  $text = preg_replace('~[^\pL\d]+~u', '-', $text);
  if (function_exists('iconv')) {
    $t = @iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    if ($t !== false) $text = $t;
  }
  $text = preg_replace('~[^-\w]+~', '', $text);
  $text = trim($text, '-');
  $text = preg_replace('~-+~', '-', $text);
  $text = strtolower($text);
  return $text ?: 'post';
}
function post_url(string $title): string {
  return 'BlogDetails.php?title=' . urlencode(slugify($title));
}

function get_content(PDO $pdo, string $key, string $default=''): string {
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS blog_content (
      `key`   VARCHAR(64) PRIMARY KEY,
      `value` MEDIUMTEXT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  ");
  $stmt = $pdo->prepare("SELECT `value` FROM blog_content WHERE `key`=?");
  $stmt->execute([$key]);
  $v = $stmt->fetchColumn();
  return $v !== false ? (string)$v : $default;
}

$hero_h1 = get_content($pdo, 'blogs_hero_h1', 'Insights, Updates & Stories');
$hero_p  = get_content($pdo, 'blogs_hero_p',  'Explore articles about our developments, community impact, and perspectives from the Paramount Development Group team.');

$q    = trim((string)($_GET['q'] ?? ''));
$like = '%' . $q . '%';
$hasQ = ($q !== '');

if ($hasQ) {
  $featuredSql = "SELECT * FROM blogs
                  WHERE title LIKE :q
                  ORDER BY created_at DESC, id DESC
                  LIMIT 1";
  $stmt = $pdo->prepare($featuredSql);
  $stmt->bindValue(':q', $like, PDO::PARAM_STR);
  $stmt->execute();
  $featured = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($featured) {
    $listSql = "SELECT * FROM blogs
                WHERE id <> :fid AND title LIKE :q2
                ORDER BY created_at DESC, id DESC";
    $stmt = $pdo->prepare($listSql);
    $stmt->bindValue(':fid', (int)$featured['id'], PDO::PARAM_INT);
    $stmt->bindValue(':q2', $like, PDO::PARAM_STR);
    $stmt->execute();
  } else {
    $listSql = "SELECT * FROM blogs
                WHERE title LIKE :q2
                ORDER BY created_at DESC, id DESC";
    $stmt = $pdo->prepare($listSql);
    $stmt->bindValue(':q2', $like, PDO::PARAM_STR);
    $stmt->execute();
  }
  $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} else {
  $featuredStmt = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC, id DESC LIMIT 1");
  $featured = $featuredStmt->fetch(PDO::FETCH_ASSOC);

  if ($featured) {
    $listStmt = $pdo->prepare("SELECT * FROM blogs WHERE id <> ? ORDER BY created_at DESC, id DESC");
    $listStmt->execute([(int)$featured['id']]);
  } else {
    $listStmt = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC, id DESC");
  }
  $blogs = $listStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="icon" type="image/png" href="asset/paramount.png">
<title>Paramount Development Group — Blogs</title>

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="asset/css/blogs.css">
</head>
<body>

<?php include __DIR__ . '/navbar.php'; ?>

<main class="page">
  <div class="wrap">

    <section class="hero">
      <h1><?= h($hero_h1) ?></h1>
      <p><?= nl2br(h($hero_p)) ?></p>
    </section>

    <form class="search-bar" action="search.php" method="get">
      <input type="text" name="q" placeholder="Search by title..." value="<?= h($q) ?>">
      <button type="submit"><i class="bi bi-search"></i></button>
    </form>

    <?php if (!$featured && !$blogs): ?>
      <section class="grid block">
        <article class="post empty">
          <h3>No Blog Posts <?= $hasQ ? 'matched your search' : 'yet' ?>.</h3>
          <?php if ($hasQ): ?><p>Try different keywords or clear the search.</p><?php endif; ?>
        </article>
      </section>

    <?php else: ?>

      <?php if ($featured): ?>
        <h2 class="section-title olive">Featured Blog Post</h2>
        <section class="featured">
          <img src="<?= h($featured['image_path']) ?>" alt="Featured banner">
          <div class="featured-meta">
            <span class="date"><?= h(date('F j, Y', strtotime($featured['created_at'] ?? 'now'))) ?></span>
          </div>
          <h2><?= h($featured['title']) ?></h2>
          <p class="summary"><?= h(excerpt_text($featured['content'] ?? '', 400)) ?></p>
          <div class="pills">
            <a href="<?= h(post_url($featured['title'])) ?>" class="pill">Read More</a>
            <a href="#" class="pill share-btn" data-link="<?= h(post_url($featured['title'])) ?>">Share</a>
          </div>
        </section>
      <?php endif; ?>

      <?php if ($blogs): ?>
        <h2 class="section-title">All Blog Posts</h2>
        <section class="grid block"> 
          <?php foreach($blogs as $post): ?>
            <article class="post">
              <img src="<?= h($post['image_path']) ?>" alt="Post preview">
              <span class="date"><?= h(date('F j, Y', strtotime($post['created_at'] ?? 'now'))) ?></span>
              <h3><?= h($post['title']) ?></h3>
              <p class="summary"><?= h(excerpt_text($post['content'] ?? '', 160)) ?></p>
              <div class="pills">
                <a href="<?= h(post_url($post['title'])) ?>" class="pill">Read More</a>
                <a href="#" class="pill share-btn" data-link="<?= h(post_url($post['title'])) ?>">Share</a>
              </div>
            </article>
          <?php endforeach; ?>
        </section>
      <?php endif; ?>

    <?php endif; ?>

    <div class="after"></div>
  </div>
</main>

<script>
document.querySelectorAll('.share-btn').forEach(btn=>{
  btn.addEventListener('click', async e=>{
    e.preventDefault();
    const relative = btn.getAttribute('data-link') || '';
    const base = window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '/');
    const url  = new URL(relative, base).toString();
    const title = document.title;

    if (navigator.share) {
      try { await navigator.share({ title, text:"Check out this blog post!", url }); }
      catch(err){ console.log("Share cancelled/failed:", err); }
    } else if (navigator.clipboard) {
      try { await navigator.clipboard.writeText(url); alert("Link copied: " + url); }
      catch(err){ prompt("Copy this link:", url); }
    } else {
      prompt("Copy this link:", url);
    }
  });
});

(function(){
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce) return;

  const topSelectors = [
    '.hero',
    '.section-title',
    '.featured',
    '.grid.block .post',
    '.post.empty'
  ];
  const tops = document.querySelectorAll(topSelectors.join(','));
  tops.forEach(el => el.classList.add('reveal'));

  const partMaps = [
    ['.featured', ['h2', '.summary', '.pills']],
    ['.post',     ['h3', '.summary', '.pills']]
  ];
  const parts = [];
  partMaps.forEach(([scopeSel, childSels])=>{
    document.querySelectorAll(scopeSel).forEach(scope=>{
      childSels.forEach((childSel, idx)=>{
        scope.querySelectorAll(childSel).forEach(el=>{
          el.classList.add('reveal-part');
          el.style.transitionDelay = (idx * 80) + 'ms';
          parts.push(el);
        });
      });
    });
  });

  document.querySelectorAll('.grid.block').forEach(grid=>{
    const items = grid.querySelectorAll('.post');
    items.forEach((el, i)=>{
      el.style.transitionDelay = (i * 80) + 'ms';
    });
  });

  const io = new IntersectionObserver((entries)=>{
    entries.forEach(entry=>{
      const cls = entry.target.classList;
      if (entry.isIntersecting) cls.add('in'); else cls.remove('in');
    });
  }, { threshold: 0.15, rootMargin: '0px 0px -5% 0px' });

  tops.forEach(el => io.observe(el));
  parts.forEach(el => io.observe(el));

  requestAnimationFrame(()=> {
    [...tops, ...parts].forEach(el=>{
      const r = el.getBoundingClientRect();
      const vh = window.innerHeight || document.documentElement.clientHeight;
      if (r.top < vh * 0.85 && r.bottom > 0) el.classList.add('in');
    });
  });
})();
</script>
<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>
