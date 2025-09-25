<?php

session_start();
require __DIR__ . '/db.php';

if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
    $errors[] = 'Invalid CSRF token.';
  }

  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $password2 = $_POST['password_confirmation'] ?? '';

  if ($name === '' || $email === '' || $password === '') {
    $errors[] = 'All fields are required.';
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email.';
  }
  if ($password !== $password2) {
    $errors[] = 'Passwords do not match.';
  }
  if (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
  }

  if (!$errors) {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
      $errors[] = 'Email already registered.';
    } else {
      try {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, $hash]);
        $success = 'Account created! You can now log in.';
      } catch (PDOException $e) {
        $errors[] = 'Database error: '.$e->getMessage();
      }
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Sign up</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;max-width:520px;margin:40px auto;padding:0 16px}
  form{display:grid;gap:12px;border:1px solid #ddd;border-radius:12px;padding:20px}
  input[type=text],input[type=email],input[type=password]{width:100%;padding:10px;border:1px solid #ccc;border-radius:8px}
  button{padding:10px 14px;border:0;border-radius:8px;background:#111;color:#fff;cursor:pointer}
  .msg{padding:10px;border-radius:8px}
  .error{background:#ffe8e8;border:1px solid #ffb3b3}
  .success{background:#e8ffef;border:1px solid #b3ffca}
  a{color:#0a58ca;text-decoration:none}
</style>
</head>
<body>
  <h1>Create your account</h1>

  <?php if ($errors): ?>
    <div class="msg error">
      <ul><?php foreach ($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="msg success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <form method="post" action="">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
    <label>
      Full name
      <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
    </label>
    <label>
      Email
      <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </label>
    <label>
      Password
      <input type="password" name="password" required>
    </label>
    <label>
      Confirm password
      <input type="password" name="password_confirmation" required>
    </label>
    <button type="submit">Sign up</button>
  </form>

  <p>Already have an account? <a href="login.php">Log in</a></p>
</body>
</html>
