<?php
require_once __DIR__ . '/includes/auth-check.php';

$adminTitle = 'Enquiries';
$adminPage  = 'enquiries';

$status = get('status', 'all');
$search = get('search');
$page   = max(1, intGet('page', 1));
$perPage = 20;

$where  = ['1=1'];
$params = [];
if ($status !== 'all') { $where[] = 'status = ?'; $params[] = $status; }
if ($search) { $where[] = '(name LIKE ? OR phone LIKE ? OR email LIKE ?)'; $s = "%$search%"; $params = array_merge($params, [$s, $s, $s]); }
$whereStr = implode(' AND ', $where);

$total   = (int)DB::fetchValue("SELECT COUNT(*) FROM enquiries WHERE $whereStr", $params);
$offset  = ($page - 1) * $perPage;
$enquiries = DB::fetchAll("SELECT * FROM enquiries WHERE $whereStr ORDER BY created_at DESC LIMIT $perPage OFFSET $offset", $params);

// Mark shown enquiries as read
$ids = array_column($enquiries, 'id');
if ($ids) {
  DB::execute('UPDATE enquiries SET is_read=1 WHERE id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')', $ids);
}

$totalPages = ceil($total / $perPage);
$counts = DB::fetchAll("SELECT status, COUNT(*) as cnt FROM enquiries GROUP BY status");
$cnts   = array_column($counts, 'cnt', 'status');

require_once __DIR__ . '/includes/header.php';
?>

<?= renderFlash() ?>

<!-- Tabs -->
<div class="bg-white rounded-2xl shadow-sm p-1 mb-4 inline-flex gap-1">
  <?php
  $tabs = ['all' => 'All', 'new' => 'New', 'confirmed' => 'Confirmed', 'rejected' => 'Rejected', 'completed' => 'Completed'];
  foreach ($tabs as $val => $label):
    $cnt = $val === 'all' ? array_sum($cnts) : ($cnts[$val] ?? 0);
    $isActive = $status === $val;
  ?>
  <a href="?status=<?= $val ?>"
     class="px-4 py-2 rounded-xl text-sm font-medium transition-all <?= $isActive ? 'bg-amber text-white' : 'text-gray-500 hover:bg-gray-100' ?>">
    <?= $label ?><?php if ($cnt > 0): ?> <span class="<?= $isActive ? 'bg-white/25' : 'bg-gray-200' ?> text-xs rounded-full px-1.5 py-0.5"><?= $cnt ?></span><?php endif; ?>
  </a>
  <?php endforeach; ?>
</div>

<!-- Search -->
<form method="get" class="bg-white rounded-2xl shadow-sm p-4 mb-4 flex gap-3">
  <input type="hidden" name="status" value="<?= e($status) ?>">
  <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by name, phone or email…"
         class="admin-input flex-1 !py-2 !text-sm">
  <button type="submit" class="px-5 py-2 bg-amber text-white rounded-xl text-sm font-semibold hover:bg-amber-dark transition-all">Search</button>
  <?php if ($search): ?><a href="?status=<?= e($status) ?>" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-xl text-sm hover:bg-gray-200 transition-all">Clear</a><?php endif; ?>
</form>

<!-- Table -->
<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
  <?php if ($enquiries): ?>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-gray-400 text-xs uppercase tracking-wider">
        <tr>
          <th class="px-5 py-3 text-left">Customer</th>
          <th class="px-5 py-3 text-left">Event</th>
          <th class="px-5 py-3 text-left">Date</th>
          <th class="px-5 py-3 text-left">Venue</th>
          <th class="px-5 py-3 text-left">Status</th>
          <th class="px-5 py-3 text-left">Received</th>
          <th class="px-5 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-50">
        <?php
        $statusColors = [
          'new'       => 'bg-blue-50 text-blue-600',
          'confirmed' => 'bg-green-50 text-green-600',
          'rejected'  => 'bg-red-50 text-red-500',
          'completed' => 'bg-gray-100 text-gray-500',
        ];
        foreach ($enquiries as $enq):
          $sColor = $statusColors[$enq['status']] ?? 'bg-gray-100 text-gray-500';
        ?>
        <tr class="hover:bg-gray-50 transition-colors <?= !$enq['is_read'] && $enq['status']==='new' ? 'bg-blue-50/40 font-medium' : '' ?>">
          <td class="px-5 py-4">
            <div class="font-medium text-navy"><?= e($enq['name']) ?></div>
            <div class="text-gray-400 text-xs"><?= e($enq['phone']) ?></div>
          </td>
          <td class="px-5 py-4 text-gray-600"><?= e($enq['event_type']) ?></td>
          <td class="px-5 py-4 text-gray-600 whitespace-nowrap"><?= date('d M Y', strtotime($enq['event_date'])) ?></td>
          <td class="px-5 py-4 text-gray-500 max-w-[160px] truncate"><?= e($enq['venue']) ?></td>
          <td class="px-5 py-4">
            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium <?= $sColor ?>"><?= ucfirst($enq['status']) ?></span>
          </td>
          <td class="px-5 py-4 text-gray-400 text-xs whitespace-nowrap"><?= date('d M, g:ia', strtotime($enq['created_at'])) ?></td>
          <td class="px-5 py-4">
            <a href="<?= BASE_URL ?>/admin/enquiry-detail.php?id=<?= $enq['id'] ?>"
               class="text-amber text-xs font-semibold hover:underline whitespace-nowrap">View →</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
  <div class="p-4 flex items-center justify-between border-t border-gray-100 text-sm">
    <span class="text-gray-400">Showing <?= ($offset+1) ?>–<?= min($offset+$perPage, $total) ?> of <?= $total ?></span>
    <div class="flex gap-2">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <a href="?status=<?= e($status) ?>&page=<?= $i ?><?= $search ? '&search='.urlencode($search) : '' ?>"
         class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-medium <?= $i==$page ? 'bg-amber text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?> transition-all">
        <?= $i ?>
      </a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php else: ?>
  <div class="p-12 text-center text-gray-400">
    <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 9v.906a2.25 2.25 0 01-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 001.183 1.981l6.478 3.488m8.839 2.51l-4.66-2.51m0 0l-1.023-.55a2.25 2.25 0 00-2.134 0l-1.022.55m0 0l-4.661 2.51m16.5 1.615a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V8.844a2.25 2.25 0 011.183-1.981l7.5-4.04a2.25 2.25 0 012.134 0l7.5 4.04a2.25 2.25 0 011.183 1.98V19.5z"/></svg>
    <p>No enquiries found<?= $search ? " for "$search"" : '' ?>.</p>
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
