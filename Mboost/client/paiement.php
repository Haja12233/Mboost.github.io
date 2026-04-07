<?php
/**
 * M'Boost — Paiement Mobile Money — Premium Design
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

if (!isLoggedIn()) {
    redirect(APP_URL . '/auth/login.php');
}

$userId = $_SESSION['user_id'];
$commandeId = (int) ($_GET['commande'] ?? 0);

if (!$commandeId) {
    redirect(APP_URL . '/client/profil.php');
}

$stmt = $pdo->prepare("
    SELECT c.*, p.id as paiement_id, p.statut as paiement_statut
    FROM commandes c
    LEFT JOIN paiements p ON c.id = p.commande_id
    WHERE c.id = :id AND c.user_id = :user_id
    LIMIT 1
");
$stmt->execute([':id' => $commandeId, ':user_id' => $userId]);
$order = $stmt->fetch();

if (!$order) {
    redirect(APP_URL . '/client/profil.php');
}

if ($order['statut'] !== 'en_attente') {
    redirect(APP_URL . '/client/commande-detail.php?id=' . $commandeId);
}

$stmt = $pdo->prepare("SELECT * FROM paiements WHERE commande_id = :id LIMIT 1");
$stmt->execute([':id' => $commandeId]);
$payment = $stmt->fetch();

$orangeMoneyNumber = getParam('numero_orange', 'numero_orange', '+261 34 00 000 00');
$mvolaNumber = getParam('numero_mvola', 'numero_mvola', '+261 38 00 000 00');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCSRFOrAbort();
    $numeroTransaction = trim($_POST['numero_transaction'] ?? '');

    if (empty($numeroTransaction)) {
        $error = 'Veuillez entrer le numéro de transaction.';
    } elseif (!empty($_FILES['justificatif']['tmp_name'])) {
        $result = uploadFile($_FILES['justificatif'], UPLOAD_DIR . 'payments/');

        if ($result['success']) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE paiements
                    SET numero_transaction = :numero, justificatif = :justificatif, statut = 'en_attente'
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':numero' => $numeroTransaction,
                    ':justificatif' => $result['filename'],
                    ':id' => $payment['id']
                ]);

                $stmt = $pdo->prepare("
                    UPDATE commandes
                    SET statut = 'paye', numero_transaction = :numero
                    WHERE id = :id
                ");
                $stmt->execute([':numero' => $numeroTransaction, ':id' => $commandeId]);
                logOrderStatusChange(
                    $pdo,
                    $commandeId,
                    (string) ($order['statut'] ?? ''),
                    'paye',
                    null,
                    'Paiement soumis par client'
                );

                $success = 'Votre paiement a été soumis et est en cours de validation.';
            } catch (PDOException $e) {
                $error = 'Une erreur est survenue lors de la mise à jour de la base de données.';
            }
        } else {
            $error = 'Erreur upload: ' . $result['error'];
        }
    } else {
        $error = 'Veuillez uploader un justificatif de paiement.';
    }
}

$pageTitle = "Paiement";
include __DIR__ . '/../includes/header.php';
?>

<section class="bg-gradient-to-b from-gray-50/80 to-white min-h-screen py-10 lg:py-14 page-enter">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumb -->
        <div class="flex items-center gap-3 mb-6">
            <a href="<?= APP_URL ?>/client/commande-detail.php?id=<?= $commandeId ?>" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <nav class="text-sm text-gray-400">
                <a href="<?= APP_URL ?>" class="hover:text-gray-600">Accueil</a>
                <span class="mx-1.5">/</span>
                <span class="text-gray-700 font-medium">Paiement #<?= $commandeId ?></span>
            </nav>
        </div>

        <h1 class="text-3xl font-extrabold font-display text-gray-900 mb-2">Paiement Mobile Money</h1>
        <p class="text-gray-500 mb-8">Commande #<?= $commandeId ?></p>

        <?php if ($success): ?>
        <!-- Success State -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center animate-scale-in">
            <div class="w-20 h-20 rounded-2xl bg-green-100 flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold font-display text-gray-900 mb-2">Paiement soumis!</h2>
            <p class="text-gray-500 mb-6"><?= htmlspecialchars($success) ?></p>
            <a href="<?= APP_URL ?>/client/commande-detail.php?id=<?= $commandeId ?>"
               class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold text-sm rounded-xl shadow-lg hover:-translate-y-0.5 transition-all">
                Voir ma commande →
            </a>
        </div>

        <?php else: ?>

        <?php if ($error): ?>
        <div class="mb-6 flex items-start gap-3 p-4 rounded-2xl bg-red-50 border border-red-100 animate-scale-in">
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-red-100 text-red-600 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </span>
            <p class="text-sm text-red-700 font-medium"><?= htmlspecialchars($error) ?></p>
        </div>
        <?php endif; ?>

        <!-- Amount Card -->
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-2xl p-6 mb-6 text-white shadow-lg shadow-green-200/30">
            <p class="text-green-100 text-sm font-medium">Montant à payer</p>
            <p class="text-3xl font-extrabold font-display mt-1"><?= formatPrice($order['total']) ?></p>
            <div class="mt-3 flex items-center gap-2 text-green-200 text-sm">
                <?php if ($order['mode_paiement'] === 'orange_money'): ?>
                <span class="w-6 h-6 rounded-lg bg-white/20 flex items-center justify-center text-xs">🟠</span>
                <span>Orange Money</span>
                <?php else: ?>
                <span class="w-6 h-6 rounded-lg bg-white/20 flex items-center justify-center text-xs">🔵</span>
                <span>MVola</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payment Steps -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-lg font-bold font-display text-gray-900 mb-5">Instructions</h2>

            <div class="space-y-0">
                <!-- Step 1 -->
                <div class="timeline-step completed">
                    <div class="timeline-dot bg-green-500 text-white">1</div>
                    <h3 class="font-bold text-gray-900 text-sm">Effectuez le transfert</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Envoyez <strong class="text-gray-700"><?= formatPrice($order['total']) ?></strong> au numéro :
                        <?php if ($order['mode_paiement'] === 'orange_money'): ?>
                        <span class="inline-block mt-1 font-mono font-bold text-orange-600 bg-orange-50 px-3 py-1 rounded-lg"><?= htmlspecialchars($orangeMoneyNumber) ?></span>
                        <?php else: ?>
                        <span class="inline-block mt-1 font-mono font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-lg"><?= htmlspecialchars($mvolaNumber) ?></span>
                        <?php endif; ?>
                    </p>
                </div>
                <!-- Step 2 -->
                <div class="timeline-step active">
                    <div class="timeline-dot bg-orange-500 text-white">2</div>
                    <h3 class="font-bold text-gray-900 text-sm">Conservez le reçu</h3>
                    <p class="text-sm text-gray-500 mt-1">Prenez une capture d'écran ou photo de la confirmation avec le numéro de transaction.</p>
                </div>
                <!-- Step 3 -->
                <div class="timeline-step pending">
                    <div class="timeline-dot">3</div>
                    <h3 class="font-bold text-gray-900 text-sm">Confirmez ci-dessous</h3>
                    <p class="text-sm text-gray-500 mt-1">Remplissez le formulaire avec le numéro de transaction et le justificatif.</p>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold font-display text-gray-900 mb-5">Confirmer le paiement</h2>

            <form method="POST" enctype="multipart/form-data" class="space-y-5" x-data="{ fileName: '' }">
                <?= csrfInput() ?>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Numéro de transaction <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="numero_transaction" required
                           class="input-modern w-full" placeholder="Ex: TX123456789">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Justificatif de paiement <span class="text-red-400">*</span>
                    </label>
                    <label for="justificatif"
                           class="flex flex-col items-center justify-center border-2 border-dashed border-gray-200 rounded-2xl p-8
                                  hover:border-green-400 hover:bg-green-50/30 transition-all cursor-pointer group">
                        <input type="file" name="justificatif" id="justificatif" accept="image/*,.pdf,application/pdf" required class="hidden"
                               @change="fileName = $event.target.files[0]?.name || ''">
                        <div class="w-14 h-14 rounded-2xl bg-gray-100 group-hover:bg-green-100 flex items-center justify-center mb-3 transition-colors">
                            <svg class="w-6 h-6 text-gray-400 group-hover:text-green-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-500 group-hover:text-green-700 transition-colors" x-text="fileName || 'Cliquez pour sélectionner un fichier'"></p>
                        <p class="text-xs text-gray-400 mt-1">JPG, PNG, GIF ou PDF — max 5 Mo</p>
                    </label>
                </div>

                <button type="submit"
                        class="w-full py-3.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold text-sm
                               rounded-xl shadow-lg shadow-green-200/50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 btn-shine
                               flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Confirmer le paiement
                </button>
            </form>
        </div>

        <!-- Back link -->
        <div class="mt-6 text-center">
            <a href="<?= APP_URL ?>/client/commande-detail.php?id=<?= $commandeId ?>"
               class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-gray-600 transition-colors group">
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour à la commande
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
