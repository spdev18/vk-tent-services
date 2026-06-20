<?php
require_once __DIR__ . '/includes/auth-check.php';

$adminTitle = 'Payment Tracking';
$adminPage  = 'payments';

$payFilter  = get('pay', 'all');
$month      = get('month', date('Y-m'));

// Summary totals
$totalReceived = (float)DB::fetchValue("SELECT COALESCE(SUM(amount),0) FROM payments");
$totalAgreed   = (float)DB::fetchValue("SELECT COALESCE(SUM(total_amount),0) FROM bookings WHERE status != 'cancelled'");
$totalDue      = max(0, $totalAgreed - $totalReceived);

$monthReceived = (float)DB::fetchValue("SELECT COALESCE(SUM(amount),0) FROM payments WHERE DATE_FORMAT(paid_on,'%Y-%m')=?", [$month]);

// Bookings with payment breakdown
$query = "SELECT b.*, COALESCE(SUM(p.amount),0) AS received,
                 (b.total_amount - COALESCE(SUM(p.amount),0)) AS balance
          FROM bookings b
          LEFT JOIN payments p ON b.id = p.booking_id
          WHERE b.status != 'cancelled'
          GROUP BY b.id";

$bookings = DB::fetchAll($query);

if ($payFilter === 'paid')    $bookings = array_filter($bookings, fn($b) => $b['balance'] <= 0 && $b['total_amount'] > 0);
if ($payFilter === 'due')     $bookings = array_filter($bookings, fn($b) => $b['balance'] > 0 && $b['received'] == 0);
if ($payFilter === 'partial') $bookings = array_filter($bookings, fn($b) => $b['balance'] > 0 && $b['received'] > 0);

// Monthly breakdown
$monthlyTotals = DB::fetchAll(
  "SELECT DATE_FORMAT(paid_on,'%Y-%m') AS mo, SUM(amount) AS total, COUNT(*) AS cnt
   FROM payments GROUP BY mo ORDER BY mo DESC LIMIT 12"
);

