<?php
// Public site footer
$settings = $settings ?? DB::settings();
?>
<!-- ── Footer ──────────────────────────────────────────────────────────────── -->
<footer class="bg-navy text-white">
  <div class="container mx-auto px-4 lg:px-8 py-12">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-10">

      <!-- Brand -->
      <div>
        <div class="flex items-center gap-3 mb-4">
          <div class="w-10 h-10 rounded-full bg-amber flex items-center justify-center">
            <span class="font-display font-bold text-white text-sm">VK</span>
          </div>
          <div>
            <div class="font-display font-bold text-lg"><?= e($settings['business_name'] ?? 'VK Tent Services') ?></div>
            <div class="text-white/50 text-xs">Tent &amp; Event Specialists</div>
          </div>
        </div>
        <p class="text-white/60 text-sm leading-relaxed">
          <?= e($settings['tagline'] ?? 'Your Event, Our Expertise — Every Detail, Perfectly Placed') ?>
        </p>
        <?php
        $fb  = $settings['facebook']  ?? '';
        $ig  = $settings['instagram'] ?? '';
        $yt  = $settings['youtube']   ?? '';
        if ($fb || $ig || $yt):
        ?>
        <div class="flex gap-4 mt-5">
          <?php if ($fb): ?><a href="<?= e($fb) ?>" target="_blank" rel="noopener" class="text-white/50 hover:text-amber transition-colors" aria-label="Facebook">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
          </a><?php endif; ?>
          <?php if ($ig): ?><a href="<?= e($ig) ?>" target="_blank" rel="noopener" class="text-white/50 hover:text-amber transition-colors" aria-label="Instagram">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
          </a><?php endif; ?>
          <?php if ($yt): ?><a href="<?= e($yt) ?>" target="_blank" rel="noopener" class="text-white/50 hover:text-amber transition-colors" aria-label="YouTube">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.97C18.88 4 12 4 12 4s-6.88 0-8.59.45A2.78 2.78 0 001.46 6.42 29 29 0 001 12a29 29 0 00.46 5.58 2.78 2.78 0 001.95 1.97C5.12 20 12 20 12 20s6.88 0 8.59-.45a2.78 2.78 0 001.95-1.97A29 29 0 0023 12a29 29 0 00-.46-5.58z"/><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02" fill="#0E1B2C"/></svg>
          </a><?php endif; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Quick Links -->
      <div>
        <h3 class="font-display font-semibold text-amber mb-4">Quick Links</h3>
        <ul class="space-y-2 text-sm">
          <li><a href="<?= BASE_URL ?>/index.php"          class="text-white/60 hover:text-amber transition-colors">Home</a></li>
          <li><a href="<?= BASE_URL ?>/index.php#services" class="text-white/60 hover:text-amber transition-colors">Services</a></li>
          <li><a href="<?= BASE_URL ?>/gallery.php"         class="text-white/60 hover:text-amber transition-colors">Gallery</a></li>
          <li><a href="<?= BASE_URL ?>/index.php#about"    class="text-white/60 hover:text-amber transition-colors">About Us</a></li>
          <li><a href="<?= BASE_URL ?>/enquiry.php"         class="text-white/60 hover:text-amber transition-colors">Book / Enquire</a></li>
        </ul>
      </div>

      <!-- Contact -->
      <div>
        <h3 class="font-display font-semibold text-amber mb-4">Contact Us</h3>
        <ul class="space-y-3 text-sm">
          <?php if ($p = $settings['phone'] ?? ''): ?>
          <li class="flex items-start gap-3">
            <svg class="w-4 h-4 mt-0.5 text-amber shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.8 19.79 19.79 0 0145.96 2.19 2 2 0 016 4.18v3a2 2 0 01-1.45 1.94 16 16 0 007 7z"/></svg>
            <a href="tel:<?= e(preg_replace('/\s/', '', $p)) ?>" class="text-white/60 hover:text-amber transition-colors"><?= e($p) ?></a>
          </li>
          <?php endif; ?>
          <?php if ($w = $settings['whatsapp'] ?? ''): ?>
          <li class="flex items-start gap-3">
            <svg class="w-4 h-4 mt-0.5 text-amber shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M11.999 0C5.373 0 0 5.373 0 12c0 2.117.555 4.104 1.523 5.83L0 24l6.305-1.505A11.945 11.945 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.885 0-3.645-.49-5.168-1.348l-.37-.219-3.743.894.93-3.65-.241-.383A10 10 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
            <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $w)) ?>" target="_blank" class="text-white/60 hover:text-amber transition-colors">WhatsApp</a>
          </li>
          <?php endif; ?>
          <?php if ($em = $settings['email'] ?? ''): ?>
          <li class="flex items-start gap-3">
            <svg class="w-4 h-4 mt-0.5 text-amber shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <a href="mailto:<?= e($em) ?>" class="text-white/60 hover:text-amber transition-colors"><?= e($em) ?></a>
          </li>
          <?php endif; ?>
          <?php if ($addr = $settings['address'] ?? ''): ?>
          <li class="flex items-start gap-3">
            <svg class="w-4 h-4 mt-0.5 text-amber shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <span class="text-white/60"><?= e($addr) ?></span>
          </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <!-- Samastam Branding -->
    <div class="border-t border-white/10 mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-white/40">
      <p>&copy; <?= date('Y') ?> <?= e($settings['business_name'] ?? 'VK Tent Services') ?>. All rights reserved.</p>
      <a href="https://samastam.com" target="_blank" rel="noopener"
         class="flex items-center gap-2 hover:text-amber transition-colors group">
        <span>Developed by</span>
        <img src="<?= BASE_URL ?>/assets/images/samastam-logo.svg" alt="Samastam Technologies"
             class="h-5 opacity-50 group-hover:opacity-100 transition-opacity"
             onerror="this.style.display='none'">
        <span class="font-semibold text-white/60 group-hover:text-amber transition-colors">Samastam Technologies Pvt. Ltd.</span>
      </a>
    </div>
  </div>
</footer>

<!-- ── Scripts ─────────────────────────────────────────────────────────────── -->
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
