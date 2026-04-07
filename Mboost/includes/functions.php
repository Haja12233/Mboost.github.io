<?php
/**
 * M'Boost - Global helper functions
 *
 * Requires: config/config.php (already loaded via db.php in most pages)
 * The global $pdo variable must be set before calling DB-dependent helpers.
 */

if (!defined('DB_HOST')) {
    require_once __DIR__ . '/../config/config.php';
}

// ============================================================
// String / output helpers
// ============================================================

/**
 * Sanitize a string for safe HTML output.
 *
 * @param  string $str  Raw input value.
 * @return string       HTML-escaped string.
 */
function sanitize(string $str): string
{
    return htmlspecialchars(trim($str), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Perform an HTTP redirect and terminate execution.
 *
 * @param  string $url  Destination URL (absolute or root-relative).
 * @return void
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

// ============================================================
// Session / authentication helpers
// ============================================================

/**
 * Check whether a customer is currently logged in.
 *
 * @return bool
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check whether an admin is currently logged in.
 *
 * @return bool
 */
function isAdminLoggedIn(): bool
{
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// ============================================================
// Cart helpers
// ============================================================

/**
 * Return the total number of items (quantity sum) in the session cart.
 *
 * Cart structure expected:
 *   $_SESSION['cart'] = [
 *       'jus_id' => [
 *           'nom'       => string,
 *           'taille'    => string,
 *           'quantite'  => int,
 *           'prix'      => float,
 *       ],
 *       ...
 *   ]
 *
 * @return int
 */
function getCartCount(): int
{
    if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return 0;
    }

    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += (int) ($item['quantite'] ?? 1);
    }
    return $total;
}

// ============================================================
// Formatting helpers
// ============================================================

/**
 * Format a numeric price as a localized Ariary string.
 *
 * @param  float|int $price  Price in Ariary.
 * @return string            e.g. "1 500 Ar"
 */
/**
 * Format a numeric price as a string in Ariary and Euro.
 *
 * @param  float|int $price  Price in Ariary.
 * @param  float $eurRate    Conversion rate Ariary -> Euro (default 1 EUR = 5000 Ar)
 * @return string            e.g. "1 500 Ar (0,30 €)"
 */
function formatPrice(float|int $price, float $eurRate = 5000): string
{
    $ariary = number_format((float) $price, 0, ',', ' ') . ' Ar';
    $euro = '';
    if ($eurRate > 0) {
        $euroValue = round($price / $eurRate, 2);
        $euro = ' (' . number_format($euroValue, 2, ',', ' ') . ' €)';
    }
    return $ariary . $euro;
}

/**
 * Format an order id with leading zeros (e.g. 1 => 0001).
 *
 * @param  int $orderId
 * @return string
 */
function formatOrderNumber(int $orderId): string
{
    return str_pad((string) max(0, $orderId), 4, '0', STR_PAD_LEFT);
}

/**
 * Format a MySQL datetime / timestamp string into a human-readable French date.
 *
 * @param  string|null $date  MySQL datetime string (Y-m-d H:i:s) or null.
 * @param  bool        $withTime  Include the time portion.
 * @return string              e.g. "13 février 2026 à 14h30"
 */
function formatDate(?string $date, bool $withTime = true): string
{
    if (empty($date) || $date === '0000-00-00 00:00:00') {
        return '—';
    }

    $monthsFr = [
        1  => 'janvier',   2  => 'février',  3  => 'mars',
        4  => 'avril',     5  => 'mai',      6  => 'juin',
        7  => 'juillet',   8  => 'août',     9  => 'septembre',
        10 => 'octobre',   11 => 'novembre', 12 => 'décembre',
    ];

    try {
        $dt      = new DateTime($date);
        $day     = (int) $dt->format('j');
        $month   = $monthsFr[(int) $dt->format('n')];
        $year    = $dt->format('Y');
        $result  = "{$day} {$month} {$year}";

        if ($withTime) {
            $result .= ' à ' . $dt->format('H') . 'h' . $dt->format('i');
        }

        return $result;
    } catch (Exception $e) {
        return sanitize($date);
    }
}

// ============================================================
// Order status helpers
// ============================================================

/**
 * Return a human-readable French label for a given order status.
 *
 * @param  string $statut  Raw status value from the database.
 * @return string          Translated label.
 */
function getOrderStatusLabel(string $statut): string
{
    $labels = [
        'en_attente'     => 'En attente',
        'paye'           => 'Payé',
        'en_preparation' => 'En préparation',
        'en_livraison'   => 'En livraison',
        'livre'          => 'Livré',
        'annule'         => 'Annulé',
    ];

    return $labels[$statut] ?? ucfirst(str_replace('_', ' ', $statut));
}

/**
 * Return a Tailwind CSS color utility class pair (bg + text) for a given order status.
 *
 * @param  string $statut  Raw status value from the database.
 * @return string          Tailwind class string, e.g. "bg-yellow-100 text-yellow-800".
 */
function getOrderStatusColor(string $statut): string
{
    $colors = [
        'en_attente'     => 'bg-yellow-100 text-yellow-800',
        'paye'           => 'bg-blue-100 text-blue-800',
        'en_preparation' => 'bg-orange-100 text-orange-800',
        'en_livraison'   => 'bg-purple-100 text-purple-800',
        'livre'          => 'bg-green-100 text-green-800',
        'annule'         => 'bg-red-100 text-red-800',
    ];

    return $colors[$statut] ?? 'bg-gray-100 text-gray-800';
}

// ============================================================
// File upload helper
// ============================================================

/**
 * Handle a secure file upload.
 * Changes: Returns array ['success' => bool, 'error' => string, 'filename' => string]
 */
function uploadFile(array $file, string $dir): array
{
    // Basic sanity check
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        $msg = match ($file['error']) {
            UPLOAD_ERR_INI_SIZE => 'Fichier trop volumineux (serveur).',
            UPLOAD_ERR_FORM_SIZE => 'Fichier trop volumineux (formulaire).',
            UPLOAD_ERR_PARTIAL => 'Upload partiel.',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier envoyé.',
            default => 'Erreur upload inconnue (' . $file['error'] . ').',
        };
        return ['success' => false, 'error' => $msg];
    }

    // Size check
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'error' => 'Fichier trop volumineux (max 5Mo).'];
    }

    // MIME type check using finfo
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    // DEBUG: Log the detected mime type
    error_log("Upload MimeType Detected: " . $mimeType);

    if (!in_array($mimeType, ALLOWED_TYPES, true)) {
        return ['success' => false, 'error' => 'Type de fichier non autorisé: ' . $mimeType];
    }

    // Build a safe, unique filename
    $ext      = match ($mimeType) {
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png',
        'image/gif'       => 'gif',
        'image/webp'      => 'webp',
        'application/pdf' => 'pdf',
        default           => 'bin',
    };

    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $destPath = rtrim($dir, '/') . '/' . $filename;

    // Ensure target directory exists
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        return ['success' => false, 'error' => 'Erreur création dossier upload.'];
    }

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return ['success' => false, 'error' => 'Erreur lors du déplacement du fichier.'];
    }

    return ['success' => true, 'filename' => $filename, 'error' => ''];
}

