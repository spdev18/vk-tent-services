<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// CSRF check
$csrf = $_POST[CSRF_TOKEN_NAME] ?? '';
if (!Auth::verifyCsrf($csrf)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Security token mismatch. Please refresh and try again.']);
    exit;
}

// Honeypot
if (isSpam()) {
    echo json_encode(['success' => true]); // silently succeed for bots
    exit;
}

// Rate limiting: max 3 enquiries per IP in 10 minutes, 30 global
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$ipCount = (int)DB::fetchValue(
    "SELECT COUNT(*) FROM enquiries WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)",
    [$ip]
);
$globalCount = (int)DB::fetchValue(
    "SELECT COUNT(*) FROM enquiries WHERE created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)",
    []
);
if ($ipCount >= 3 || $globalCount >= 30) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many requests. Please try again later.']);
    exit;
}

// Sanitise & validate inputs
$name       = trim(strip_tags(post('name')));
$phone      = trim(strip_tags(post('phone')));
$email      = trim(filter_var(post('email'), FILTER_SANITIZE_EMAIL));
$event_date = trim(post('event_date'));
$event_type = trim(strip_tags(post('event_type')));
$venue      = trim(strip_tags(post('venue')));
$message    = trim(strip_tags(post('message')));

$errors = [];

if (strlen($name) < 2)   $errors[] = 'Please enter your full name.';
if (!preg_match('/^[+0-9\s\-]{7,15}$/', $phone)) $errors[] = 'Please enter a valid phone number.';
if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
if (!$event_date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date)) $errors[] = 'Please select an event date.';
if ($event_date && $event_date <= date('Y-m-d')) $errors[] = 'Event date must be in the future.';
if (!$event_type) $errors[] = 'Please select an event type.';
if (strlen($venue) < 3)  $errors[] = 'Please enter the venue / location.';

if ($errors) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// Check availability again server-side
if (!isDateAvailable($event_date)) {
    echo json_encode(['success' => false, 'message' => 'The selected date is not available. Please choose a different date.']);
    exit;
}

// Insert enquiry
try {
    DB::insert(
        "INSERT INTO enquiries (name, phone, email, event_date, event_type, venue, message, ip_address, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'new')",
        [$name, $phone, $email ?: null, $event_date, $event_type, $venue, $message ?: null, $ip]
    );
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log('Enquiry insert error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to submit. Please try again.']);
}
