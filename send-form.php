<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

// Honeypot: bots fill this in, humans never see it
if (!empty($_POST['website'])) {
    echo json_encode(['ok' => true]);
    exit;
}

function clean($v) {
    return trim(str_replace(["\r", "\n"], ' ', strip_tags($v ?? '')));
}

$persona  = clean($_POST['persona'] ?? '');
$name     = clean($_POST['name'] ?? '');
$email    = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$org      = clean($_POST['org'] ?? '');
$interest = clean($_POST['interest'] ?? '');
$message  = trim(strip_tags($_POST['message'] ?? ''));

if (!$name || !$email || !$org || !$message) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing fields']);
    exit;
}

// Recipients (comma separated)
$to = 'hulisani@curitylogic.co.za, info@curitylogic.co.za';

$subject = "Website enquiry from $name ($org)";
$body  = "Type: $persona\n";
$body .= "Name: $name\n";
$body .= "Email: $email\n";
$body .= "Organisation: $org\n";
$body .= "Interest: $interest\n\n";
$body .= "Message:\n$message\n";

$headers  = "From: CurityLogic Website <noreply@curitylogic.co.za>\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$sent = mail($to, $subject, $body, $headers, '-fnoreply@curitylogic.co.za');

echo json_encode(['ok' => (bool)$sent]);
