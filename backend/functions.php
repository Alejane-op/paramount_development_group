<?php
// backend/functions.php
require_once __DIR__ . '/db.php';

/**
 * Filesystem and URL helpers for consistent paths.
 * Adjust BASE_URL if your project lives in a subfolder.
 */
define('ROOT_FS', realpath(__DIR__ . '/..'));            // filesystem root of project
define('UPLOADS_FS', ROOT_FS . '/uploads');              // fs: /.../uploads
define('UPLOADS_VIDEOS_FS', UPLOADS_FS . '/videos');     // fs: /.../uploads/videos

// If project is the web root, leave BASE_URL = ''.
// If project is served at e.g. https://site.com/paramount, set BASE_URL = '/paramount'.
define('BASE_URL', '');                                  // web base
define('UPLOADS_URL', BASE_URL . '/uploads');            // web: /uploads
define('UPLOADS_VIDEOS_URL', UPLOADS_URL . '/videos');   // web: /uploads/videos

/**
 * HERO VIDEO HELPERS
 */

// Get the latest uploaded hero video path, or null if none
function get_hero_video(PDO $pdo): ?string {
  $stmt = $pdo->query("SELECT video_path FROM hero_video ORDER BY id DESC LIMIT 1");
  $row = $stmt->fetch();
  return $row ? $row['video_path'] : null;
}

// Insert a new video path (we truncate before insert to keep only one)
function set_hero_video(PDO $pdo, string $path): bool {
  $stmt = $pdo->prepare("INSERT INTO hero_video (video_path) VALUES (:p)");
  return $stmt->execute([':p' => $path]);
}

// Clear the table (so only one latest video exists)
function clear_hero_video(PDO $pdo): void {
  $pdo->query("TRUNCATE TABLE hero_video");
}

