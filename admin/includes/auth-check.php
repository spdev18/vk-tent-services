<?php
// Must be included at the top of every admin page (after requires)
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

Auth::requireLogin();
$currentUser = Auth::currentUser();
$settings    = DB::settings();

// Unread enquiry count for badge
$newEnquiries = (int)DB::fetchValue("SELECT COUNT(*) FROM enquiries WHERE status='new' AND is_read=0");
