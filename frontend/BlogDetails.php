<?php

session_start();
$active = 'blogs.php'; 
require_once __DIR__ . '/../backend/db.php';

if ($pdo instanceof PDO) {
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

if (!function_exists('str_starts_with')) {
  function str_starts_with($haystack, $needle){
    return substr($haystack, 0, strlen($needle)) === $needle;
  }
}

function csrf_token(): string {
  if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
  return $_SESSION['csrf'];
}
function check_csrf($t): bool {
  return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$t);
}

function asset_url(string $path, string $fallback = 'asset/Banner.png', string $base = ''): string {
  $p = trim((string)$path);
  if ($p === '' || strtolower($p) === 'null') $p = $fallback;


  if (preg_match('~^(https?:)?//|^data:~i', $p)) return $p;


  if ($p[0] === '/') return ($base !== '' ? rtrim($base,'/') : '') . $p;


  return ($base !== '' ? rtrim($base,'/') . '/' : '') . ltrim($p, '/');
}

function user_avatar_url(?string $raw, string $base = ''): string {
  $fallback = 'uploads/default-avatar.png';
  $p = trim((string)$raw);
  if ($p === '' || strtolower($p) === 'null') $p = $fallback;

  if (preg_match('~^(https?:)?//|^data:~i', $p)) return $p;
  if ($p[0] === '/') return ($base !== '' ? rtrim($base,'/') : '') . $p;
  return ($base !== '' ? rtrim($base,'/') . '/' : '') . ltrim($p, '/');
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
  return strtolower($text) ?: 'post';
}

$titleSlug = $_GET['title'] ?? '';

$post   = null;
$author = ['name' => 'Paramount Development Group', 'avatar' => 'uploads/default-avatar.png'];

if ($titleSlug !== '') {
  $sql = "SELECT 
            b.*,
            u.name   AS author_name,
            u.avatar AS author_avatar
          FROM blogs b
          LEFT JOIN users u ON u.id = b.user_id";
  $stmt = $pdo->query($sql);

  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if (slugify($row['title']) === $titleSlug) {
      $post = $row;

      if (!empty($row['author_name'])) {
        $author['name']   = $row['author_name'];
      }

      $author['avatar'] = user_avatar_url($row['author_avatar'] ?? null);

      break;
    }
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'like') {
  if (!check_csrf($_POST['csrf'] ?? '')) {
    http_response_code(400);
    die('Invalid CSRF token.');
  }
  if ($post) {
    $id = (int)$post['id'];
    $cookieKey = 'liked_blog_'.$id;
    if (empty($_COOKIE[$cookieKey])) {
      $stmt = $pdo->prepare("UPDATE blogs SET likes = likes + 1 WHERE id = ?");
      $stmt->execute([$id]);
      setcookie($cookieKey, '1', time() + 86400, '/');
    }
    header("Location: BlogDetails.php?title=" . urlencode($titleSlug));
    exit;
  }
}

if ($post) {
  $pdo->prepare("UPDATE blogs SET views = views + 1 WHERE id = ?")->execute([(int)$post['id']]);
  $post['views'] = (int)$post['views'] + 1;
}

$title      = $post ? $post['title'] : 'Blog Post';
$created_at = $post ? ($post['created_at'] ?? '') : '';
$niceDate   = $created_at ? date('F j, Y', strtotime($created_at)) : '';
$heroImg    = asset_url($post['image_path'] ?? 'asset/Banner.png', 'asset/Banner.png');
$content    = $post['content'] ?? '';
$likes      = (int)($post['likes'] ?? 0);
$views      = (int)($post['views'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?= h($title) ?> — Paramount Development</title>

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="asset/css/blogDetails.css">
</head>
<body>

<?php include __DIR__ . '/navbar.php'; ?>

<main class="blog-details">
  <div class="bd-wrap">
    <?php if(!$post): ?>
      <p class="bd-crumb"><a href="blogs.php">‹ All Blog Posts</a></p>
      <h1>Post not found</h1>
      <p class="bd-content">Sorry, that article doesn’t exist. <a href="blogs.php">Back to all posts</a>.</p>

    <?php else: ?>
      <p class="bd-crumb"><a href="blogs.php">‹ All Blog Posts</a></p>

      <div class="bd-author">
        <img src="<?= h($author['avatar']) ?>" alt="Author" />
        <div>
          <strong><?= h($author['name']) ?></strong>
          <small><?= h($niceDate) ?></small>
        </div>
      </div>

      <div class="bd-meta-row">
        <div class="bd-actions">
          <form method="post" style="display:inline">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="like">
            <button type="submit" class="like-btn" title="Like this post" style="all:unset;cursor:pointer;display:inline-flex;align-items:center;gap:6px">
              <i class="bi bi-hand-thumbs-up"></i><span><?= $likes ?> likes</span>
            </button>
          </form>
          <span class="bd-stat" style="margin-left:14px"><i class="bi bi-eye"></i> <?= $views ?> views</span>
        </div>
        <div class="bd-share">
          <a href="#" id="shareLink" title="Share this post"><i class="bi bi-share"></i></a>
        </div>
      </div>

      <h1><?= h($title) ?></h1>

      <div class="bd-hero">
        <img src="<?= h($heroImg) ?>" alt="Cover image">
      </div>
      
      <div class="bd-content">
        <?= preg_replace('/<\/?p[^>]*>/', '', $content) ?>
      </div>

      <div class="bd-after"></div>
    <?php endif; ?>
  </div>
</main>

<script>
document.getElementById('shareLink')?.addEventListener('click', async function(e){
  e.preventDefault();
  const url = window.location.href;
  const title = document.title;

  if (navigator.share) {
    try { await navigator.share({ title, text: "Check out this blog post!", url }); }
    catch(err){ console.log("Share cancelled or failed:", err); }
  } else if (navigator.clipboard) {
    try { await navigator.clipboard.writeText(url); alert("Link copied: " + url); }
    catch(err){ prompt("Copy this link:", url); }
  } else {
    prompt("Copy this link:", url);
  }
});

// ===== Fade-in & fade-out on scroll =====
(function(){
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce) return;

  const topSelectors = [
    '.bd-crumb',
    '.bd-author',
    '.bd-meta-row',
    'main.blog-details h1',
    '.bd-hero',
    '.bd-content',
    '.bd-after'
  ];
  const tops = document.querySelectorAll(topSelectors.join(','));
  tops.forEach(el => el.classList.add('reveal'));

  const parts = [];

  document.querySelectorAll('.bd-hero img').forEach((el)=>{
    el.classList.add('reveal-part');
    el.style.transitionDelay = '80ms';
    parts.push(el);
  });

  const content = document.querySelector('.bd-content');
  if (content){
    const childSel = 'p, h2, h3, h4, h5, h6, ul, ol, blockquote, img, figure, table, pre, code';
    const kids = content.querySelectorAll(childSel);
    kids.forEach((el, i)=>{
      el.classList.add('reveal-part');
      el.style.transitionDelay = (60 * i) + 'ms';
      parts.push(el);
    });
  }

  const io = new IntersectionObserver((entries)=>{
    entries.forEach(entry=>{
      const cls = entry.target.classList;
      if (entry.isIntersecting) cls.add('in'); else cls.remove('in');
    });
  }, { threshold: 0.15, rootMargin: '0px 0px -5% 0px' });

  [...tops, ...parts].forEach(el => io.observe(el));

  requestAnimationFrame(()=>{
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
