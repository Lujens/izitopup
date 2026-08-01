<?php
require_once __DIR__ . '/../config/config.php';

function sendEmail(string $toEmail, string $toName, string $subject, string $htmlBody): bool {
    // Using PHP's mail() as fallback — replace with PHPMailer for production
    // Install PHPMailer: composer require phpmailer/phpmailer
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . MAIL_NAME . " <" . MAIL_FROM . ">\r\n";
    $headers .= "Reply-To: " . MAIL_FROM . "\r\n";
    return mail($toEmail, $subject, $htmlBody, $headers);
}

function emailWelcome(string $email, string $name): void {
    $html = "
    <div style='font-family:sans-serif;max-width:560px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;border:1px solid #E8EAF0'>
      <div style='background:#6C63FF;padding:2rem;text-align:center'>
        <h1 style='color:#fff;font-size:1.5rem;margin:0'>⚡ IziToPop</h1>
      </div>
      <div style='padding:2rem'>
        <h2 style='color:#0F172A;margin-bottom:0.5rem'>Byenvini, {$name}! 👋</h2>
        <p style='color:#6B7280;line-height:1.75'>Kont ou a kreye ak siksè sou IziToPop — platfòm top-up gaming #1 pou kominotè Haïtyen an.</p>
        <p style='color:#6B7280;line-height:1.75'>Ou ka kounye a achte diamonds Free Fire, UC PUBG, V-Bucks Fortnite ak plis encore — peye ak MonCash oswa NatCash epi resevwa kòd ou an mwens ke 60 segonn.</p>
        <div style='text-align:center;margin:1.5rem 0'>
          <a href='".APP_URL."/#shop' style='background:#6C63FF;color:#fff;padding:0.875rem 2rem;border-radius:10px;text-decoration:none;font-weight:700;display:inline-block'>🎮 Kòmanse achte →</a>
        </div>
        <p style='color:#9CA3AF;font-size:0.8rem;text-align:center'>© 2025 IziToPop · Developed by LujensP LLC</p>
      </div>
    </div>";
    sendEmail($email, $name, "Byenvini sou IziToPop! 🎮", $html);
}

function emailOrderConfirm(string $email, string $name, array $order): void {
    $html = "
    <div style='font-family:sans-serif;max-width:560px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;border:1px solid #E8EAF0'>
      <div style='background:#6C63FF;padding:2rem;text-align:center'>
        <h1 style='color:#fff;font-size:1.5rem;margin:0'>⚡ IziToPop</h1>
      </div>
      <div style='padding:2rem'>
        <h2 style='color:#0F172A'>Kòmand ou konfime ✅</h2>
        <p style='color:#6B7280'>Bonjou {$name}, nou resevwa kòmand ou a.</p>
        <div style='background:#F8F9FC;border-radius:12px;padding:1rem;margin:1rem 0'>
          <p style='margin:0.3rem 0;font-size:0.85rem;color:#374151'><strong>Nimewo kòmand:</strong> {$order['order_number']}</p>
          <p style='margin:0.3rem 0;font-size:0.85rem;color:#374151'><strong>Pwodwi:</strong> {$order['product_name']} · {$order['package_label']}</p>
          <p style='margin:0.3rem 0;font-size:0.85rem;color:#374151'><strong>Total:</strong> \${$order['price_usd']} ({$order['price_htg']} HTG)</p>
          <p style='margin:0.3rem 0;font-size:0.85rem;color:#374151'><strong>Peman:</strong> {$order['payment_method']}</p>
        </div>
        <p style='color:#6B7280;font-size:0.85rem'>Ou ap resevwa yon lòt email ak kòd ou a touswit apre peman konfime.</p>
        <p style='color:#9CA3AF;font-size:0.8rem;text-align:center;margin-top:1.5rem'>© 2025 IziToPop</p>
      </div>
    </div>";
    sendEmail($email, $name, "Kòmand #{$order['order_number']} konfime — IziToPop", $html);
}

function emailCodeDelivery(string $email, string $name, array $order, string $code): void {
    $html = "
    <div style='font-family:sans-serif;max-width:560px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;border:1px solid #E8EAF0'>
      <div style='background:#6C63FF;padding:2rem;text-align:center'>
        <h1 style='color:#fff;font-size:1.5rem;margin:0'>⚡ IziToPop</h1>
      </div>
      <div style='padding:2rem'>
        <h2 style='color:#0F172A'>Kòd ou a prè! ⚡</h2>
        <p style='color:#6B7280'>Bonjou {$name}, men kòd {$order['product_name']} ou a:</p>
        <div style='background:#F5F4FF;border:2px solid #6C63FF;border-radius:12px;padding:1.5rem;text-align:center;margin:1.25rem 0'>
          <p style='font-size:0.75rem;color:#6B7280;text-transform:uppercase;letter-spacing:1px;margin-bottom:0.5rem'>Kòd ou an</p>
          <p style='font-family:monospace;font-size:1.6rem;font-weight:700;color:#6C63FF;letter-spacing:3px;margin:0'>{$code}</p>
        </div>
        <div style='background:#F8F9FC;border-radius:10px;padding:1rem;margin-bottom:1rem'>
          <p style='margin:0.25rem 0;font-size:0.82rem;color:#374151'><strong>Kòmand:</strong> {$order['order_number']}</p>
          <p style='margin:0.25rem 0;font-size:0.82rem;color:#374151'><strong>Pwodwi:</strong> {$order['product_name']} · {$order['package_label']}</p>
          <p style='margin:0.25rem 0;font-size:0.82rem;color:#374151'><strong>ID jwèt:</strong> {$order['game_uid']}</p>
        </div>
        <p style='color:#6B7280;font-size:0.82rem'>Si ou gen yon pwoblèm ak kòd la, <a href='".APP_URL."/pages/contact.html' style='color:#6C63FF'>kontakte sipò nou an</a> ak nimewo kòmand ou a.</p>
        <p style='color:#9CA3AF;font-size:0.8rem;text-align:center;margin-top:1.5rem'>© 2025 IziToPop · Developed by LujensP LLC</p>
      </div>
    </div>";
    sendEmail($email, $name, "⚡ Kòd {$order['product_name']} ou a live — IziToPop", $html);
}

function emailPasswordReset(string $email, string $name, string $resetLink): void {
    $html = "
    <div style='font-family:sans-serif;max-width:560px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;border:1px solid #E8EAF0'>
      <div style='background:#6C63FF;padding:2rem;text-align:center'>
        <h1 style='color:#fff;font-size:1.5rem;margin:0'>⚡ IziToPop</h1>
      </div>
      <div style='padding:2rem'>
        <h2 style='color:#0F172A'>Reset modpas ou 🔒</h2>
        <p style='color:#6B7280'>Bonjou {$name}, ou te mande pou reset modpas ou. Klike bouton an pou kreye yon nouvo modpas.</p>
        <div style='text-align:center;margin:1.5rem 0'>
          <a href='{$resetLink}' style='background:#6C63FF;color:#fff;padding:0.875rem 2rem;border-radius:10px;text-decoration:none;font-weight:700;display:inline-block'>Reset Modpas Mwen →</a>
        </div>
        <p style='color:#9CA3AF;font-size:0.8rem'>Lyen sa a ekspire nan 30 minit. Si ou pa te mande reset sa a, ignore email sa.</p>
        <p style='color:#9CA3AF;font-size:0.8rem;text-align:center;margin-top:1rem'>© 2025 IziToPop</p>
      </div>
    </div>";
    sendEmail($email, $name, "Reset modpas ou — IziToPop", $html);
}
