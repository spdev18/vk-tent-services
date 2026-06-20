<?php
/**
 * VK Tent Services — One-time Setup Script
 * Samastam Technologies Private Limited
 *
 * Run this ONCE after uploading to server: http://yourdomain.com/setup.php
 * DELETE this file immediately after setup is complete.
 */

// Basic protection — set a temporary access key here
define('SETUP_KEY', 'VKSetup2026!');
if (($_GET['key'] ?? '') !== SETUP_KEY) {
    die('<h2>Access denied.</h2><p>Pass ?key=VKSetup2026! to run setup.</p>');
}

require_once __DIR__ . '/includes/config.php';

$msgs = [];
$errors = [];

// ── Create database tables ────────────────────────────────────────────────────
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    // Create DB if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `" . DB_NAME . "`");
    $msgs[] = "✅ Database `" . DB_NAME . "` created/verified.";

    // Read and execute schema
    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    // Split by semicolons and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $stmt) {
        if ($stmt) {
            try { $pdo->exec($stmt); } catch (\Exception $e) {
                // Ignore duplicate key errors for INSERT default data
                if (strpos($e->getMessage(), 'Duplicate') === false) {
                    $errors[] = "Warning: " . substr($stmt, 0, 60) . "… → " . $e->getMessage();
                }
            }
        }
    }
    $msgs[] = "✅ Database schema installed.";
} catch (\Exception $e) {
    $errors[] = "❌ Database error: " . $e->getMessage();
}

// ── Create upload directories ─────────────────────────────────────────────────
$dirs = [UPLOADS_PATH, GALLERY_PATH, THUMB_PATH, PROFILE_PATH];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            $msgs[] = "✅ Created directory: $dir";
        } else {
            $errors[] = "❌ Failed to create: $dir — please create manually.";
        }
    } else {
        $msgs[] = "✅ Directory exists: " . basename($dir) . "/";
    }
}

// ── Verify PHP extensions ────────────────────────────────────────────────────
$required = ['pdo', 'pdo_mysql', 'gd', 'fileinfo', 'mbstring', 'session'];
foreach ($required as $ext) {
    if (extension_loaded($ext)) {
        $msgs[] = "✅ PHP extension: $ext";
    } else {
        $errors[] = "⚠️ Missing PHP extension: $ext (some features may not work)";
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>VK Tent Services — Setup</title>
  <style>
    body { font-family: system-ui, sans-serif; max-width: 680px; margin: 40px auto; padding: 20px; background: #F4EFE6; }
    h1 { color: #0E1B2C; }
    .card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 12px rgba(0,0,0,.06); margin-bottom: 20px; }
    .ok   { color: #16a34a; }
    .err  { color: #dc2626; }
    .msg  { margin: 4px 0; font-size: .9rem; }
    .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 8px; margin-top: 16px; font-size: .9rem; }
    .creds { background: #0E1B2C; color: #C4763A; border-radius: 8px; padding: 16px; font-family: monospace; font-size: 1rem; }
  </style>
</head>
<body>
<h1>🏕 VK Tent Services — Setup</h1>

<div class="card">
  <h2>Setup Results</h2>
  <?php foreach ($msgs   as $m): ?><p class="msg ok"><?= htmlspecialchars($m) ?></p><?php endforeach; ?>
  <?php foreach ($errors as $e): ?><p class="msg err"><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
</div>

<?php if (!$errors): ?>
<div class="card">
  <h2>✅ Setup Complete!</h2>
  <p>Your VK Tent Services platform is ready. Use these credentials to log in:</p>
  <div class="creds">
    URL: <a href="admin/index.php" style="color:#C4763A">admin/index.php</a><br>
    Email: admin@vktent.com<br>
    Password: Admin@1234
  </div>
  <div class="warning">
    ⚠️ <strong>Important:</strong>
    <ul>
      <li>Change the admin password immediately after login (Settings → Change Password).</li>
      <li><strong>Delete this setup.php file from the server now.</strong></li>
      <li>Update <code>includes/config.php</code> with your production database credentials and BASE_URL.</li>
      <li>Set <code>APP_ENV</code> to <code>'production'</code> in config.php before going live.</li>
    </ul>
  </div>
</div>
<?php else: ?>
<div class="card">
  <h2>⚠️ Setup had issues</h2>
  <p>Fix the errors above and run setup again, or create the database and tables manually using <code>database/schema.sql</code>.</p>
</div>
<?php endif; ?>

<p style="color:#999;font-size:.8rem;margin-top:20px;">Developed by Samastam Technologies Private Limited</p>
</body>
</html>
