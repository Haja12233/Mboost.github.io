<?php
/**
 * M'Boost — Admin Paiements — Premium Design
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

if (!isAdminLoggedIn()) {
    redirect(APP_URL . '/auth/admin.php');
}

$success = '';

// Handle validation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentId = (int) ($_POST['payment_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if ($paymentId && in_array($action, ['valider', 'refuser'])) {
        $newStatus = ($action === 'valider') ? 'valide' : 'refuse';
        $stmt = $pdo->prepare("UPDATE paiements SET statut = :statut, notes_admin = :notes, validated_at = NOW() WHERE id = :id");
        $stmt->execute([':statut' => $newStatus, ':notes' => $notes, ':id' => $paymentId]);

        if ($action === 'valider') {
            $stmt = $pdo->prepare("
                SELECT c.id, c.statut
                FROM commandes c
                JOIN paiements p ON c.id = p.commande_id
                WHERE p.id = :id
                LIMIT 1
            ");
            $stmt->execute([':id' => $paymentId]);
            $orderRow = $stmt->fetch();

            if ($orderRow && $orderRow['statut'] !== 'paye') {
                $stmt = $pdo->prepare("UPDATE commandes SET statut = 'paye' WHERE id = :id");
                $stmt->execute([':id' => (int) $orderRow['id']]);
                logOrderStatusChange(
                    $pdo,
                    (int) $orderRow['id'],
                    (string) $orderRow['statut'],
                    'paye',
                    (int) ($_SESSION['admin_id'] ?? 0),
                    'Validation paiement admin'
                );
            }
        }
        $success = 'Paiement ' . ($action === 'valider' ? 'validé' : 'refusé') . '!';
    }
}

$statusFilter = $_GET['statut'] ?? '';
$modeFilter = $_GET['mode'] ?? '';

$where = ['1=1'];
$params = [];
if ($statusFilter) { $where[] = "p.statut = :statut"; $params[':statut'] = $statusFilter; }
if ($modeFilter) { $where[] = "p.mode_paiement = :mode"; $params[':mode'] = $modeFilter; }
$whereClause = implode(' AND ', $where);

$stmt = $pdo->prepare("
    SELECT p.*, c.id as commande_id, c.total, c.user_id, u.nom, u.prenom, u.email
    FROM paiements p JOIN commandes c ON p.commande_id = c.id JOIN users u ON c.user_id = u.id
    WHERE $whereClause ORDER BY p.created_at DESC
");
$stmt->execute($params);
$payments = $stmt->fetchAll();

$pendingCount = count(array_filter($payments, fn($p) => $p['statut'] === 'en_attente'));
$validatedTotal = array_sum(array_map(fn($p) => $p['statut'] === 'valide' ? $p['montant'] : 0, $payments));

$pageTitle = "Gestion des paiements";
$activePage = "paiements";
include __DIR__ . '/includes/admin-layout-top.php';
?>

<?php if ($success): ?>
<div class="flex items-start gap-3 p-4 rounded-2xl bg-green-50 border border-green-100 animate-scale-in">
    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-green-100 text-green-600 flex-shrink-0"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
    <p class="text-sm text-green-700 font-medium"><?= htmlspecialchars($success) ?></p>
</div>
<?php endif; ?>

<!-- Stats -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-yellow-400 to-amber-500 flex items-center justify-center shadow-md shadow-yellow-200/40"><svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div>
                <p class="text-xs text-gray-400 font-medium">En attente</p>
                <p class="text-xl font-extrabold font-display text-gray-900"><?= $pendingCount ?></p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center shadow-md shadow-green-200/40"><svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
            <div>
                <p class="text-xs text-gray-400 font-medium">Total validé</p>
                <p class="text-xl font-extrabold font-display text-gray-900"><?= formatPrice($validatedTotal) ?></p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-400 to-blue-500 flex items-center justify-center shadow-md shadow-blue-200/40"><svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
            <div>
                <p class="text-xs text-gray-400 font-medium">Total paiements</p>
                <p class="text-xl font-extrabold font-display text-gray-900"><?= count($payments) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
    <form method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-[11px] font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Statut</label>
            <select name="statut" class="input-modern text-sm">
                <option value="">Tous</option>
                <option value="en_attente" <?= $statusFilter === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                <option value="valide" <?= $statusFilter === 'valide' ? 'selected' : '' ?>>Validé</option>
                <option value="refuse" <?= $statusFilter === 'refuse' ? 'selected' : '' ?>>Refusé</option>
            </select>
        </div>
        <div>
            <label class="block text-[11px] font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Mode</label>
            <select name="mode" class="input-modern text-sm">
                <option value="">Tous</option>
                <option value="orange_money" <?= $modeFilter === 'orange_money' ? 'selected' : '' ?>>Orange Money</option>
                <option value="mvola" <?= $modeFilter === 'mvola' ? 'selected' : '' ?>>MVola</option>
            </select>
        </div>
        <button type="submit" class="px-5 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-green-200/50 hover:shadow-xl transition-all">Filtrer</button>
        <?php if ($statusFilter || $modeFilter): ?>
        <a href="paiements.php" class="px-4 py-3 bg-gray-100 text-gray-600 font-semibold text-sm rounded-xl hover:bg-gray-200 transition-colors">✕</a>
        <?php endif; ?>
    </form>
</div>

<!-- Payments Table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">ID</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Commande</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Client</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Montant</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Mode</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">N° Transaction</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Statut</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Preuve</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($payments as $payment): ?>
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-5 py-3.5 text-gray-500 font-mono text-xs"><?= $payment['id'] ?></td>
                    <td class="px-5 py-3.5 font-bold text-gray-700">#<?= formatOrderNumber((int) $payment['commande_id']) ?></td>
                    <td class="px-5 py-3.5 font-semibold text-gray-800"><?= htmlspecialchars($payment['prenom'] . ' ' . $payment['nom']) ?></td>
                    <td class="px-5 py-3.5 font-bold text-gray-800"><?= formatPrice($payment['montant']) ?></td>
                    <td class="px-5 py-3.5">
                        <span class="text-[10px] px-2 py-1 rounded-lg font-semibold <?= $payment['mode_paiement'] === 'orange_money' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' ?>">
                            <?= $payment['mode_paiement'] === 'orange_money' ? 'Orange Money' : 'MVola' ?>
                        </span>
                    </td>
                    <td class="px-5 py-3.5 font-mono text-xs text-gray-500"><?= htmlspecialchars($payment['numero_transaction']) ?: '—' ?></td>
                    <td class="px-5 py-3.5">
                        <span class="text-[10px] px-2 py-1 rounded-lg font-semibold <?=
                            $payment['statut'] === 'valide' ? 'bg-green-100 text-green-700' :
                            ($payment['statut'] === 'refuse' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700')
                        ?>"><?= $payment['statut'] === 'valide' ? 'Validé' : ($payment['statut'] === 'refuse' ? 'Refusé' : 'En attente') ?></span>
                    </td>
                    <td class="px-5 py-3.5">
                        <?php if ($payment['justificatif']): ?>
                        <a href="<?= UPLOAD_URL . 'payments/' . htmlspecialchars($payment['justificatif']) ?>" target="_blank"
                           class="text-xs text-blue-600 hover:text-blue-700 font-bold px-2.5 py-1.5 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">Voir</a>
                        <?php else: ?>
                        <span class="text-gray-300 text-xs">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3.5">
                        <?php if ($payment['statut'] === 'en_attente'): ?>
                        <form method="POST" class="flex gap-1.5">
                            <input type="hidden" name="payment_id" value="<?= $payment['id'] ?>">
                            <button type="submit" name="action" value="valider"
                                    class="text-xs text-green-600 hover:text-green-700 font-bold px-2.5 py-1.5 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">✓</button>
                            <button type="submit" name="action" value="refuser"
                                    class="text-xs text-red-600 hover:text-red-700 font-bold px-2.5 py-1.5 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">✗</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($payments)): ?>
                <tr>
                    <td colspan="9" class="px-5 py-12 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-3"><span class="text-xl">💳</span></div>
                        <p class="text-sm text-gray-400 font-medium">Aucun paiement trouvé</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/admin-layout-bottom.php'; ?>
