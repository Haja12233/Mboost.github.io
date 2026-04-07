<?php
/**
 * M'Boost - Admin Payment Validation
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

// Check admin authentication
if (!isAdminLoggedIn()) {
    redirect(APP_URL . '/auth/admin.php');
}

$paymentId = (int) ($_GET['id'] ?? 0);
if (!$paymentId) {
    redirect(APP_URL . '/admin/paiements.php');
}

// Get payment
$stmt = $pdo->prepare("
    SELECT p.*, c.id as commande_id, c.total, c.statut as commande_statut,
           u.nom, u.prenom, u.email, u.telephone
    FROM paiements p
    JOIN commandes c ON p.commande_id = c.id
    JOIN users u ON c.user_id = u.id
    WHERE p.id = :id
    LIMIT 1
");
$stmt->execute([':id' => $paymentId]);
$payment = $stmt->fetch();

if (!$payment) {
    redirect(APP_URL . '/admin/paiements.php');
}

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if (in_array($action, ['valider', 'refuser'])) {
        $newStatus = ($action === 'valider') ? 'valide' : 'refuse';

        $stmt = $pdo->prepare("
            UPDATE paiements
            SET statut = :statut, notes_admin = :notes, validated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            ':statut' => $newStatus,
            ':notes' => $notes,
            ':id' => $paymentId
        ]);

        // Update order status
        if ($action === 'valider') {
            if (($payment['commande_statut'] ?? '') !== 'paye') {
                $stmt = $pdo->prepare("UPDATE commandes SET statut = 'paye' WHERE id = :id");
                $stmt->execute([':id' => $payment['commande_id']]);
                logOrderStatusChange(
                    $pdo,
                    (int) $payment['commande_id'],
                    (string) ($payment['commande_statut'] ?? ''),
                    'paye',
                    (int) ($_SESSION['admin_id'] ?? 0),
                    'Validation paiement admin detail'
                );
            }
        }

        $success = 'Paiement ' . ($action === 'valider' ? 'validé' : 'refusé') . ' avec succès!';

        // Refresh payment data
        $stmt->execute([':id' => $paymentId]);
        $payment = $stmt->fetch();
    }
}

$pageTitle = "Validation Paiement #" . $paymentId;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> Admin - <?= $pageTitle ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- CSS Fallback -->
    <style>*{box-sizing:border-box}html{font-family:Inter,sans-serif;line-height:1.5}body{margin:0;background:#f3f4f6;color:#1f2937}.bg-white{background:#fff}.bg-gray-100{background:#f3f4f6}.bg-gray-900{background:#111827}.bg-green-50{background:#f0fdf4}.bg-green-100{background:#dcfce7}.bg-green-600{background:#16a34a}.bg-red-100{background:#fee2e2}.bg-yellow-50{background:#fefce8}.bg-blue-50{background:#eff6ff}.bg-purple-50{background:#faf5ff}.text-white{color:#fff}.text-gray-500{color:#6b7280}.text-gray-600{color:#4b5563}.text-gray-700{color:#374151}.text-gray-800{color:#1f2937}.text-green-600{color:#16a34a}.text-green-700{color:#15803d}.text-red-600{color:#dc2626}.text-yellow-800{color:#92400e}.text-blue-600{color:#2563eb}.text-orange-600{color:#ea580c}.rounded-lg{border-radius:.5rem}.rounded-xl{border-radius:.75rem}.rounded-full{border-radius:9999px}.shadow-sm{box-shadow:0 1px 2px rgba(0,0,0,.05)}.shadow{box-shadow:0 1px 3px rgba(0,0,0,.1)}.shadow-lg{box-shadow:0 10px 15px -3px rgba(0,0,0,.1)}.p-2{padding:.5rem}.p-3{padding:.75rem}.p-4{padding:1rem}.p-6{padding:1.5rem}.px-4{padding-left:1rem;padding-right:1rem}.px-6{padding-left:1.5rem;padding-right:1.5rem}.py-2{padding-top:.5rem;padding-bottom:.5rem}.py-3{padding-top:.75rem;padding-bottom:.75rem}.mb-2{margin-bottom:.5rem}.mb-4{margin-bottom:1rem}.mb-6{margin-bottom:1.5rem}.mt-4{margin-top:1rem}.gap-2{gap:.5rem}.gap-3{gap:.75rem}.gap-4{gap:1rem}.items-center{align-items:center}.justify-center{justify-content:center}.justify-between{justify-content:space-between}.w-full{width:100%}.w-64{width:16rem}.max-w-7xl{max-width:80rem}.flex{display:flex}.grid{display:grid}.hidden{display:none}.border{border:1px solid #e5e7eb}.border-t{border-top:1px solid #e5e7eb}.font-medium{font-weight:500}.font-semibold{font-weight:600}.font-bold{font-weight:700}.text-sm{font-size:.875rem}.text-xs{font-size:.75rem}.text-lg{font-size:1.125rem}.text-xl{font-size:1.25rem}.text-2xl{font-size:1.5rem}.min-h-screen{min-height:100vh}.overflow-x-auto{overflow-x:auto}.fixed{position:fixed}.sticky{position:sticky}.top-0{top:0}.inset-y-0{top:0;bottom:0}.left-0{left:0}.z-30{z-index:30}.z-50{z-index:50}.space-y-4>*+*{margin-top:1rem}.space-y-6>*+*{margin-top:1.5rem}.hover\:bg-gray-50:hover{background:#f9fafb}.hover\:bg-gray-800:hover{background:#1f2937}.hover\:text-green-700:hover{color:#15803d}.focus\:border-green-500:focus{border-color:#22c55e}.focus\:outline-none:focus{outline:2px solid transparent;outline-offset:2px}.lg\:ml-64{margin-left:16rem}.lg\:block{display:block}.lg\:hidden{display:none}</style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif;}</style>
</head>
<body class="bg-gray-100">

    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 w-64 bg-gray-900 text-white hidden lg:block">
        <div class="flex items-center gap-3 p-6 border-b border-gray-800">
            <span class="text-2xl">🥤</span>
            <span class="font-bold text-lg"><?= APP_NAME ?> Admin</span>
        </div>
        <nav class="p-4 space-y-1">
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 rounded-lg">Dashboard</a>
            <a href="commandes.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 rounded-lg">Commandes</a>
            <a href="ingredients.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 rounded-lg">Ingrédients</a>
            <a href="clients.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 rounded-lg">Clients</a>
            <a href="annonces.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 rounded-lg">Annonces</a>
            <a href="paiements.php" class="flex items-center gap-3 px-4 py-3 bg-green-600 rounded-lg">Paiements</a>
            <a href="parametres.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 rounded-lg">Paramètres</a>
        </nav>
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-800">
            <a href="<?= APP_URL ?>/auth/logout.php" class="text-red-400 hover:text-red-300 text-sm">Déconnexion</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen">
        <header class="bg-white shadow-sm sticky top-0 z-30">
            <div class="px-6 py-4 flex items-center gap-4">
                <a href="paiements.php" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-800">Validation Paiement #<?= $paymentId ?></h1>
            </div>
        </header>

        <div class="p-6 max-w-4xl mx-auto">
            <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6"><?= sanitize($success) ?></div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Payment Proof -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Justificatif de paiement</h2>
                    <?php if ($payment['justificatif']): ?>
                    <div class="border rounded-lg overflow-hidden">
                        <img src="<?= UPLOAD_URL . 'payments/' . sanitize($payment['justificatif']) ?>"
                            alt="Justificatif" class="w-full max-h-96 object-contain">
                    </div>
                    <div class="mt-4 text-center">
                        <a href="<?= UPLOAD_URL . 'payments/' . sanitize($payment['justificatif']) ?>" target="_blank"
                            class="text-blue-600 hover:text-blue-700 font-medium">
                            Ouvrir en grand →
                        </a>
                    </div>
                    <?php else: ?>
                    <p class="text-gray-500 text-center py-8">Aucun justificatif uploadé</p>
                    <?php endif; ?>
                </div>

                <!-- Payment Info -->
                <div class="space-y-6">
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Informations</h2>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Commande</span>
                                <a href="commande-detail.php?id=<?= $payment['commande_id'] ?>" class="text-blue-600 hover:underline font-medium">#<?= formatOrderNumber((int) $payment['commande_id']) ?></a>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Client</span>
                                <span class="font-medium"><?= sanitize($payment['prenom'] . ' ' . $payment['nom']) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Email</span>
                                <span><?= sanitize($payment['email']) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Téléphone</span>
                                <span><?= sanitize($payment['telephone']) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Montant</span>
                                <span class="font-bold text-lg text-green-600"><?= formatPrice($payment['montant']) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Mode</span>
                                <span class="px-2 py-1 rounded text-xs <?= $payment['mode_paiement'] === 'orange_money' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' ?>">
                                    <?= $payment['mode_paiement'] === 'orange_money' ? 'Orange Money' : 'MVola' ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">N° Transaction</span>
                                <span class="font-mono"><?= sanitize($payment['numero_transaction']) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Date</span>
                                <span><?= formatDate($payment['created_at']) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Validation Form -->
                    <?php if ($payment['statut'] === 'en_attente'): ?>
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Validation</h2>
                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optionnel)</label>
                                <textarea name="notes" rows="2"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-green-500 outline-none resize-none"
                                    placeholder="Commentaires sur la validation..."></textarea>
                            </div>
                            <div class="flex gap-3">
                                <button type="submit" name="action" value="valider"
                                    class="flex-1 bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 font-medium">
                                    ✓ Valider le paiement
                                </button>
                                <button type="submit" name="action" value="refuser"
                                    class="flex-1 bg-red-600 text-white px-4 py-3 rounded-lg hover:bg-red-700 font-medium">
                                    ✗ Refuser
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php else: ?>
                    <div class="bg-gray-50 rounded-xl p-6">
                        <p class="text-gray-600">
                            Ce paiement a déjà été
                            <span class="font-medium <?= $payment['statut'] === 'valide' ? 'text-green-600' : 'text-red-600' ?>">
                                <?= $payment['statut'] === 'valide' ? 'validé' : 'refusé' ?>
                            </span>.
                        </p>
                        <?php if ($payment['notes_admin']): ?>
                        <div class="mt-3 p-3 bg-white rounded border">
                            <p class="text-sm text-gray-500 mb-1">Notes:</p>
                            <p class="text-gray-800"><?= sanitize($payment['notes_admin']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
