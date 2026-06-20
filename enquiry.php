<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$settings = DB::settings();
$eventTypes = ['Wedding', 'Reception', 'Engagement', 'Birthday', 'Corporate Event', 'Social Function', 'Religious Ceremony', 'Other'];

$pageTitle = 'Book / Enquire';
$pageDesc  = 'Check date availability and submit your enquiry for tent setup, decoration and event services.';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Page Hero ──────────────────────────────────────────────────────────── -->
<section class="bg-navy pt-28 pb-14 text-center">
  <span class="inline-block text-amber text-sm font-semibold uppercase tracking-widest mb-3">Get In Touch</span>
  <h1 class="font-display font-bold text-white text-5xl">Book Your Event</h1>
  <p class="text-white/50 mt-4 max-w-xl mx-auto">Fill in your details below. We'll confirm availability and get back to you promptly.</p>
</section>

<!-- ── Enquiry Form ───────────────────────────────────────────────────────── -->
<section class="py-16 bg-cream">
  <div class="container mx-auto px-4 lg:px-8 max-w-3xl">

    <!-- Success / Error messages (shown via JS) -->
    <div id="form-alert" class="hidden mb-6 p-4 rounded-xl text-sm font-medium"></div>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
      <div class="bg-navy p-8 text-white">
        <h2 class="font-display font-bold text-2xl mb-1">Enquiry Form</h2>
        <p class="text-white/60 text-sm">Fields marked <span class="text-amber">*</span> are required.</p>
      </div>

      <form id="enquiry-form" class="p-8 space-y-6" novalidate>
        <?= Auth::csrfField() ?>
        <!-- Honeypot anti-spam -->
        <div class="hidden" aria-hidden="true">
          <input type="text" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <!-- Name -->
          <div>
            <label class="form-label">Full Name <span class="text-amber">*</span></label>
            <input type="text" name="name" id="name" class="form-input" placeholder="e.g. Ramesh Kumar" required>
            <p class="form-error hidden text-red-500 text-xs mt-1"></p>
          </div>
          <!-- Phone -->
          <div>
            <label class="form-label">Mobile Number <span class="text-amber">*</span></label>
            <input type="tel" name="phone" id="phone" class="form-input" placeholder="+91 98765 43210" required>
            <p class="form-error hidden text-red-500 text-xs mt-1"></p>
          </div>
        </div>

        <!-- Email -->
        <div>
          <label class="form-label">Email Address <span class="text-navy/40 font-normal">(optional)</span></label>
          <input type="email" name="email" id="email" class="form-input" placeholder="yourname@example.com">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <!-- Event Date -->
          <div>
            <label class="form-label">Event Date <span class="text-amber">*</span></label>
            <input type="date" name="event_date" id="event_date" class="form-input"
                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
            <!-- Availability indicator -->
            <div id="date-availability" class="mt-2 text-xs font-medium hidden"></div>
          </div>
          <!-- Event Type -->
          <div>
            <label class="form-label">Event Type <span class="text-amber">*</span></label>
            <select name="event_type" id="event_type" class="form-input" required>
              <option value="">-- Select Event Type --</option>
              <?php foreach ($eventTypes as $t): ?>
              <option value="<?= e($t) ?>"><?= e($t) ?></option>
              <?php endforeach; ?>
            </select>
            <p class="form-error hidden text-red-500 text-xs mt-1"></p>
          </div>
        </div>

        <!-- Venue -->
        <div>
          <label class="form-label">Venue / Location <span class="text-amber">*</span></label>
          <input type="text" name="venue" id="venue" class="form-input" placeholder="e.g. Near Civil Lines, Prayagraj" required>
          <p class="form-error hidden text-red-500 text-xs mt-1"></p>
        </div>

        <!-- Message -->
        <div>
          <label class="form-label">Message / Requirements</label>
          <textarea name="message" id="message" rows="4" class="form-input resize-none"
                    placeholder="Tell us about your event — number of guests, special requirements, etc."></textarea>
        </div>

        <button type="submit" id="submit-btn"
                class="w-full py-4 rounded-xl bg-amber text-white font-bold text-lg hover:bg-amber-dark transition-all hover:shadow-lg hover:shadow-amber/30 flex items-center justify-center gap-3 disabled:opacity-60 disabled:cursor-not-allowed">
          <svg id="submit-icon" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M22 2L11 13M22 2L15 22l-4-9-9-4 20-7z"/>
          </svg>
          <span id="submit-text">Send Enquiry</span>
        </button>
      </form>
    </div>

    <!-- Quick contact -->
    <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 gap-4 text-center">
      <?php if ($p = $settings['phone'] ?? ''): ?>
      <a href="tel:<?= e(preg_replace('/\s/', '', $p)) ?>"
         class="flex items-center justify-center gap-3 p-4 bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow text-navy font-medium">
        <svg class="w-5 h-5 text-amber" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.8 19.79 19.79 0 015.96 2.19 2 2 0 018 4.18v3a2 2 0 01-1.45 1.94 16 16 0 007 7z"/>
        </svg>
        <?= e($p) ?>
      </a>
      <?php endif; ?>
      <?php if ($w = $settings['whatsapp'] ?? ''): ?>
      <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $w)) ?>?text=Hi%2C%20I%20want%20to%20enquire%20about%20your%20services."
         target="_blank"
         class="flex items-center justify-center gap-3 p-4 bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow text-navy font-medium">
        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M11.999 0C5.373 0 0 5.373 0 12c0 2.117.555 4.104 1.523 5.83L0 24l6.305-1.505A11.945 11.945 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.885 0-3.645-.49-5.168-1.348l-.37-.219-3.743.894.93-3.65-.241-.383A10 10 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
        WhatsApp Us
      </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<script>
