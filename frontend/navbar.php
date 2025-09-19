<?php
/* ---- Config (optional) ---- */
$brandName = $brandName ?? 'Paramount Development Group';
$brandLogo = $brandLogo ?? 'asset/hori-logo.png'; // change path if needed

// If a page sets $active, use it; else detect from URL
if (!isset($active) || !$active) {
  $active = strtolower(basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
  $active = $active ?: 'index.php';
}

/* Menu items: label => url */
$navItems = $navItems ?? [
  'Home'            => 'index.php',
  'About'           => 'about.php',
  'Teams'           => 'team.php',
  'Partner With Us' => 'partner.php',
  'Projects'        => 'projects.php',
  'Blogs'           => 'blogs.php',
  'Contact'         => 'contact.php',
];
?>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="asset/css/navbar.css">

<div class="navbar-wrap">
  <div class="navbar" id="navbar">
    <a class="nav-brand" href="index.php" aria-label="<?= htmlspecialchars($brandName) ?>">
      <img src="<?= htmlspecialchars($brandLogo) ?>" alt="Logo">
      <span><?= htmlspecialchars($brandName) ?></span>
    </a>

    <nav class="nav-links" id="navLinks">
      <?php foreach ($navItems as $label => $url):
        $isActive = (strcasecmp($active, $url) === 0);
      ?>
        <a href="<?= htmlspecialchars($url) ?>" class="<?= $isActive ? 'is-active' : '' ?>">
          <?= htmlspecialchars($label) ?>
        </a>
      <?php endforeach; ?>
      <div class="magic-line" id="magicLine"></div>
    </nav>

    <button class="hamburger" id="hamburger" aria-label="Menu" aria-expanded="false" aria-controls="mobilePanel">
      <span class="bar"></span><span class="bar"></span><span class="bar"></span>
    </button>
  </div>

  <div class="mobile-panel" id="mobilePanel" hidden>
    <div class="mobile-panel-inner">
      <?php foreach ($navItems as $label => $url):
        $isActive = (strcasecmp($active, $url) === 0);
      ?>
        <a href="<?= htmlspecialchars($url) ?>" class="mobile-link <?= $isActive ? 'is-active' : '' ?>">
          <?= htmlspecialchars($label) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
(function(){
  const linksWrap = document.getElementById('navLinks');
  const magic = document.getElementById('magicLine');
  const hamburger = document.getElementById('hamburger');
  const panel = document.getElementById('mobilePanel');
  const navbar = document.getElementById('navbar');

  /* Moving underline (short & centered) */
  function placeMagic(el){
    if (!el || !linksWrap || !magic) return;
    const wrapRect = linksWrap.getBoundingClientRect();
    const r = el.getBoundingClientRect();

    const underlineW = Math.min(Math.max(r.width * 0.6, 40), 64);
    const left = (r.left - wrapRect.left) + (r.width - underlineW)/2;

    magic.style.width = underlineW + 'px';
    magic.style.left  = left + 'px';
    magic.style.opacity = 1;
  }
  function activeLink(){
    return linksWrap?.querySelector('a.is-active') || linksWrap?.querySelector('a');
  }
  function initMagic(){
    const a = activeLink();
    if (a) placeMagic(a);
  }
  window.addEventListener('load', initMagic);
  window.addEventListener('resize', initMagic);

  if (linksWrap){
    linksWrap.addEventListener('mouseenter', e=>{
      const t = e.target.closest('a'); if (t) placeMagic(t);
    }, true);
    linksWrap.addEventListener('mousemove', e=>{
      const t = e.target.closest('a'); if (t) placeMagic(t);
    });
    linksWrap.addEventListener('mouseleave', ()=>{
      const a = activeLink(); if (a) placeMagic(a);
    });
  }

  /* Hamburger toggle (robust) */
  function setPanel(open){
    hamburger.setAttribute('aria-expanded', String(open));
    if (open){
      panel.hidden = false;
      panel.style.display = 'block';
    } else {
      panel.style.display = 'none';
      panel.hidden = true;
    }
  }
  hamburger.addEventListener('click', ()=>{
    setPanel(hamburger.getAttribute('aria-expanded') !== 'true');
  });

  /* Close mobile panel when resizing back to desktop or when a link is clicked */
  window.addEventListener('resize', ()=>{
    if (window.innerWidth > 960 && hamburger.getAttribute('aria-expanded') === 'true'){
      setPanel(false);
    }
  });
  panel.addEventListener('click', e=>{
    if (e.target.closest('a')) setPanel(false);
  });

  /* ===== Change navbar background on scroll ===== */
  function handleScroll(){
    if (window.scrollY > 20){
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  }
  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll(); // initialize on load in case user reloads mid-page
})();
</script>
