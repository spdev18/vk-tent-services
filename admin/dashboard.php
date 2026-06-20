<?php
require_once __DIR__ . '/includes/auth-check.php';

$adminTitle = 'Dashboard';
$adminPage  = 'dashboard';

// ── Stats ─────────────────────────────────────────────────────────────────
$stats = [
  'new_enquiries'   => (int)DB::fetchValue("SELECT COUNT(*) FROM enquiries WHERE status='new'"),
  'upcoming_events' => (int)DB::fetchValue("SELECT COUNT(*) FROM bookings WHERE status='upcoming' AND event_date >= CURDATE()"),
  'today_events'    => (int)DB::fetchValue("SELECT COUNT(*) FROM bookings WHERE status='upcoming' AND event_date = CURDATE()"),
  'total_due'       => (float)DB::fetchValue("SELECT COALESCE(SUM(b.total_amount) - COALESCE(SUM(p.paid),0), 0)
                          FROM bookings b
                          LEFT JOIN (SELECT booking_id, SUM(amount) AS paid FROM payments GROUP BY booking_id) p
                          ON b.id = p.booking_id
                          WHERE b.status != 'cancelled'"),
  'total_received'  => (float)DB::fetchValue("SELECT COALESCE(SUM(amount),0) FROM payments"),
];

$upcomingBookings = DB::fetchAll(
  "SELECT b.*, COALESCE(SUM(p.amount),0) AS received
   FROM bookings b
   LEFT JOIN payments p ON b.id = p.booking_id
   WHERE b.status = 'upcoming' AND b.event_date >= CURDATE()
   GROUP BY b.id
   ORDER BY b.event_date ASC LIMIT 5"
);

$recentEnquiries = DB::fetchAll(
  "SELECT * FROM enquiries ORDER BY created_at DESC LIMIT 6"
);

$pendingPayments = DB::fetchAll(
  "SELECT b.*, COALESCE(SUM(p.amount),0) AS received,
          (b.total_amount - COALESCE(SUM(p.amount),0)) AS balance
   FROM bookings b
   LEFT JOIN payments p ON b.id = p.booking_id
   WHERE b.status != 'cancelled'
   GROUP BY b.id
   HAVING balance > 0
   ORDER BY b.event_date ASC LIMIT 5"
);

require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Stat Cards ─────────────────────────────────────────────────────────── -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <?php
  $cards = [
    ['label' => 'New Enquiries',    'value' => $stats['new_enquiries'],   'color' => 'blue',   'href' => 'enquiries.php',
     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>'],
    ['label' => 'Upcoming Events',  'value' => $stats['upcoming_events'], 'color' => 'green',  'href' => 'events.php',
     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>'],
    ['label' => 'Total Received',   'value' => '₹' . number_format($stats['total_received']), 'color' => 'amber',  'href' => 'payments.php',
     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
    ['label' => 'Total Due',        'value' => '₹' . number_format($stats['total_due']),      'color' => 'red',    'href' => 'payments.php',
     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>'],
  ];
  $colorMap = [
    'blue'  => 'bg-blue-50 text-blue-600',
    'green' => 'bg-green-50 text-green-600',
    'amber' => 'bg-amber/10 text-amber',
    'red'   => 'bg-red-50 text-red-500',
  ];
  foreach ($cards as $card):
    $cls = $colorMap[$card['color']];
  ?>
  <a href="<?= BASE_URL ?>/admin/<?= $card['href'] ?>"
     class="bg-white rounded-2xl p-5 shadow-sm hover:shadow-md transition-all group">
    <div class="flex items-start justify-between mb-3">
      <div class="w-10 h-10 rounded-xl <?= $cls ?> flex items-center justify-center">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
          <?= $card['icon'] ?>
        </svg>
      </div>
    </div>
    <div class="font-bold text-navy text-2xl"><?= $card['value'] ?></div>
    <div class="text-gray-500 text-sm mt-0.5"><?= $card['label'] ?></div>
  </a>
  <?php endforeach; ?>
</div>

<?php if ($stats['today_events'] > 0): ?>
<div class="bg-amber/10 border border-amber/30 rounded-2xl p-4 mb-6 flex items-center gap-3">
  <div class="w-8 h-8 rounded-full bg-amber flex items-center justify-center shrink-0">
    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
  </div>
  <p class="text-amber font-semibold text-sm">
    You have <strong><?= $stats['today_events'] ?> event<?= $stats['today_events'] > 1 ? 's' : '' ?></strong> scheduled for today!
    <a href="<?= BASE_URL ?>/admin/events.php" class="underline ml-2">View →</a>
  </p>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

  <!-- Upcoming Bookings -->
  <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="flex items-center justify-between p-5 border-b border-gray-100">
      <h2 class="font-display font-bold text-navy">Upcoming Bookings</h2>
      <a href="<?= BASE_URL ?>/admin/bookings.php" class="text-amber text-sm hover:underline">View all →</a>
    </div>
    <?php if ($upcomingBookings): ?>
    <div class="divide-y divide-gray-50">
      <?php foreach ($upcomingBookings as $b):
        $due = max(0, $b['total_amount'] - $b['received']);
        $daysLeft = (int)ceil((strtotime($b['event_date']) - time()) / 86400);
      ?>
      <a href="<?= BASE_URL ?>/admin/booking-detail.php?id=<?= $b['id'] ?>"
         class="flex items-center gap-3 px-5 py-3.5 hover:bg-gray-50 transition-colors">
        <div class="w-10 h-10 rounded-xl bg-amber/10 flex flex-col items-center justify-center shrink-0">
          <span class="text-amber font-bold text-sm leading-none"><?= date('d', strtotime($b['event_date'])) ?></span>
          <span class="text-amber/70 text-[10px] uppercase"><?= date('M', strtotime($b['event_date'])) ?></span>
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-medium text-navy text-sm truncate"><?= e($b['customer_name']) ?></p>
          <p class="text-gray-400 text-xs truncate"><?= e($b['event_type']) ?> · <?= e($b['venue']) ?></p>
        </div>
        <div class="text-right shrink-0">
          <?php if ($due > 0): ?>
          <span class="text-red-500 text-xs font-semibold">₹<?= number_format($due) ?> due</span>
          <?php else: ?>
          <span class="text-green-500 text-xs font-semibold">Paid</span>
          <?php endif; ?>
          <div class="text-gray-300 text-xs"><?= $daysLeft ?> day<?= $daysLeft !== 1 ? 's' : '' ?></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="p-8 text-center text-gray-400 text-sm">No upcoming bookings.</div>
    <?php endif; ?>
  </div>

  <!-- Recent Enquiries -->
  <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <div class="flex items-center justify-between p-5 border-b border-gray-100">
      <h2 class="font-display font-bold text-navy">Recent Enquiries</h2>
      <a href="<?= BASE_URL ?>/admin/enquiries.php" class="text-amber text-sm hover:underline">View all →</a>
    </div>
    <?php if ($recentEnquiries): ?>
    <div class="divide-y divide-gray-50">
      <?php
      $statusColors = [
        'new'       => 'bg-blue-50 text-blue-600',
        'confirmed' => 'bg-green-50 text-green-600',
        'rejected'  => 'bg-red-50 text-red-500',
        'completed' => 'bg-gray-100 text-gray-500',
      ];
      foreach ($recentEnquiries as $enq):
        $sColor = $statusColors[$enq['status']] ?? 'bg-gray-100 text-gray-500';
      ?>
      <a href="<?= BASE_URL ?>/admin/enquiry-detail.php?id=<?= $enq['id'] ?>"
         class="flex items-center gap-3 px-5 py-3.5 hover:bg-gray-50 transition-colors <?= !$enq['is_read'] ? 'bg-blue-50/30' : '' ?>">
        <div class="w-8 h-8 rounded-full bg-navy/10 flex items-center justify-center shrink-0">
          <span class="text-navy text-xs font-bold"><?= strtoupper(substr($enq['name'], 0, 1)) ?></span>
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-medium text-navy text-sm truncate"><?= e($enq['name']) ?></p>
          <p class="text-gray-400 text-xs"><?= e($enq['event_type']) ?> · <?= date('d M', strtotime($enq['event_date'])) ?></p>
        </div>
        <span class="shrink-0 px-2.5 py-0.5 rounded-full text-xs font-medium <?= $sColor ?>">
          <?= ucfirst($enq['status']) ?>
        </span>
      </a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="p-8 text-center text-gray-400 text-sm">No enquiries yet.</div>
    <?php endif; ?>
  </div>

  <!-- Pending Payments -->
  <?php if ($pendingPayments): ?>
  <div class="bg-white rounded-2xl shadow-sm overflow-hidden lg:col-span-2">
    <div class="flex items-center justify-between p-5 border-b border-gray-100">
      <h2 class="font-display font-bold text-navy flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-red-400"></span>
        Bookings with Pending Payments
      </h2>
      <a href="<?= BASE_URL ?>/admin/payments.php" class="text-amber text-sm hover:underline">View all →</a>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-400 text-xs uppercase tracking-wider">
          <tr>
            <th class="px-5 py-3 text-left">Customer</th>
            <th class="px-5 py-3 text-left">Event Date</th>
            <th class="px-5 py-3 text-left">Total</th>
            <th class="px-5 py-3 text-left">Received</th>
            <th class="px-5 py-3 text-left font-semibold text-red-400">Balance Due</th>
            <th class="px-5 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <?php foreach ($pendingPayments as $b): ?>
          <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-5 py-3 font-medium text-navy"><?= e($b['customer_name']) ?></td>
            <td class="px-5 py-3 text-gray-500"><?= date('d M Y', strtotime($b['event_date'])) ?></td>
            <td class="px-5 py-3 text-gray-700">₹<?= number_format($b['total_amount'], 0) ?></td>
            <td class="px-5 py-3 text-green-600">₹<?= number_format($b['received'], 0) ?></td>
            <td class="px-5 py-3 text-red-500 font-bold">₹<?= number_format($b['balance'], 0) ?></td>
            <td class="px-5 py-3">
              <a href="<?= BASE_URL ?>/admin/booking-detail.php?id=<?= $b['id'] ?>" class="text-amber text-xs hover:underline">Add Payment →</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
