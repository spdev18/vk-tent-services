<?php
// Public site header — included at the top of every public page
// $pageTitle and $pageDesc should be set by the including file.
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$settings  = DB::settings();
$bizName   = $settings['business_name'] ?? 'VK Tent Services';
$tagline   = $settings['tagline']       ?? '';
$pageTitle = isset($pageTitle) ? e($pageTitle) . ' | ' . e($bizName) : e($bizName) . ' — Event & Tent Specialists';
$pageDesc  = isset($pageDesc)  ? e($pageDesc)  : e($tagline);
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?></title>
  <meta name="description" content="<?= $pageDesc ?>">
  <meta name="theme-color" content="#0E1B2C">

  <!-- Open Graph -->
  <meta property="og:title"       content="<?= $pageTitle ?>">
  <meta property="og:description" content="<?= $pageDesc ?>">
  <meta property="og:type"        content="website">
  <meta property="og:url"         content="<?= e(BASE_URL) ?>">

  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/images/favicon.svg">
  <link rel="apple-touch-icon"          href="<?= BASE_URL ?>/assets/images/favicon.svg">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            navy:  { DEFAULT: '#0E1B2C', 700: '#0a1520', 800: '#07101a' },
            cream: { DEFAULT: '#F4EFE6', dark: '#EBE4D8' },
            amber: { DEFAULT: '#C4763A', light: '#D4894D', dark: '#A8622E' },
          },
          fontFamily: {
            sans:    ['Inter', 'sans-serif'],
            display: ['"Playfair Display"', 'serif'],
          }
        }
      }
    }
  </script>

  <!-- Custom CSS -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
</head>
<body class="bg-cream text-navy font-sans antialiased">

<!-- ── VK Loader ─────────────────────────────────────────────────────────── -->
<div id="vk-loader" class="fixed inset-0 z-[9999] flex items-center justify-center bg-navy">
  <div class="loader-content text-center">
    <svg class="vk-logo w-24 h-24 mx-auto mb-4" viewBox="0 0 120 80" fill="none" xmlns="http://www.w3.org/2000/svg">
      <text class="vk-text" x="50%" y="68" text-anchor="middle" font-family="Playfair Display, serif"
            font-size="72" font-weight="700" fill="#C4763A">VK</text>
    </svg>
    <div class="loader-bar mx-auto mt-4 h-0.5 w-32 bg-amber/30 overflow-hidden rounded">
      <div class="loader-fill h-full bg-amber rounded"></div>
    </div>
  </div>
</div>

<!-- ── Navigation ────────────────────────────────────────────────────────── -->
<header id="site-header" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300">
  <nav class="container mx-auto px-4 lg:px-8 py-4 flex items-center justify-between">

    <!-- Logo -->
    <a href="<?= BASE_URL ?>/index.php" class="flex items-center gap-3 group">
      <div class="w-10 h-10 rounded-full bg-amber flex items-center justify-center shrink-0">
        <span class="font-display font-bold text-white text-sm leading-none">VK</span>
      </div>
      <div class="hidden sm:block">
        <div class="font-display font-bold text-white text-lg leading-tight group-hover:text-amber transition-colors">
          <?= e($bizName) ?>
        </div>
        <div class="text-white/60 text-xs">Tent &amp; Event Specialists</div>
      </div>
    </a>

    <!-- Desktop Nav -->
    <ul class="hidden md:flex items-center gap-8 text-sm font-medium">
      <?php
      $navLinks = [
        'index'   => ['href' => BASE_URL . '/index.php',   'label' => 'Home'],
        'gallery' => ['href' => BASE_URL . '/gallery.php',  'label' => 'Gallery'],
        'enquiry' => ['href' => BASE_URL . '/enquiry.php',  'label' => 'Book Now'],
      ];
      foreach ($navLinks as $page => $link):
        $isActive = ($currentPage === $page);
      ?>
        <li>
          <a href="<?= $link['href'] ?>"
             class="<?= $isActive ? 'text-amber' : 'text-white hover:text-amber' ?> transition-colors">
            <?= $link['label'] ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <!-- CTA + Hamburger -->
    <div class="flex items-center gap-4">
      <a href="<?= BASE_URL ?>/enquiry.php"
         class="hidden md:inline-flex items-center gap-2 px-5 py-2 rounded-full bg-amber text-white text-sm font-semibold hover:bg-amber-dark transition-colors">
        Enquire Now
      </a>
      <button id="mobile-menu-btn" class="md:hidden text-white p-2" aria-label="Menu">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
    </div>
  </nav>

  <!-- Mobile menu -->
  <div id="mobile-menu" class="hidden md:hidden bg-navy border-t border-white/10 px-4 pb-4">
    <ul class="flex flex-col gap-3 pt-3">
      <li><a href="<?= BASE_URL ?>/index.php"   class="block text-white py-2 hover:text-amber transition-colors">Home</a></li>
      <li><a href="<?= BASE_URL ?>/gallery.php"  class="block text-white py-2 hover:text-amber transition-colors">Gallery</a></li>
      <li><a href="<?= BASE_URL ?>/enquiry.php"  class="block text-white py-2 hover:text-amber transition-colors">Book Now</a></li>
    </ul>
    <a href="<?= BASE_URL ?>/enquiry.php"
       class="mt-4 w-full inline-block text-center px-5 py-3 rounded-full bg-amber text-white text-sm font-semibold">
      Enquire Now
    </a>
  </div>
</header>
