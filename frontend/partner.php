<?php
// partner.php (public site)
$active = 'partner.php';
require_once __DIR__ . '/../backend/db.php';
if ($pdo instanceof PDO) {
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$market_intro    = $pdo->query("SELECT `value` FROM partner_content WHERE `key`='market_intro'")->fetchColumn() ?: '';
$invest_intro    = $pdo->query("SELECT `value` FROM partner_content WHERE `key`='invest_intro'")->fetchColumn() ?: '';
$invest_result   = $pdo->query("SELECT `value` FROM partner_content WHERE `key`='invest_result'")->fetchColumn() ?: '';
$vision_intro    = $pdo->query("SELECT `value` FROM partner_content WHERE `key`='vision_intro'")->fetchColumn() ?: '';
$community_intro = $pdo->query("SELECT `value` FROM partner_content WHERE `key`='community_intro'")->fetchColumn() ?: '';
$why_we_do_it    = $pdo->query("SELECT `value` FROM partner_content WHERE `key`='why_we_do_it'")->fetchColumn() ?: '';
$market_image    = $pdo->query("SELECT `value` FROM partner_content WHERE `key`='market_image'")->fetchColumn() ?: 'asset/Building.jpg';

$market  = $pdo->query("SELECT * FROM partner_market_bullets ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
$invest  = $pdo->query("SELECT * FROM partner_invest_items ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
$logos   = $pdo->query("SELECT * FROM partner_partners ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="icon" type="image/png" href="asset/paramount.png">
<title>Paramount Development Group — Partner With Us</title>

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="asset/css/partner.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<main class="page">
  <!-- ================= Market Opportunity ================= -->
  <section class="market cut-top">
    <div class="container market-grid">

      <!-- LEFT: Image -->
      <div class="media">
        <img class="market-fig" src="<?= h($market_image) ?>" alt="Paramount high-rise">
      </div>

      <!-- RIGHT: Title + Text -->
      <div class="copy">
        <h2>Market Opportunity</h2>
        <div class="market-text">
          <?php if ($market_intro): ?>
            <p><?= nl2br(h($market_intro)) ?></p>
          <?php endif; ?>

          <div class="why">
            <h3>Why This Market?</h3>
            <ul class="bullets">
              <?php foreach($market as $m): ?>
                <li>
                  <span class="dot" aria-hidden="true"></span>
                  <div class="text">
                    <h4><?= h($m['title']) ?></h4>
                    <p><?= h($m['body']) ?></p>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ================= Our Investment Plan ================= -->
  <section class="invest">
    <div class="container invest-grid">
      <ul class="mini-acc" id="invest-accordion">
        <?php foreach($invest as $it): $id='acc'.$it['id']; ?>
          <li>
            <button class="acc-btn" aria-expanded="false" aria-controls="<?=$id?>">
              <span><?= h($it['heading']) ?></span>
              <span class="plus" aria-hidden="true">+</span>
            </button>
            <div id="<?=$id?>" class="panel">
              <p><?= h($it['body']) ?></p>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>

      <div>
        <h2>Our Investment Plan</h2>
        <?php if ($invest_intro): ?>
          <p><?= nl2br(h($invest_intro)) ?></p>
        <?php endif; ?>
        <?php if ($invest_result): ?>
          <p><b>The Result:</b> <?= h($invest_result) ?></p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- ================= Vision / Target ================= -->
  <section class="vision" style="margin-top: -100px;">
    <div class="container">
      <h2>Our Vision</h2>
      <?php if ($vision_intro): ?>
        <p><?= nl2br(h($vision_intro)) ?></p>
      <?php endif; ?>

      <div class="target">
        <h3>What We Target:</h3>
        <ul class="bullets">
          <li>
            <span class="dot" aria-hidden="true"></span>
            <div class="text">
              <h4>Multifamily Focus</h4>
              <p>Value-add, stabilized, and new development multifamily projects designed to deliver
                 consistent returns and durable cash flow.</p>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </section>

  <!-- ================= Community ================= -->
  <section class="cut-gray" style="margin-top: -95px;">
    <section class="community">
      <div class="container">
        <h2>Community Involvement</h2>
        <?php if ($community_intro): ?>
          <p><?= nl2br(h($community_intro)) ?></p>
        <?php endif; ?>

        <div class="why">
          <h3>Why We Do It:</h3>
          <p><?= nl2br(h($why_we_do_it)) ?></p>
        </div>
      </div>
    </section>
  </section>

  <!-- ================= Partners ================= -->
  <section class="partners-section">
    <div class="partners-header">
      <h2>Our Company Partners</h2>
      <p>
        We are proud to work with trusted partners who share our vision and support our developments,
        helping us create lasting value for the communities we serve.
      </p>
    </div>

    <div class="company-logos">
      <button class="prev" aria-label="Previous partner">&#10094;</button>

      <div class="logo-container">
        <?php foreach($logos as $lg): ?>
          <?php $img = h($lg['logo_path']); $alt = h($lg['name']); ?>
          <?php if (!empty($lg['website'])): ?>
            <a href="<?=h($lg['website'])?>" target="_blank" rel="noopener" class="logo">
              <img src="<?=$img?>" alt="<?=$alt?>">
            </a>
          <?php else: ?>
            <img src="<?=$img?>" class="logo" alt="<?=$alt?>">
          <?php endif; ?>
        <?php endforeach; ?>
      </div>

      <button class="next" aria-label="Next partner">&#10095;</button>
    </div>
  </section>

</main>

<?php include 'footer.php'; ?>

<script>
(function () {
  const items = document.querySelectorAll('#invest-accordion .acc-btn');

  function closeAll(exceptBtn){
    items.forEach(btn=>{
      if(btn !== exceptBtn){
        const p = btn.parentElement.querySelector('.panel');
        btn.setAttribute('aria-expanded','false');
        btn.querySelector('.plus').textContent = '+';
        p.style.maxHeight = 0;
        p.classList.remove('open');
      }
    });
  }

  items.forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const panel = btn.parentElement.querySelector('.panel');
      const isOpen = btn.getAttribute('aria-expanded') === 'true';

      // optional: accordion behavior (only one open)
      closeAll(btn);

      if(!isOpen){
        btn.setAttribute('aria-expanded','true');
        btn.querySelector('.plus').textContent = '–';
        panel.classList.add('open');
        panel.style.maxHeight = panel.scrollHeight + 'px';
      }else{
        btn.setAttribute('aria-expanded','false');
        btn.querySelector('.plus').textContent = '+';
        panel.style.maxHeight = 0;
        panel.classList.remove('open');
      }
    });
  });

  // handle resize so open panels keep correct height
  window.addEventListener('resize', ()=>{
    document.querySelectorAll('#invest-accordion .panel.open').forEach(p=>{
      p.style.maxHeight = p.scrollHeight + 'px';
    });
  });
})();

