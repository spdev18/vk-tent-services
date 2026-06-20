<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

header('Content-Type: application/json');
Auth::requireLogin();

$csrf = $_POST[CSRF_TOKEN_NAME] ?? '';
if (!Auth::verifyCsrf($csrf)) { json_resp(['success'=>false,'message'=>'CSRF error.'], 403); }

$id          = intPost('id');
$title       = trim(strip_tags(post('title')));
$catId       = intPost('category_id') ?: null;
$desc        = trim(strip_tags(post('description')));
$isFeatured  = !empty($_POST['is_featured']) ? 1 : 0;
$isVisible   = !empty($_POST['is_visible']) ? 1 : 0;

if (!$id) { json_resp(['success'=>false,'message'=>'Invalid ID.']); }

DB::execute(
  'UPDATE gallery_media SET title=?, category_id=?, description=?, is_featured=?, is_visible=? WHERE id=?',
  [$title ?: null, $catId, $desc ?: null, $isFeatured, $isVisible, $id]
);
json_resp(['success'=>true]);