const API_BASE = '<?= BASE_URL ?>/api';

// Date availability check
const dateInput = document.getElementById('event_date');
const dateStatus = document.getElementById('date-availability');

dateInput.addEventListener('change', function() {
  const date = this.value;
  if (!date) return;
  dateStatus.className = 'mt-2 text-xs font-medium';
  dateStatus.textContent = 'Checking availability…';
  dateStatus.classList.remove('hidden');
  fetch(`${API_BASE}/check-availability.php?date=${encodeURIComponent(date)}`)
    .then(r => r.json())
    .then(data => {
      if (data.available) {
        dateStatus.className = 'mt-2 text-xs font-medium text-green-600';
        dateStatus.textContent = '✓ This date is available!';
      } else {
        dateStatus.className = 'mt-2 text-xs font-medium text-red-500';
        dateStatus.textContent = '✗ Sorry, this date is already booked. Please choose another date.';
      }
    })
    .catch(() => { dateStatus.classList.add('hidden'); });
});

// Form submission
document.getElementById('enquiry-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  const form = this;
  const btn  = document.getElementById('submit-btn');
  const txt  = document.getElementById('submit-text');
  const alert = document.getElementById('form-alert');

  // Validate
  let valid = true;
  form.querySelectorAll('[required]').forEach(field => {
    const err = field.parentElement.querySelector('.form-error');
    if (!field.value.trim()) {
      valid = false;
      field.classList.add('border-red-400');
      if (err) { err.textContent = 'This field is required.'; err.classList.remove('hidden'); }
    } else {
      field.classList.remove('border-red-400');
      if (err) err.classList.add('hidden');
    }
  });
  if (!valid) return;

  // Check availability before submit
  const date = document.getElementById('event_date').value;
  const avail = await fetch(`${API_BASE}/check-availability.php?date=${encodeURIComponent(date)}`).then(r => r.json());
  if (!avail.available) {
    alert.className = 'mb-6 p-4 rounded-xl text-sm font-medium bg-red-50 border border-red-200 text-red-700';
    alert.textContent = 'The selected date is no longer available. Please choose a different date.';
    alert.classList.remove('hidden');
    window.scrollTo({top: alert.offsetTop - 100, behavior: 'smooth'});
    return;
  }

  btn.disabled = true;
  txt.textContent = 'Sending…';

  const fd = new FormData(form);
  fetch(`${API_BASE}/submit-enquiry.php`, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        alert.className = 'mb-6 p-4 rounded-xl text-sm font-medium bg-green-50 border border-green-200 text-green-700';
        alert.innerHTML = '<strong>Thank you!</strong> Your enquiry has been received. We will contact you within 24 hours to confirm.';
        alert.classList.remove('hidden');
        form.reset();
        dateStatus.classList.add('hidden');
        window.scrollTo({top: alert.offsetTop - 100, behavior: 'smooth'});
      } else {
        alert.className = 'mb-6 p-4 rounded-xl text-sm font-medium bg-red-50 border border-red-200 text-red-700';
        alert.textContent = data.message || 'An error occurred. Please try again.';
        alert.classList.remove('hidden');
      }
    })
    .catch(() => {
      alert.className = 'mb-6 p-4 rounded-xl text-sm font-medium bg-red-50 border border-red-200 text-red-700';
      alert.textContent = 'Network error. Please try again or call us directly.';
      alert.classList.remove('hidden');
    })
    .finally(() => {
      btn.disabled = false;
      txt.textContent = 'Send Enquiry';
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
