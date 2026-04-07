<?php
/**
 * M'Boost — Admin Historique des statuts de commande
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

if (!isAdminLoggedIn()) {
    redirect(APP_URL . '/auth/admin.php');
}

ensureOrderStatusHistoryTable($pdo);

$commandeId = (int) ($_GET['commande_id'] ?? 0);
$statut = trim($_GET['statut'] ?? '');
$adminId = (int) ($_GET['admin_id'] ?? 0);
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');

$where = ['1=1'];
$params = [];

if ($commandeId > 0) {
    $where[] = 'h.commande_id = :commande_id';
    $params[':commande_id'] = $commandeId;
}

if ($statut !== '') {
    $where[] = 'h.nouveau_statut = :statut';
    $params[':statut'] = $statut;
}

if ($adminId > 0) {
    $where[] = 'h.changed_by_admin = :admin_id';
    $params[':admin_id'] = $adminId;
}

if ($dateFrom !== '') {
    $where[] = 'DATE(h.created_at) >= :date_from';
    $params[':date_from'] = $dateFrom;
}

if ($dateTo !== '') {
    $where[] = 'DATE(h.created_at) <= :date_to';
    $params[':date_to'] = $dateTo;
}

$whereSql = implode(' AND ', $where);

$stmt = $pdo->prepare("
    SELECT
        h.*,
        c.total,
        c.user_id,
        u.nom AS user_nom,
        u.prenom AS user_prenom,
        a.nom AS admin_nom,
        a.prenom AS admin_prenom
    FROM commande_statuts_historique h
    LEFT JOIN commandes c ON c.id = h.commande_id
    LEFT JOIN users u ON u.id = c.user_id
    LEFT JOIN admins a ON a.id = h.changed_by_admin
    WHERE {$whereSql}
    ORDER BY h.created_at DESC, h.id DESC
    LIMIT 300
");
$stmt->execute($params);
$rows = $stmt->fetchAll();

$stmt = $pdo->query("SELECT id, prenom, nom FROM admins ORDER BY prenom, nom");
$admins = $stmt->fetchAll();

$availableStatuts = [
    'en_attente' => 'En attente',
    'paye' => 'Paye',
    'en_preparation' => 'En preparation',
    'en_livraison' => 'En livraison',
    'livre' => 'Livre',
    'annule' => 'Annule',
];

$pageTitle = 'Historique Statuts';
$activePage = 'historique-statuts';
include __DIR__ . '/includes/admin-layout-top.php';
?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
        <div>
            <label class="block text-[11px] font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Commande #</label>
            <input type="number" name="commande_id" min="1" value="<?= $commandeId > 0 ? $commandeId : '' ?>" class="input-modern w-full text-sm" placeholder="Ex: 12">
        </div>
        <div>
            <label class="block text-[11px] font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Nouveau statut</label>
            <select name="statut" class="input-modern w-full text-sm">
                <option value="">Tous</option>
                <?php foreach ($availableStatuts as $key => $label): ?>
                <option value="<?= $key ?>" <?= $statut === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-[11px] font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Admin</label>
            <select name="admin_id" class="input-modern w-full text-sm">
                <option value="">Tous</option>
                <?php foreach ($admins as $admin): ?>
                <option value="<?= (int) $admin['id'] ?>" <?= $adminId === (int) $admin['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars(trim(($admin['prenom'] ?? '') . ' ' . ($admin['nom'] ?? ''))) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-[11px] font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Date debut</label>
            <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>" class="input-modern w-full text-sm">
        </div>
        <div>
            <label class="block text-[11px] font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Date fin</label>
            <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>" class="input-modern w-full text-sm">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-4 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-green-200/50 hover:shadow-xl transition-all">Filtrer</button>
            <?php if ($commandeId || $statut || $adminId || $dateFrom || $dateTo): ?>
            <a href="historique-statuts.php" class="px-4 py-3 bg-gray-100 text-gray-600 font-semibold text-sm rounded-xl hover:bg-gray-200 transition-colors">✕</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="flex items-center justify-between">
    <p class="text-sm text-gray-500"><span class="font-bold text-gray-900"><?= count($rows) ?></span> transition<?= count($rows) > 1 ? 's' : '' ?> (max 300)</p>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Date</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Commande</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Client</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Transition</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Admin/Source</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Note</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($rows as $row): ?>
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-5 py-3.5 text-gray-500 text-xs"><?= formatDate($row['created_at'] ?? null, true) ?></td>
                    <td class="px-5 py-3.5">
                        <a href="commande-detail.php?id=<?= (int) $row['commande_id'] ?>" class="font-bold text-gray-700 hover:text-green-700 transition-colors">
                            #<?= formatOrderNumber((int) $row['commande_id']) ?>
                        </a>
                        <?php if (!empty($row['total'])): ?>
                        <p class="text-[11px] text-gray-400"><?= formatPrice((float) $row['total']) ?></p>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3.5">
                        <p class="font-semibold text-gray-800">
                            <?= htmlspecialchars(trim(($row['user_prenom'] ?? '') . ' ' . ($row['user_nom'] ?? ''))) ?: '—' ?>
                        </p>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] px-2 py-1 rounded-lg bg-gray-100 text-gray-600 font-semibold">
                                <?= htmlspecialchars($availableStatuts[$row['ancien_statut']] ?? ($row['ancien_statut'] ?: '—')) ?>
                            </span>
                            <span class="text-gray-300">→</span>
                            <span class="text-[10px] px-2 py-1 rounded-lg bg-green-100 text-green-700 font-semibold">
                                <?= htmlspecialchars($availableStatuts[$row['nouveau_statut']] ?? $row['nouveau_statut']) ?>
                            </span>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-gray-600">
                        <?php if (!empty($row['changed_by_admin'])): ?>
                            <?= htmlspecialchars(trim(($row['admin_prenom'] ?? '') . ' ' . ($row['admin_nom'] ?? ''))) ?>
                        <?php else: ?>
                            <span class="text-gray-400">Automatique / Client</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-gray-500 max-w-[260px]">
                        <?= htmlspecialchars($row['note'] ?? '—') ?>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-3"><span class="text-xl">🕒</span></div>
                        <p class="text-sm text-gray-400 font-medium">Aucun historique trouve</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/admin-layout-bottom.php'; ?>
