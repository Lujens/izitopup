<?php
require_once __DIR__ . '/../config/config.php';

function sendEmail(string $toEmail, string $toName, string $subject, string $htmlBody): bool {
    $host = 'smtp-relay.brevo.com';
    $port = 587;
    $user = SMTP_USER;
    $pass = SMTP_PASS;
    $from = MAIL_FROM;
    $fromName = MAIL_NAME;

    $socket = fsockopen($host, $port, $errno, $errstr, 15);
    if(!$socket) return false;

    $read = function() use($socket){ return fgets($socket, 512); };
    $send = function($cmd) use($socket){ fwrite($socket, $cmd."\r\n"); };

    $read();
    $send('EHLO izitopop.com');
    while(true){ $line=$read(); if(substr($line,3,1)==' ') break; }

    $send('STARTTLS');
    $read();
    stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

    $send('EHLO izitopop.com');
    while(true){ $line=$read(); if(substr($line,3,1)==' ') break; }

    $send('AUTH LOGIN');
    $read();
    $send(base64_encode($user));
    $read();
    $send(base64_encode($pass));
    $auth = $read();
    if(strpos($auth,'235')===false){ fclose($socket); return false; }

    $send('MAIL FROM:<'.$from.'>');
    $read();
    $send('RCPT TO:<'.$toEmail.'>');
    $read();
    $send('DATA');
    $read();

    $msg  = "From: =?UTF-8?B?".base64_encode($fromName)."?= <{$from}>\r\n";
    $msg .= "To: =?UTF-8?B?".base64_encode($toName)."?= <{$toEmail}>\r\n";
    $msg .= "Subject: =?UTF-8?B?".base64_encode($subject)."?=\r\n";
    $msg .= "MIME-Version: 1.0\r\n";
    $msg .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
    $msg .= $htmlBody."\r\n.";

    $send($msg);
    $sent = $read();
    $send('QUIT');
    fclose($socket);
    return strpos($sent,'250')!==false;
}

function emailTemplate(string $body): string {
    return "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body style='margin:0;padding:0;background:#F8F9FC;font-family:sans-serif'>
    <div style='max-width:560px;margin:0 auto;padding:2rem 1rem'>
      <div style='background:#6C63FF;border-radius:16px 16px 0 0;padding:1.5rem 2rem;text-align:center'>
        <h1 style='color:#fff;margin:0;font-size:1.4rem;font-weight:800'>⚡ IziToPop</h1>
        <p style='color:rgba(255,255,255,0.8);margin:0.25rem 0 0;font-size:0.8rem'>Top-up jwèt ou an 60 segonn</p>
      </div>
      <div style='background:#fff;border-radius:0 0 16px 16px;padding:2rem;border:1px solid #E8EAF0;border-top:none'>
        {$body}
        <hr style='border:none;border-top:1px solid #E8EAF0;margin:1.5rem 0'>
        <p style='color:#9CA3AF;font-size:0.72rem;text-align:center;margin:0'>
          © 2025 IziToPop · Developed by LujensP LLC<br>
          <a href='https://izitopop.com' style='color:#6C63FF'>izitopop.com</a>
        </p>
      </div>
    </div></body></html>";
}

function emailWelcome(string $email, string $name): void {
    $body = "
      <h2 style='color:#0F172A;margin:0 0 0.5rem'>Byenvini, {$name}! 👋</h2>
      <p style='color:#6B7280;line-height:1.75;margin:0 0 1rem'>Kont ou a kreye ak siksè sou IziToPop.</p>
      <p style='color:#6B7280;line-height:1.75;margin:0 0 1.5rem'>Achte diamonds Free Fire, UC PUBG, V-Bucks Fortnite — peye ak MonCash oswa NatCash epi resevwa kòd ou an mwens ke 60 segonn.</p>
      <div style='text-align:center'>
        <a href='https://izitopop.com/#shop' style='background:#6C63FF;color:#fff;padding:0.875rem 2rem;border-radius:10px;text-decoration:none;font-weight:700;display:inline-block'>🎮 Kòmanse achte →</a>
      </div>";
    sendEmail($email, $name, "Byenvini sou IziToPop! 🎮", emailTemplate($body));
}

