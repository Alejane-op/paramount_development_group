<?php

session_start();
require __DIR__ . '/db.php';

const REMEMBER_COOKIE = 'remember_me';

if (!empty($_SESSION['user_id'])) {
  $stmt = $pdo->prepare('UPDATE users SET remember_token_hash = NULL, remember_token_expires = NULL WHERE id = ?');
  $stmt->execute([$_SESSION['user_id']]);
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

setcookie(REMEMBER_COOKIE, '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);

header('Location: login.php');
exit;
