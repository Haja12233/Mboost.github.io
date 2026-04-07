<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_secure', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? '1' : '0');
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mboost_db');
define('APP_NAME', "M'Boost");
define('APP_URL', 'http://localhost/Mboost');
define('ADMIN_EMAIL', 'admin@mboost.mg');
define('SESSION_LIFETIME', 7200);
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');
define('UPLOAD_URL', APP_URL . '/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf']);
define('DELIVERY_FEE_DEFAULT', 1000);
define('JUICE_SIZES', [
    'petit'  => ['label' => 'Petit',  'ml' => 250, 'multiplier' => 0.8],
    'moyen'  => ['label' => 'Moyen',  'ml' => 400, 'multiplier' => 1.0],
    'grand'  => ['label' => 'Grand',  'ml' => 600, 'multiplier' => 1.3],
]);

define('HEALTH_CATEGORIES', [
    'foie'               => ['label' => 'Santé du Foie',       'emoji' => '🫁'],
    'reins'              => ['label' => 'Santé des Reins',     'emoji' => '💧'],
    'coeur'              => ['label' => 'Santé du Cœur',       'emoji' => '❤️'],
    'digestion'          => ['label' => 'Système Digestif',    'emoji' => '🍽️'],
    'immunite'           => ['label' => 'Immunité & Défenses', 'emoji' => '🛡️'],
    'peau'               => ['label' => 'Peau & Beauté',       'emoji' => '✨'],
    'energie'            => ['label' => 'Énergie & Vitalité',  'emoji' => '⚡'],
    'detox'              => ['label' => 'Détoxification',      'emoji' => '🌿'],
    'anti_inflammatoire' => ['label' => 'Anti-inflammatoire',  'emoji' => '🧊'],
    'perte_poids'        => ['label' => 'Perte de poids',      'emoji' => '🏃'],
]);

define('HEALTH_ORGANS', [
    'foie'    => 'Foie',
    'reins'   => 'Reins',
    'coeur'   => 'Cœur',
    'estomac' => 'Estomac',
    'peau'    => 'Peau',
    'cerveau' => 'Cerveau',
    'os'      => 'Os',
    'yeux'    => 'Yeux',
]);
