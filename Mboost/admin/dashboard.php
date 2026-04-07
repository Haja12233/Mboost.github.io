<?php
/**
 * M'Boost — Admin Dashboard — Premium Design
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

if (!isAdminLoggedIn()) {
    redirect(APP_URL . '/auth/admin.php');
}

// Stats today
$stmt = $pdo->query("SELECT COUNT(*) as count, COALESCE(SUM(total), 0) as total FROM commandes WHERE DATE(created_at) = CURDATE()");
$statsToday = $stmt->fetch();

// Stats month
$stmt = $pdo->query("SELECT COUNT(*) as count, COALESCE(SUM(total), 0) as total FROM commandes WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
$statsMonth = $stmt->fetch();

// Total customers
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE statut = 'actif'");
$totalCustomers = $stmt->fetch()['count'];

// Orders by status
$stmt = $pdo->query("SELECT statut, COUNT(*) as count FROM commandes GROUP BY statut");
$ordersByStatus = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Recent orders
$stmt = $pdo->query("SELECT c.*, u.nom, u.prenom, u.email FROM commandes c JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC LIMIT 10");
$recentOrders = $stmt->fetchAll();

// Low stock
$stmt = $pdo->query("SELECT * FROM ingredients WHERE stock < 50 AND actif = 1 ORDER BY stock ASC LIMIT 5");
$lowStock = $stmt->fetchAll();

// Pending payments
$stmt = $pdo->query("SELECT p.*, c.id as commande_id, c.total, u.nom, u.prenom FROM paiements p JOIN commandes c ON p.commande_id = c.id JOIN users u ON c.user_id = u.id WHERE p.statut = 'en_attente' ORDER BY p.created_at DESC LIMIT 5");
$pendingPayments = $stmt->fetchAll();

$pageTitle = "Tableau de bord";
$activePage = "dashboard";
include __DIR__ . '/includes/admin-layout-top.php';
?>

<!-- Welcome -->
<div class="flex flex-wrap items-center justify-between gap-4 mb-2">
    <div>
        <h2 class="text-2xl font-extrabold font-display text-gray-900">Bonjour, <?= htmlspecialchars($_SESSION['admin_prenom'] ?? 'Admin') ?> 👋</h2>
        <p class="text-sm text-gray-400 mt-1">Voici un résumé de votre activité aujourd'hui.</p>
    </div>
    <p class="text-xs text-gray-400"><?= date('l j F Y') ?></p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
    <!-- Today Revenue -->
    <div class="stat-card bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-lg transition-all duration-300">
        <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center shadow-lg shadow-green-200/40">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="text-[10px] font-bold text-green-600 bg-green-50 px-2 py-1 rounded-lg uppercase tracking-wider">Aujourd'hui</span>
        </div>
        <p class="text-xs text-gray-400 font-medium">Chiffre d'affaires</p>
        <p class="text-2xl font-extrabold font-display text-gray-900 mt-0.5"><?= formatPrice($statsToday['total']) ?></p>
        <p class="text-[11px] text-gray-400 mt-1"><?= $statsToday['count'] ?> commande<?= $statsToday['count'] > 1 ? 's' : '' ?></p>
    </div>

    <!-- Monthly Revenue -->
    <div class="stat-card bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-lg transition-all duration-300">
        <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-blue-400 to-blue-500 flex items-center justify-center shadow-lg shadow-blue-200/40">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded-lg uppercase tracking-wider">Ce mois</span>
        </div>
        <p class="text-xs text-gray-400 font-medium">Chiffre d'affaires</p>
        <p class="text-2xl font-extrabold font-display text-gray-900 mt-0.5"><?= formatPrice($statsMonth['total']) ?></p>
        <p class="text-[11px] text-gray-400 mt-1"><?= $statsMonth['count'] ?> commande<?= $statsMonth['count'] > 1 ? 's' : '' ?></p>
    </div>

    <!-- Customers -->
    <div class="stat-card bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-lg transition-all duration-300">
        <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 flex items-center justify-center shadow-lg shadow-orange-200/40">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <span class="text-[10px] font-bold text-orange-600 bg-orange-50 px-2 py-1 rounded-lg uppercase tracking-wider">Total</span>
        </div>
        <p class="text-xs text-gray-400 font-medium">Clients actifs</p>
        <p class="text-2xl font-extrabold font-display text-gray-900 mt-0.5"><?= number_format($totalCustomers) ?></p>
        <p class="text-[11px] text-gray-400 mt-1">Inscrits</p>
    </div>

    <!-- Pending orders -->
    <div class="stat-card bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-lg transition-all duration-300">
        <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-yellow-400 to-amber-500 flex items-center justify-center shadow-lg shadow-yellow-200/40">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="text-[10px] font-bold text-yellow-600 bg-yellow-50 px-2 py-1 rounded-lg uppercase tracking-wider">En attente</span>
        </div>
        <p class="text-xs text-gray-400 font-medium">Commandes</p>
        <p class="text-2xl font-extrabold font-display text-gray-900 mt-0.5"><?= $ordersByStatus['en_attente'] ?? 0 ?></p>
        <p class="text-[11px] text-gray-400 mt-1">À traiter</p>
    </div>
</div>

<!-- Two-column layout -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Orders -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-bold font-display text-gray-900">Commandes récentes</h3>
            <a href="commandes.php" class="text-xs text-green-600 hover:text-green-700 font-semibold transition-colors">Voir tout →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="pb-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">N°</th>
                        <th class="pb-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Client</th>
                        <th class="pb-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total</th>
                        <th class="pb-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($recentOrders as $order): ?>
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="py-3 font-semibold text-gray-700">#<?= formatOrderNumber((int) $order['id']) ?></td>
                        <td class="py-3 text-gray-600"><?= htmlspecialchars($order['prenom'] . ' ' . $order['nom']) ?></td>
                        <td class="py-3 font-semibold text-gray-800"><?= formatPrice($order['total']) ?></td>
                        <td class="py-3">
                            <span class="text-[10px] px-2 py-1 rounded-lg font-semibold <?= getOrderStatusColor($order['statut']) ?>"><?= getOrderStatusLabel($order['statut']) ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pending Payments -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-bold font-display text-gray-900">Paiements en attente</h3>
            <a href="paiements.php" class="text-xs text-green-600 hover:text-green-700 font-semibold transition-colors">Voir tout →</a>
        </div>
        <?php if (empty($pendingPayments)): ?>
        <div class="py-8 text-center">
            <div class="w-12 h-12 rounded-2xl bg-green-50 flex items-center justify-center mx-auto mb-3"><span class="text-xl">✅</span></div>
            <p class="text-sm text-gray-400 font-medium">Aucun paiement en attente</p>
        </div>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($pendingPayments as $payment): ?>
            <div class="flex items-center justify-between p-3.5 bg-gray-50 rounded-xl hover:bg-gray-100/80 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-yellow-400 to-amber-500 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                        <?= mb_strtoupper(mb_substr($payment['prenom'], 0, 1)) ?>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">#<?= formatOrderNumber((int) $payment['commande_id']) ?> — <?= htmlspecialchars($payment['prenom'] . ' ' . $payment['nom']) ?></p>
                        <p class="text-[11px] text-gray-400"><?= formatPrice($payment['montant']) ?> • <?= $payment['mode_paiement'] === 'orange_money' ? 'Orange Money' : 'MVola' ?></p>
                    </div>
                </div>
                <a href="paiement-valider.php?id=<?= $payment['id'] ?>"
                   class="text-xs text-green-600 hover:text-green-700 font-bold px-3 py-1.5 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">Valider →</a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Low Stock Alert -->
<?php if (!empty($lowStock)): ?>
<div class="bg-gradient-to-br from-red-50 to-orange-50 border border-red-100 rounded-2xl p-6">
    <h3 class="font-bold font-display text-red-800 mb-4 flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        Stock faible — Action requise
    </h3>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        <?php foreach ($lowStock as $ing): ?>
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="text-2xl mb-1"><?= $ing['emoji'] ?? '🥤' ?></div>
            <p class="font-semibold text-gray-800 text-sm"><?= htmlspecialchars($ing['nom']) ?></p>
            <p class="text-sm font-bold mt-1 <?= $ing['stock'] < 20 ? 'text-red-600' : 'text-orange-500' ?>"><?= $ing['stock'] ?> restant<?= $ing['stock'] > 1 ? 's' : '' ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin-layout-bottom.php'; ?>
