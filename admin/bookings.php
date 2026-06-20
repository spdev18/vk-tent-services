<?php
require_once __DIR__ . '/includes/auth-check.php';

$adminTitle = 'Bookings';
$adminPage  = 'bookings';

$statusFilter = get('status', 'upcoming');
$payFilter    = get('pay', 'all');
$search       = get('search');
$page         = max(1, intGet('page', 1));
$perPage      = 20;

$where  = ['1=1'];
$params = [];
if ($statusFilter !== 'all') { $where[] = 'b.status = ?'; $params[] = $statusFilter; }
if ($search) { $where[] = '(b.customer_name LIKE ? OR b.phone LIKE ?)'; $s = "%$search%"; $params[] = $s; $params[] = $s; }
$whereStr = implode(' AND ', $where);

$total = (int)DB::fetchValue(
  "SELECT COUNT(*) FROM bookings b WHERE $whereStr", $params
);
$offset = ($page - 1) * $perPage;

$bookings = DB::fetchAll(
  "SELECT b.*, COALESCE(SUM(p.amount),0) AS received,
          (b.total_amount - COALESCE(SUM(p.amount),0)) AS balance
   FROM bookings b
   LEFT JOIN payments p ON b.id = p.booking_id
   WHERE $whereStr
   GROUP BY b.id
   ORDER BY b.event_date " . ($statusFilter === 'upcoming' ? 'ASC' : 'DESC') . "
   LIMIT $perPage OFFSET $offset",
  $params
);

// Filter by payment in PHP (simpler for now)
if ($payFilter !== 'all') {
  $bookings = array_filter($bookings, function($b) use ($payFilter) {
    if ($payFilter === 'paid')    return $b['balance'] <= 0;
    if ($payFilter === 'due')     return $b['balance'] > 0 && $b['received'] == 0;
    if ($payFilter === 'partial') return $b['balance'] > 0 && $b['received'] > 0;
    return true;
  });
}

$statusCounts = DB::fetchAll("SELECT status, COUNT(*) as cnt FROM bookings GROUP BY status");
$sCtrs = array_column($statusCounts, 'cnt', 'status');

$totalPages = ceil($total / $perPage);
require_once __DIR__ . '/includes/header.php';
?>

<?= renderFlash() ?>

<!-- Tabs -->
<div class="flex flex-wrap gap-2 mb-4">
  <?php
  $tabs = ['upcoming' => 'Upcoming', 'held' => 'Completed', 'cancelled' => 'Cancelled', 'all' => 'All'];
  foreach ($tabs as $val => $label):
    $cnt = $val === 'all' ? array_sum($sCtrs) : ($sCtrs[$val] ?? 0);
    $isActive = $statusFilter === $val;
  ?>
  <a href="?status=<?= $val ?>"
     class="px-4 py-2 rounded-xl text-sm font-medium transition-all <?= $isActive ? 'bg-amber text-white' : 'bg-white text-gray-500 hover:bg-gray-100 shadow-sm' ?>">
    <?= $label ?> <?php if ($cnt): ?><span class="<?= $isActive?'opacity-70':'text-gray-400' ?> text-xs">(<?= $cnt ?>)</span><?php endif; ?>
  </a>
  <?php endforeach; ?>
  <a href="<?= BASE_URL ?>/admin/bookings.php?status=upcoming"
     onclick="event.preventDefault(); window.location='<?= BASE_URL ?>/admin/bookings.php?status=upcoming&new=1'"
     class="ml-auto px-4 py-2 rounded-xl text-sm font-semibold bg-navy text-white hover:bg-navy/90 transition-all shadow-sm flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
    New Booking
  </a>
</div>

<!-- Filters -->
<div class="bg-white rounded-2xl shadow-sm p-4 mb-4 flex flex-wrap gap-3 items-center">
  <form method="get" class="flex gap-3 flex-1 min-w-0">
    <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search customer or phone…" class="admin-input flex-1 !py-2 !text-sm">
    <button type="submit" class="px-4 py-2 bg-amber text-white rounded-xl text-sm font-semibold hover:bg-amber-dark transition-all">Search</button>
  </form>
  <div class="flex gap-2">
    <?php foreach(['all'=>'All', 'paid'=>'Paid', 'partial'=>'Partial', 'due'=>'Due'] as $v=>$l): ?>
    <a href="?status=<?= e($statusFilter) ?>&pay=<?= $v ?>"
       class="px-3 py-1.5 rounded-lg text-xs font-medium <?= $payFilter===$v ? 'bg-navy text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?> transition-all"><?= $l ?></a>
    <?php endforeach; ?>
  </div>
</div>

