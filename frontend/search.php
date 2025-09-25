<?php
// search.php — redirect back to blogs.php with ?q=
$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
$target = 'blogs.php';
if ($q !== '') {
  $target .= '?q=' . urlencode($q);
}
header("Location: $target");
exit;