// ============================================================
// CSRF helpers
// ============================================================

/**
 * Generate (or reuse) a CSRF token for the current session.
 *
 * Stores the token in $_SESSION['csrf_token'].
 *
 * @return string  The CSRF token.
 */
function generateCSRF(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Verify a submitted CSRF token against the one stored in the session.
 *
 * Uses a timing-safe comparison to prevent side-channel attacks.
 *
 * @param  string $token  The token value submitted by the form.
 * @return bool           True if the token is valid, false otherwise.
 */
function verifyCSRF(string $token): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $stored = $_SESSION['csrf_token'] ?? '';

    if (empty($stored) || empty($token)) {
        return false;
    }

    return hash_equals($stored, $token);
}

/**
 * Render a hidden CSRF input for HTML forms.
 *
 * @return string
 */
function csrfInput(): string
{
    $token = generateCSRF();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Validate CSRF token from POST and stop execution on failure.
 *
 * @return void
 */
function requireValidCSRFOrAbort(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCSRF((string) $token)) {
        http_response_code(419);
        die('Requete invalide (CSRF).');
    }
}

// ============================================================
// Application settings helper
// ============================================================

/**
 * Retrieve an application setting from the `parametres` table.
 *
 * Falls back to $default when the key is not found or DB is unavailable.
 * Requires the global $pdo PDO instance to be set.
 *
 * @param  string $name     The setting key (parametres.cle).
 * @param  string $key      Alias parameter – kept for compatibility; pass the same value as $name or leave empty.
 * @param  mixed  $default  Fallback value when the key is not found.
 * @return mixed            Setting value as string, or $default.
 */
