<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

header('Content-Type: application/json');
Auth::requireLogin();

$csrf = $_POST[CSRF_TOKEN_NAME] ?? '';
if (!Auth::verifyCsrf($csrf)) { json_resp(['success'=>false,'message'=>'CSRF error.'], 403); }

$id      = intPost('id');
$visible = (int)($_POST['visible'] ?? 0);

DB::execute('UPDATE gallery_media SET is_visible=? WHERE id=?', [$visible ? 1 : 0, $id]);
json_resp(['success'=>true]);
