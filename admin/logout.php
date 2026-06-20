<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
Auth::logout();
header('Location: ' . BASE_URL . '/admin/index.php');
exit;