function getParam(string $name, string $key = '', mixed $default = null): mixed
{
    global $pdo;

    // Determine the actual key to look up
    $lookup = !empty($key) ? $key : $name;

    if (!($pdo instanceof PDO)) {
        return $default;
    }

    try {
        $stmt = $pdo->prepare('SELECT `valeur` FROM `parametres` WHERE `cle` = :cle LIMIT 1');
        $stmt->execute([':cle' => $lookup]);
        $row = $stmt->fetch();

        return ($row !== false) ? $row['valeur'] : $default;
    } catch (PDOException $e) {
        // Non-fatal: return default silently
        return $default;
    }
}

/**
 * Ensure the hero advertisements table exists.
 *
 * @param PDO $pdo
 * @return void
 */
function ensureHeroAdsTable(PDO $pdo): void
{
    static $initialized = false;

    if ($initialized) {
        return;
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `hero_publicites` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `titre` VARCHAR(255) NOT NULL,
            `description` TEXT DEFAULT NULL,
            `media_type` ENUM('image','video') NOT NULL DEFAULT 'image',
            `media_url` VARCHAR(255) DEFAULT NULL,
            `lien_url` VARCHAR(255) DEFAULT NULL,
            `statut` ENUM('brouillon','publie') NOT NULL DEFAULT 'brouillon',
            `ordre_affichage` INT NOT NULL DEFAULT 0,
            `created_by` INT UNSIGNED DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_hero_publicites_statut` (`statut`),
            KEY `idx_hero_publicites_ordre` (`ordre_affichage`),
            KEY `idx_hero_publicites_created_by` (`created_by`)
        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_unicode_ci
    ");

    $initialized = true;
}

/**
 * Ensure the order status history table exists.
 *
 * @param PDO $pdo
 * @return void
 */
function ensureOrderStatusHistoryTable(PDO $pdo): void
{
    static $initialized = false;

    if ($initialized) {
        return;
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `commande_statuts_historique` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `commande_id` INT UNSIGNED NOT NULL,
            `ancien_statut` VARCHAR(50) DEFAULT NULL,
            `nouveau_statut` VARCHAR(50) NOT NULL,
            `note` VARCHAR(255) DEFAULT NULL,
            `changed_by_admin` INT UNSIGNED DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_csh_commande` (`commande_id`),
            KEY `idx_csh_nouveau_statut` (`nouveau_statut`),
            KEY `idx_csh_created_at` (`created_at`)
        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_unicode_ci
    ");

    $initialized = true;
}

/**
 * Log an order status transition in history.
 *
 * @param PDO $pdo
 * @param int $commandeId
 * @param string|null $oldStatus
 * @param string $newStatus
 * @param int|null $adminId
 * @param string|null $note
 * @return void
 */
function logOrderStatusChange(
    PDO $pdo,
    int $commandeId,
    ?string $oldStatus,
    string $newStatus,
    ?int $adminId = null,
    ?string $note = null
): void {
    if ($commandeId <= 0 || $newStatus === '') {
        return;
    }

    if ($oldStatus !== null && $oldStatus === $newStatus) {
        return;
    }

    ensureOrderStatusHistoryTable($pdo);

    $stmt = $pdo->prepare("
        INSERT INTO commande_statuts_historique
            (commande_id, ancien_statut, nouveau_statut, note, changed_by_admin)
        VALUES
            (:commande_id, :ancien_statut, :nouveau_statut, :note, :admin_id)
    ");
    $stmt->execute([
        ':commande_id' => $commandeId,
        ':ancien_statut' => $oldStatus,
        ':nouveau_statut' => $newStatus,
        ':note' => $note,
        ':admin_id' => $adminId,
    ]);
}

// ============================================================
// Juice price calculation
// ============================================================

/**
 * Calculate the total price of a custom juice.
 *
 * @param  array  $ingredients  Array of ingredient rows, each must have a 'prix' key
 *                              and optionally a 'quantite' key (default 1).
 *                              e.g. [['prix' => 300, 'quantite' => 2], ['prix' => 500]]
 * @param  string $size         Size key: 'petit', 'moyen', or 'grand'.
 * @return float                Total price in Ariary (rounded to 2 decimal places).
 */
function calculateJuicePrice(array $ingredients, string $size): float
{
    $sizes      = JUICE_SIZES;
    $multiplier = $sizes[$size]['multiplier'] ?? 1.0;

    $basePrice = 0.0;
    foreach ($ingredients as $ingredient) {
        $prix     = (float) ($ingredient['prix']     ?? 0);
        $quantite = (int)   ($ingredient['quantite'] ?? 1);
        $basePrice += $prix * $quantite;
    }

    return round($basePrice * $multiplier, 2);
}
