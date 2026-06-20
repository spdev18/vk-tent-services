<?php
require_once __DIR__ . '/includes/auth-check.php';

$id  = intGet('id');
$enq = DB::fetchOne('SELECT * FROM enquiries WHERE id=?', [$id]);
if (!$enq) { setFlash('error', 'Enquiry not found.'); redirect(BASE_URL . '/admin/enquiries.php'); }

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = post('action');
    $csrf   = $_POST[CSRF_TOKEN_NAME] ?? '';
    if (!Auth::verifyCsrf($csrf)) { setFlash('error', 'Security token error.'); redirect("enquiry-detail.php?id=$id"); }

    if ($action === 'update_status') {
        $newStatus = post('status');
        $notes     = post('notes');
        if (!in_array($newStatus, ['new','confirmed','rejected','completed'])) {
            setFlash('error', 'Invalid status.'); redirect("enquiry-detail.php?id=$id");
        }
        DB::execute('UPDATE enquiries SET status=?, notes=? WHERE id=?', [$newStatus, $notes, $id]);

        // If confirmed → create booking
        if ($newStatus === 'confirmed' && $enq['status'] !== 'confirmed') {
            $exists = DB::fetchValue('SELECT id FROM bookings WHERE enquiry_id=?', [$id]);
            if (!$exists) {
                DB::insert(
                    "INSERT INTO bookings (enquiry_id, customer_name, phone, email, event_date, venue, event_type, status)
                     VALUES (?,?,?,?,?,?,?,'upcoming')",
                    [$id, $enq['name'], $enq['phone'], $enq['email'], $enq['event_date'], $enq['venue'], $enq['event_type']]
                );
            }
        }
        setFlash('success', 'Enquiry updated successfully.');
        redirect("enquiry-detail.php?id=$id");
    }
}

// Mark as read
if (!$enq['is_read']) DB::execute('UPDATE enquiries SET is_read=1 WHERE id=?', [$id]);

$adminTitle = 'Enquiry #' . $id;
$adminPage  = 'enquiries';

$linkedBooking = DB::fetchOne('SELECT * FROM bookings WHERE enquiry_id=?', [$id]);

require_once __DIR__ . '/includes/header.php';
?>

<?= renderFlash() ?>

