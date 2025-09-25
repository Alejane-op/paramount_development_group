<?php

session_start();
require_once __DIR__ . '/../backend/db.php';
if ($pdo instanceof PDO) { $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); }
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

try {
  $stmt = $pdo->query('SELECT * FROM contact_settings WHERE id=1');
  $cs = $stmt->fetch() ?: [];
} catch (Throwable $e) { $cs = []; }

$ok  = isset($_GET['sent']) && $_GET['sent'] === '1';
$err = isset($_GET['err'])  ? (string)$_GET['err'] : '';

unset($_SESSION['last_contact_post']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="icon" type="image/png" href="asset/paramount.png">
<title>Paramount Development Group — Contact</title>

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="asset/css/contact.css">

</head>
<body>

<?php include __DIR__ . '/navbar.php'; ?>

<section class="page-head">
  <h1><?= h($cs['headline'] ?? 'Have a project in mind?') ?> <span class="accent">Contact Us</span></h1>
  <p class="page-sub"><?= h($cs['subtext'] ?? '') ?></p>
</section>

<section class="hero">
  <img src="<?= h($cs['hero_image'] ?? 'asset/Office-.jpg') ?>" alt="Office interior" />
  <div class="overlay">
    <div class="overlay-inner details-3up">
      <div class="detail"><i class="bi bi-geo-alt-fill"></i><span><?= h($cs['address'] ?? '') ?></span></div>
      <div class="detail"><i class="bi bi-telephone-fill"></i><span><?= h($cs['phone'] ?? '') ?></span></div>
      <div class="detail"><i class="bi bi-envelope-fill"></i><span><?= h($cs['email'] ?? '') ?></span></div>
    </div>
  </div>
</section>

<section class="card-wrap">
    <?php if ($err): ?>
      <div class="alert err"><i class="bi bi-exclamation-triangle"></i> <?= h($err) ?></div>
    <?php endif; ?>

  <form class="card" action="../backend/adminContact.php" method="post" novalidate>
    <input type="hidden" name="public_submit" value="1">

          <div class="row-2col">
        <div class="input-block">
          <label for="first">First Name</label>
          <input id="first" name="first" type="text" required>
        </div>
        <div class="input-block">
          <label for="last">Last Name</label>
          <input id="last" name="last" type="text" required>
        </div>
      </div>

      <div class="input-block">
        <label for="email">Email Address</label>
        <input id="email" name="email" type="email" required>
      </div>

      <div class="input-block">
        <label for="message">Message</label>
        <textarea id="message" name="message" rows="6" required></textarea>
      </div>

      <div class="form-actions">
        <button class="btn" type="submit"> Send</button>
      </div>
  </form>
</section>

<div class="modal-backdrop" id="successModal" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="modal" role="document">
    <div class="icon-wrap"><i class="bi bi-check2-circle" style="font-size:34px;"></i></div>
    <h3>Your email was sent!</h3>
    <p>We’ve received your message and emailed you a copy.</p>
    <div class="actions">
      <button type="button" class="btn ok" id="okBtn">Okay</button>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>

<script>
(function(){
  const params = new URLSearchParams(window.location.search);
  const show = params.get('sent') === '1';
  const modal = document.getElementById('successModal');
  const okBtn = document.getElementById('okBtn');

  function closeModal(){
    modal.classList.remove('show');

    const url = new URL(window.location);
    url.searchParams.delete('sent');
    history.replaceState({}, '', url);
  }

  if (show && modal){
    modal.classList.add('show');
    okBtn?.addEventListener('click', closeModal);

    modal.addEventListener('click', (e)=>{ if(e.target === modal) closeModal(); });
    document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape') closeModal(); });
  }
})();
</script>
<script>
(function(){
  const params = new URLSearchParams(window.location.search);
  const show = params.get('sent') === '1';
  const modal = document.getElementById('successModal');
  const okBtn = document.getElementById('okBtn');

  function closeModal(){
    modal.classList.remove('show');
    const url = new URL(window.location);
    url.searchParams.delete('sent');
    history.replaceState({}, '', url);
  }

  if (show && modal){
    modal.classList.add('show');
    okBtn?.addEventListener('click', closeModal);
    modal.addEventListener('click', (e)=>{ if(e.target === modal) closeModal(); });
    document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape') closeModal(); });
  }
})();

(function(){
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce) return;


  const topSelectors = [
    '.page-head',   
    '.hero',        
    '.card-wrap',  
    '.alert.err'    
  ];
  const tops = document.querySelectorAll(topSelectors.join(','));
  tops.forEach(el => el && el.classList.add('reveal'));

  const parts = [];
  const form = document.querySelector('.card-wrap .card');
  if (form){
    const childSel = `
      .row-2col .input-block,
      .input-block,
      .form-actions
    `;
    const nodes = form.querySelectorAll(childSel);
    nodes.forEach((el, i)=>{
      el.classList.add('reveal-part');
      el.style.transitionDelay = (70 * i) + 'ms'; 
      parts.push(el);
    });
  }

  const io = new IntersectionObserver((entries)=>{
    entries.forEach(entry=>{
      const cls = entry.target.classList;
      if (entry.isIntersecting) cls.add('in'); else cls.remove('in');
    });
  }, { threshold: 0.15, rootMargin: '0px 0px -5% 0px' });

  [...tops, ...parts].forEach(el => el && io.observe(el));

  requestAnimationFrame(()=>{
    [...tops, ...parts].forEach(el=>{
      if (!el) return;
      const r = el.getBoundingClientRect();
      const vh = window.innerHeight || document.documentElement.clientHeight;
      if (r.top < vh * 0.85 && r.bottom > 0) el.classList.add('in');
    });
  });
})();
</script>
</body>
</html>
