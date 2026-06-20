<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

header('Content-Type: application/json');
Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { json_resp(['success'=>false,'message'=>'Bad method.'], 405); }

$csrf = $_POST[CSRF_TOKEN_NAME] ?? '';
if (!Auth::verifyCsrf($csrf)) { json_resp(['success'=>false,'message'=>'CSRF error.'], 403); }

if (empty($_FILES['files'])) { json_resp(['success'=>false,'message'=>'No files received.']); }

$catId      = intPost('category_id') ?: null;
$title      = trim(strip_tags(post('title')));
$isFeatured = !empty($_POST['is_featured']) ? 1 : 0;
$isVisible  = !empty($_POST['is_visible']) ? 1 : 0;

// Restructure multi-file array
$files = $_FILES['files'];
$count = count($files['name']);
$uploaded = 0;
$errors   = [];

for ($i = 0; $i < $count; $i++) {
    $file = [
        'name'     => $files['name'][$i],
        'tmp_name' => $files['tmp_name'][$i],
        'type'     => $files['type'][$i],
        'size'     => $files['size'][$i],
        'error'    => $files['error'][$i],
    ];

    $result = uploadMedia($file);
    if (!$result['success']) {
        $errors[] = $file['name'] . ': ' . $result['message'];
        continue;
    }

    $order = (int)DB::fetchValue('SELECT COALESCE(MAX(sort_order),0)+1 FROM gallery_media');
    DB::insert(
      'INSERT INTO gallery_media (category_id, type, file_path, thumbnail, title, is_featured, is_visible, sort_order)
       VALUES (?,?,?,?,?,?,?,?)',
      [$catId, $result['type'], $result['file_path'], $result['thumbnail'], $title ?: null, $isFeatured, $isVisible, $order]
    );
    $uploaded++;
}

if ($uploaded > 0) {
    $msg = "$uploaded file(s) uploaded successfully.";
    if ($errors) $msg .= ' Errors: ' . implode('; ', $errors);
    json_resp(['success'=>true,'message'=>$msg]);
} else {
    json_resp(['success'=>false,'message'=>'No files uploaded. ' . implode(' ', $errors)]);
}
