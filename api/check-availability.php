<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

$date = trim($_GET['date'] ?? '');

if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['available' => false, 'error' => 'Invalid date.']);
    exit;
}

// Reject past dates
if ($date <= date('Y-m-d')) {
    echo json_encode(['available' => false, 'error' => 'Date is in the past.']);
    exit;
}

$available = isDateAvailable($date);
echo json_encode(['available' => $available]);
