<?php

session_start();

require __DIR__ . '/db.php'; 
if ($pdo instanceof PDO) { $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); }

$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) { require $autoload; }
require __DIR__ . '/mail.config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function clean($v){ return trim((string)$v); }
function ext_from_mime(string $mime): string {
  return match ($mime) {
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
    default      => 'bin',
  };
}

function mustMailer(): PHPMailer {
  if (!class_exists('\\PHPMailer\\PHPMailer\\PHPMailer')) {
    throw new Exception('PHPMailer not installed. Run `composer require phpmailer/phpmailer`.');
  }
  $m = new PHPMailer(true);
  $m->isSMTP();
  $m->Host = SMTP_HOST;
  $m->SMTPAuth = true;
  $m->Username = SMTP_USER;
  $m->Password = SMTP_PASS;
  $m->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  $m->Port = SMTP_PORT;
  $m->CharSet = 'UTF-8';
  $m->setFrom(SMTP_FROM, SMTP_FROM_NAME);
  return $m;
}

function redirect_front(bool $ok, string $msg = ''): void {
  $qs = $ok ? 'sent=1' : ('err=' . rawurlencode($msg));
  header('Location: ../frontend/contact.php?' . $qs);
  exit;
}

$reqPath   = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$self      = basename($reqPath);
$userName  = $_SESSION['name']  ?? 'Admin';
$userEmail = $_SESSION['email'] ?? 'paramount_admin@example.com';

if (!function_exists('alFile')) {
  function alFile(string $url, string $label, string $icon): void {
    global $self;
    $urlPath  = basename(parse_url($url, PHP_URL_PATH) ?? '');
    $isActive = ($self === $urlPath);
    $cls      = $isActive ? 'aside-link active' : 'aside-link';
    echo '<a class="'.$cls.'" href="'.$url.'"><i class="bi '.$icon.'"></i><span>'.$label.'</span></a>';
  }
}

$flash = [
  'ok'  => $_SESSION['flash_ok']  ?? [],
  'err' => $_SESSION['flash_err'] ?? [],
];
unset($_SESSION['flash_ok'], $_SESSION['flash_err']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['public_submit'])) {
  $first = clean($_POST['first'] ?? '');
  $last  = clean($_POST['last']  ?? '');
  $email = clean($_POST['email'] ?? '');
  $msg   = clean($_POST['message'] ?? '');

  $_SESSION['last_contact_post'] = ['first'=>$first,'last'=>$last,'email'=>$email,'message'=>$msg];

  if ($first === '' || $last === '' || $email === '' || $msg === '') {
    redirect_front(false, 'Please fill out all fields.');
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_front(false, 'Please enter a valid email address.');
  }

  try {
    $ip  = $_SERVER['REMOTE_ADDR'] ?? null;
    $ua  = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250);
    $ins = $pdo->prepare("INSERT INTO contact_messages (first,last,email,message,ip,user_agent) VALUES (?,?,?,?,?,?)");
    $ins->execute([$first,$last,$email,$msg,$ip,$ua]);
    $msgId = (int)$pdo->lastInsertId();
    try { $pdo->prepare("UPDATE contact_messages SET status='new' WHERE id=?")->execute([$msgId]); } catch (Throwable $e) {}

    $first_s = str_replace(["\r", "\n"], '', $first);
    $last_s  = str_replace(["\r", "\n"], '', $last);
    $email_s = str_replace(["\r", "\n"], '', $email);

    $mail = mustMailer();
    $mail->clearAllRecipients();
    $mail->addAddress(SMTP_TO, SMTP_TO_NAME ?: 'Inbox');
    $mail->addReplyTo($email_s, "$first_s $last_s");

    $mail->isHTML(true);
    $mail->Subject = "New contact message from $first_s $last_s";

    $mail->Body = '<p>You received a new message!</p>'
                . '<p><strong>From:</strong> ' . h("$first_s $last_s") . '</p>'
                . '<p><strong>Email:</strong> <a href="mailto:' . h($email_s) . '">'
                . h($email_s) . '</a></p>'
                . '<p><strong>Message:</strong><br>' . nl2br(h($msg)) . '</p>';

    $mail->AltBody = "You received a new message!\n\n"
                  . "From: $first_s $last_s\n"
                  . "Email: $email_s\n\n"
                  . "Message:\n"
                  . wordwrap($msg, 70) . "\n";

    $mail->send();

    $ack = mustMailer();
    $ack->clearAllRecipients();
    $ack->addAddress($email, "$first $last");
    $ack->addReplyTo(SMTP_TO, SMTP_TO_NAME ?: 'Inbox');
    $ack->Subject = "We received your message";
    $ack->Body = "Hi $first,\n\nThanks for reaching out. We received your message and will get back to you soon.\n\n— Copy of your message —\n$msg\n";
    $ack->send();

    redirect_front(true);
  } catch (Throwable $e) {
    redirect_front(false, 'Error sending message. Please try again later.');
  }
}

