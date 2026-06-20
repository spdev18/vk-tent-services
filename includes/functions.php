<?php
// VK Tent Services — Utility Functions

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function json_resp(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

// ── Media helpers ─────────────────────────────────────────────────────────────

function generateThumbnail(string $sourcePath, string $thumbPath, int $w = THUMB_WIDTH, int $h = THUMB_HEIGHT): bool {
    if (!extension_loaded('gd')) return false;
    $info = getimagesize($sourcePath);
    if (!$info) return false;

    [$origW, $origH, $type] = $info;

    $src = match ($type) {
        IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
        IMAGETYPE_PNG  => imagecreatefrompng($sourcePath),
        IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
        IMAGETYPE_GIF  => imagecreatefromgif($sourcePath),
        default        => false,
    };
    if (!$src) return false;

    // Maintain aspect ratio — crop to fill
    $srcRatio  = $origW / $origH;
    $dstRatio  = $w / $h;
    if ($srcRatio > $dstRatio) {
        $cropH = $origH;
        $cropW = (int)($origH * $dstRatio);
        $cropX = (int)(($origW - $cropW) / 2);
        $cropY = 0;
    } else {
        $cropW = $origW;
        $cropH = (int)($origW / $dstRatio);
        $cropX = 0;
        $cropY = (int)(($origH - $cropH) / 2);
    }

    $dst = imagecreatetruecolor($w, $h);
    if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }
    imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, $w, $h, $cropW, $cropH);

    $result = match ($type) {
        IMAGETYPE_JPEG => imagejpeg($dst, $thumbPath, 85),
        IMAGETYPE_PNG  => imagepng($dst, $thumbPath, 7),
        IMAGETYPE_WEBP => imagewebp($dst, $thumbPath, 85),
        IMAGETYPE_GIF  => imagegif($dst, $thumbPath),
        default        => false,
    };
    imagedestroy($src);
    imagedestroy($dst);
    return (bool)$result;
}

function safeFilename(string $name): string {
    $name = pathinfo($name, PATHINFO_FILENAME);
    $name = strtolower(preg_replace('/[^a-z0-9_-]/i', '_', $name));
    return $name . '_' . substr(md5(uniqid('', true)), 0, 8);
}

function uploadMedia(array $file, string $category = 'general'): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error code: ' . $file['error']];
    }

    $mime = mime_content_type($file['tmp_name']);
    $isImage = in_array($mime, ALLOWED_IMAGE_TYPES, true);
    $isVideo = in_array($mime, ALLOWED_VIDEO_TYPES, true);

    if (!$isImage && !$isVideo) {
        return ['success' => false, 'message' => 'File type not allowed.'];
    }

    if ($isImage && $file['size'] > MAX_IMAGE_SIZE) {
        return ['success' => false, 'message' => 'Image exceeds 8 MB limit.'];
    }
    if ($isVideo && $file['size'] > MAX_VIDEO_SIZE) {
        return ['success' => false, 'message' => 'Video exceeds 100 MB limit.'];
    }

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $basename = safeFilename($file['name']) . '.' . strtolower($ext);
    $destPath = GALLERY_PATH . '/' . $basename;
    $thumbRel = null;

    if (!is_dir(GALLERY_PATH)) mkdir(GALLERY_PATH, 0755, true);
    if (!is_dir(THUMB_PATH))   mkdir(THUMB_PATH, 0755, true);

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return ['success' => false, 'message' => 'Could not save file.'];
    }

    if ($isImage) {
        $thumbName = 'thumb_' . $basename;
        $thumbFull = THUMB_PATH . '/' . $thumbName;
        if (generateThumbnail($destPath, $thumbFull)) {
            $thumbRel = $thumbName;
        }
    }

    return [
        'success'   => true,
        'type'      => $isImage ? 'image' : 'video',
        'file_path' => $basename,
        'thumbnail' => $thumbRel,
    ];
}

// ── Date / booking helpers ────────────────────────────────────────────────────

function getUnavailableDates(int $year, int $month): array {
    require_once __DIR__ . '/db.php';
    // Confirmed bookings
    $bookings = DB::fetchAll(
        "SELECT event_date FROM bookings WHERE status='upcoming' AND YEAR(event_date)=? AND MONTH(event_date)=?",
        [$year, $month]
    );
    // Blocked dates
    $blocked = DB::fetchAll(
        "SELECT blocked_date AS event_date FROM blocked_dates WHERE YEAR(blocked_date)=? AND MONTH(blocked_date)=?",
        [$year, $month]
    );
    $dates = [];
    foreach (array_merge($bookings, $blocked) as $row) {
        $dates[] = $row['event_date'];
    }
    return array_unique($dates);
}

function isDateAvailable(string $date): bool {
    require_once __DIR__ . '/db.php';
    $booked  = DB::fetchValue("SELECT COUNT(*) FROM bookings WHERE event_date=? AND status='upcoming'", [$date]);
    $blocked = DB::fetchValue("SELECT COUNT(*) FROM blocked_dates WHERE blocked_date=?", [$date]);
    return ($booked == 0 && $blocked == 0);
}

// ── Finance helpers ───────────────────────────────────────────────────────────

function getBookingPaymentSummary(int $bookingId): array {
    require_once __DIR__ . '/db.php';
    $booking  = DB::fetchOne('SELECT total_amount FROM bookings WHERE id=?', [$bookingId]);
    $received = (float)DB::fetchValue('SELECT COALESCE(SUM(amount),0) FROM payments WHERE booking_id=?', [$bookingId]);
    $total    = $booking ? (float)$booking['total_amount'] : 0;
    return [
        'total'    => $total,
        'received' => $received,
        'due'      => max(0, $total - $received),
        'paid'     => $total > 0 && $received >= $total,
    ];
}

function formatCurrency(float $amount): string {
    return '₹' . number_format($amount, 2);
}

// ── Input helpers ─────────────────────────────────────────────────────────────

function post(string $key, string $default = ''): string {
    return trim($_POST[$key] ?? $default);
}

function get(string $key, string $default = ''): string {
    return trim($_GET[$key] ?? $default);
}

function intPost(string $key, int $default = 0): int {
    return (int)($_POST[$key] ?? $default);
}

function intGet(string $key, int $default = 0): int {
    return (int)($_GET[$key] ?? $default);
}

// Simple honeypot check — returns true when spam is suspected
function isSpam(): bool {
    return !empty($_POST['website'] ?? '');
}

// ── Slug helper ───────────────────────────────────────────────────────────────

function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

// ── Flash messages ────────────────────────────────────────────────────────────

function setFlash(string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function renderFlash(): string {
    $flash = getFlash();
    if (!$flash) return '';
    $colors = [
        'success' => 'bg-green-50 border-green-400 text-green-800',
        'error'   => 'bg-red-50 border-red-400 text-red-800',
        'info'    => 'bg-blue-50 border-blue-400 text-blue-800',
    ];
    $cls = $colors[$flash['type']] ?? $colors['info'];
    return '<div class="flash-msg border-l-4 p-4 mb-6 rounded ' . $cls . '">' . e($flash['message']) . '</div>';
}
