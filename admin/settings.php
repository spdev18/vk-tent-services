<?php
require_once __DIR__ . '/includes/auth-check.php';

$adminTitle = 'Settings';
$adminPage  = 'settings';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST[CSRF_TOKEN_NAME] ?? '';
    if (!Auth::verifyCsrf($csrf)) { setFlash('error', 'Security token error.'); redirect('settings.php'); }

    $action = post('action');

    if ($action === 'update_profile') {
        $fields = ['business_name','tagline','owner_name','about_text','experience_years','area_served','phone','whatsapp','email','address','facebook','instagram','youtube'];
        foreach ($fields as $f) {
            $val = trim(strip_tags(post($f)));
            DB::execute("INSERT INTO settings (`key`,`value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `value`=?", [$f, $val, $val]);
        }
        // Owner photo upload
        if (!empty($_FILES['owner_photo']['name'])) {
            $file = $_FILES['owner_photo'];
            if (!is_dir(PROFILE_PATH)) mkdir(PROFILE_PATH, 0755, true);
            $mime = mime_content_type($file['tmp_name']);
            if (in_array($mime, ALLOWED_IMAGE_TYPES)) {
                $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
                $name = 'owner_' . time() . '.' . strtolower($ext);
                if (move_uploaded_file($file['tmp_name'], PROFILE_PATH . '/' . $name)) {
                    DB::execute("INSERT INTO settings (`key`,`value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `value`=?", ['owner_photo', $name, $name]);
                }
            }
        }
        setFlash('success', 'Profile updated.'); redirect('settings.php');
    }

    if ($action === 'change_password') {
        $current = post('current_password');
        $new     = post('new_password');
        $confirm = post('confirm_password');
        $user    = DB::fetchOne('SELECT * FROM admin_users WHERE id=?', [$currentUser['id']]);
        if (!password_verify($current, $user['password_hash'])) {
            setFlash('error', 'Current password is incorrect.'); redirect('settings.php');
        }
        if (strlen($new) < 8) { setFlash('error', 'Password must be at least 8 characters.'); redirect('settings.php'); }
        if ($new !== $confirm) { setFlash('error', 'Passwords do not match.'); redirect('settings.php'); }
        $hash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]);
        DB::execute('UPDATE admin_users SET password_hash=? WHERE id=?', [$hash, $currentUser['id']]);
        setFlash('success', 'Password changed successfully.'); redirect('settings.php');
    }

    if ($action === 'add_service') {
        $title = trim(strip_tags(post('title')));
        $desc  = trim(strip_tags(post('description')));
        if ($title) {
            $order = (int)DB::fetchValue('SELECT COALESCE(MAX(sort_order),0)+1 FROM services');
            DB::insert('INSERT INTO services (title, description, sort_order) VALUES (?,?,?)', [$title, $desc, $order]);
        }
        setFlash('success', 'Service added.'); redirect('settings.php#services');
    }
    if ($action === 'delete_service') {
        DB::execute('DELETE FROM services WHERE id=?', [intPost('service_id')]);
        setFlash('success', 'Service deleted.'); redirect('settings.php#services');
    }
    if ($action === 'toggle_service') {
        $sid  = intPost('service_id');
        $curr = (int)DB::fetchValue('SELECT is_active FROM services WHERE id=?', [$sid]);
        DB::execute('UPDATE services SET is_active=? WHERE id=?', [$curr ? 0 : 1, $sid]);
        redirect('settings.php#services');
    }
}

$services   = DB::fetchAll('SELECT * FROM services ORDER BY sort_order');
$ownerPhoto = $settings['owner_photo'] ?? '';

require_once __DIR__ . '/includes/header.php';
?>

<?= renderFlash() ?>

