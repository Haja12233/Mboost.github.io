<?php
/**
 * M'Boost — Admin Paramètres — Premium Design
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

if (!isAdminLoggedIn()) {
    redirect(APP_URL . '/auth/admin.php');
}

$success = '';
$error = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'frais_livraison' => (float) ($_POST['frais_livraison'] ?? 0),
        'numero_orange' => trim($_POST['numero_orange'] ?? ''),
        'numero_mvola' => trim($_POST['numero_mvola'] ?? ''),
        'delai_livraison' => trim($_POST['delai_livraison'] ?? ''),
        'message_accueil' => trim($_POST['message_accueil'] ?? ''),
    ];

    foreach ($fields as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO parametres (cle, valeur) VALUES (:cle, :valeur) ON DUPLICATE KEY UPDATE valeur = :valeur2");
        $stmt->execute([':cle' => $key, ':valeur' => $value, ':valeur2' => $value]);
    }
    $success = 'Paramètres mis à jour avec succès!';
}

// Load current settings
$stmt = $pdo->query("SELECT cle, valeur FROM parametres");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['cle']] = $row['valeur'];
}

$pageTitle = "Paramètres";
$activePage = "parametres";
include __DIR__ . '/includes/admin-layout-top.php';
?>

<?php if ($success): ?>
<div class="flex items-start gap-3 p-4 rounded-2xl bg-green-50 border border-green-100 animate-scale-in">
    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-green-100 text-green-600 flex-shrink-0"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
    <p class="text-sm text-green-700 font-medium"><?= htmlspecialchars($success) ?></p>
</div>
<?php endif; ?>

<form method="POST" class="space-y-6">

    <!-- Delivery -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold font-display text-gray-900 mb-5 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-400 to-blue-500 flex items-center justify-center shadow-md shadow-blue-200/40"><span class="text-sm">🚚</span></div>
            Livraison
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Frais de livraison (Ar)</label>
                <input type="number" name="frais_livraison" value="<?= $settings['frais_livraison'] ?? DELIVERY_FEE ?>" min="0" step="100" class="input-modern w-full">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Délai de livraison</label>
                <input type="text" name="delai_livraison" value="<?= htmlspecialchars($settings['delai_livraison'] ?? '30-45 min') ?>" class="input-modern w-full" placeholder="30-45 min">
            </div>
        </div>
    </div>

    <!-- Mobile Money -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold font-display text-gray-900 mb-5 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 flex items-center justify-center shadow-md shadow-orange-200/40"><span class="text-sm">📱</span></div>
            Mobile Money
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">N° Orange Money</label>
                <input type="text" name="numero_orange" value="<?= htmlspecialchars($settings['numero_orange'] ?? '') ?>" class="input-modern w-full" placeholder="032 XX XXX XX">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">N° MVola</label>
                <input type="text" name="numero_mvola" value="<?= htmlspecialchars($settings['numero_mvola'] ?? '') ?>" class="input-modern w-full" placeholder="034 XX XXX XX">
            </div>
        </div>
    </div>

    <!-- General -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold font-display text-gray-900 mb-5 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center shadow-md shadow-green-200/40"><span class="text-sm">⚙️</span></div>
            Général
        </h3>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Message d'accueil</label>
            <textarea name="message_accueil" rows="3" class="input-modern w-full resize-none" placeholder="Bienvenue sur M'Boost..."><?= htmlspecialchars($settings['message_accueil'] ?? '') ?></textarea>
        </div>
    </div>

    <!-- Submit -->
    <div class="flex">
        <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-green-200/50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
            Enregistrer les paramètres
        </button>
    </div>
</form>

<!-- System Info -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <h3 class="font-bold font-display text-gray-900 mb-5 flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-gray-400 to-gray-500 flex items-center justify-center shadow-md shadow-gray-200/40"><span class="text-sm">🖥️</span></div>
        Informations système
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
            <span class="text-sm text-gray-500">PHP</span>
            <span class="text-sm font-semibold text-gray-800"><?= phpversion() ?></span>
        </div>
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
            <span class="text-sm text-gray-500">MySQL</span>
            <span class="text-sm font-semibold text-gray-800"><?= $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) ?></span>
        </div>
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
            <span class="text-sm text-gray-500">Serveur</span>
            <span class="text-sm font-semibold text-gray-800"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?></span>
        </div>
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
            <span class="text-sm text-gray-500">App Version</span>
            <span class="text-sm font-semibold text-gray-800">1.0</span>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/admin-layout-bottom.php'; ?>