$csStmt = $pdo->query("SELECT * FROM contact_settings WHERE id=1");
$cs = $csStmt->fetch(PDO::FETCH_ASSOC) ?: [
  'id'         => 1,
  'hero_image' => '',
  'headline'   => '',
  'subtext'    => '',
  'address'    => '',
  'phone'      => '',
  'email'      => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_settings') {
  $headline = clean($_POST['headline'] ?? '');
  $subtext  = clean($_POST['subtext']  ?? '');
  $address  = clean($_POST['address']  ?? '');
  $phone    = clean($_POST['phone']    ?? '');
  $emailSet = clean($_POST['email']    ?? '');

  if ($headline === '' || $subtext === '' || $address === '' || $phone === '' || $emailSet === '') {
    $flash['err'][] = 'All fields are required.';
  } elseif (!filter_var($emailSet, FILTER_VALIDATE_EMAIL)) {
    $flash['err'][] = 'Please enter a valid email for the contact page.';
  } else {
    $newHero = '';
    if (!empty($_FILES['hero']['name']) && is_uploaded_file($_FILES['hero']['tmp_name'])) {
      $tmp   = $_FILES['hero']['tmp_name'];
      $mime  = mime_content_type($tmp) ?: '';
      if (!str_starts_with($mime, 'image/')) {
        $flash['err'][] = 'Hero image must be an image file.';
      } else {
        $fsDir = rtrim($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__), '/').'/uploads';
        if (!is_dir($fsDir)) { @mkdir($fsDir, 0775, true); }
        $ext  = ext_from_mime($mime);
        $name = 'hero_'.date('Ymd_His').'_'.bin2hex(random_bytes(3)).'.'.$ext;
        $dst  = $fsDir.'/'.$name;
        if (move_uploaded_file($tmp, $dst)) {
          $newHero = '/uploads/'.$name;
        } else {
          $flash['err'][] = 'Failed to move uploaded hero image.';
        }
      }
    }

    if (empty($flash['err'])) {
      if ($cs && !empty($cs['id'])) {
        if ($newHero) {
          $upd = $pdo->prepare("UPDATE contact_settings
                                SET hero_image=?, headline=?, subtext=?, address=?, phone=?, email=?, updated_at=NOW()
                                WHERE id=1");
          $upd->execute([$newHero,$headline,$subtext,$address,$phone,$emailSet]);
          $cs['hero_image'] = $newHero;
        } else {
          $upd = $pdo->prepare("UPDATE contact_settings
                                SET headline=?, subtext=?, address=?, phone=?, email=?, updated_at=NOW()
                                WHERE id=1");
          $upd->execute([$headline,$subtext,$address,$phone,$emailSet]);
        }
        $flash['ok'][] = 'Contact page settings updated.';
      } else {
        $ins = $pdo->prepare("INSERT INTO contact_settings (id,hero_image,headline,subtext,address,phone,email,updated_at)
                              VALUES (1,?,?,?,?,?,?,NOW())");
        $ins->execute([$newHero,$headline,$subtext,$address,$phone,$emailSet]);
        $flash['ok'][] = 'Contact page settings saved.';
        $cs['hero_image'] = $newHero;
      }

      $cs = array_merge($cs, [
        'headline' => $headline,
        'subtext'  => $subtext,
        'address'  => $address,
        'phone'    => $phone,
        'email'    => $emailSet,
      ]);
    }
  }
}

$err   = isset($_GET['err']) ? (string)$_GET['err'] : '';
$page  = max(1, (int)($_GET['p'] ?? 1));
$per   = 10;
$off   = ($page - 1) * $per;
$total = (int)$pdo->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn();
$pages = max(1, (int)ceil($total / $per));

$stmt = $pdo->prepare('SELECT * FROM contact_messages ORDER BY id DESC LIMIT :lim OFFSET :off');
$stmt->bindValue(':lim', $per, PDO::PARAM_INT);
$stmt->bindValue(':off', $off, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin — Contact</title>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="asset/css/adminContact.css">
</head>
<body>
<div class="admin-shell">
  <aside class="admin-aside">
    <div class="aside-user">
      <div>
        <div class="name"><?= h($userName) ?></div>
        <small style="color:#8a8a8a"><?= h($userEmail) ?></small>
      </div>
    </div>
    <nav class="aside-nav">
      <?php
        alFile('admin.php','Dashboard','bi-grid');
        alFile('adminAbout.php','About','bi-info-circle');
        alFile('adminTeam.php','Team','bi-person-badge');
        alFile('adminProject.php','Projects','bi-kanban');
        alFile('adminBlog.php','Blogs','bi-journal-text');
        alFile('adminContact.php','Contacts','bi-envelope');
        alFile('adminPartner.php','Partners','bi-people');
        alFile('adminSettings.php','Settings','bi-gear');
        alFile('logout.php','Logout','bi-box-arrow-right');
      ?>
    </nav>
    <div class="aside-footer">
      <div class="brand-pill">
        <strong style="font-size:12px">PARAMOUNT<br>DEVELOPMENT GROUP</strong>
      </div>
    </div>
  </aside>

  <main class="admin-main">
    <?php foreach($flash['ok'] as $m): ?>
      <div class="alert success"><i class="bi bi-check-circle me-2"></i><?= h($m) ?></div>
    <?php endforeach; ?>
    <?php foreach($flash['err'] as $m): ?>
      <div class="alert danger"><i class="bi bi-exclamation-triangle me-2"></i><?= h($m) ?></div>
    <?php endforeach; ?>

    <div class="main-card">
      <h2 class="card-title">Contact Page Content</h2>
      <form method="post" enctype="multipart/form-data" class="grid-2">
        <input type="hidden" name="action" value="save_settings">
        <div class="stack">
          <label class="form-label">Headline</label>
          <input class="form-control" type="text" name="headline" value="<?= h($cs['headline']) ?>" required>
        </div>
        <div class="stack">
          <label class="form-label">Subtext</label>
          <textarea class="form-control" name="subtext" rows="3" required><?= h($cs['subtext']) ?></textarea>
        </div>
        <div class="stack">
          <label class="form-label">Address</label>
          <input class="form-control" type="text" name="address" value="<?= h($cs['address']) ?>" required>
        </div>
        <div class="stack">
          <label class="form-label">Phone</label>
          <input class="form-control" type="text" name="phone" value="<?= h($cs['phone']) ?>" required>
        </div>
        <div class="stack">
          <label class="form-label">Email (receives email)</label>
          <input class="form-control" type="email" name="email" value="<?= h($cs['email']) ?>" required>
        </div>
        <div class="stack">
          <label class="form-label">Hero Image (optional)</label>
          <input class="form-control" type="file" name="hero" accept="image/*">
          <small>Current: <code><?= h($cs['hero_image']) ?></code></small>
          <div style="margin-top:8px">
            <?php if (!empty($cs['hero_image'])): ?>
              <img src="<?= h($cs['hero_image']) ?>" alt="" style="max-width:320px;border-radius:10px">
            <?php endif; ?>
          </div>
        </div>
        <div class="form-actions">
          <button class="btn" type="submit"><i class="bi bi-save me-1"></i>Save</button>
        </div>
      </form>
    </div>

    <div class="main-card">
      <h2 class="card-title">Contact Inbox</h2>

      <?php if ($err): ?><div class="alert err"><i class="bi bi-exclamation-triangle"></i> <?= h($err) ?></div><?php endif; ?>
      
      <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th style="width:260px">From</th>
            <th>Message</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr id="msg-<?= (int)$r['id'] ?>">
              <td>
                <div><strong><?= h($r['first'].' '.$r['last']) ?></strong></div>
                <div><a href="mailto:<?= h($r['email']) ?>"><?= h($r['email']) ?></a></div>
                <small class="mono">#<?= (int)$r['id'] ?> · <?= h($r['created_at']) ?></small>
              </td>
              <td><?= nl2br(h($r['message'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
      <div class="pager">
        <?php if ($page > 1): ?><a class="btn" href="?p=<?= $page-1 ?>">« Prev</a><?php endif; ?>
        <span class="muted">Page <?= $page ?> of <?= $pages ?></span>
        <?php if ($page < $pages): ?><a class="btn" href="?p=<?= $page+1 ?>">Next »</a><?php endif; ?>
      </div>
    </div>
  </main>
</div>
</body>
</html>
