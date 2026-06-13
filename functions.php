<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/email.php';

function cleanInput(string $data): string { return trim(strip_tags($data)); }
function generateSlug(string $text): string { $text = mb_strtolower($text, 'UTF-8'); $text = str_replace(['á','é','í','ó','ö','ő','ú','ü','ű',' '], ['a','e','i','o','o','o','u','u','u','-'], $text); $text = preg_replace('/[^a-z0-9\-]/', '', $text); $text = preg_replace('/-+/', '-', $text); return trim($text, '-'); }
function generateHirdetesUrl(int $id, string $cim): string { return BASE_URL . '/hirdetes/' . $id . '-' . generateSlug($cim); }
function calculateExpiryDate(int $days): string { return date('Y-m-d H:i:s', strtotime("+{$days} days")); }
function isValidFokategoria(string $fok): bool { return in_array($fok, ['allas','ingatlan','jarmu','muszaki','haztartas','szolgaltatas','hobbi','ruhazat','allatok','egyeb']); }
function requireLogin(): void { if (!isLoggedIn()) { redirect(BASE_URL . '/belepes'); } }
function requireAdmin(): void { if (!isAdmin()) { redirect(BASE_URL . '/admin/login'); } }

function getKategoriaNev(string $fok): string {
    return match($fok) { 'allas' => 'Állás', 'ingatlan' => 'Ingatlan', 'jarmu' => 'Jármű', 'muszaki' => 'Műszaki cikk', 'haztartas' => 'Háztartás, bútor', 'szolgaltatas' => 'Szolgáltatás', 'hobbi' => 'Hobbi, sport', 'ruhazat' => 'Ruházat, divat', 'allatok' => 'Állatok', 'egyeb' => 'Egyéb', default => $fok };
}

function getKategoriaIcon(string $fok): string {
    return match($fok) { 'allas' => '💼', 'ingatlan' => '🏠', 'jarmu' => '🚗', 'muszaki' => '📱', 'haztartas' => '🛋️', 'szolgaltatas' => '🔧', 'hobbi' => '⚽', 'ruhazat' => '👗', 'allatok' => '🐾', 'egyeb' => '📦', default => '📌' };
}

function arFormatum(array $h): string {
    return match($h['ar_tipus']) { 'ingyen' => 'Ingyen elvihető', 'megbeszeles' => 'Megbeszélés szerint', default => number_format($h['ar'], 0, ',', ' ') . ' Ft' };
}

function uploadImage(array $file): array|false {
    $finfo = finfo_open(FILEINFO_MIME_TYPE); $mimeType = finfo_file($finfo, $file['tmp_name']); finfo_close($finfo);
    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) return false;
    if ($file['size'] > MAX_FILE_SIZE) return false;
    $uploadDir = UPLOAD_DIR . date('Y/m/'); if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $extension = match($mimeType) { 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', default => 'jpg' };
    $uniqueName = uniqid('hird_', true) . '.' . $extension; $destination = $uploadDir . $uniqueName;
    $sourceImage = match($mimeType) { 'image/jpeg' => imagecreatefromjpeg($file['tmp_name']), 'image/png' => imagecreatefrompng($file['tmp_name']), 'image/webp' => imagecreatefromwebp($file['tmp_name']), default => null };
    if (!$sourceImage) return false;
    $origW = imagesx($sourceImage); $origH = imagesy($sourceImage);
    if ($origW > THUMBNAIL_WIDTH) { $newH = (int)(($origH / $origW) * THUMBNAIL_WIDTH); $resized = imagecreatetruecolor(THUMBNAIL_WIDTH, $newH);
        if ($mimeType === 'image/png') { imagealphablending($resized, false); imagesavealpha($resized, true); }
        imagecopyresampled($resized, $sourceImage, 0, 0, 0, 0, THUMBNAIL_WIDTH, $newH, $origW, $origH); imagedestroy($sourceImage); $sourceImage = $resized; }
    $saved = match($mimeType) { 'image/jpeg' => imagejpeg($sourceImage, $destination, 85), 'image/png' => imagepng($sourceImage, $destination, 8), 'image/webp' => imagewebp($sourceImage, $destination, 85), default => false };
    imagedestroy($sourceImage);
    return $saved ? ['fajl_nev' => date('Y/m/') . $uniqueName, 'eredeti_nev' => $file['name']] : false;
}

function logMegtekintes(int $hirdetesId): void {
    $pdo = getDB(); $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown'; $sid = session_id();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM megtekintes_naplo WHERE hirdetes_id = :hid AND session_id = :sid AND datum > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $stmt->execute([':hid' => $hirdetesId, ':sid' => $sid]);
    if ($stmt->fetchColumn() == 0) { $pdo->prepare("INSERT INTO megtekintes_naplo (hirdetes_id, ip_cim, session_id) VALUES (:hid, :ip, :sid)")->execute([':hid' => $hirdetesId, ':ip' => $ip, ':sid' => $sid]); $pdo->prepare("UPDATE hirdetesek SET megtekintesek = megtekintesek + 1 WHERE id = :hid")->execute([':hid' => $hirdetesId]); }
}