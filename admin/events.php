<?php
require_once __DIR__ . '/includes/auth-check.php';

$adminTitle = 'Events';
$adminPage  = 'events';

$filter = get('filter', 'upcoming');

$upcoming  = DB::fetchAll("SELECT b.*, COALESCE(SUM(p.amount),0) as received FROM bookings b LEFT JOIN payments p ON b.id=p.booking_id WHERE b.status='upcoming' AND b.event_date >= CURDATE() GROUP BY b.id ORDER BY b.event_date ASC");
$today     = DB::fetchAll("SELECT b.*, COALESCE(SUM(p.amount),0) as received FROM bookings b LEFT JOIN payments p ON b.id=p.booking_id WHERE b.status='upcoming' AND b.event_date = CURDATE() GROUP BY b.id");
$completed = DB::fetchAll("SELECT b.*, COALESCE(SUM(p.amount),0) as received FROM bookings b LEFT JOIN payments p ON b.id=p.booking_id WHERE b.status='held' GROUP BY b.id ORDER BY b.event_date DESC LIMIT 30");
$cancelled = DB::fetchAll("SELECT * FROM bookings WHERE status='cancelled' ORDER BY event_date DESC LIMIT 20");

require_once __DIR__ . '/includes/header.php';
?>

<?= renderFlash() ?>

<!-- Summary cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="bg-amber/10 border border-amber/20 rounded-2xl p-4 text-center">
    <div class="text-amber font-bold text-3xl"><?= count($today) ?></div>
    <div class="text-amber/80 text-sm mt-1">Today's Events</div>
  </div>
  <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 text-center">
    <div class="text-blue-600 font-bold text-3xl"><?= count($upcoming) ?></div>
    <div class="text-blue-500 text-sm mt-1">Upcoming Events</div>
  </div>
  <div class="bg-green-50 border border-green-100 rounded-2xl p-4 text-center">
    <div class="text-green-600 font-bold text-3xl"><?= count($completed) ?></div>
    <div class="text-green-500 text-sm mt-1">Completed (Recent)</div>
  </div>
  <div class="bg-red-50 border border-red-100 rounded-2xl p-4 text-center">
    <div class="text-red-500 font-bold text-3xl"><?= count($cancelled) ?></div>
    <div class="text-red-400 text-sm mt-1">Cancelled</div>
  </div>
</div>

<!-- Tabs -->
<div class="flex gap-2 mb-5 overflow-x-auto pb-1">
  <?php foreach(['upcoming'=>'Upcoming','today'=>'Today','completed'=>'Completed','cancelled'=>'Cancelled'] as $f=>$l): ?>
  <a href="?filter=<?= $f ?>"
     class="shrink-0 px-5 py-2 rounded-xl text-sm font-medium transition-all <?= $filter===$f ? 'bg-amber text-white' : 'bg-white text-gray-500 hover:bg-gray-100 shadow-sm' ?>">
    <?= $l ?>
  </a>
  <?php endforeach; ?>
</div>

<?php
$display = match($filter) {
  'today'     => $today,
  'completed' => $completed,
  'cancelled' => $cancelled,
  default     => $upcoming,
};
$statusLabel = match($filter) {
  'today'     => "Today's Events",
  'completed' => 'Completed Events',
  'cancelled' => 'Cancelled Events',
  default     => 'Upcoming Events',
};
?>

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
  <div class="p-5 border-b border-gray-100">
    <h2 class="font-display font-bold text-navy"><?= $statusLabel ?> <span class="text-gray-400 font-normal text-base">(<?= count($display) ?>)</span></h2>
  </div>

  <?php if ($display): ?>
  <div class="divide-y divide-gray-50">
    <?php foreach ($display as $b):
      $daysTo    = (int)ceil((strtotime($b['event_date']) - time()) / 86400);
      $due       = max(0, $b['total_amount'] - $b['received']);
      $isToday_b = $b['event_date'] === date('Y-m-d');
    ?>
    <div class="flex items-start gap-4 px-5 py-4 hover:bg-gray-50 transition-colors">
      <!-- Date badge -->
      <div class="<?= $isToday_b ? 'bg-amber' : 'bg-navy/5' ?> rounded-xl p-2.5 text-center shrink-0 w-14">
        <div class="<?= $isToday_b ? 'text-white' : 'text-amber' ?> font-bold text-lg leading-none"><?= date('d', strtotime($b['event_date'])) ?></div>
        <div class="<?= $isToday_b ? 'text-white/70' : 'text-gray-400' ?> text-[10px] uppercase"><?= date('M Y', strtotime($b['event_date'])) ?></div>
      </div>
      <!-- Info -->
      <div class="flex-1 min-w-0">
        <div class="flex items-start justify-between gap-2">
          <div>
            <p class="font-semibold text-navy"><?= e($b['customer_name']) ?></p>
            <p class="text-gray-500 text-sm"><?= e($b['event_type']) ?> · <?= e($b['venue']) ?></p>
          </div>
          <div class="text-right shrink-0">
            <?php if ($filter === 'upcoming'): ?>
            <p class="text-xs text-gray-400"><?= $daysTo > 0 ? "in $daysTo day".($daysTo>1?'s':'') : ($daysTo===0?'Today':'Overdue') ?></p>
            <?php endif; ?>
            <?php if ($due > 0): ?>
            <p class="text-xs text-red-500 font-semibold">₹<?= number_format($due) ?> due</p>
            <?php elseif ($b['total_amount'] > 0): ?>
            <p class="text-xs text-green-500 font-semibold">Fully paid</p>
            <?php endif; ?>
          </div>
        </div>
        <!-- Quick status update -->
        <?php if ($filter === 'upcoming'): ?>
        <div class="flex items-center gap-2 mt-2">
          <form method="post" action="<?= BASE_URL ?>/admin/ajax/update-booking-status.php" class="flex gap-2">
            <?= Auth::csrfField() ?>
            <input type="hidden" name="id" value="<?= $b['id'] ?>">
            <button name="status" value="held"      class="px-3 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-medium hover:bg-green-200 transition-all">Mark Completed</button>
            <button name="status" value="cancelled" class="px-3 py-1 bg-red-100 text-red-600 rounded-lg text-xs font-medium hover:bg-red-200 transition-all" onclick="return confirm('Cancel this booking?')">Cancel</button>
          </form>
          <a href="<?= BASE_URL ?>/admin/booking-detail.php?id=<?= $b['id'] ?>" class="text-amber text-xs hover:underline">Details →</a>
        </div>
        <?php else: ?>
        <a href="<?= BASE_URL ?>/admin/booking-detail.php?id=<?= $b['id'] ?>" class="mt-1.5 inline-block text-amber text-xs hover:underline">View Details →</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="p-12 text-center text-gray-400">
    <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/></svg>
    <p>No <?= strtolower($statusLabel) ?> at the moment.</p>
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
