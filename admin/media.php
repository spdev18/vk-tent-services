<?php
require_once __DIR__ . '/includes/auth-check.php';

$adminTitle = 'Gallery Management';
$adminPage  = 'media';

$categories = DB::fetchAll("SELECT * FROM gallery_categories ORDER BY sort_order ASC");
$filterCat  = intGet('category');
$filterType = get('type');

$where  = ['1=1'];
$params = [];
if ($filterCat) { $where[] = 'm.category_id = ?'; $params[] = $filterCat; }
if ($filterType) { $where[] = 'm.type = ?'; $params[] = $filterType; }

$whereStr = implode(' AND ', $where);
$media = DB::fetchAll(
  "SELECT m.*, gc.name AS category_name
   FROM gallery_media m
   LEFT JOIN gallery_categories gc ON m.category_id = gc.id
   WHERE $whereStr
   ORDER BY m.sort_order ASC, m.created_at DESC",
  $params
);

require_once __DIR__ . '/includes/header.php';
?>

<?= renderFlash() ?>

<!-- ── Upload Area ────────────────────────────────────────────────────────── -->
<div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
  <div class="flex items-center justify-between mb-5">
    <h2 class="font-display font-bold text-navy text-lg">Upload Media</h2>
    <span class="text-gray-400 text-xs">Images: max 8 MB · Videos: max 100 MB</span>
  </div>

  <form id="upload-form" enctype="multipart/form-data" class="space-y-4">
    <?= Auth::csrfField() ?>
    <div id="drop-zone"
         class="border-2 border-dashed border-gray-200 rounded-xl p-10 text-center hover:border-amber/50 hover:bg-amber/5 transition-all cursor-pointer group">
      <input type="file" name="files[]" id="file-input" multiple accept="image/*,video/*" class="hidden">
      <svg class="w-10 h-10 text-gray-300 mx-auto mb-3 group-hover:text-amber transition-colors" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
      </svg>
      <p class="text-gray-500 text-sm font-medium group-hover:text-amber transition-colors">Drag & drop files here or click to browse</p>
      <p class="text-gray-300 text-xs mt-1">Supported: JPG, PNG, WEBP, GIF, MP4, WEBM</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div>
        <label class="admin-label">Category</label>
        <select name="category_id" class="admin-input">
          <option value="">— No Category —</option>
          <?php foreach ($categories as $c): ?>
          <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="admin-label">Title (optional)</label>
        <input type="text" name="title" class="admin-input" placeholder="e.g. Sharma Wedding 2024">
      </div>
      <div class="flex items-end gap-3">
        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
          <input type="checkbox" name="is_featured" value="1" class="rounded accent-amber"> Featured
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
          <input type="checkbox" name="is_visible" value="1" checked class="rounded accent-amber"> Visible
        </label>
      </div>
    </div>

    <div id="upload-preview" class="hidden">
      <div id="preview-list" class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 mt-3"></div>
    </div>

    <div id="upload-progress" class="hidden">
      <div class="flex items-center gap-3">
        <div class="flex-1 bg-gray-100 rounded-full h-2">
          <div id="progress-bar" class="bg-amber h-2 rounded-full transition-all" style="width:0%"></div>
        </div>
        <span id="progress-text" class="text-sm text-gray-500 w-16 text-right">0%</span>
      </div>
    </div>

    <div class="flex items-center gap-3">
      <button type="submit" id="upload-btn"
              class="px-6 py-2.5 bg-amber text-white rounded-xl font-semibold text-sm hover:bg-amber-dark transition-all flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
        Upload Files
      </button>
      <span id="upload-status" class="text-sm text-gray-500"></span>
    </div>
  </form>
</div>

