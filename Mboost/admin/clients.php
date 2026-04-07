<?php
/**
 * M'Boost — Admin Clients — Premium Design
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

if (!isAdminLoggedIn()) {
    redirect(APP_URL . '/auth/admin.php');
}

$success = '';

// Handle block/unblock
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $action = $_GET['action'];
    $newStatus = ($action === 'block') ? 'bloque' : 'actif';
    $stmt = $pdo->prepare("UPDATE users SET statut = :status WHERE id = :id");
    $stmt->execute([':status' => $newStatus, ':id' => $id]);
    $success = 'Client mis à jour!';
}

$search = $_GET['search'] ?? '';

if ($search) {
    $stmt = $pdo->prepare("
        SELECT u.*, COUNT(c.id) as nb_commandes, COALESCE(SUM(c.total), 0) as ca_total
        FROM users u LEFT JOIN commandes c ON u.id = c.user_id
        WHERE u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search OR u.telephone LIKE :search
        GROUP BY u.id ORDER BY u.created_at DESC
    ");
    $stmt->execute([':search' => "%$search%"]);
} else {
    $stmt = $pdo->query("
        SELECT u.*, COUNT(c.id) as nb_commandes, COALESCE(SUM(c.total), 0) as ca_total
        FROM users u LEFT JOIN commandes c ON u.id = c.user_id
        GROUP BY u.id ORDER BY u.created_at DESC
    ");
}
$clients = $stmt->fetchAll();

$pageTitle = "Gestion des clients";
$activePage = "clients";
include __DIR__ . '/includes/admin-layout-top.php';
?>

<?php if ($success): ?>
<div class="flex items-start gap-3 p-4 rounded-2xl bg-green-50 border border-green-100 animate-scale-in">
    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-green-100 text-green-600 flex-shrink-0"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
    <p class="text-sm text-green-700 font-medium"><?= htmlspecialchars($success) ?></p>
</div>
<?php endif; ?>

<!-- Search -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
    <form method="GET" class="flex gap-3">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
               class="input-modern flex-1" placeholder="Rechercher par nom, email, téléphone...">
        <button type="submit" class="px-5 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-green-200/50 hover:shadow-xl transition-all">Rechercher</button>
        <?php if ($search): ?>
        <a href="clients.php" class="px-4 py-3 bg-gray-100 text-gray-600 font-semibold text-sm rounded-xl hover:bg-gray-200 transition-colors">✕</a>
        <?php endif; ?>
    </form>
</div>

<!-- Count -->
<p class="text-sm text-gray-500"><span class="font-bold text-gray-900"><?= count($clients) ?></span> client<?= count($clients) > 1 ? 's' : '' ?></p>

<!-- Clients Table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Client</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Contact</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Commandes</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">CA Total</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Inscription</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Statut</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($clients as $client): ?>
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                                <?= mb_strtoupper(mb_substr($client['prenom'], 0, 1)) ?>
                            </div>
                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($client['prenom'] . ' ' . $client['nom']) ?></p>
                        </div>
                    </td>
                    <td class="px-5 py-3.5">
                        <p class="text-gray-700 text-xs"><?= htmlspecialchars($client['email']) ?></p>
                        <p class="text-[11px] text-gray-400"><?= htmlspecialchars($client['telephone']) ?></p>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center text-xs px-2 py-0.5 bg-blue-50 text-blue-600 rounded-lg font-medium"><?= $client['nb_commandes'] ?></span>
                    </td>
                    <td class="px-5 py-3.5 font-bold text-gray-700"><?= formatPrice($client['ca_total']) ?></td>
                    <td class="px-5 py-3.5 text-gray-400 text-xs"><?= formatDate($client['created_at'], false) ?></td>
                    <td class="px-5 py-3.5">
                        <span class="text-[10px] px-2 py-1 rounded-lg font-semibold <?= $client['statut'] === 'actif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                            <?= $client['statut'] === 'actif' ? 'Actif' : 'Bloqué' ?>
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        <?php if ($client['statut'] === 'actif'): ?>
                        <a href="?action=block&id=<?= $client['id'] ?>" onclick="return confirm('Bloquer ce client?')"
                           class="text-xs text-red-600 hover:text-red-700 font-bold px-2.5 py-1.5 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">Bloquer</a>
                        <?php else: ?>
                        <a href="?action=unblock&id=<?= $client['id'] ?>"
                           class="text-xs text-green-600 hover:text-green-700 font-bold px-2.5 py-1.5 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">Débloquer</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($clients)): ?>
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-3"><span class="text-xl">👥</span></div>
                        <p class="text-sm text-gray-400 font-medium">Aucun client trouvé</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/admin-layout-bottom.php'; ?>
