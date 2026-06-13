<?php
/**
 * includes/email.php - Email küldő függvények
 */

/**
 * Egyszerű email küldés PHP mail() függvénnyel
 */
function sendEmail(string $to, string $subject, string $body, string $fromEmail = null, string $fromName = null): bool {
    $fromEmail = $fromEmail ?? 'noreply@orszagkozepe.hu';
    $fromName = $fromName ?? 'Ország Közepe';
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        "From: {$fromName} <{$fromEmail}>",
        'Reply-To: ' . $fromEmail,
        'X-Mailer: PHP/' . phpversion()
    ];
    
    $success = mail($to, $subject, $body, implode("\r\n", $headers));
    
    if (!$success) {
        error_log("Email küldés sikertelen: {$to} - {$subject}");
    }
    
    return $success;
}

/**
 * HTML formátumú email küldése
 */
function sendHTMLEmail(string $to, string $subject, string $htmlBody, string $fromEmail = null, string $fromName = null): bool {
    $fromEmail = $fromEmail ?? 'noreply@orszagkozepe.hu';
    $fromName = $fromName ?? 'Ország Közepe';
    
    $boundary = md5(time());
    
    $headers = [
        'MIME-Version: 1.0',
        "Content-Type: multipart/alternative; boundary=\"{$boundary}\"",
        "From: {$fromName} <{$fromEmail}>",
        'Reply-To: ' . $fromEmail
    ];
    
    $body = "--{$boundary}\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $htmlBody)) . "\r\n\r\n";
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= $htmlBody . "\r\n\r\n";
    $body .= "--{$boundary}--";
    
    return mail($to, $subject, $body, implode("\r\n", $headers));
}

/**
 * Regisztrációs megerősítő email
 */
function sendRegistrationEmail(string $to, string $nev, string $token): void {
    $verifyLink = BASE_URL . "/auth/email_megerosites.php?token={$token}";
    $subject = "Email megerősítés - Ország Közepe";
    
    $html = "<h2>Üdvözlünk az Ország Közepén, {$nev}!</h2>";
    $html .= "<p>Köszönjük a regisztrációt! Kérjük, erősítsd meg az email címedet az alábbi linkre kattintva:</p>";
    $html .= "<p><a href=\"{$verifyLink}\" style=\"background:#2c6e49;color:white;padding:12px 24px;text-decoration:none;border-radius:6px;display:inline-block;\">Email megerősítése</a></p>";
    $html .= "<p>Vagy másold be a böngésződbe: {$verifyLink}</p>";
    $html .= "<p>A link 24 órán belül érvényes.</p>";
    $html .= "<p>Üdvözlettel,<br>Az Ország Közepe csapata</p>";
    
    sendHTMLEmail($to, $subject, $html);
}

/**
 * Üzenet értesítő email
 */
function sendUzenetNotification(string $to, string $hirdetoNev, string $kuldoNev, string $hirdetesCim, string $uzenet, string $hirdetesUrl): void {
    $subject = "Új üzenet a hirdetésedre: {$hirdetesCim}";
    
    $html = "<h2>Kedves {$hirdetoNev}!</h2>";
    $html .= "<p><strong>{$kuldoNev}</strong> üzenetet küldött a(z) <strong>{$hirdetesCim}</strong> hirdetésedre:</p>";
    $html .= "<blockquote style=\"background:#f5f5f5;padding:12px;border-left:4px solid #2c6e49;margin:16px 0;\">{$uzenet}</blockquote>";
    $html .= "<p><a href=\"{$hirdetesUrl}\" style=\"background:#2c6e49;color:white;padding:12px 24px;text-decoration:none;border-radius:6px;\">Válasz megtekintése</a></p>";
    $html .= "<p>Üdvözlettel,<br>Az Ország Közepe csapata</p>";
    
    sendHTMLEmail($to, $subject, $html);
}

/**
 * Hirdetés jóváhagyás email
 */
function sendApprovalEmail(string $to, string $nev, string $hirdetesCim, string $hirdetesUrl): void {
    $subject = "Hirdetésed jóváhagyva - {$hirdetesCim}";
    
    $html = "<h2>Kedves {$nev}!</h2>";
    $html .= "<p>Örömmel értesítünk, hogy a(z) <strong>{$hirdetesCim}</strong> hirdetésed jóváhagyásra került és már él az oldalon!</p>";
    $html .= "<p><a href=\"{$hirdetesUrl}\" style=\"background:#2c6e49;color:white;padding:12px 24px;text-decoration:none;border-radius:6px;\">Hirdetés megtekintése</a></p>";
    $html .= "<p>Üdvözlettel,<br>Az Ország Közepe csapata</p>";
    
    sendHTMLEmail($to, $subject, $html);
}
