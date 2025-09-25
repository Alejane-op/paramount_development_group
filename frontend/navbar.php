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

  <!-- Full-page overlay behind the menu -->
  <div class="nav-overlay" id="navOverlay" hidden></div>
</div>

<script>
(function(){
  const linksWrap  = document.getElementById('navLinks');
  const magic      = document.getElementById('magicLine');
  const hamburger  = document.getElementById('hamburger');
  const panel      = document.getElementById('mobilePanel');
  const navbar     = document.getElementById('navbar');
  const overlay    = document.getElementById('navOverlay');
  const wrap       = document.querySelector('.navbar-wrap');

  const pageEl = document.querySelector('.page') || document.body;

  /* ===== Moving underline ===== */
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
  function activeLink(){ return linksWrap?.querySelector('a.is-active') || linksWrap?.querySelector('a'); }
  function initMagic(){ const a = activeLink(); if (a) placeMagic(a); }
  window.addEventListener('load', initMagic);
  window.addEventListener('resize', initMagic);
  if (linksWrap){
    linksWrap.addEventListener('mouseenter', e=>{ const t = e.target.closest('a'); if (t) placeMagic(t); }, true);
    linksWrap.addEventListener('mousemove',  e=>{ const t = e.target.closest('a'); if (t) placeMagic(t); });
    linksWrap.addEventListener('mouseleave', ()=>{ const a = activeLink(); if (a) placeMagic(a); });
  }

  /* ===== Scoped freeze ===== */
  let scrollYBeforeOpen = 0;
  function freezePage(){
    scrollYBeforeOpen = window.scrollY || window.pageYOffset || 0;
    if (wrap) wrap.classList.add('is-open');

    pageEl.classList.add('nav-locked');
    pageEl.style.position = 'fixed';
    pageEl.style.top = `-${scrollYBeforeOpen}px`;
    pageEl.style.left = '0';
    pageEl.style.right = '0';
    pageEl.style.width = '100%';
    pageEl.style.height = '100dvh';
    pageEl.style.overflow = 'hidden';
    pageEl.style.zIndex = '0';
  }
  function unfreezePage(){
    pageEl.classList.remove('nav-locked');
    pageEl.style.position = '';
    pageEl.style.top = '';
    pageEl.style.left = '';
    pageEl.style.right = '';
    pageEl.style.width = '';
    pageEl.style.height = '';
    pageEl.style.overflow = '';
    pageEl.style.zIndex = '';
    if (wrap) wrap.classList.remove('is-open');
    window.scrollTo(0, scrollYBeforeOpen);
  }

  /* ===== Hamburger toggle ===== */
  function setPanel(open){
    hamburger.setAttribute('aria-expanded', String(open));
    panel.hidden = !open;
    panel.style.display = open ? 'block' : 'none';
    if (overlay) overlay.hidden = !open;
    if (open) freezePage(); else unfreezePage();
  }

  hamburger.addEventListener('click', ()=>{
    const open = hamburger.getAttribute('aria-expanded') !== 'true';
    setPanel(open);
  });

  window.addEventListener('resize', ()=>{
    if (window.innerWidth > 960 && hamburger.getAttribute('aria-expanded') === 'true'){
      setPanel(false);
    }
  });

  panel.addEventListener('click', e=>{
    if (e.target.closest('a')) setPanel(false);
  });

  if (overlay){ overlay.addEventListener('click', ()=> setPanel(false)); }

  window.addEventListener('keydown', (e)=>{
    if (e.key === 'Escape' && hamburger.getAttribute('aria-expanded') === 'true'){
      setPanel(false);
    }
  });

  /* ===== Scroll-to-white ===== */
  function handleScroll(){
    if (wrap && wrap.classList.contains('is-open')) return;
    if (window.scrollY > 20) navbar.classList.add('scrolled');
    else navbar.classList.remove('scrolled');
  }
  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll();
})();
</script>
