<?php
// Admin panel shared header + sidebar
$adminPage = isset($adminPage) ? $adminPage : basename($_SERVER['PHP_SELF'], '.php');
$adminTitle = isset($adminTitle) ? $adminTitle : 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($adminTitle) ?> — VK Admin</title>
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/images/favicon.svg">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: { extend: {
        colors: {
          navy:  { DEFAULT: '#0E1B2C', 700: '#0a1520', 800: '#07101a' },
          cream: '#F4EFE6',
          amber: { DEFAULT: '#C4763A', light: '#D4894D', dark: '#A8622E' },
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
<body class="bg-gray-50 font-sans antialiased">

<!-- Mobile overlay -->
<div id="sidebar-overlay" class="fixed inset-0 z-30 bg-navy/60 hidden lg:hidden" onclick="toggleSidebar()"></div>

<!-- ── Sidebar ───────────────────────────────────────────────────────────── -->
<aside id="sidebar" class="fixed left-0 top-0 h-full w-64 bg-navy z-40 flex flex-col transition-transform duration-300 -translate-x-full lg:translate-x-0">
  <!-- Logo -->
  <div class="flex items-center gap-3 p-5 border-b border-white/10">
    <div class="w-9 h-9 rounded-full bg-amber flex items-center justify-center shrink-0">
      <span class="font-display font-bold text-white text-sm">VK</span>
    </div>
    <div>
      <div class="font-display font-semibold text-white text-sm leading-tight">VK Tent Services</div>
      <div class="text-white/40 text-xs">Admin Panel</div>
    </div>
    <button class="ml-auto lg:hidden text-white/40 hover:text-white" onclick="toggleSidebar()">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
    </button>
  </div>

  <!-- Navigation -->
  <nav class="flex-1 overflow-y-auto p-3 space-y-0.5">
    <?php
    $navItems = [
      ['href' => 'dashboard.php',  'icon' => 'home',      'label' => 'Dashboard',   'page' => 'dashboard'],
      ['href' => 'enquiries.php',  'icon' => 'mail',      'label' => 'Enquiries',   'page' => 'enquiries',  'badge' => $newEnquiries],
      ['href' => 'bookings.php',   'icon' => 'calendar',  'label' => 'Bookings',    'page' => 'bookings'],
      ['href' => 'calendar.php',   'icon' => 'cal-view',  'label' => 'Calendar',    'page' => 'calendar'],
      ['href' => 'events.php',     'icon' => 'flag',      'label' => 'Events',      'page' => 'events'],
      ['href' => 'payments.php',   'icon' => 'currency',  'label' => 'Payments',    'page' => 'payments'],
      ['href' => 'media.php',      'icon' => 'image',     'label' => 'Gallery',     'page' => 'media'],
      ['href' => 'settings.php',   'icon' => 'settings',  'label' => 'Settings',    'page' => 'settings'],
    ];
    $icons = [
      'home'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
      'mail'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
      'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
      'cal-view' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>',
      'flag'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 21V3m0 0l9 3 9-3v13l-9 3-9-3V3z"/>',
      'currency' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z"/>',
      'image'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
      'settings' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
    ];
    foreach ($navItems as $item):
      $isActive = ($adminPage === $item['page']);
    ?>
    <a href="<?= BASE_URL ?>/admin/<?= $item['href'] ?>"
       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
              <?= $isActive ? 'bg-amber text-white' : 'text-white/60 hover:text-white hover:bg-white/5' ?>">
      <svg class="w-4.5 h-4.5 w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
        <?= $icons[$item['icon']] ?? '' ?>
      </svg>
      <span><?= $item['label'] ?></span>
      <?php if (!empty($item['badge']) && $item['badge'] > 0): ?>
      <span class="ml-auto bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
        <?= min($item['badge'], 9) ?><?= $item['badge'] > 9 ? '+' : '' ?>
      </span>
      <?php endif; ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <!-- Bottom -->
  <div class="border-t border-white/10 p-3">
    <a href="<?= BASE_URL ?>/index.php" target="_blank"
       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-white/40 hover:text-white hover:bg-white/5 transition-all">
      <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
      View Website
    </a>
    <a href="<?= BASE_URL ?>/admin/logout.php"
       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-all mt-1">
      <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      Logout
    </a>
  </div>
</aside>

<!-- ── Main area ──────────────────────────────────────────────────────────── -->
<div class="lg:ml-64 min-h-screen flex flex-col">

  <!-- Top bar -->
  <header class="bg-white border-b border-gray-100 px-4 lg:px-6 py-3 flex items-center justify-between sticky top-0 z-20">
    <div class="flex items-center gap-3">
      <button onclick="toggleSidebar()" class="lg:hidden text-gray-400 hover:text-navy transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <div>
        <h1 class="font-display font-bold text-navy text-lg leading-tight"><?= e($adminTitle) ?></h1>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <?php if ($newEnquiries > 0): ?>
      <a href="<?= BASE_URL ?>/admin/enquiries.php" class="relative text-gray-400 hover:text-navy transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center"><?= $newEnquiries ?></span>
      </a>
      <?php endif; ?>
      <div class="flex items-center gap-2 text-sm text-gray-600">
        <div class="w-8 h-8 rounded-full bg-amber/10 flex items-center justify-center">
          <span class="text-amber font-bold text-xs"><?= strtoupper(substr($currentUser['name'], 0, 2)) ?></span>
        </div>
        <span class="hidden sm:block font-medium text-navy"><?= e($currentUser['name']) ?></span>
      </div>
    </div>
  </header>

  <!-- Page content -->
  <main class="flex-1 p-4 lg:p-6">