<!-- ── Filters ─────────────────────────────────────────────────────────────── -->
<div class="bg-white rounded-2xl shadow-sm p-4 mb-4 flex flex-wrap gap-3 items-center">
  <a href="<?= BASE_URL ?>/admin/media.php"
     class="px-4 py-1.5 rounded-lg text-sm font-medium <?= !$filterCat && !$filterType ? 'bg-amber text-white' : 'text-gray-500 hover:bg-gray-100' ?> transition-all">
    All
  </a>
  <?php foreach ($categories as $c): ?>
  <a href="<?= BASE_URL ?>/admin/media.php?category=<?= $c['id'] ?>"
     class="px-4 py-1.5 rounded-lg text-sm font-medium <?= $filterCat==$c['id'] ? 'bg-amber text-white' : 'text-gray-500 hover:bg-gray-100' ?> transition-all">
    <?= e($c['name']) ?>
  </a>
  <?php endforeach; ?>
  <div class="ml-auto flex gap-2">
    <a href="?type=image<?= $filterCat ? '&category='.$filterCat : '' ?>"
       class="px-3 py-1.5 rounded-lg text-xs <?= $filterType==='image' ? 'bg-navy text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?> transition-all">Images</a>
    <a href="?type=video<?= $filterCat ? '&category='.$filterCat : '' ?>"
       class="px-3 py-1.5 rounded-lg text-xs <?= $filterType==='video' ? 'bg-navy text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?> transition-all">Videos</a>
  </div>
</div>

<!-- ── Media Grid ──────────────────────────────────────────────────────────── -->
<div class="bg-white rounded-2xl shadow-sm p-5">
  <div class="flex items-center justify-between mb-4">
    <p class="text-gray-500 text-sm"><?= count($media) ?> item<?= count($media)!==1?'s':'' ?></p>
    <button onclick="openCategoryModal()" class="text-sm text-amber hover:underline">Manage Categories</button>
  </div>

  <?php if ($media): ?>
  <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3" id="media-grid">
    <?php foreach ($media as $item):
      $thumb = $item['thumbnail'] ? THUMB_URL . '/' . $item['thumbnail'] : GALLERY_URL . '/' . $item['file_path'];
      $fullSrc = GALLERY_URL . '/' . $item['file_path'];
    ?>
    <div class="group relative rounded-xl overflow-hidden bg-gray-50 border border-gray-100 hover:border-amber/40 transition-all"
         data-id="<?= $item['id'] ?>">
      <!-- Thumb -->
      <div class="relative aspect-square">
        <img src="<?= e($thumb) ?>"
             alt="<?= e($item['title'] ?? '') ?>"
             class="w-full h-full object-cover <?= !$item['is_visible'] ? 'opacity-40 grayscale' : '' ?>"
             loading="lazy">
        <?php if ($item['type'] === 'video'): ?>
        <div class="absolute inset-0 flex items-center justify-center">
          <div class="w-8 h-8 rounded-full bg-black/50 flex items-center justify-center">
            <svg class="w-3.5 h-3.5 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          </div>
        </div>
        <?php endif; ?>
        <?php if ($item['is_featured']): ?>
        <div class="absolute top-1.5 right-1.5 w-5 h-5 bg-amber rounded-full flex items-center justify-center">
          <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
        <?php endif; ?>
        <!-- Hover controls -->
        <div class="absolute inset-0 bg-navy/70 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
          <button onclick="editMedia(<?= $item['id'] ?>, <?= htmlspecialchars(json_encode($item), ENT_QUOTES) ?>)"
                  class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/40 text-white flex items-center justify-center transition-colors" title="Edit">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          </button>
          <button onclick="toggleVisible(<?= $item['id'] ?>, <?= $item['is_visible'] ?>)"
                  class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/40 text-white flex items-center justify-center transition-colors" title="Toggle Visibility">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <?= $item['is_visible']
                ? '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>'
                : '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24M1 1l22 22"/>' ?>
            </svg>
          </button>
          <button onclick="deleteMedia(<?= $item['id'] ?>)"
                  class="w-8 h-8 rounded-full bg-red-500/80 hover:bg-red-500 text-white flex items-center justify-center transition-colors" title="Delete">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
          </button>
        </div>
      </div>
      <!-- Info -->
      <div class="p-2">
        <p class="text-xs font-medium text-navy truncate"><?= e($item['title'] ?? '—') ?></p>
        <p class="text-[10px] text-gray-400"><?= e($item['category_name'] ?? 'Uncategorised') ?></p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="py-16 text-center text-gray-400">
    <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
    <p>No media uploaded yet. Use the upload area above to add photos and videos.</p>
  </div>
  <?php endif; ?>
