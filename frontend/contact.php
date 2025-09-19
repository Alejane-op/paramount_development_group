<?php
// frontend/contact.php — success modal + no "Your submission" box
session_start();
require_once __DIR__ . '/../backend/db.php';
if ($pdo instanceof PDO) { $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); }
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// Optional: pull display settings if you have them
try {
  $stmt = $pdo->query('SELECT * FROM contact_settings WHERE id=1');
  $cs = $stmt->fetch() ?: [];
} catch (Throwable $e) { $cs = []; }

// Flags from redirect
$ok  = isset($_GET['sent']) && $_GET['sent'] === '1';
$err = isset($_GET['err'])  ? (string)$_GET['err'] : '';

// We no longer show "Your submission" box;
// still clear any old flash to avoid growing the session.
unset($_SESSION['last_contact_post']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
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
      <!-- error stays as inline alert so visible even if JS is blocked -->
      <div class="alert err"><i class="bi bi-exclamation-triangle"></i> <?= h($err) ?></div>
    <?php endif; ?>

  <!-- contact.php -->
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

<!-- Success Modal (shown when ?sent=1) -->
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
// Show success modal if redirected with ?sent=1
(function(){
  const params = new URLSearchParams(window.location.search);
  const show = params.get('sent') === '1';
  const modal = document.getElementById('successModal');
  const okBtn = document.getElementById('okBtn');

  function closeModal(){
    modal.classList.remove('show');
    // Clean the URL (?sent=1) so refresh won't show again
    const url = new URL(window.location);
    url.searchParams.delete('sent');
    history.replaceState({}, '', url);
  }

  if (show && modal){
    modal.classList.add('show');
    okBtn?.addEventListener('click', closeModal);
    // Close on backdrop click or Esc
    modal.addEventListener('click', (e)=>{ if(e.target === modal) closeModal(); });
    document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape') closeModal(); });
  }
})();
</script>
</body>
</html>
