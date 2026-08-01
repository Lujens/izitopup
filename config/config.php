<?php
define('DB_HOST',    'localhost');
define('DB_NAME',    'u589246572_izitopop');
define('DB_USER',    'u589246572_izitopop');
define('DB_PASS',    getenv('DB_PASS'));
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'IziToPop');
define('APP_URL',  'https://izitopop.com');
define('API_URL',  'https://izitopop.com/api');

define('TOKEN_SECRET', getenv('TOKEN_SECRET'));
define('TOKEN_EXPIRE', 60 * 60 * 24 * 30);

define('POINTS_PER_REFERRAL', 50);
define('POINTS_PER_DOLLAR',   10);
define('POINTS_TO_HTG_RATE',  0.5);

define('SMTP_HOST', 'smtp-relay.brevo.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'admin@izitopop.com');
define('SMTP_PASS', getenv('BREVO_SMTP_PASS'));
define('MAIL_FROM', 'admin@izitopop.com');
define('MAIL_NAME', 'IziToPop');

define('MONCASH_CLIENT_ID', getenv('MONCASH_CLIENT_ID'));
define('MONCASH_SECRET',    getenv('MONCASH_SECRET'));
define('MONCASH_BASE_URL',  'https://moncashbutton.digicelhaiti.com');

define('NATCASH_MERCHANT_ID', getenv('NATCASH_MERCHANT_ID'));
define('NATCASH_API_KEY',     getenv('NATCASH_API_KEY'));
define('NATCASH_BASE_URL',    'https://api.natcash.com');

define('STRIPE_PUBLIC_KEY',     getenv('STRIPE_PUBLIC_KEY'));
define('STRIPE_SECRET_KEY',     getenv('STRIPE_SECRET_KEY'));
define('STRIPE_WEBHOOK_SECRET', getenv('STRIPE_WEBHOOK_SECRET'));

define('APP_ENV',   'production');
define('APP_DEBUG', false);

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