<div class="space-y-6">

  <!-- Business Profile -->
  <div class="bg-white rounded-2xl shadow-sm p-6">
    <h2 class="font-display font-bold text-navy text-lg mb-5">Business Profile</h2>
    <form method="post" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-5">
      <?= Auth::csrfField() ?>
      <input type="hidden" name="action" value="update_profile">

      <div class="sm:col-span-2 flex items-center gap-5 p-4 bg-gray-50 rounded-xl">
        <div class="w-20 h-20 rounded-xl overflow-hidden bg-gray-200 shrink-0">
          <?php if ($ownerPhoto): ?>
          <img src="<?= PROFILE_URL ?>/<?= e($ownerPhoto) ?>" alt="Owner" class="w-full h-full object-cover">
          <?php else: ?>
          <div class="w-full h-full flex items-center justify-center text-gray-400 text-2xl font-bold"><?= strtoupper(substr($settings['owner_name'] ?? 'V', 0, 1)) ?></div>
          <?php endif; ?>
        </div>
        <div class="flex-1">
          <label class="admin-label mb-1.5">Owner Photo</label>
          <input type="file" name="owner_photo" accept="image/*" class="block text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:bg-amber/10 file:text-amber file:font-medium hover:file:bg-amber/20 transition-all">
          <p class="text-gray-400 text-xs mt-1">JPG/PNG/WEBP, max 8 MB</p>
        </div>
      </div>

      <div><label class="admin-label">Business Name</label><input type="text" name="business_name" class="admin-input" value="<?= e($settings['business_name'] ?? '') ?>"></div>
      <div><label class="admin-label">Tagline</label><input type="text" name="tagline" class="admin-input" value="<?= e($settings['tagline'] ?? '') ?>"></div>
      <div><label class="admin-label">Owner Name</label><input type="text" name="owner_name" class="admin-input" value="<?= e($settings['owner_name'] ?? '') ?>"></div>
      <div><label class="admin-label">Years of Experience</label><input type="text" name="experience_years" class="admin-input" value="<?= e($settings['experience_years'] ?? '') ?>"></div>
      <div class="sm:col-span-2"><label class="admin-label">About / Bio</label><textarea name="about_text" rows="4" class="admin-input resize-none"><?= e($settings['about_text'] ?? '') ?></textarea></div>
      <div><label class="admin-label">Area Served</label><input type="text" name="area_served" class="admin-input" value="<?= e($settings['area_served'] ?? '') ?>"></div>
      <div></div>
      <div><label class="admin-label">Phone</label><input type="tel" name="phone" class="admin-input" value="<?= e($settings['phone'] ?? '') ?>"></div>
      <div><label class="admin-label">WhatsApp (with country code)</label><input type="tel" name="whatsapp" class="admin-input" value="<?= e($settings['whatsapp'] ?? '') ?>" placeholder="+919876543210"></div>
      <div><label class="admin-label">Email</label><input type="email" name="email" class="admin-input" value="<?= e($settings['email'] ?? '') ?>"></div>
      <div><label class="admin-label">Address</label><input type="text" name="address" class="admin-input" value="<?= e($settings['address'] ?? '') ?>"></div>
      <div><label class="admin-label">Facebook URL</label><input type="url" name="facebook" class="admin-input" value="<?= e($settings['facebook'] ?? '') ?>"></div>
      <div><label class="admin-label">Instagram URL</label><input type="url" name="instagram" class="admin-input" value="<?= e($settings['instagram'] ?? '') ?>"></div>
      <div><label class="admin-label">YouTube URL</label><input type="url" name="youtube" class="admin-input" value="<?= e($settings['youtube'] ?? '') ?>"></div>
      <div class="sm:col-span-2">
        <button type="submit" class="px-8 py-2.5 bg-amber text-white rounded-xl font-semibold text-sm hover:bg-amber-dark transition-all">Save Profile</button>
      </div>
    </form>
  </div>

  <!-- Services Management -->
  <div class="bg-white rounded-2xl shadow-sm p-6" id="services">
    <h2 class="font-display font-bold text-navy text-lg mb-5">Services</h2>
    <div class="space-y-2 mb-5">
      <?php foreach ($services as $svc): ?>
      <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 <?= !$svc['is_active'] ? 'opacity-50' : '' ?>">
        <div class="flex-1 min-w-0">
          <p class="font-medium text-navy text-sm"><?= e($svc['title']) ?></p>
          <p class="text-gray-400 text-xs truncate"><?= e(substr($svc['description'] ?? '', 0, 80)) ?></p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <form method="post">
            <?= Auth::csrfField() ?>
            <input type="hidden" name="action" value="toggle_service">
            <input type="hidden" name="service_id" value="<?= $svc['id'] ?>">
            <button type="submit" class="text-xs px-2.5 py-1 rounded-lg <?= $svc['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500' ?> hover:opacity-80 transition-all">
              <?= $svc['is_active'] ? 'Active' : 'Hidden' ?>
            </button>
          </form>
          <form method="post" onsubmit="return confirm('Delete this service?')">
            <?= Auth::csrfField() ?>
            <input type="hidden" name="action" value="delete_service">
            <input type="hidden" name="service_id" value="<?= $svc['id'] ?>">
            <button type="submit" class="text-red-400 hover:text-red-600 text-xs px-2 py-1">Delete</button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <form method="post" class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-100">
      <?= Auth::csrfField() ?>
      <input type="hidden" name="action" value="add_service">
      <input type="text" name="title" class="admin-input flex-1 !py-2 !text-sm" placeholder="Service title" required>
      <input type="text" name="description" class="admin-input flex-1 !py-2 !text-sm" placeholder="Short description">
      <button type="submit" class="px-5 py-2 bg-amber text-white rounded-xl text-sm font-semibold hover:bg-amber-dark transition-all shrink-0">Add Service</button>
    </form>
  </div>

  <!-- Change Password -->
  <div class="bg-white rounded-2xl shadow-sm p-6">
    <h2 class="font-display font-bold text-navy text-lg mb-5">Change Password</h2>
    <form method="post" class="grid grid-cols-1 sm:grid-cols-3 gap-4 max-w-xl">
      <?= Auth::csrfField() ?>
      <input type="hidden" name="action" value="change_password">
      <div><label class="admin-label">Current Password</label><input type="password" name="current_password" class="admin-input" required></div>
      <div><label class="admin-label">New Password</label><input type="password" name="new_password" class="admin-input" minlength="8" required></div>
      <div><label class="admin-label">Confirm Password</label><input type="password" name="confirm_password" class="admin-input" required></div>
      <div class="sm:col-span-3">
        <button type="submit" class="px-8 py-2.5 bg-navy text-white rounded-xl font-semibold text-sm hover:bg-navy/90 transition-all">Update Password</button>
      </div>
    </form>
  </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