<div class="mb-4">
  <a href="<?= BASE_URL ?>/admin/enquiries.php" class="text-amber text-sm hover:underline flex items-center gap-1">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Back to Enquiries
  </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  <!-- Main details -->
  <div class="lg:col-span-2 space-y-5">
    <div class="bg-white rounded-2xl shadow-sm p-6">
      <div class="flex items-start justify-between mb-5">
        <div>
          <h2 class="font-display font-bold text-navy text-xl"><?= e($enq['name']) ?></h2>
          <p class="text-gray-400 text-sm">Received <?= date('d M Y, g:ia', strtotime($enq['created_at'])) ?></p>
        </div>
        <?php
        $statusColors = [
          'new'       => 'bg-blue-50 text-blue-600 border-blue-200',
          'confirmed' => 'bg-green-50 text-green-600 border-green-200',
          'rejected'  => 'bg-red-50 text-red-500 border-red-200',
          'completed' => 'bg-gray-100 text-gray-500 border-gray-200',
        ];
        $sColor = $statusColors[$enq['status']] ?? 'bg-gray-100 text-gray-500 border-gray-200';
        ?>
        <span class="px-3 py-1 rounded-full text-sm font-medium border <?= $sColor ?>">
          <?= ucfirst($enq['status']) ?>
        </span>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <div class="bg-gray-50 rounded-xl p-4">
          <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Phone</p>
          <a href="tel:<?= e($enq['phone']) ?>" class="text-navy font-medium hover:text-amber transition-colors"><?= e($enq['phone']) ?></a>
        </div>
        <?php if ($enq['email']): ?>
        <div class="bg-gray-50 rounded-xl p-4">
          <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Email</p>
          <a href="mailto:<?= e($enq['email']) ?>" class="text-navy font-medium hover:text-amber transition-colors"><?= e($enq['email']) ?></a>
        </div>
        <?php endif; ?>
        <div class="bg-gray-50 rounded-xl p-4">
          <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Event Date</p>
          <p class="text-navy font-medium"><?= date('d M Y (l)', strtotime($enq['event_date'])) ?></p>
        </div>
        <div class="bg-gray-50 rounded-xl p-4">
          <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Event Type</p>
          <p class="text-navy font-medium"><?= e($enq['event_type']) ?></p>
        </div>
        <div class="bg-gray-50 rounded-xl p-4 sm:col-span-2">
          <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Venue / Location</p>
          <p class="text-navy font-medium"><?= e($enq['venue']) ?></p>
        </div>
        <?php if ($enq['message']): ?>
        <div class="bg-gray-50 rounded-xl p-4 sm:col-span-2">
          <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Message</p>
          <p class="text-navy"><?= nl2br(e($enq['message'])) ?></p>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($linkedBooking): ?>
    <div class="bg-green-50 border border-green-200 rounded-2xl p-5">
      <div class="flex items-center gap-3 mb-3">
        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="font-semibold text-green-700">Booking Created — #<?= $linkedBooking['id'] ?></p>
      </div>
      <a href="<?= BASE_URL ?>/admin/booking-detail.php?id=<?= $linkedBooking['id'] ?>"
         class="inline-block text-sm text-green-600 border border-green-300 hover:bg-green-100 transition-colors rounded-xl px-4 py-2">
        View Booking Details →
      </a>
    </div>
    <?php endif; ?>
  </div>

  <!-- Actions sidebar -->
  <div class="space-y-5">
    <div class="bg-white rounded-2xl shadow-sm p-5">
      <h3 class="font-display font-bold text-navy mb-4">Update Status</h3>
      <form method="post" class="space-y-4">
        <?= Auth::csrfField() ?>
        <input type="hidden" name="action" value="update_status">
        <div>
          <label class="admin-label">Status</label>
          <select name="status" class="admin-input">
            <option value="new"       <?= $enq['status']==='new'       ? 'selected' : '' ?>>New</option>
            <option value="confirmed" <?= $enq['status']==='confirmed' ? 'selected' : '' ?>>Confirmed (creates booking)</option>
            <option value="rejected"  <?= $enq['status']==='rejected'  ? 'selected' : '' ?>>Rejected</option>
            <option value="completed" <?= $enq['status']==='completed' ? 'selected' : '' ?>>Completed</option>
          </select>
        </div>
        <div>
          <label class="admin-label">Internal Notes</label>
          <textarea name="notes" rows="4" class="admin-input resize-none" placeholder="Add notes visible only to you…"><?= e($enq['notes'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="w-full py-2.5 bg-amber text-white rounded-xl font-semibold text-sm hover:bg-amber-dark transition-all">
          Save Changes
        </button>
      </form>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-2xl shadow-sm p-5 space-y-3">
      <h3 class="font-display font-bold text-navy mb-3">Quick Actions</h3>
      <a href="tel:<?= e($enq['phone']) ?>"
         class="flex items-center gap-3 w-full px-4 py-3 rounded-xl bg-gray-50 hover:bg-amber/10 text-navy text-sm font-medium transition-all">
        <svg class="w-4 h-4 text-amber" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.8 19.79 19.79 0 015.96 2.19 2 2 0 018 4.18v3a2 2 0 01-1.45 1.94 16 16 0 007 7z"/></svg>
        Call <?= e($enq['name']) ?>
      </a>
      <?php if ($settings['whatsapp'] ?? ''): ?>
      <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $enq['phone'])) ?>?text=<?= urlencode("Hi {$enq['name']}, regarding your enquiry for {$enq['event_type']} on " . date('d M Y', strtotime($enq['event_date'])) . "…") ?>"
         target="_blank"
         class="flex items-center gap-3 w-full px-4 py-3 rounded-xl bg-gray-50 hover:bg-green-50 text-navy text-sm font-medium transition-all">
        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.117.555 4.104 1.523 5.83L0 24l6.305-1.505A12 12 0 1012 0zm0 22c-1.885 0-3.645-.49-5.168-1.348l-.37-.219-3.743.894.93-3.65-.241-.383A10 10 0 112 12c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10z"/></svg>
        WhatsApp Customer
      </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
