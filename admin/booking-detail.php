<?php
require_once __DIR__ . '/includes/auth-check.php';

$id      = intGet('id');
$booking = DB::fetchOne('SELECT * FROM bookings WHERE id=?', [$id]);
if (!$booking) { setFlash('error', 'Booking not found.'); redirect(BASE_URL . '/admin/bookings.php'); }

// POST handlers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = post('action');
    $csrf   = $_POST[CSRF_TOKEN_NAME] ?? '';
    if (!Auth::verifyCsrf($csrf)) { setFlash('error', 'Security token error.'); redirect("booking-detail.php?id=$id"); }

    if ($action === 'update_booking') {
        DB::execute(
          'UPDATE bookings SET customer_name=?, phone=?, email=?, event_date=?, venue=?, event_type=?, status=?, total_amount=?, notes=? WHERE id=?',
          [post('customer_name'), post('phone'), post('email') ?: null, post('event_date'), post('venue'), post('event_type'), post('status'), (float)post('total_amount'), post('notes'), $id]
        );
        setFlash('success', 'Booking updated.'); redirect("booking-detail.php?id=$id");
    }
    if ($action === 'add_payment') {
        $amount = (float)post('amount');
        if ($amount > 0) {
            DB::insert('INSERT INTO payments (booking_id, amount, paid_on, method, note) VALUES (?,?,?,?,?)',
              [$id, $amount, post('paid_on') ?: date('Y-m-d'), post('method'), post('note') ?: null]);
            setFlash('success', 'Payment recorded.'); redirect("booking-detail.php?id=$id");
        }
    }
    if ($action === 'delete_payment') {
        $pid = intPost('payment_id');
        DB::execute('DELETE FROM payments WHERE id=? AND booking_id=?', [$pid, $id]);
        setFlash('success', 'Payment entry removed.'); redirect("booking-detail.php?id=$id");
    }
}

$payments = DB::fetchAll('SELECT * FROM payments WHERE booking_id=? ORDER BY paid_on DESC', [$id]);
$summary  = getBookingPaymentSummary($id);
$enquiry  = $booking['enquiry_id'] ? DB::fetchOne('SELECT * FROM enquiries WHERE id=?', [$booking['enquiry_id']]) : null;

$adminTitle = 'Booking #' . $id;
$adminPage  = 'bookings';
require_once __DIR__ . '/includes/header.php';
?>

<?= renderFlash() ?>

<div class="mb-4">
  <a href="<?= BASE_URL ?>/admin/bookings.php" class="text-amber text-sm hover:underline flex items-center gap-1">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
    Back to Bookings
  </a>
</div>