(function () {
  const logos = Array.from(document.querySelectorAll('.logo'));
  const prevBtn = document.querySelector('.prev');
  const nextBtn = document.querySelector('.next');

  let i = 0;                       // active index
  let timer = null;
  const INTERVAL = 3500;           // time between slides
  const POP_MS   = 700;            // center pop duration

  function applyClasses() {
    const n = logos.length;
    const prev = (i - 1 + n) % n;
    const next = (i + 1) % n;

    logos.forEach((el, idx) => {
      el.className = 'logo is-off';
      if (idx === i)      el.className = 'logo is-active';
      else if (idx === prev) el.className = 'logo is-prev';
      else if (idx === next) el.className = 'logo is-next';
    });
  }

  // subtle elastic pop like the video when a logo becomes active
  function pop(el){
    el.animate(
      [
        { transform: 'translate(-50%, -50%) scale(0.92)' },
        { transform: 'translate(-50%, -50%) scale(1.06)' },
        { transform: 'translate(-50%, -50%) scale(1.00)' }
      ],
      { duration: POP_MS, easing: 'cubic-bezier(0.22, 1, 0.36, 1)' }
    );
  }

  function go(step = 1){
    const n = logos.length;
    i = (i + step + n) % n;
    applyClasses();
    pop(logos[i]);                 // animate the new center
  }

  function start(){ timer = setInterval(go, INTERVAL); }
  function stop(){ clearInterval(timer); }

  // init
  applyClasses(); pop(logos[i]); start();

  // arrows
  nextBtn.addEventListener('click', () => { stop(); go(1); start(); });
  prevBtn.addEventListener('click', () => { stop(); go(-1); start(); });

  // hover pause on the stage
  const stage = document.querySelector('.logo-container');
  stage.addEventListener('mouseenter', stop);
  stage.addEventListener('mouseleave', start);
})();
</script>
<script>
/* ============ Per-section fade in/out; exclude .company-logos ============ */
(function(){
  const io = new IntersectionObserver((entries)=>{
    entries.forEach(entry=>{
      const el = entry.target;
      if(entry.isIntersecting){
        el.classList.add('in');
      }else{
        el.classList.remove('in');
      }
    });
  }, {
    root: null,
    threshold: 0.2,
    rootMargin: '0px 0px -5% 0px'
  });

  function attachRevealToSection(section){
    // direct children
    const direct = Array.from(section.children);

    // inner elements to stagger (removed .logo-container .logo)
    const inner = section.querySelectorAll(
      '.container > *,' +
      '.bullets > li,' +
      '.mini-acc > li'
    );

    const seen = new Set();
    const list = [];
    [...direct, ...inner].forEach(n=>{
      if(!seen.has(n)){ seen.add(n); list.push(n); }
    });

    list.forEach((el, idx)=>{
      // SKIP anything inside the partners carousel
      if (el.closest('.company-logos')) return;

      el.setAttribute('data-reveal', idx < 6 ? '' : 'soft');
      el.style.transitionDelay = (Math.min(idx, 8) * 80) + 'ms';
      io.observe(el);
    });
  }

  document.querySelectorAll('.page section').forEach(attachRevealToSection);
})();
</script>
</body>
</html>