// Recent payments
$recentPayments = DB::fetchAll(
  "SELECT p.*, b.customer_name, b.event_date, b.event_type
   FROM payments p
   JOIN bookings b ON p.booking_id = b.id
   ORDER BY p.paid_on DESC, p.created_at DESC LIMIT 20"
);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Summary cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="bg-white rounded-2xl shadow-sm p-5">
    <div class="text-gray-400 text-xs uppercase tracking-wider mb-1">Total Agreed</div>
    <div class="font-display font-bold text-navy text-2xl">₹<?= number_format($totalAgreed, 0) ?></div>
    <div class="text-gray-400 text-xs mt-1">Across all bookings</div>
  </div>
  <div class="bg-white rounded-2xl shadow-sm p-5">
    <div class="text-gray-400 text-xs uppercase tracking-wider mb-1">Total Received</div>
    <div class="font-display font-bold text-green-600 text-2xl">₹<?= number_format($totalReceived, 0) ?></div>
    <div class="text-gray-400 text-xs mt-1">All time</div>
  </div>
  <div class="bg-white rounded-2xl shadow-sm p-5 <?= $totalDue > 0 ? 'border border-red-200' : '' ?>">
    <div class="text-gray-400 text-xs uppercase tracking-wider mb-1">Total Due</div>
    <div class="font-display font-bold <?= $totalDue > 0 ? 'text-red-500' : 'text-gray-300' ?> text-2xl">₹<?= number_format($totalDue, 0) ?></div>
    <div class="text-gray-400 text-xs mt-1">Outstanding balance</div>
  </div>
  <div class="bg-amber/10 border border-amber/20 rounded-2xl p-5">
    <div class="text-amber/70 text-xs uppercase tracking-wider mb-1">This Month</div>
    <div class="font-display font-bold text-amber text-2xl">₹<?= number_format($monthReceived, 0) ?></div>
    <div class="text-amber/60 text-xs mt-1"><?= date('F Y') ?></div>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <!-- Bookings payment list -->
  <div class="lg:col-span-2">
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
      <div class="p-5 border-b border-gray-100 flex items-center justify-between">
        <h2 class="font-display font-bold text-navy">Payment Status by Booking</h2>
        <div class="flex gap-2">
          <?php foreach(['all'=>'All','due'=>'Due','partial'=>'Partial','paid'=>'Paid'] as $f=>$l): ?>
          <a href="?pay=<?= $f ?>&month=<?= e($month) ?>"
             class="px-3 py-1 rounded-lg text-xs font-medium <?= $payFilter===$f ? 'bg-amber text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?> transition-all">
            <?= $l ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

      <?php if ($bookings): ?>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-400 text-xs uppercase tracking-wider">
            <tr>
              <th class="px-4 py-3 text-left">Customer</th>
              <th class="px-4 py-3 text-left">Date</th>
              <th class="px-4 py-3 text-right">Agreed</th>
              <th class="px-4 py-3 text-right">Received</th>
              <th class="px-4 py-3 text-right">Due</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <?php foreach ($bookings as $b): $due = $b['balance']; ?>
            <tr class="hover:bg-gray-50 transition-colors">
              <td class="px-4 py-3">
                <p class="font-medium text-navy"><?= e($b['customer_name']) ?></p>
                <p class="text-gray-400 text-xs"><?= e($b['event_type']) ?></p>
              </td>
              <td class="px-4 py-3 text-gray-500 whitespace-nowrap"><?= date('d M Y', strtotime($b['event_date'])) ?></td>
              <td class="px-4 py-3 text-right text-gray-700">₹<?= number_format($b['total_amount'], 0) ?></td>
              <td class="px-4 py-3 text-right text-green-600 font-medium">₹<?= number_format($b['received'], 0) ?></td>
              <td class="px-4 py-3 text-right">
                <?php if ($due > 0): ?>
                <span class="text-red-500 font-bold">₹<?= number_format($due, 0) ?></span>
                <?php else: ?>
                <span class="text-green-400 text-xs">Paid ✓</span>
                <?php endif; ?>
              </td>
              <td class="px-4 py-3">
                <a href="<?= BASE_URL ?>/admin/booking-detail.php?id=<?= $b['id'] ?>" class="text-amber text-xs hover:underline">Manage</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
      <div class="p-10 text-center text-gray-400 text-sm">No bookings match this filter.</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Monthly summary + recent payments -->
  <div class="space-y-5">
    <!-- Monthly chart -->
    <div class="bg-white rounded-2xl shadow-sm p-5">
      <h3 class="font-display font-bold text-navy mb-4">Monthly Summary</h3>
      <?php if ($monthlyTotals): ?>
      <div class="space-y-2">
        <?php
        $maxAmt = max(array_column($monthlyTotals, 'total'));
        foreach ($monthlyTotals as $mo):
          $pct = $maxAmt > 0 ? round(($mo['total'] / $maxAmt) * 100) : 0;
        ?>
        <div>
          <div class="flex justify-between text-xs mb-1">
            <span class="text-gray-500"><?= date('M Y', strtotime($mo['mo'].'-01')) ?></span>
            <span class="font-medium text-navy">₹<?= number_format($mo['total'], 0) ?></span>
          </div>
          <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full bg-amber rounded-full" style="width:<?= $pct ?>%"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <p class="text-gray-400 text-sm">No payment data yet.</p>
      <?php endif; ?>
    </div>

    <!-- Recent payments -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
      <div class="p-4 border-b border-gray-100">
        <h3 class="font-display font-bold text-navy text-base">Recent Payments</h3>
      </div>
      <div class="divide-y divide-gray-50">
        <?php foreach ($recentPayments as $p): ?>
        <div class="px-4 py-3">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-sm font-medium text-navy"><?= e($p['customer_name']) ?></p>
              <p class="text-xs text-gray-400"><?= date('d M Y', strtotime($p['paid_on'])) ?> <?= $p['method'] ? '· '.e($p['method']) : '' ?></p>
            </div>
            <span class="text-green-600 font-bold text-sm">+₹<?= number_format($p['amount'], 0) ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
