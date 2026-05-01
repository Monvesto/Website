<?php
/**
 * Monvesto – Kontaktformular Handler
 * Später einfach auf Resend umstellen
 */

// Nur POST-Requests verarbeiten
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /kontakt/');
    exit;
}

// ── EINGABEN VALIDIEREN ──────────────────────────
$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$betreff = trim($_POST['betreff'] ?? '');
$typ     = trim($_POST['typ']     ?? '');
$nachricht = trim($_POST['nachricht'] ?? '');

$errors = [];

if (strlen($name) < 2) $errors[] = 'Bitte gib deinen Namen ein.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Bitte gib eine gültige E-Mail-Adresse ein.';
if (strlen($nachricht) < 10) $errors[] = 'Bitte schreib uns etwas mehr – mindestens 10 Zeichen.';
if (!in_array($typ, ['allgemein', 'support', 'presse'])) $errors[] = 'Bitte wähle einen Anfragetyp.';

// Honeypot gegen Spam
if (!empty($_POST['website'])) {
    header('Location: /kontakt/?status=success');
    exit;
}

if (!empty($errors)) {
    $error_string = urlencode(implode('|', $errors));
    header("Location: /kontakt/?status=error&msg={$error_string}");
    exit;
}

// ── E-MAIL SENDEN (PHP Mail) ─────────────────────
// TODO: Später durch Resend ersetzen
$to      = 'info@monvesto.de'; // ← Deine E-Mail hier
$subject = '[Monvesto Kontakt] ' . ucfirst($typ) . ': ' . ($betreff ?: 'Neue Anfrage');

$typ_label = match($typ) {
    'support'   => 'Support & Hilfe',
    'presse'    => 'Presse & Kooperationen',
    default     => 'Allgemeine Anfrage',
};

$body = "Neue Kontaktanfrage über monvesto.de\n";
$body .= "=====================================\n\n";
$body .= "Typ:       {$typ_label}\n";
$body .= "Name:      {$name}\n";
$body .= "E-Mail:    {$email}\n";
$body .= "Betreff:   {$betreff}\n\n";
$body .= "Nachricht:\n{$nachricht}\n\n";
$body .= "=====================================\n";
$body .= "Gesendet:  " . date('d.m.Y H:i') . "\n";
$body .= "IP:        " . $_SERVER['REMOTE_ADDR'] . "\n";

$headers  = "From: noreply@monvesto.de\r\n";
$headers .= "Reply-To: {$email}\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

$sent = mail($to, $subject, $body, $headers);

if ($sent) {
    header('Location: /kontakt/?status=success');
} else {
    header('Location: /kontakt/?status=error&msg=' . urlencode('E-Mail konnte nicht gesendet werden. Bitte versuche es später erneut.'));
}
exit;