<!-- New booking quick form (shown when ?new=1) -->
<?php if (isset($_GET['new'])): ?>
<div class="bg-white rounded-2xl shadow-sm p-6 mb-6 border-l-4 border-amber">
  <h3 class="font-display font-bold text-navy mb-4">Create Manual Booking</h3>
  <form method="post" action="<?= BASE_URL ?>/admin/ajax/create-booking.php" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <?= Auth::csrfField() ?>
    <div><label class="admin-label">Customer Name *</label><input type="text" name="customer_name" class="admin-input" required></div>
    <div><label class="admin-label">Phone *</label><input type="tel" name="phone" class="admin-input" required></div>
    <div><label class="admin-label">Email</label><input type="email" name="email" class="admin-input"></div>
    <div><label class="admin-label">Event Date *</label><input type="date" name="event_date" class="admin-input" min="<?= date('Y-m-d') ?>" required></div>
    <div><label class="admin-label">Event Type *</label>
      <select name="event_type" class="admin-input" required>
        <option value="">Select…</option>
        <?php foreach(['Wedding','Reception','Engagement','Birthday','Corporate Event','Social Function','Religious Ceremony','Other'] as $t): ?>
        <option><?= $t ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div><label class="admin-label">Venue *</label><input type="text" name="venue" class="admin-input" required></div>
    <div><label class="admin-label">Total Amount (₹)</label><input type="number" name="total_amount" class="admin-input" min="0" step="0.01" value="0"></div>
    <div class="sm:col-span-2 lg:col-span-3"><label class="admin-label">Notes</label><textarea name="notes" rows="2" class="admin-input resize-none"></textarea></div>
    <div class="sm:col-span-2 lg:col-span-3 flex gap-3">
      <button type="submit" class="px-6 py-2.5 bg-amber text-white rounded-xl font-semibold text-sm hover:bg-amber-dark transition-all">Create Booking</button>
      <a href="?status=<?= e($statusFilter) ?>" class="px-5 py-2.5 bg-gray-100 text-gray-600 rounded-xl font-semibold text-sm hover:bg-gray-200 transition-all">Cancel</a>
    </div>
  </form>
</div>
<?php endif; ?>

<!-- Table -->
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
  <?php if ($bookings): ?>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-gray-400 text-xs uppercase tracking-wider">
        <tr>
          <th class="px-5 py-3 text-left">Customer</th>
          <th class="px-5 py-3 text-left">Event</th>
          <th class="px-5 py-3 text-left">Date</th>
          <th class="px-5 py-3 text-left">Total</th>
          <th class="px-5 py-3 text-left">Received</th>
          <th class="px-5 py-3 text-left">Balance</th>
          <th class="px-5 py-3 text-left">Status</th>
          <th class="px-5 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-50">
        <?php
        $statusColors = [
          'upcoming'  => 'bg-blue-50 text-blue-600',
          'held'      => 'bg-green-50 text-green-600',
          'cancelled' => 'bg-red-50 text-red-500',
        ];
        foreach ($bookings as $b):
          $sColor = $statusColors[$b['status']] ?? 'bg-gray-100 text-gray-500';
          $isOverdue = $b['event_date'] < date('Y-m-d') && $b['status'] === 'upcoming';
        ?>
        <tr class="hover:bg-gray-50 transition-colors <?= $isOverdue ? 'bg-yellow-50/30' : '' ?>">
          <td class="px-5 py-4">
            <div class="font-medium text-navy"><?= e($b['customer_name']) ?></div>
            <div class="text-gray-400 text-xs"><?= e($b['phone']) ?></div>
          </td>
          <td class="px-5 py-4 text-gray-600"><?= e($b['event_type']) ?></td>
          <td class="px-5 py-4 text-gray-600 whitespace-nowrap"><?= date('d M Y', strtotime($b['event_date'])) ?></td>
          <td class="px-5 py-4 text-gray-700">₹<?= number_format($b['total_amount'], 0) ?></td>
          <td class="px-5 py-4 text-green-600">₹<?= number_format($b['received'], 0) ?></td>
          <td class="px-5 py-4 <?= $b['balance'] > 0 ? 'text-red-500 font-semibold' : 'text-gray-300' ?>">
            <?= $b['balance'] > 0 ? '₹'.number_format($b['balance'],0) : '—' ?>
          </td>
          <td class="px-5 py-4">
            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium <?= $sColor ?>"><?= ucfirst($b['status']) ?></span>
          </td>
          <td class="px-5 py-4">
            <a href="<?= BASE_URL ?>/admin/booking-detail.php?id=<?= $b['id'] ?>"
               class="text-amber text-xs font-semibold hover:underline whitespace-nowrap">View →</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <div class="p-12 text-center text-gray-400">
    <p>No bookings found.</p>
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
