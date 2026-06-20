<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

header('Content-Type: application/json');
Auth::requireLogin();

$csrf = $_POST[CSRF_TOKEN_NAME] ?? '';
if (!Auth::verifyCsrf($csrf)) { json_resp(['success'=>false,'message'=>'CSRF error.'], 403); }

$action = post('action');

if ($action === 'add') {
    $name = trim(strip_tags(post('name')));
    if (!$name) { json_resp(['success'=>false,'message'=>'Name required.']); }
    $slug  = slugify($name);
    $order = (int)DB::fetchValue('SELECT COALESCE(MAX(sort_order),0)+1 FROM gallery_categories');
    try {
        DB::insert('INSERT INTO gallery_categories (name, slug, sort_order) VALUES (?,?,?)', [$name, $slug, $order]);
        json_resp(['success'=>true]);
    } catch (\Exception $e) {
        json_resp(['success'=>false,'message'=>'Category already exists.']);
    }
}

if ($action === 'delete') {
    $id = intPost('id');
    DB::execute('UPDATE gallery_media SET category_id=NULL WHERE category_id=?', [$id]);
    DB::execute('DELETE FROM gallery_categories WHERE id=?', [$id]);
    json_resp(['success'=>true]);
}

json_resp(['success'=>false,'message'=>'Unknown action.']);
