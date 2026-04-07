<?php
/**
 * M'Boost — Admin Commandes — Premium Design
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

if (!isAdminLoggedIn()) {
    redirect(APP_URL . '/auth/admin.php');
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    requireValidCSRFOrAbort();
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';
    if ($orderId && in_array($newStatus, ['en_attente', 'paye', 'en_preparation', 'en_livraison', 'livre', 'annule'])) {
        $stmt = $pdo->prepare("SELECT statut FROM commandes WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $orderId]);
        $current = $stmt->fetchColumn();

        if ($current !== false && $current !== $newStatus) {
            $stmt = $pdo->prepare("UPDATE commandes SET statut = :status WHERE id = :id");
            $stmt->execute([':status' => $newStatus, ':id' => $orderId]);
            logOrderStatusChange(
                $pdo,
                $orderId,
                (string) $current,
                $newStatus,
                (int) ($_SESSION['admin_id'] ?? 0),
                'Mise a jour statut depuis admin commandes'
            );
        }
    }
    redirect(APP_URL . '/admin/commandes.php');
}

$statusFilter = $_GET['statut'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';

$where = ['1=1'];
$params = [];

if ($statusFilter) { $where[] = "c.statut = :status"; $params[':status'] = $statusFilter; }
if ($dateFrom) { $where[] = "DATE(c.created_at) >= :date_from"; $params[':date_from'] = $dateFrom; }
if ($dateTo) { $where[] = "DATE(c.created_at) <= :date_to"; $params[':date_to'] = $dateTo; }
if ($search) { $where[] = "(c.id LIKE :search OR u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search)"; $params[':search'] = "%$search%"; }

$whereClause = implode(' AND ', $where);

$stmt = $pdo->prepare("
    SELECT c.*, u.nom, u.prenom, u.email, u.telephone,
           (SELECT COUNT(*) FROM lignes_commandes WHERE commande_id = c.id) as nb_items
    FROM commandes c JOIN users u ON c.user_id = u.id
    WHERE $whereClause ORDER BY c.created_at DESC
");
$stmt->execute($params);
$orders = $stmt->fetchAll();

$pageTitle = "Gestion des commandes";
$activePage = "commandes";
include __DIR__ . '/includes/admin-layout-top.php';
?>

<!-- Filters -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
        <div>
            <label class="block text-[11px] font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Statut</label>
            <select name="statut" class="input-modern w-full text-sm">
                <option value="">Tous</option>
                <option value="en_attente" <?= $statusFilter === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                <option value="paye" <?= $statusFilter === 'paye' ? 'selected' : '' ?>>Payé</option>
                <option value="en_preparation" <?= $statusFilter === 'en_preparation' ? 'selected' : '' ?>>En préparation</option>
                <option value="en_livraison" <?= $statusFilter === 'en_livraison' ? 'selected' : '' ?>>En livraison</option>
                <option value="livre" <?= $statusFilter === 'livre' ? 'selected' : '' ?>>Livré</option>
                <option value="annule" <?= $statusFilter === 'annule' ? 'selected' : '' ?>>Annulé</option>
            </select>
        </div>
        <div>
            <label class="block text-[11px] font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Date début</label>
            <input type="date" name="date_from" value="<?= $dateFrom ?>" class="input-modern w-full text-sm">
        </div>
        <div>
            <label class="block text-[11px] font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Date fin</label>
            <input type="date" name="date_to" value="<?= $dateTo ?>" class="input-modern w-full text-sm">
        </div>
        <div>
            <label class="block text-[11px] font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Recherche</label>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="input-modern w-full text-sm" placeholder="N°, client, email...">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-4 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-green-200/50 hover:shadow-xl transition-all">Filtrer</button>
            <?php if ($statusFilter || $dateFrom || $dateTo || $search): ?>
            <a href="commandes.php" class="px-4 py-3 bg-gray-100 text-gray-600 font-semibold text-sm rounded-xl hover:bg-gray-200 transition-colors">✕</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Results count -->
<div class="flex items-center justify-between">
    <p class="text-sm text-gray-500"><span class="font-bold text-gray-900"><?= count($orders) ?></span> commande<?= count($orders) > 1 ? 's' : '' ?></p>
</div>

<!-- Orders Table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">N°</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Client</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Articles</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Statut</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Date</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($orders as $order): ?>
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-5 py-3.5 font-bold text-gray-700">#<?= formatOrderNumber((int) $order['id']) ?></td>
                    <td class="px-5 py-3.5">
                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($order['prenom'] . ' ' . $order['nom']) ?></p>
                        <p class="text-[11px] text-gray-400"><?= htmlspecialchars($order['email']) ?></p>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded-lg font-medium">🥤 <?= $order['nb_items'] ?></span>
                    </td>
                    <td class="px-5 py-3.5 font-bold text-gray-800"><?= formatPrice($order['total']) ?></td>
                    <td class="px-5 py-3.5">
                        <form method="POST" class="inline">
                            <?= csrfInput() ?>
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <input type="hidden" name="update_status" value="1">
                            <select name="status" onchange="this.form.submit()"
                                    class="text-[11px] px-2.5 py-1 rounded-lg border-0 font-semibold cursor-pointer focus:ring-2 focus:ring-green-200 <?= getOrderStatusColor($order['statut']) ?>">
                                <option value="en_attente" <?= $order['statut'] === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                <option value="paye" <?= $order['statut'] === 'paye' ? 'selected' : '' ?>>Payé</option>
                                <option value="en_preparation" <?= $order['statut'] === 'en_preparation' ? 'selected' : '' ?>>En préparation</option>
                                <option value="en_livraison" <?= $order['statut'] === 'en_livraison' ? 'selected' : '' ?>>En livraison</option>
                                <option value="livre" <?= $order['statut'] === 'livre' ? 'selected' : '' ?>>Livré</option>
                                <option value="annule" <?= $order['statut'] === 'annule' ? 'selected' : '' ?>>Annulé</option>
                            </select>
                        </form>
                    </td>
                    <td class="px-5 py-3.5 text-gray-400 text-xs"><?= formatDate($order['created_at'], false) ?></td>
                    <td class="px-5 py-3.5">
                        <a href="commande-detail.php?id=<?= $order['id'] ?>" class="text-xs text-green-600 hover:text-green-700 font-bold px-3 py-1.5 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">Voir →</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-3"><span class="text-xl">📋</span></div>
                        <p class="text-sm text-gray-400 font-medium">Aucune commande trouvée</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/admin-layout-bottom.php'; ?>
