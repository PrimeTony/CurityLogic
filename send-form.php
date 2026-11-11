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
function h($v) {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
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

// ---- Settings -------------------------------------------------------
$to       = '****@curitylogic.co.za, ****@curitylogic.co.za';
$siteUrl  = 'https://curitylogic.co.za';
$logoUrl  = $siteUrl . '/assets/Logo%20no%20background.png';
// ---------------------------------------------------------------------

// Embed the logo inside the email itself (avoids Outlook's "blocked content" for remote images)
$logoPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/assets/Logo no background.png';
$logoData = is_readable($logoPath) ? file_get_contents($logoPath) : false;
$logoSrc  = $logoData ? 'cid:curitylogo' : $logoUrl;

$personaLabel = ($persona === 'partner') ? 'Channel Partner / VAR' : 'Enterprise / End-User';

date_default_timezone_set('Africa/Johannesburg');
$when = date('j F Y, H:i');

$subject = "New website enquiry: $name ($org)";
$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

// ---- Plain text version (fallback) ----------------------------------
$text  = "NEW WEBSITE ENQUIRY\n";
$text .= "Received: $when\n\n";
$text .= "Type: $personaLabel\n";
$text .= "Name: $name\n";
$text .= "Email: $email\n";
$text .= "Organisation: $org\n";
$text .= "Interest: $interest\n\n";
$text .= "Message:\n$message\n";

// ---- HTML version ----------------------------------------------------
$nName    = h($name);
$nEmail   = h($email);
$nOrg     = h($org);
$nInt     = h($interest);
$nPersona = h($personaLabel);
$nMsg     = nl2br(h($message));
$nWhen    = h($when);
$replySubject = rawurlencode("Re: Your enquiry to CurityLogic");

$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>New website enquiry</title>
</head>
<body style="margin:0;padding:0;background:#eef2f7;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#eef2f7;padding:24px 12px;">
  <tr>
    <td align="center">
      <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #dbe3ec;">

        <!-- Header -->
        <tr>
          <td bgcolor="#07101f" style="background:#07101f;padding:22px 28px;border-bottom:4px solid #06b6d4;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td style="vertical-align:middle;padding-right:12px;">
                  <img src="{$logoSrc}" alt="" width="40" height="40" style="display:block;border:0;">
                </td>
                <td style="vertical-align:middle;font-size:20px;font-weight:bold;letter-spacing:2px;color:#ffffff;">
                  CURITY<span style="color:#06b6d4;">LOGIC</span>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Title -->
        <tr>
          <td style="padding:28px 28px 8px 28px;">
            <div style="font-size:12px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;color:#0e7490;">New website enquiry</div>
            <div style="font-size:22px;font-weight:bold;color:#0f172a;margin-top:6px;">{$nName} from {$nOrg}</div>
            <div style="font-size:12px;color:#64748b;margin-top:6px;">Received {$nWhen}</div>
          </td>
        </tr>

        <!-- Details -->
        <tr>
          <td style="padding:16px 28px 8px 28px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e2e8f0;border-radius:10px;">
              <tr>
                <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;width:140px;font-size:12px;font-weight:bold;color:#64748b;text-transform:uppercase;letter-spacing:1px;">Type</td>
                <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#0f172a;">{$nPersona}</td>
              </tr>
              <tr>
                <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:12px;font-weight:bold;color:#64748b;text-transform:uppercase;letter-spacing:1px;">Name</td>
                <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#0f172a;">{$nName}</td>
              </tr>
              <tr>
                <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:12px;font-weight:bold;color:#64748b;text-transform:uppercase;letter-spacing:1px;">Email</td>
                <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:14px;"><a href="mailto:{$nEmail}" style="color:#0e7490;text-decoration:none;">{$nEmail}</a></td>
              </tr>
              <tr>
                <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:12px;font-weight:bold;color:#64748b;text-transform:uppercase;letter-spacing:1px;">Organisation</td>
                <td style="padding:12px 16px;border-bottom:1px solid #e2e8f0;font-size:14px;color:#0f172a;">{$nOrg}</td>
              </tr>
              <tr>
                <td style="padding:12px 16px;font-size:12px;font-weight:bold;color:#64748b;text-transform:uppercase;letter-spacing:1px;">Interest</td>
                <td style="padding:12px 16px;font-size:14px;color:#0f172a;">{$nInt}</td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Message -->
        <tr>
          <td style="padding:16px 28px 8px 28px;">
            <div style="font-size:12px;font-weight:bold;color:#64748b;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Message</div>
            <div style="background:#f1f5f9;border-left:4px solid #06b6d4;border-radius:6px;padding:16px;font-size:14px;line-height:1.6;color:#1e293b;">{$nMsg}</div>
          </td>
        </tr>

        <!-- Reply button -->
        <tr>
          <td align="left" style="padding:24px 28px 32px 28px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td bgcolor="#06b6d4" style="background:#06b6d4;border-radius:8px;">
                  <a href="mailto:{$nEmail}?subject={$replySubject}" style="display:inline-block;padding:12px 24px;font-size:14px;font-weight:bold;color:#07101f;text-decoration:none;">Reply to {$nName}</a>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td bgcolor="#f8fafc" style="background:#f8fafc;padding:16px 28px;border-top:1px solid #e2e8f0;font-size:11px;color:#94a3b8;text-align:center;">
            Sent automatically from the contact form at <a href="{$siteUrl}" style="color:#64748b;">curitylogic.co.za</a>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>
</body>
</html>
HTML;

// ---- Build multipart email -------------------------------------------
$uid   = md5(uniqid((string)mt_rand(), true));
$bAlt  = "alt_$uid";
$bRel  = "rel_$uid";

$headers  = "From: CurityLogic Website <****@curitylogic.co.za>\r\n";
$headers .= "Reply-To: $name <$email>\r\n";
$headers .= "MIME-Version: 1.0\r\n";

$alt  = "--$bAlt\r\n";
$alt .= "Content-Type: text/plain; charset=UTF-8\r\n";
$alt .= "Content-Transfer-Encoding: base64\r\n\r\n";
$alt .= chunk_split(base64_encode($text)) . "\r\n";
$alt .= "--$bAlt\r\n";
$alt .= "Content-Type: text/html; charset=UTF-8\r\n";
$alt .= "Content-Transfer-Encoding: base64\r\n\r\n";
$alt .= chunk_split(base64_encode($html)) . "\r\n";
$alt .= "--$bAlt--\r\n";

if ($logoData) {
    $headers .= "Content-Type: multipart/related; boundary=\"$bRel\"\r\n";
    $body  = "--$bRel\r\n";
    $body .= "Content-Type: multipart/alternative; boundary=\"$bAlt\"\r\n\r\n";
    $body .= $alt;
    $body .= "--$bRel\r\n";
    $body .= "Content-Type: image/png; name=\"logo.png\"\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n";
    $body .= "Content-ID: <curitylogo>\r\n";
    $body .= "Content-Disposition: inline; filename=\"logo.png\"\r\n\r\n";
    $body .= chunk_split(base64_encode($logoData)) . "\r\n";
    $body .= "--$bRel--";
} else {
    $headers .= "Content-Type: multipart/alternative; boundary=\"$bAlt\"\r\n";
    $body = $alt;
}

$sent = mail($to, $encodedSubject, $body, $headers, '-f****@curitylogic.co.za');

echo json_encode(['ok' => (bool)$sent]);