</div>

<!-- ── Edit Modal ──────────────────────────────────────────────────────────── -->
<div id="edit-modal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
    <div class="p-6 border-b border-gray-100 flex items-center justify-between">
      <h3 class="font-display font-bold text-navy">Edit Media</h3>
      <button onclick="closeEditModal()" class="text-gray-400 hover:text-navy">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <form id="edit-form" class="p-6 space-y-4">
      <input type="hidden" id="edit-id" name="id">
      <div>
        <label class="admin-label">Title</label>
        <input type="text" id="edit-title" name="title" class="admin-input" placeholder="Media title">
      </div>
      <div>
        <label class="admin-label">Category</label>
        <select id="edit-category" name="category_id" class="admin-input">
          <option value="">— No Category —</option>
          <?php foreach ($categories as $c): ?>
          <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="admin-label">Description</label>
        <textarea id="edit-desc" name="description" rows="2" class="admin-input resize-none"></textarea>
      </div>
      <div class="flex gap-5">
        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
          <input type="checkbox" id="edit-featured" name="is_featured" value="1" class="accent-amber"> Featured
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
          <input type="checkbox" id="edit-visible" name="is_visible" value="1" class="accent-amber"> Visible
        </label>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="submit" class="flex-1 py-2.5 bg-amber text-white rounded-xl font-semibold text-sm hover:bg-amber-dark transition-all">Save Changes</button>
        <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 bg-gray-100 text-gray-600 rounded-xl font-semibold text-sm hover:bg-gray-200 transition-all">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Category Modal -->
<div id="cat-modal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
    <div class="p-5 border-b border-gray-100 flex items-center justify-between">
      <h3 class="font-display font-bold text-navy">Gallery Categories</h3>
      <button onclick="document.getElementById('cat-modal').classList.add('hidden')" class="text-gray-400 hover:text-navy">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="p-5 space-y-3">
      <?php foreach ($categories as $c): ?>
      <div class="flex items-center justify-between py-2 border-b border-gray-50">
        <span class="text-sm text-navy"><?= e($c['name']) ?></span>
        <button onclick="deleteCategory(<?= $c['id'] ?>, '<?= e($c['name']) ?>')"
                class="text-red-400 hover:text-red-600 text-xs">Delete</button>
      </div>
      <?php endforeach; ?>
      <form id="add-cat-form" class="flex gap-2 mt-4">
        <input type="text" id="new-cat-name" class="admin-input flex-1 !py-2 !text-sm" placeholder="New category name">
        <button type="submit" class="px-4 py-2 bg-amber text-white rounded-xl text-sm font-semibold shrink-0 hover:bg-amber-dark transition-all">Add</button>
      </form>
    </div>
  </div>
</div>

<script>
const CSRF = '<?= Auth::generateCsrf() ?>';
const adminBase = '<?= BASE_URL ?>/admin';

// Drop zone
const dropZone = document.getElementById('drop-zone');
const fileInput = document.getElementById('file-input');
dropZone.addEventListener('click', () => fileInput.click());
dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('border-amber','bg-amber/5'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('border-amber','bg-amber/5'));
dropZone.addEventListener('drop', e => { e.preventDefault(); fileInput.files = e.dataTransfer.files; previewFiles(); });
fileInput.addEventListener('change', previewFiles);

function previewFiles() {
  const files = fileInput.files;
  if (!files.length) return;
  const preview = document.getElementById('upload-preview');
  const list    = document.getElementById('preview-list');
  list.innerHTML = '';
  preview.classList.remove('hidden');
  Array.from(files).forEach(f => {
    const div = document.createElement('div');
    div.className = 'aspect-square rounded-lg overflow-hidden bg-gray-100 relative';
    if (f.type.startsWith('image/')) {
      const img = document.createElement('img');
      img.src = URL.createObjectURL(f);
      img.className = 'w-full h-full object-cover';
      div.appendChild(img);
    } else {
      div.innerHTML = '<div class="w-full h-full flex items-center justify-center text-xs text-gray-400">VIDEO</div>';
    }
    const name = document.createElement('div');
    name.className = 'absolute bottom-0 left-0 right-0 bg-black/50 text-white text-[9px] px-1 py-0.5 truncate';
    name.textContent = f.name;
    div.appendChild(name);
    list.appendChild(div);
  });
}

