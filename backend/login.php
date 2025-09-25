<?php
session_start();
require __DIR__ . '/db.php';

const REMEMBER_COOKIE = 'remember_me';
const COOKIE_DAYS = 30;

if (empty($_SESSION['user_id']) && !empty($_COOKIE[REMEMBER_COOKIE])) {
    [$selector, $validator] = explode(':', $_COOKIE[REMEMBER_COOKIE]) + [null, null];
    if ($selector && $validator) {
        $stmt = $pdo->prepare('SELECT id, name, email, remember_token_hash, remember_token_expires
                               FROM users WHERE id = ?');
        $stmt->execute([(int)$selector]);
        if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($user['remember_token_hash'])
                && !empty($user['remember_token_expires'])
                && new DateTime($user['remember_token_expires']) > new DateTime()
                && hash_equals($user['remember_token_hash'], hash('sha256', $validator))) {

                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['name']    = $user['name'];
                $_SESSION['email']   = $user['email'];
                header('Location: admin.php');
                exit;
            }
        }
    }
}

if (!empty($_SESSION['user_id'])) {
    header('Location: admin.php');
    exit;
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = !empty($_POST['remember']);

        $stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Invalid email or password.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['email']   = $user['email'];

            if ($remember) {
                $validator = bin2hex(random_bytes(32));
                $hash     = hash('sha256', $validator);
                $expires  = (new DateTime("+".COOKIE_DAYS." days"))->format('Y-m-d H:i:s');

                $upd = $pdo->prepare('UPDATE users
                                      SET remember_token_hash = ?, remember_token_expires = ?
                                      WHERE id = ?');
                $upd->execute([$hash, $expires, $user['id']]);

                $cookieValue = $user['id'] . ':' . $validator;
                setcookie(
                    REMEMBER_COOKIE,
                    $cookieValue,
                    [
                        'expires'  => time() + (60*60*24*COOKIE_DAYS),
                        'path'     => '/',
                        'secure'   => !empty($_SERVER['HTTPS']),
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]
                );
            }

            header('Location: admin.php');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="asset/css/login.css" />
</head>
<body>
  <section class="login-page">
    <div class="card">
      <img class="brand" src="asset/logo.png" alt="Paramount Logo" />
      <p class="subtitle">Paramount Development Admin</p>

      <?php if (!empty($errors)): ?>
        <div class="msg error">
          <ul><?php foreach ($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul>
        </div>
      <?php endif; ?>

      <form method="post" action="">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

        <label class="field">
          <span class="field-label">Email</span>
          <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </label>

        <label class="field">
          <span class="field-label">Password</span>
          <input type="password" name="password" required>
        </label>

        <label class="remember">
          <input type="checkbox" name="remember" value="1"> Remember me
        </label>
        <div class="btn-container">
          <button type="submit" class="btn">Log in</button>
        </div>     
      </form>
    </div>
  </section>
</body>
</html>
