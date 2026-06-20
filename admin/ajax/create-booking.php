<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

Auth::requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(BASE_URL . '/admin/bookings.php'); }

$csrf = $_POST[CSRF_TOKEN_NAME] ?? '';
if (!Auth::verifyCsrf($csrf)) { setFlash('error', 'Security error.'); redirect(BASE_URL . '/admin/bookings.php'); }

$name    = trim(strip_tags(post('customer_name')));
$phone   = trim(strip_tags(post('phone')));
$email   = trim(filter_var(post('email'), FILTER_SANITIZE_EMAIL)) ?: null;
$date    = post('event_date');
$type    = post('event_type');
$venue   = post('venue');
$amount  = (float)post('total_amount');
$notes   = trim(strip_tags(post('notes')));

if (!$name || !$phone || !$date || !$type || !$venue) {
    setFlash('error', 'Please fill all required fields.'); redirect(BASE_URL . '/admin/bookings.php?new=1');
}

DB::insert(
  'INSERT INTO bookings (customer_name, phone, email, event_date, venue, event_type, status, total_amount, notes)
   VALUES (?,?,?,?,?,?,\'upcoming\',?,?)',
  [$name, $phone, $email, $date, $venue, $type, $amount, $notes ?: null]
);
setFlash('success', 'Booking created successfully.');
redirect(BASE_URL . '/admin/bookings.php');