document.getElementById('upload-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const form = this;
  const btn  = document.getElementById('upload-btn');
  const progress = document.getElementById('upload-progress');
  const bar  = document.getElementById('progress-bar');
  const txt  = document.getElementById('progress-text');
  const status = document.getElementById('upload-status');

  if (!fileInput.files.length) { status.textContent = 'Please select files first.'; return; }

  btn.disabled = true;
  progress.classList.remove('hidden');
  status.textContent = 'Uploading…';

  const fd = new FormData(form);
  const xhr = new XMLHttpRequest();
  xhr.upload.addEventListener('progress', evt => {
    if (evt.lengthComputable) {
      const pct = Math.round((evt.loaded / evt.total) * 100);
      bar.style.width = pct + '%';
      txt.textContent = pct + '%';
    }
  });
  xhr.addEventListener('load', () => {
    try {
      const data = JSON.parse(xhr.responseText);
      if (data.success) {
        status.textContent = data.message || 'Uploaded successfully!';
        setTimeout(() => location.reload(), 1000);
      } else {
        status.textContent = data.message || 'Upload failed.';
        btn.disabled = false;
      }
    } catch { status.textContent = 'Server error.'; btn.disabled = false; }
  });
  xhr.open('POST', adminBase + '/ajax/upload-media.php');
  xhr.send(fd);
});

function editMedia(id, item) {
  document.getElementById('edit-id').value = id;
  document.getElementById('edit-title').value = item.title || '';
  document.getElementById('edit-category').value = item.category_id || '';
  document.getElementById('edit-desc').value = item.description || '';
  document.getElementById('edit-featured').checked = item.is_featured == 1;
  document.getElementById('edit-visible').checked   = item.is_visible  == 1;
  document.getElementById('edit-modal').classList.remove('hidden');
}
function closeEditModal() { document.getElementById('edit-modal').classList.add('hidden'); }

document.getElementById('edit-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  const fd = new FormData(this);
  fd.append('_vk_csrf', CSRF);
  const res  = await fetch(adminBase + '/ajax/update-media.php', {method:'POST', body: fd});
  const data = await res.json();
  if (data.success) { closeEditModal(); location.reload(); }
  else alert(data.message || 'Update failed.');
});

function toggleVisible(id, current) {
  const fd = new FormData();
  fd.append('id', id);
  fd.append('visible', current ? 0 : 1);
  fd.append('_vk_csrf', CSRF);
  fetch(adminBase + '/ajax/toggle-media.php', {method:'POST', body: fd})
    .then(r => r.json()).then(d => { if (d.success) location.reload(); });
}

function deleteMedia(id) {
  if (!confirm('Delete this media item permanently?')) return;
  const fd = new FormData();
  fd.append('id', id);
  fd.append('_vk_csrf', CSRF);
  fetch(adminBase + '/ajax/delete-media.php', {method:'POST', body: fd})
    .then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message); });
}

function openCategoryModal() { document.getElementById('cat-modal').classList.remove('hidden'); }

function deleteCategory(id, name) {
  if (!confirm(`Delete category "${name}"? Media items will become uncategorised.`)) return;
  const fd = new FormData();
  fd.append('action', 'delete'); fd.append('id', id); fd.append('_vk_csrf', CSRF);
  fetch(adminBase + '/ajax/manage-categories.php', {method:'POST', body: fd})
    .then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message); });
}

document.getElementById('add-cat-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const name = document.getElementById('new-cat-name').value.trim();
  if (!name) return;
  const fd = new FormData();
  fd.append('action', 'add'); fd.append('name', name); fd.append('_vk_csrf', CSRF);
  fetch(adminBase + '/ajax/manage-categories.php', {method:'POST', body: fd})
    .then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message); });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
