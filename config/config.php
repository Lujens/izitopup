<?php
// ═══════════════════════════════════════════
// IziTopUp — Database & App Config
// Replace DB_* values with your Hostinger credentials
// ═══════════════════════════════════════════

define('DB_HOST',     'localhost');
define('DB_NAME',     'u589246572_izitopop');       // e.g. u123456789_izitopop
define('DB_USER',     'u589246572_izitopop');       // e.g. u123456789_iziuser
define('DB_PASS',     'Bh#&:FCPc$T9');
define('DB_CHARSET',  'utf8mb4');

define('APP_NAME',    'IziTopUp');
define('APP_URL',     'https://izitopop.com');
define('API_URL',     'https://izitopop.com/api');

// JWT / Token
define('TOKEN_SECRET',  'f9189f76d79cdb59de64b5a1923aae7407d0a5eb7416cb2c5a2251682d5706a0');
define('TOKEN_EXPIRE',  60 * 60 * 24 * 30); // 30 days in seconds

// Points config
define('POINTS_PER_REFERRAL',   50);   // points earned when referred user buys
define('POINTS_PER_DOLLAR',     10);   // points earned per $1 spent
define('POINTS_TO_HTG_RATE',    0.5);  // 100 points = 50 HTG

// Email (SMTP via Brevo/Mailgun)
define('SMTP_HOST',   'smtp-relay.brevo.com');
define('SMTP_PORT',   587);
define('SMTP_USER',   'admin@izitopop.com');
define('SMTP_PASS',   'xsmtpsib-bffbaa06fbf56dee2f95a84829c85db47b9eb95f3e8dc1b49bdd66709ec79ec1-EFi9arhmI10fMvby');
define('MAIL_FROM',   'noreply@izitopop.com');
define('MAIL_NAME',   'IziTopUp');

// MonCash API (Digicel)
define('MONCASH_CLIENT_ID',     'YOUR_MONCASH_CLIENT_ID');
define('MONCASH_SECRET',        'YOUR_MONCASH_SECRET');
define('MONCASH_BASE_URL',      'https://moncashbutton.digicelhaiti.com'); // prod
// define('MONCASH_BASE_URL',   'https://sandbox.moncashbutton.digicelhaiti.com'); // sandbox

// NatCash API
define('NATCASH_MERCHANT_ID',   'YOUR_NATCASH_MERCHANT_ID');
define('NATCASH_API_KEY',       'YOUR_NATCASH_API_KEY');
define('NATCASH_BASE_URL',      'https://api.natcash.com'); // update with real endpoint

// Stripe
define('STRIPE_PUBLIC_KEY',     'pk_live_YOUR_STRIPE_PUBLIC_KEY');
define('STRIPE_SECRET_KEY',     'sk_live_YOUR_STRIPE_SECRET_KEY');
define('STRIPE_WEBHOOK_SECRET', 'whsec_YOUR_WEBHOOK_SECRET');

// Environment
define('APP_ENV',    'production'); // 'development' or 'production'
define('APP_DEBUG',  false);

// ── DB CONNECTION ──
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['success' => false, 'message' => 'Database connection failed']));
        }
    }
    return $pdo;
}
