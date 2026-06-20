/* VK Tent Services — Public Site JS
   Samastam Technologies Private Limited */

(function () {
  'use strict';

  // ── VK Loader ──────────────────────────────────────────────────────────────
  const loader = document.getElementById('vk-loader');
  if (loader) {
    window.addEventListener('load', function () {
      setTimeout(function () {
        loader.classList.add('fade-out');
        setTimeout(function () { loader.style.display = 'none'; }, 500);
      }, 600);
    });
  }

  // ── Header scroll effect ────────────────────────────────────────────────────
  const header = document.getElementById('site-header');
  if (header) {
    function updateHeader() {
      if (window.scrollY > 40) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    }
    window.addEventListener('scroll', updateHeader, { passive: true });
    updateHeader();
  }

  // ── Mobile menu ──────────────────────────────────────────────────────────────
  const menuBtn  = document.getElementById('mobile-menu-btn');
  const mobileMenu = document.getElementById('mobile-menu');
  if (menuBtn && mobileMenu) {
    menuBtn.addEventListener('click', function () {
      mobileMenu.classList.toggle('hidden');
    });
  }

  // ── Lightbox ─────────────────────────────────────────────────────────────────
  const lightbox = document.getElementById('lightbox');
  const lbContent = document.getElementById('lightbox-content');
  const lbClose   = document.getElementById('lightbox-close');
  const lbPrev    = document.getElementById('lightbox-prev');
  const lbNext    = document.getElementById('lightbox-next');
  const lbTitle   = document.getElementById('lightbox-title');
  const lbDesc    = document.getElementById('lightbox-desc');
  const lbCaption = document.getElementById('lightbox-caption');

  let galleryItems = [];
  let currentIndex = 0;

  function openLightbox(index) {
    if (!lightbox || !galleryItems.length) return;
    currentIndex = index;
    const item = galleryItems[index];
    lbContent.innerHTML = '';

    if (item.type === 'video') {
      const vid = document.createElement('video');
      vid.src = item.src;
      vid.controls = true;
      vid.autoplay = true;
      vid.className = 'max-w-full max-h-[80vh] rounded-xl';
      lbContent.appendChild(vid);
    } else {
      const img = document.createElement('img');
      img.src = item.src;
      img.alt = item.title || '';
      img.className = 'max-w-full max-h-[80vh] rounded-xl object-contain';
      lbContent.appendChild(img);
    }

    if (lbTitle) lbTitle.textContent = item.title || '';
    if (lbDesc)  lbDesc.textContent  = item.desc  || '';
    if (lbCaption) lbCaption.classList.toggle('hidden', !item.title && !item.desc);

    lightbox.classList.remove('hidden');
    lightbox.classList.add('flex');
    document.body.style.overflow = 'hidden';

    if (lbPrev) lbPrev.style.display = galleryItems.length > 1 ? '' : 'none';
    if (lbNext) lbNext.style.display = galleryItems.length > 1 ? '' : 'none';
  }

  function closeLightbox() {
    if (!lightbox) return;
    if (lbContent) {
      const vid = lbContent.querySelector('video');
      if (vid) { vid.pause(); vid.src = ''; }
    }
    lightbox.classList.add('hidden');
    lightbox.classList.remove('flex');
    document.body.style.overflow = '';
  }

  function navLightbox(dir) {
    const newIdx = (currentIndex + dir + galleryItems.length) % galleryItems.length;
    openLightbox(newIdx);
  }

  // Collect gallery items on page load
  document.querySelectorAll('.gallery-item').forEach(function (el, i) {
    galleryItems.push({
      type:  el.dataset.type  || 'image',
      src:   el.dataset.src   || '',
      title: el.dataset.title || '',
      desc:  el.dataset.desc  || '',
    });
    el.addEventListener('click', function () { openLightbox(i); });
  });

  if (lbClose)  lbClose.addEventListener('click', closeLightbox);
  if (lbPrev)   lbPrev.addEventListener('click',  function () { navLightbox(-1); });
  if (lbNext)   lbNext.addEventListener('click',  function () { navLightbox(1);  });
  if (lightbox) lightbox.addEventListener('click', function (e) {
    if (e.target === lightbox) closeLightbox();
  });

  // Keyboard nav
  document.addEventListener('keydown', function (e) {
    if (!lightbox || lightbox.classList.contains('hidden')) return;
    if (e.key === 'Escape')     closeLightbox();
    if (e.key === 'ArrowLeft')  navLightbox(-1);
    if (e.key === 'ArrowRight') navLightbox(1);
  });

  // ── Scroll reveal ─────────────────────────────────────────────────────────────
  const observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('animate-fade-in-up');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.08 });

  document.querySelectorAll('[data-reveal]').forEach(function (el) {
    el.style.opacity = '0';
    observer.observe(el);
  });

})();