function emailOrderConfirm(string $email, string $name, array $order): void {
    $body = "
      <h2 style='color:#0F172A;margin:0 0 0.5rem'>Kòmand ou konfime ✅</h2>
      <p style='color:#6B7280;margin:0 0 1rem'>Bonjou {$name}, nou resevwa kòmand ou a.</p>
      <div style='background:#F8F9FC;border-radius:12px;padding:1.25rem;margin:0 0 1rem'>
        <p style='margin:0.3rem 0;font-size:0.85rem;color:#374151'><strong>Nimewo:</strong> {$order['order_number']}</p>
        <p style='margin:0.3rem 0;font-size:0.85rem;color:#374151'><strong>Pwodwi:</strong> {$order['product_name']} · {$order['package_label']}</p>
        <p style='margin:0.3rem 0;font-size:0.85rem;color:#374151'><strong>Total:</strong> \${$order['price_usd']} ({$order['price_htg']} HTG)</p>
        <p style='margin:0.3rem 0;font-size:0.85rem;color:#374151'><strong>Peman:</strong> ".ucfirst($order['payment_method'])."</p>
      </div>
      <p style='color:#6B7280;font-size:0.85rem;margin:0'>Ou ap resevwa yon lòt email ak kòd ou a touswit apre peman konfime.</p>";
    sendEmail($email, $name, "Kòmand #{$order['order_number']} konfime — IziToPop", emailTemplate($body));
}

function emailCodeDelivery(string $email, string $name, array $order, string $code): void {
    $body = "
      <h2 style='color:#0F172A;margin:0 0 0.5rem'>Kòd ou a prè! ⚡</h2>
      <p style='color:#6B7280;margin:0 0 1rem'>Bonjou {$name}, men kòd {$order['product_name']} ou a:</p>
      <div style='background:#F5F4FF;border:2px solid #6C63FF;border-radius:12px;padding:1.5rem;text-align:center;margin:0 0 1rem'>
        <p style='font-size:0.72rem;color:#6B7280;text-transform:uppercase;letter-spacing:1px;margin:0 0 0.5rem'>Kòd ou an</p>
        <p style='font-family:monospace;font-size:1.6rem;font-weight:700;color:#6C63FF;letter-spacing:3px;margin:0 0 0.75rem'>{$code}</p>
        <p style='font-size:0.75rem;color:#6B7280;margin:0'>Kopye kòd sa a epi antre l nan jwèt ou</p>
      </div>
      <div style='background:#F8F9FC;border-radius:10px;padding:1rem;margin:0 0 1rem'>
        <p style='margin:0.25rem 0;font-size:0.82rem;color:#374151'><strong>Kòmand:</strong> {$order['order_number']}</p>
        <p style='margin:0.25rem 0;font-size:0.82rem;color:#374151'><strong>Pwodwi:</strong> {$order['product_name']} · {$order['package_label']}</p>
        <p style='margin:0.25rem 0;font-size:0.82rem;color:#374151'><strong>ID jwèt:</strong> {$order['game_uid']}</p>
      </div>
      <p style='color:#6B7280;font-size:0.82rem;margin:0'>Pwoblèm ak kòd la? <a href='https://izitopop.com/pages/contact.html' style='color:#6C63FF'>Kontakte sipò nou an</a>.</p>";
    sendEmail($email, $name, "⚡ Kòd {$order['product_name']} ou a — IziToPop", emailTemplate($body));
}

function emailPasswordReset(string $email, string $name, string $resetLink): void {
    $body = "
      <h2 style='color:#0F172A;margin:0 0 0.5rem'>Reset modpas ou 🔒</h2>
      <p style='color:#6B7280;margin:0 0 1.5rem'>Bonjou {$name}, klike bouton an pou kreye yon nouvo modpas.</p>
      <div style='text-align:center;margin:0 0 1.5rem'>
        <a href='{$resetLink}' style='background:#6C63FF;color:#fff;padding:0.875rem 2rem;border-radius:10px;text-decoration:none;font-weight:700;display:inline-block'>Reset Modpas Mwen →</a>
      </div>
      <p style='color:#9CA3AF;font-size:0.8rem;margin:0'>Lyen sa a ekspire nan 30 minit.</p>";
    sendEmail($email, $name, "Reset modpas ou — IziToPop", emailTemplate($body));
}
