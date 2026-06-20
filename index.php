<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$settings = DB::settings();
$services = DB::fetchAll("SELECT * FROM services WHERE is_active=1 ORDER BY sort_order ASC");
$featured = DB::fetchAll("SELECT * FROM gallery_media WHERE is_visible=1 AND is_featured=1 ORDER BY sort_order ASC LIMIT 6");
if (count($featured) < 6) {
    $ids    = array_column($featured, 'id');
    $idList = $ids ? implode(',', array_map('intval', $ids)) : '0';
    $more   = DB::fetchAll("SELECT * FROM gallery_media WHERE is_visible=1 AND id NOT IN ($idList) ORDER BY created_at DESC LIMIT " . (6 - count($featured)));
    $featured = array_merge($featured, $more);
}

$ownerPhoto = $settings['owner_photo'] ?? '';
$ownerPhotoSrc = $ownerPhoto ? PROFILE_URL . '/' . e($ownerPhoto) : BASE_URL . '/assets/images/owner-placeholder.svg';

$pageTitle = null; // use default
$pageDesc  = $settings['tagline'] ?? '';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Hero ──────────────────────────────────────────────────────────────── -->
<section id="hero" class="relative min-h-screen flex items-center overflow-hidden bg-navy">
  <!-- Background pattern -->
  <div class="absolute inset-0 opacity-5">
    <div class="absolute inset-0" style="background-image:repeating-linear-gradient(45deg,#C4763A 0,#C4763A 1px,transparent 0,transparent 50%);background-size:30px 30px;"></div>
  </div>
  <!-- Gradient overlay -->
  <div class="absolute inset-0 bg-gradient-to-br from-navy via-navy/95 to-navy/80"></div>

  <div class="relative container mx-auto px-4 lg:px-8 pt-24 pb-16 text-center">
    <div class="inline-block mb-6 px-4 py-1.5 rounded-full border border-amber/30 text-amber text-sm font-medium tracking-wider uppercase">
      Prayagraj's Trusted Event Partner
    </div>
    <h1 class="font-display font-bold text-white text-5xl md:text-7xl lg:text-8xl leading-tight mb-6">
      <?= e($settings['business_name'] ?? 'VK Tent Services') ?>
    </h1>
    <p class="text-white/70 text-lg md:text-xl max-w-2xl mx-auto mb-10 leading-relaxed">
      <?= e($settings['tagline'] ?? 'Your Event, Our Expertise — Every Detail, Perfectly Placed') ?>
    </p>
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <a href="<?= BASE_URL ?>/enquiry.php"
         class="inline-flex items-center gap-2 px-8 py-4 rounded-full bg-amber text-white font-semibold text-lg hover:bg-amber-dark transition-all hover:shadow-lg hover:shadow-amber/30 hover:-translate-y-0.5">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Book / Enquire Now
      </a>
      <a href="<?= BASE_URL ?>/gallery.php"
         class="inline-flex items-center gap-2 px-8 py-4 rounded-full border border-white/30 text-white font-semibold text-lg hover:border-amber hover:text-amber transition-all">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
        View Gallery
      </a>
    </div>

    <!-- Stats -->
    <div class="mt-16 grid grid-cols-3 gap-8 max-w-lg mx-auto">
      <div class="text-center">
        <div class="font-display font-bold text-amber text-4xl"><?= e($settings['experience_years'] ?? '10+') ?></div>
        <div class="text-white/50 text-sm mt-1">Years Experience</div>
      </div>
      <div class="text-center border-x border-white/10">
        <div class="font-display font-bold text-amber text-4xl">500+</div>
        <div class="text-white/50 text-sm mt-1">Events Done</div>
      </div>
      <div class="text-center">
        <div class="font-display font-bold text-amber text-4xl">100%</div>
        <div class="text-white/50 text-sm mt-1">Satisfied Clients</div>
      </div>
    </div>
  </div>

  <!-- Scroll indicator -->
  <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
    <svg class="w-6 h-6 text-white/40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M19 9l-7 7-7-7"/>
    </svg>
  </div>
</section>

