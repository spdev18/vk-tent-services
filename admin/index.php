<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

Auth::startSession();

// Redirect if already logged in
if (Auth::check()) {
    redirect(BASE_URL . '/admin/dashboard.php');
}

$error   = '';
$timeout = !empty($_GET['timeout']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = post('email');
    $password = post('password');
    $csrf     = $_POST[CSRF_TOKEN_NAME] ?? '';

    if (!Auth::verifyCsrf($csrf)) {
        $error = 'Security token mismatch. Please refresh and try again.';
    } else {
        $result = Auth::login($email, $password);
        if ($result['success']) {
            redirect(BASE_URL . '/admin/dashboard.php');
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — VK Tent Services</title>
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/images/favicon.svg">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: { extend: {
        colors: {
          navy:  { DEFAULT: '#0E1B2C' },
          cream: '#F4EFE6',
          amber: { DEFAULT: '#C4763A', dark: '#A8622E' },
        },
        fontFamily: {
          sans:    ['Inter', 'sans-serif'],
          display: ['"Playfair Display"', 'serif'],
        }
      }}
    }
  </script>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
  <meta name="robots" content="noindex,nofollow">
</head>
<body class="min-h-screen bg-navy flex items-center justify-center p-4">

<div class="w-full max-w-md">
  <!-- Logo -->
  <div class="text-center mb-8">
    <div class="w-16 h-16 rounded-full bg-amber flex items-center justify-center mx-auto mb-4">
      <span class="font-display font-bold text-white text-xl">VK</span>
    </div>
    <h1 class="font-display font-bold text-white text-2xl">VK Tent Services</h1>
    <p class="text-white/40 text-sm mt-1">Owner Admin Panel</p>
  </div>

  <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
    <div class="bg-amber p-6">
      <h2 class="font-display font-bold text-white text-xl">Sign In</h2>
      <p class="text-white/70 text-sm">Access your management dashboard</p>
    </div>
    <div class="p-8">
      <?php if ($timeout): ?>
      <div class="mb-5 p-3 rounded-lg bg-amber/10 border border-amber/30 text-amber text-sm">
        Your session expired. Please sign in again.
      </div>
      <?php endif; ?>
      <?php if ($error): ?>
      <div class="mb-5 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
        <?= e($error) ?>
      </div>
      <?php endif; ?>

      <form method="post" action="" novalidate>
        <?= Auth::csrfField() ?>
        <div class="space-y-5">
          <div>
            <label for="email" class="block text-sm font-medium text-navy mb-1.5">Email Address</label>
            <input type="email" name="email" id="email" required autocomplete="email"
                   value="<?= e(post('email')) ?>"
                   class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-amber/40 focus:border-amber text-navy placeholder-gray-300 transition-all"
                   placeholder="admin@vktent.com">
          </div>
          <div>
            <label for="password" class="block text-sm font-medium text-navy mb-1.5">Password</label>
            <div class="relative">
              <input type="password" name="password" id="password" required autocomplete="current-password"
                     class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-amber/40 focus:border-amber text-navy placeholder-gray-300 transition-all pr-11"
                     placeholder="••••••••">
              <button type="button" id="toggle-pw" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-navy transition-colors" aria-label="Show password">
                <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
          </div>
          <button type="submit"
                  class="w-full py-3.5 rounded-xl bg-amber text-white font-bold text-base hover:bg-amber-dark transition-all hover:shadow-lg hover:shadow-amber/30 flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
            Sign In
          </button>
        </div>
      </form>
    </div>
  </div>

  <p class="text-center text-white/30 text-xs mt-6">
    Developed by <span class="text-white/50">Samastam Technologies Pvt. Ltd.</span>
  </p>
</div>

<script>
document.getElementById('toggle-pw').addEventListener('click', function() {
  const pw = document.getElementById('password');
  pw.type = pw.type === 'password' ? 'text' : 'password';
});
</script>
</body>
</html>