<!-- Payment Summary Bar -->
<div class="grid grid-cols-3 gap-4 mb-6">
  <div class="bg-white rounded-2xl shadow-sm p-5 text-center">
    <div class="text-gray-400 text-xs uppercase tracking-wider mb-1">Total Agreed</div>
    <div class="font-display font-bold text-navy text-2xl">₹<?= number_format($summary['total'], 0) ?></div>
  </div>
  <div class="bg-white rounded-2xl shadow-sm p-5 text-center">
    <div class="text-gray-400 text-xs uppercase tracking-wider mb-1">Received</div>
    <div class="font-display font-bold text-green-600 text-2xl">₹<?= number_format($summary['received'], 0) ?></div>
  </div>
  <div class="bg-white rounded-2xl shadow-sm p-5 text-center <?= $summary['due'] > 0 ? 'border border-red-200 bg-red-50/50' : '' ?>">
    <div class="text-gray-400 text-xs uppercase tracking-wider mb-1">Balance Due</div>
    <div class="font-display font-bold <?= $summary['due'] > 0 ? 'text-red-500' : 'text-gray-300' ?> text-2xl">
      <?= $summary['due'] > 0 ? '₹'.number_format($summary['due'],0) : 'Paid ✓' ?>
    </div>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  <!-- Booking Details -->
  <div class="lg:col-span-2 space-y-5">
    <div class="bg-white rounded-2xl shadow-sm p-6">
      <h3 class="font-display font-bold text-navy mb-5">Booking Details</h3>
      <form method="post" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <?= Auth::csrfField() ?>
        <input type="hidden" name="action" value="update_booking">
        <div><label class="admin-label">Customer Name *</label><input type="text" name="customer_name" class="admin-input" value="<?= e($booking['customer_name']) ?>" required></div>
        <div><label class="admin-label">Phone *</label><input type="tel" name="phone" class="admin-input" value="<?= e($booking['phone']) ?>" required></div>
        <div><label class="admin-label">Email</label><input type="email" name="email" class="admin-input" value="<?= e($booking['email'] ?? '') ?>"></div>
        <div><label class="admin-label">Event Date *</label><input type="date" name="event_date" class="admin-input" value="<?= e($booking['event_date']) ?>" required></div>
        <div><label class="admin-label">Event Type</label>
          <select name="event_type" class="admin-input">
            <?php foreach(['Wedding','Reception','Engagement','Birthday','Corporate Event','Social Function','Religious Ceremony','Other'] as $t): ?>
            <option <?= $booking['event_type']===$t?'selected':'' ?>><?= $t ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div><label class="admin-label">Venue</label><input type="text" name="venue" class="admin-input" value="<?= e($booking['venue']) ?>"></div>
        <div><label class="admin-label">Total Amount (₹)</label><input type="number" name="total_amount" class="admin-input" value="<?= $booking['total_amount'] ?>" min="0" step="0.01"></div>
        <div><label class="admin-label">Status</label>
          <select name="status" class="admin-input">
            <option value="upcoming"  <?= $booking['status']==='upcoming'  ?'selected':'' ?>>Upcoming</option>
            <option value="held"      <?= $booking['status']==='held'      ?'selected':'' ?>>Held / Completed</option>
            <option value="cancelled" <?= $booking['status']==='cancelled' ?'selected':'' ?>>Cancelled</option>
          </select>
        </div>
        <div class="sm:col-span-2"><label class="admin-label">Notes</label><textarea name="notes" rows="3" class="admin-input resize-none"><?= e($booking['notes'] ?? '') ?></textarea></div>
        <div class="sm:col-span-2">
          <button type="submit" class="px-6 py-2.5 bg-amber text-white rounded-xl font-semibold text-sm hover:bg-amber-dark transition-all">Save Booking</button>
        </div>
      </form>
    </div>

    <!-- Payment History -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
      <div class="p-5 border-b border-gray-100">
        <h3 class="font-display font-bold text-navy">Payment History</h3>
      </div>
      <?php if ($payments): ?>
      <div class="divide-y divide-gray-50">
        <?php foreach ($payments as $pay): ?>
        <div class="flex items-center gap-4 px-5 py-3">
          <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium text-navy">₹<?= number_format($pay['amount'], 2) ?></p>
            <p class="text-xs text-gray-400"><?= date('d M Y', strtotime($pay['paid_on'])) ?> <?= $pay['method'] ? '· ' . e($pay['method']) : '' ?> <?= $pay['note'] ? '— ' . e($pay['note']) : '' ?></p>
          </div>
          <form method="post">
            <?= Auth::csrfField() ?>
            <input type="hidden" name="action" value="delete_payment">
            <input type="hidden" name="payment_id" value="<?= $pay['id'] ?>">
            <button type="submit" class="text-red-400 hover:text-red-600 text-xs" onclick="return confirm('Remove this payment entry?')">Remove</button>
          </form>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="p-8 text-center text-gray-400 text-sm">No payments recorded yet.</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Add Payment Sidebar -->
  <div class="space-y-5">
    <div class="bg-white rounded-2xl shadow-sm p-5">
      <h3 class="font-display font-bold text-navy mb-4">Record Payment</h3>
      <form method="post" class="space-y-4">
        <?= Auth::csrfField() ?>
        <input type="hidden" name="action" value="add_payment">
        <div>
          <label class="admin-label">Amount (₹) *</label>
          <input type="number" name="amount" class="admin-input" min="1" step="0.01" placeholder="e.g. 5000" required>
        </div>
        <div>
          <label class="admin-label">Date</label>
          <input type="date" name="paid_on" class="admin-input" value="<?= date('Y-m-d') ?>">
        </div>
        <div>
          <label class="admin-label">Payment Method</label>
          <select name="method" class="admin-input">
            <option value="">— Select —</option>
            <option>Cash</option>
            <option>UPI</option>
            <option>Bank Transfer</option>
            <option>Cheque</option>
          </select>
        </div>
        <div>
          <label class="admin-label">Note</label>
          <input type="text" name="note" class="admin-input" placeholder="e.g. Advance payment">
        </div>
        <button type="submit" class="w-full py-2.5 bg-green-500 text-white rounded-xl font-semibold text-sm hover:bg-green-600 transition-all">
          Record Payment
        </button>
      </form>
    </div>

    <?php if ($enquiry): ?>
    <div class="bg-gray-50 rounded-2xl p-4 text-sm">
      <p class="font-medium text-navy mb-2">From Enquiry #<?= $enquiry['id'] ?></p>
      <p class="text-gray-500 text-xs"><?= e($enquiry['message'] ?? 'No message.') ?></p>
      <a href="<?= BASE_URL ?>/admin/enquiry-detail.php?id=<?= $enquiry['id'] ?>" class="mt-2 inline-block text-amber text-xs hover:underline">View Enquiry →</a>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