<!-- ── Services ───────────────────────────────────────────────────────────── -->
<section id="services" class="py-20 bg-cream">
  <div class="container mx-auto px-4 lg:px-8">
    <div class="text-center mb-14">
      <span class="inline-block text-amber text-sm font-semibold uppercase tracking-widest mb-3">What We Offer</span>
      <h2 class="font-display font-bold text-navy text-4xl md:text-5xl">Our Services</h2>
      <p class="text-navy/60 mt-4 max-w-xl mx-auto">From intimate ceremonies to grand celebrations — we cover every aspect of your event.</p>
    </div>

    <?php if ($services): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($services as $i => $svc): ?>
      <div class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <?php if ($svc['image']): ?>
        <div class="h-48 overflow-hidden">
          <img src="<?= BASE_URL ?>/uploads/profile/<?= e($svc['image']) ?>"
               alt="<?= e($svc['title']) ?>"
               class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
               loading="lazy">
        </div>
        <?php else: ?>
        <div class="h-48 bg-gradient-to-br from-navy/5 to-amber/10 flex items-center justify-center">
          <div class="w-16 h-16 rounded-full bg-amber/10 flex items-center justify-center">
            <svg class="w-8 h-8 text-amber" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
            </svg>
          </div>
        </div>
        <?php endif; ?>
        <div class="p-6">
          <div class="w-8 h-0.5 bg-amber mb-3"></div>
          <h3 class="font-display font-bold text-navy text-xl mb-2"><?= e($svc['title']) ?></h3>
          <p class="text-navy/60 text-sm leading-relaxed"><?= e($svc['description']) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p class="text-center text-navy/50">Services will be listed here.</p>
    <?php endif; ?>
  </div>
</section>

<!-- ── Gallery Preview ────────────────────────────────────────────────────── -->
<section id="gallery-preview" class="py-20 bg-navy">
  <div class="container mx-auto px-4 lg:px-8">
    <div class="text-center mb-14">
      <span class="inline-block text-amber text-sm font-semibold uppercase tracking-widest mb-3">Our Work</span>
      <h2 class="font-display font-bold text-white text-4xl md:text-5xl">Featured Gallery</h2>
      <p class="text-white/50 mt-4 max-w-xl mx-auto">A glimpse of the beautiful events we've brought to life.</p>
    </div>

    <?php if ($featured): ?>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4">
      <?php foreach ($featured as $i => $media): ?>
      <?php
        $imgSrc = $media['thumbnail']
          ? THUMB_URL . '/' . $media['thumbnail']
          : GALLERY_URL . '/' . $media['file_path'];
        $fullSrc = GALLERY_URL . '/' . $media['file_path'];
        $isVideo = $media['type'] === 'video';
        $spanClass = ($i === 0) ? 'col-span-2 row-span-2' : '';
      ?>
      <div class="<?= $spanClass ?> group relative overflow-hidden rounded-xl bg-navy-700 cursor-pointer gallery-item"
           data-type="<?= $media['type'] ?>"
           data-src="<?= e($fullSrc) ?>"
           data-title="<?= e($media['title'] ?? '') ?>">
        <img src="<?= e($imgSrc) ?>"
             alt="<?= e($media['title'] ?? 'Gallery') ?>"
             class="w-full h-full object-cover <?= $i===0 ? 'min-h-[300px]' : 'min-h-[150px]' ?> group-hover:scale-105 transition-transform duration-500"
             loading="lazy">
        <div class="absolute inset-0 bg-navy/0 group-hover:bg-navy/40 transition-all duration-300 flex items-center justify-center">
          <?php if ($isVideo): ?>
          <div class="w-14 h-14 rounded-full bg-amber/80 flex items-center justify-center opacity-100 group-hover:scale-110 transition-transform">
            <svg class="w-6 h-6 text-white ml-1" fill="currentColor" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          </div>
          <?php else: ?>
          <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
          </svg>
          <?php endif; ?>
        </div>
        <?php if ($media['title']): ?>
        <div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-navy/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
          <p class="text-white text-sm font-medium truncate"><?= e($media['title']) ?></p>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-16">
      <p class="text-white/40">Gallery photos will appear here once uploaded from the admin panel.</p>
    </div>
    <?php endif; ?>

    <div class="text-center mt-10">
      <a href="<?= BASE_URL ?>/gallery.php"
         class="inline-flex items-center gap-2 px-8 py-3 rounded-full border border-amber text-amber font-semibold hover:bg-amber hover:text-white transition-all">
        View Full Gallery
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>
  </div>
</section>

