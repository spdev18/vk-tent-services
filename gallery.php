<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$settings   = DB::settings();
$categories = DB::fetchAll("SELECT * FROM gallery_categories ORDER BY sort_order ASC");

$activeSlug = get('category', 'all');
$activeId   = null;
if ($activeSlug !== 'all') {
    $cat = DB::fetchOne('SELECT id FROM gallery_categories WHERE slug=?', [$activeSlug]);
    if ($cat) $activeId = (int)$cat['id'];
}

if ($activeId) {
    $media = DB::fetchAll("SELECT * FROM gallery_media WHERE is_visible=1 AND category_id=? ORDER BY sort_order ASC, created_at DESC", [$activeId]);
} else {
    $media = DB::fetchAll("SELECT * FROM gallery_media WHERE is_visible=1 ORDER BY sort_order ASC, created_at DESC");
}

$pageTitle = 'Gallery';
$pageDesc  = 'View our portfolio of beautiful tent setups, decorations and events.';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Page Hero ──────────────────────────────────────────────────────────── -->
<section class="bg-navy pt-28 pb-14 text-center">
  <span class="inline-block text-amber text-sm font-semibold uppercase tracking-widest mb-3">Our Portfolio</span>
  <h1 class="font-display font-bold text-white text-5xl">Event Gallery</h1>
  <p class="text-white/50 mt-4 max-w-xl mx-auto">Browse photos and videos from weddings, corporate events, and social functions we've had the privilege to serve.</p>
</section>

<!-- ── Category Filter ────────────────────────────────────────────────────── -->
<section class="bg-navy border-b border-white/10 sticky top-16 z-40">
  <div class="container mx-auto px-4 lg:px-8">
    <div class="flex items-center gap-2 overflow-x-auto py-3 scrollbar-hide">
      <a href="<?= BASE_URL ?>/gallery.php"
         class="shrink-0 px-5 py-2 rounded-full text-sm font-medium transition-all <?= $activeSlug==='all' ? 'bg-amber text-white' : 'text-white/60 hover:text-white border border-white/20 hover:border-amber/50' ?>">
        All
      </a>
      <?php foreach ($categories as $c): ?>
      <a href="<?= BASE_URL ?>/gallery.php?category=<?= e($c['slug']) ?>"
         class="shrink-0 px-5 py-2 rounded-full text-sm font-medium transition-all <?= $activeSlug===$c['slug'] ? 'bg-amber text-white' : 'text-white/60 hover:text-white border border-white/20 hover:border-amber/50' ?>">
        <?= e($c['name']) ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── Gallery Grid ───────────────────────────────────────────────────────── -->
<section class="bg-navy min-h-screen py-10">
  <div class="container mx-auto px-4 lg:px-8">
    <?php if ($media): ?>
    <div class="columns-2 md:columns-3 lg:columns-4 gap-3 md:gap-4 space-y-3 md:space-y-4" id="gallery-masonry">
      <?php foreach ($media as $item): ?>
      <?php
        $thumb   = $item['thumbnail'] ? THUMB_URL . '/' . $item['thumbnail'] : GALLERY_URL . '/' . $item['file_path'];
        $fullSrc = GALLERY_URL . '/' . $item['file_path'];
        $isVideo = $item['type'] === 'video';
      ?>
      <div class="break-inside-avoid group relative rounded-xl overflow-hidden bg-navy-700 cursor-pointer gallery-item"
           data-type="<?= $item['type'] ?>"
           data-src="<?= e($fullSrc) ?>"
           data-title="<?= e($item['title'] ?? '') ?>"
           data-desc="<?= e($item['description'] ?? '') ?>">
        <img src="<?= e($thumb) ?>"
             alt="<?= e($item['title'] ?? 'Gallery') ?>"
             class="w-full object-cover group-hover:scale-105 transition-transform duration-500"
             loading="lazy">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-navy/0 group-hover:bg-navy/50 transition-all duration-300 flex items-center justify-center">
          <?php if ($isVideo): ?>
          <div class="w-12 h-12 rounded-full bg-amber flex items-center justify-center group-hover:scale-110 transition-transform shadow-lg">
            <svg class="w-5 h-5 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          </div>
          <?php else: ?>
          <svg class="w-10 h-10 text-white opacity-0 group-hover:opacity-100 transition-opacity drop-shadow-lg" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6"/>
          </svg>
          <?php endif; ?>
        </div>
        <?php if ($item['title'] || $item['description']): ?>
        <div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-navy/90 to-transparent translate-y-full group-hover:translate-y-0 transition-transform duration-300">
          <?php if ($item['title']): ?>
          <p class="text-white text-sm font-semibold truncate"><?= e($item['title']) ?></p>
          <?php endif; ?>
          <?php if ($item['description']): ?>
          <p class="text-white/60 text-xs truncate"><?= e($item['description']) ?></p>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-24">
      <svg class="w-16 h-16 text-white/20 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
      </svg>
      <p class="text-white/40 text-lg">No media in this category yet.</p>
      <a href="<?= BASE_URL ?>/gallery.php" class="mt-4 inline-block text-amber hover:underline text-sm">View all gallery</a>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ── CTA Strip ──────────────────────────────────────────────────────────── -->
<section class="py-14 bg-amber text-center">
  <h2 class="font-display font-bold text-white text-3xl mb-3">Like What You See?</h2>
  <p class="text-white/80 mb-8">Let us create something equally beautiful for your event.</p>
  <a href="<?= BASE_URL ?>/enquiry.php"
     class="inline-flex items-center gap-2 px-10 py-4 rounded-full bg-navy text-white font-bold hover:bg-navy/90 transition-all">
    Book Your Event Now
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
  </a>
</section>

<!-- ── Lightbox ───────────────────────────────────────────────────────────── -->
<div id="lightbox" class="fixed inset-0 z-[9998] bg-black/95 hidden items-center justify-center p-4">
  <button id="lightbox-close" class="absolute top-4 right-4 z-10 text-white/70 hover:text-white transition-colors">
    <svg class="w-9 h-9" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
  </button>
  <button id="lightbox-prev" class="absolute left-4 top-1/2 -translate-y-1/2 z-10 text-white/70 hover:text-white transition-colors">
    <svg class="w-9 h-9" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
  </button>
  <button id="lightbox-next" class="absolute right-12 top-1/2 -translate-y-1/2 z-10 text-white/70 hover:text-white transition-colors">
    <svg class="w-9 h-9" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
  </button>
  <div id="lightbox-content" class="max-w-5xl max-h-[85vh] w-full flex items-center justify-center"></div>
  <div id="lightbox-caption" class="absolute bottom-6 left-1/2 -translate-x-1/2 text-center">
    <p id="lightbox-title" class="text-white font-medium"></p>
    <p id="lightbox-desc"  class="text-white/50 text-sm mt-1"></p>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
