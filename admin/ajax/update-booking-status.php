<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

Auth::requireLogin();
$csrf = $_POST[CSRF_TOKEN_NAME] ?? '';
if (!Auth::verifyCsrf($csrf)) { setFlash('error', 'Security error.'); redirect(BASE_URL . '/admin/events.php'); }

$id     = intPost('id');
$status = post('status');
if (!in_array($status, ['upcoming','held','cancelled'])) { redirect(BASE_URL . '/admin/events.php'); }

DB::execute('UPDATE bookings SET status=? WHERE id=?', [$status, $id]);
// If completed, update linked enquiry
if ($status === 'held') {
    DB::execute("UPDATE enquiries SET status='completed' WHERE id=(SELECT enquiry_id FROM bookings WHERE id=?)", [$id]);
}
setFlash('success', 'Event status updated.');
redirect(BASE_URL . '/admin/events.php');
