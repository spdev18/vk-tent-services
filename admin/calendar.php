<?php
require_once __DIR__ . '/includes/auth-check.php';

$adminTitle = 'Booking Calendar';
$adminPage  = 'calendar';

$year  = intGet('year',  (int)date('Y'));
$month = intGet('month', (int)date('m'));
if ($month < 1) { $month = 12; $year--; }
if ($month > 12) { $month = 1; $year++; }

$firstDay  = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = (int)date('t', $firstDay);
$startWeekday = (int)date('N', $firstDay); // 1=Mon, 7=Sun

// Fetch bookings and blocked dates for this month
$bookings = DB::fetchAll(
  "SELECT id, customer_name, event_type, event_date, status FROM bookings WHERE YEAR(event_date)=? AND MONTH(event_date)=? AND status!='cancelled'",
  [$year, $month]
);
$blocked = DB::fetchAll(
  "SELECT id, blocked_date, reason FROM blocked_dates WHERE YEAR(blocked_date)=? AND MONTH(blocked_date)=?",
  [$year, $month]
);

// Index by day
$byDay = [];
foreach ($bookings as $b) {
  $d = (int)date('j', strtotime($b['event_date']));
  $byDay[$d][] = ['type' => 'booking', 'data' => $b];
}
foreach ($blocked as $bl) {
  $d = (int)date('j', strtotime($bl['blocked_date']));
  $byDay[$d][] = ['type' => 'blocked', 'data' => $bl];
}

// Handle block/unblock POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrf = $_POST[CSRF_TOKEN_NAME] ?? '';
  if (Auth::verifyCsrf($csrf)) {
    $action = post('action');
    if ($action === 'block') {
      $date   = post('date');
      $reason = post('reason');
      if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        try { DB::insert('INSERT INTO blocked_dates (blocked_date, reason) VALUES (?,?)', [$date, $reason ?: null]); }
        catch (\Exception $e) {}
      }
    } elseif ($action === 'unblock') {
      DB::execute('DELETE FROM blocked_dates WHERE id=?', [intPost('block_id')]);
    }
    redirect("calendar.php?year=$year&month=$month");
  }
}

$prevMonth = $month - 1; $prevYear = $year;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
$nextMonth = $month + 1; $nextYear = $year;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

require_once __DIR__ . '/includes/header.php';
?>

<?= renderFlash() ?>

<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
  <div class="flex items-center gap-3">
    <a href="?year=<?= $prevYear ?>&month=<?= $prevMonth ?>"
       class="w-9 h-9 rounded-xl bg-white shadow-sm flex items-center justify-center text-gray-400 hover:text-navy hover:shadow transition-all">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
    <h2 class="font-display font-bold text-navy text-xl"><?= date('F Y', $firstDay) ?></h2>
    <a href="?year=<?= $nextYear ?>&month=<?= $nextMonth ?>"
       class="w-9 h-9 rounded-xl bg-white shadow-sm flex items-center justify-center text-gray-400 hover:text-navy hover:shadow transition-all">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
    </a>
    <a href="?year=<?= date('Y') ?>&month=<?= date('m') ?>"
       class="px-3 py-1.5 rounded-lg bg-amber/10 text-amber text-xs font-semibold hover:bg-amber/20 transition-all">Today</a>
  </div>

  <!-- Block a date -->
  <button onclick="document.getElementById('block-modal').classList.remove('hidden')"
          class="px-4 py-2 bg-white shadow-sm rounded-xl text-sm text-gray-600 hover:text-navy hover:shadow transition-all flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
    Block a Date
  </button>
</div>

<!-- Legend -->
<div class="flex items-center gap-5 mb-4 text-xs text-gray-500">
  <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-sm bg-amber/80"></div> Booking</div>
  <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-sm bg-red-300"></div> Blocked</div>
  <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-sm bg-blue-200"></div> Today</div>
</div>