<!-- ── About ──────────────────────────────────────────────────────────────── -->
<section id="about" class="py-20 bg-cream">
  <div class="container mx-auto px-4 lg:px-8">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
      <!-- Photo -->
      <div class="relative">
        <div class="relative w-full max-w-sm mx-auto lg:mx-0">
          <div class="absolute -top-4 -left-4 w-full h-full border-2 border-amber/30 rounded-2xl"></div>
          <img src="<?= e($ownerPhotoSrc) ?>"
               alt="<?= e($settings['owner_name'] ?? 'Vivek Shukla') ?>"
               class="relative w-full rounded-2xl object-cover shadow-2xl"
               style="aspect-ratio:4/5;object-position:top"
               onerror="this.src='<?= BASE_URL ?>/assets/images/owner-placeholder.svg'">
          <!-- Badge -->
          <div class="absolute -bottom-5 -right-5 bg-amber text-white rounded-xl px-5 py-3 shadow-lg">
            <div class="font-display font-bold text-2xl"><?= e($settings['experience_years'] ?? '10+') ?></div>
            <div class="text-white/80 text-xs">Years of<br>Experience</div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div>
        <span class="inline-block text-amber text-sm font-semibold uppercase tracking-widest mb-3">About Us</span>
        <h2 class="font-display font-bold text-navy text-4xl md:text-5xl mb-6">
          Meet <span class="text-amber"><?= e($settings['owner_name'] ?? 'Vivek Shukla') ?></span>
        </h2>
        <div class="w-12 h-0.5 bg-amber mb-6"></div>
        <p class="text-navy/70 text-lg leading-relaxed mb-6">
          <?= e($settings['about_text'] ?? '') ?>
        </p>
        <?php if ($area = $settings['area_served'] ?? ''): ?>
        <div class="flex items-center gap-3 mb-4">
          <div class="w-8 h-8 rounded-full bg-amber/10 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-amber" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
          </div>
          <p class="text-navy/70"><strong class="text-navy">Area Served:</strong> <?= e($area) ?></p>
        </div>
        <?php endif; ?>
        <div class="flex items-center gap-3 mb-8">
          <div class="w-8 h-8 rounded-full bg-amber/10 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-amber" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <p class="text-navy/70">Trusted by hundreds of happy families across the region</p>
        </div>
        <a href="<?= BASE_URL ?>/enquiry.php"
           class="inline-flex items-center gap-2 px-8 py-4 rounded-full bg-amber text-white font-semibold hover:bg-amber-dark transition-all hover:shadow-lg hover:shadow-amber/30">
          Get in Touch
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ── Contact Strip ──────────────────────────────────────────────────────── -->
<section id="contact" class="py-16 bg-amber">
  <div class="container mx-auto px-4 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">

      <?php if ($p = $settings['phone'] ?? ''): ?>
      <a href="tel:<?= e(preg_replace('/\s/', '', $p)) ?>"
         class="group flex flex-col items-center gap-3 text-white hover:scale-105 transition-transform">
        <div class="w-14 h-14 rounded-full bg-white/20 group-hover:bg-white/30 flex items-center justify-center transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.8 19.79 19.79 0 015.96 2.19 2 2 0 018 4.18v3a2 2 0 01-1.45 1.94 16 16 0 007 7z"/>
          </svg>
        </div>
        <div>
          <div class="text-white/70 text-sm uppercase tracking-wider mb-1">Call Us</div>
          <div class="font-bold text-lg"><?= e($p) ?></div>
        </div>
      </a>
      <?php endif; ?>

      <?php if ($w = $settings['whatsapp'] ?? ''): ?>
      <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $w)) ?>?text=Hi%2C%20I%20am%20interested%20in%20your%20tent%20services."
         target="_blank"
         class="group flex flex-col items-center gap-3 text-white hover:scale-105 transition-transform">
        <div class="w-14 h-14 rounded-full bg-white/20 group-hover:bg-white/30 flex items-center justify-center transition-colors">
          <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M11.999 0C5.373 0 0 5.373 0 12c0 2.117.555 4.104 1.523 5.83L0 24l6.305-1.505A11.945 11.945 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.885 0-3.645-.49-5.168-1.348l-.37-.219-3.743.894.93-3.65-.241-.383A10 10 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
        </div>
        <div>
          <div class="text-white/70 text-sm uppercase tracking-wider mb-1">WhatsApp</div>
          <div class="font-bold text-lg">Chat With Us</div>
        </div>
      </a>
      <?php endif; ?>

      <?php if ($addr = $settings['address'] ?? ''): ?>
      <div class="flex flex-col items-center gap-3 text-white">
        <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>
          </svg>
        </div>
        <div>
          <div class="text-white/70 text-sm uppercase tracking-wider mb-1">Our Location</div>
          <div class="font-bold"><?= e($addr) ?></div>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <div class="text-center mt-12">
      <a href="<?= BASE_URL ?>/enquiry.php"
         class="inline-flex items-center gap-2 px-10 py-4 rounded-full bg-navy text-white font-bold text-lg hover:bg-navy/90 transition-all hover:shadow-xl">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Check Availability & Book Now
      </a>
    </div>
  </div>
</section>

<!-- ── Lightbox ───────────────────────────────────────────────────────────── -->
<div id="lightbox" class="fixed inset-0 z-[9998] bg-black/90 hidden items-center justify-center p-4">
  <button id="lightbox-close" class="absolute top-4 right-4 text-white/70 hover:text-white">
    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
  </button>
  <div id="lightbox-content" class="max-w-4xl max-h-[90vh] w-full flex items-center justify-center"></div>
  <div id="lightbox-caption" class="absolute bottom-6 left-1/2 -translate-x-1/2 text-white/70 text-sm"></div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
