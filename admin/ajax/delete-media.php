<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

header('Content-Type: application/json');
Auth::requireLogin();

$csrf = $_POST[CSRF_TOKEN_NAME] ?? '';
if (!Auth::verifyCsrf($csrf)) { json_resp(['success'=>false,'message'=>'CSRF error.'], 403); }

$id = intPost('id');
if (!$id) { json_resp(['success'=>false,'message'=>'Invalid ID.']); }

$media = DB::fetchOne('SELECT * FROM gallery_media WHERE id=?', [$id]);
if (!$media) { json_resp(['success'=>false,'message'=>'Not found.']); }

// Delete files from disk
$filePath = GALLERY_PATH . '/' . $media['file_path'];
$thumbPath = $media['thumbnail'] ? THUMB_PATH . '/' . $media['thumbnail'] : null;
if (file_exists($filePath)) unlink($filePath);
if ($thumbPath && file_exists($thumbPath)) unlink($thumbPath);

DB::execute('DELETE FROM gallery_media WHERE id=?', [$id]);
json_resp(['success'=>true]);