<!-- Calendar Grid -->
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
  <!-- Header row -->
  <div class="grid grid-cols-7 border-b border-gray-100">
    <?php foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d): ?>
    <div class="py-3 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider"><?= $d ?></div>
    <?php endforeach; ?>
  </div>

  <!-- Days -->
  <div class="grid grid-cols-7">
    <?php
    $today    = (int)date('j');
    $isToday  = (date('Y') == $year && date('m') == $month);
    // Leading empty cells
    for ($i = 1; $i < $startWeekday; $i++): ?>
    <div class="min-h-[100px] border-b border-r border-gray-50 p-2 bg-gray-50/30"></div>
    <?php endfor;

    for ($day = 1; $day <= $daysInMonth; $day++):
      $events    = $byDay[$day] ?? [];
      $isT       = $isToday && $day === $today;
      $isPast    = mktime(0,0,0,$month,$day,$year) < mktime(0,0,0,(int)date('m'),(int)date('d'),(int)date('Y'));
      $colIdx    = ($startWeekday + $day - 2) % 7; // 0=Mon, 6=Sun
      $isWeekend = $colIdx >= 5;
    ?>
    <div class="min-h-[100px] border-b border-r border-gray-100 p-2 <?= $isPast ? 'bg-gray-50/50' : ($isWeekend ? 'bg-amber/5' : '') ?> <?= $isT ? 'bg-blue-50 ring-1 ring-inset ring-blue-200' : '' ?> hover:bg-amber/5 transition-colors">
      <div class="flex items-center justify-between mb-1.5">
        <span class="<?= $isT ? 'w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-xs' : 'text-sm font-medium ' . ($isPast ? 'text-gray-300' : 'text-navy') ?>"><?= $day ?></span>
      </div>
      <div class="space-y-0.5">
        <?php foreach ($events as $ev):
          if ($ev['type'] === 'booking'):
            $b = $ev['data'];
          ?>
          <a href="<?= BASE_URL ?>/admin/booking-detail.php?id=<?= $b['id'] ?>"
             class="block px-1.5 py-0.5 rounded bg-amber/80 text-white text-[10px] font-medium truncate hover:bg-amber transition-colors leading-tight"
             title="<?= e($b['customer_name']) ?> — <?= e($b['event_type']) ?>">
            <?= e(substr($b['customer_name'], 0, 12)) ?>
          </a>
          <?php elseif ($ev['type'] === 'blocked'):
            $bl = $ev['data'];
          ?>
          <div class="flex items-center gap-1 px-1.5 py-0.5 rounded bg-red-200 text-red-700 text-[10px] font-medium group/bl">
            <span class="truncate flex-1"><?= $bl['reason'] ? e(substr($bl['reason'],0,12)) : 'Blocked' ?></span>
            <form method="post" class="hidden group-hover/bl:block">
              <?= Auth::csrfField() ?>
              <input type="hidden" name="action" value="unblock">
              <input type="hidden" name="block_id" value="<?= $bl['id'] ?>">
              <button type="submit" class="text-red-500 hover:text-red-700 font-bold leading-none" title="Unblock">×</button>
            </form>
          </div>
          <?php endif; endforeach; ?>
      </div>
    </div>
    <?php endfor;

    // Trailing empty cells
    $lastWeekday = ($startWeekday + $daysInMonth - 1) % 7 ?: 7;
    for ($i = $lastWeekday; $i < 7; $i++): ?>
    <div class="min-h-[100px] border-b border-r border-gray-50 p-2 bg-gray-50/30"></div>
    <?php endfor; ?>
  </div>
</div>

<!-- Block Date Modal -->
<div id="block-modal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
    <div class="flex items-center justify-between mb-5">
      <h3 class="font-display font-bold text-navy">Block a Date</h3>
      <button onclick="document.getElementById('block-modal').classList.add('hidden')" class="text-gray-400 hover:text-navy">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <form method="post" class="space-y-4">
      <?= Auth::csrfField() ?>
      <input type="hidden" name="action" value="block">
      <div>
        <label class="admin-label">Date *</label>
        <input type="date" name="date" class="admin-input" min="<?= date('Y-m-d') ?>" required>
      </div>
      <div>
        <label class="admin-label">Reason (optional)</label>
        <input type="text" name="reason" class="admin-input" placeholder="e.g. Personal leave">
      </div>
      <button type="submit" class="w-full py-2.5 bg-red-500 text-white rounded-xl font-semibold text-sm hover:bg-red-600 transition-all">Block This Date</button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
