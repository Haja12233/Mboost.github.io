<?php
/**
 * M'Boost — Mon Profil — Premium Design
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = APP_URL . '/client/profil.php';
    redirect(APP_URL . '/auth/login.php');
}

$userId = $_SESSION['user_id'];
$success = '';
$error = '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    redirect(APP_URL . '/auth/logout.php');
}

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $adresse_livraison = trim($_POST['adresse_livraison'] ?? '');

    if (empty($nom) || empty($prenom) || empty($telephone)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        $stmt = $pdo->prepare("
            UPDATE users SET nom = :nom, prenom = :prenom, telephone = :telephone,
            adresse_livraison = :adresse, updated_at = NOW() WHERE id = :id
        ");
        try {
            $stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':telephone' => $telephone,
                ':adresse' => $adresse_livraison,
                ':id' => $userId
            ]);
            $success = 'Profil mis à jour avec succès!';
            $_SESSION['user_nom'] = $nom;
            $_SESSION['user_prenom'] = $prenom;

            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch();
        } catch (PDOException $e) {
            $error = 'Une erreur est survenue lors de la mise à jour.';
        }
    }
}

// Change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (strlen($new_password) < 6) {
        $error = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Les nouveaux mots de passe ne correspondent pas.';
    } elseif (!password_verify($current_password, $user['mot_de_passe'])) {
        $error = 'Mot de passe actuel incorrect.';
    } else {
        $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $pdo->prepare("UPDATE users SET mot_de_passe = :password WHERE id = :id");
        $stmt->execute([':password' => $hashedPassword, ':id' => $userId]);
        $success = 'Mot de passe changé avec succès!';
    }
}

// Get order history
$stmt = $pdo->prepare("
    SELECT c.*, COUNT(lc.id) as nb_jus
    FROM commandes c
    LEFT JOIN lignes_commandes lc ON c.id = lc.commande_id
    WHERE c.user_id = :user_id
    GROUP BY c.id
    ORDER BY c.created_at DESC
    LIMIT 10
");
$stmt->execute([':user_id' => $userId]);
$orders = $stmt->fetchAll();

$pageTitle = "Mon Profil";
include __DIR__ . '/../includes/header.php';
?>

<section class="bg-gradient-to-b from-gray-50/80 to-white min-h-screen py-10 lg:py-14 page-enter" 
         x-data="{ activeTab: new URLSearchParams(window.location.search).get('tab') || 'profil' }">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Profile Header -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8 flex flex-col sm:flex-row items-center gap-5">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-white text-3xl font-extrabold font-display shadow-lg shadow-green-200/30">
                <?= mb_strtoupper(mb_substr($user['prenom'], 0, 1)) ?><?= mb_strtoupper(mb_substr($user['nom'], 0, 1)) ?>
            </div>
            <div class="text-center sm:text-left">
                <h1 class="text-2xl font-extrabold font-display text-gray-900"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h1>
                <p class="text-gray-400 text-sm mt-1 flex items-center justify-center sm:justify-start gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <?= htmlspecialchars($user['email']) ?>
                </p>
                <p class="text-xs text-gray-300 mt-1">Membre depuis <?= formatDate($user['created_at'], false) ?></p>
            </div>
            <div class="sm:ml-auto">
                <span class="badge badge-success">Client</span>
            </div>
        </div>

        <!-- Alerts -->
        <?php if ($success): ?>
        <div class="mb-6 flex items-start gap-3 p-4 rounded-2xl bg-green-50 border border-green-100 animate-scale-in">
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-green-100 text-green-600 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </span>
            <p class="text-sm text-green-700 font-medium"><?= htmlspecialchars($success) ?></p>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="mb-6 flex items-start gap-3 p-4 rounded-2xl bg-red-50 border border-red-100 animate-scale-in">
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-red-100 text-red-600 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </span>
            <p class="text-sm text-red-700 font-medium"><?= htmlspecialchars($error) ?></p>
        </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="flex flex-wrap gap-2 mb-8">
            <button @click="activeTab = 'profil'"
                    :class="activeTab === 'profil' ? 'bg-green-600 text-white shadow-lg shadow-green-200/50' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                    class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Mon profil
            </button>
            <button @click="activeTab = 'commandes'"
                    :class="activeTab === 'commandes' ? 'bg-green-600 text-white shadow-lg shadow-green-200/50' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                    class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Mes commandes
                <?php if (!empty($orders)): ?>
                <span class="w-5 h-5 rounded-full bg-white/20 text-[10px] font-bold flex items-center justify-center"><?= count($orders) ?></span>
                <?php endif; ?>
            </button>
            <button @click="activeTab = 'securite'"
                    :class="activeTab === 'securite' ? 'bg-green-600 text-white shadow-lg shadow-green-200/50' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                    class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Sécurité
            </button>
        </div>

        <!-- Tab: Profile -->
        <div x-show="activeTab === 'profil'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8">
                <h2 class="text-lg font-bold font-display text-gray-900 mb-6 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-green-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    Informations personnelles
                </h2>
                <form method="POST" class="space-y-5">
                    <input type="hidden" name="update_profile" value="1">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nom</label>
                            <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required class="input-modern w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Prénom</label>
                            <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required class="input-modern w-full">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
                        <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled
                               class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-gray-400 text-sm cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Téléphone</label>
                        <input type="tel" name="telephone" value="<?= htmlspecialchars($user['telephone']) ?>" required class="input-modern w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Adresse de livraison</label>
                        <textarea name="adresse_livraison" rows="3" class="input-modern w-full resize-none"><?= htmlspecialchars($user['adresse_livraison'] ?? '') ?></textarea>
                    </div>
                    <button type="submit"
                            class="px-8 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold text-sm
                                   rounded-xl shadow-lg shadow-green-200/50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 btn-shine">
                        Mettre à jour
                    </button>
                </form>
            </div>
        </div>

        <!-- Tab: Orders -->
        <div x-show="activeTab === 'commandes'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="display:none;">
            <?php if (empty($orders)): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-blue-100 flex items-center justify-center mx-auto mb-4">
                    <span class="text-3xl">📋</span>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Aucune commande</h3>
                <p class="text-gray-500 text-sm mb-6">Vous n'avez pas encore passé de commande.</p>
                <a href="<?= APP_URL ?>/client/creer-jus.php"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold text-sm rounded-xl shadow-lg hover:-translate-y-0.5 transition-all">
                    Créer mon premier jus →
                </a>
            </div>
            <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($orders as $order): ?>
                <a href="<?= APP_URL ?>/client/commande-detail.php?id=<?= $order['id'] ?>"
                   class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-5 card-hover group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center text-xl flex-shrink-0">🥤</div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <h3 class="font-bold text-gray-900 text-sm">Commande #<?= $order['id'] ?></h3>
                                <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold <?= getOrderStatusColor($order['statut']) ?>">
                                    <?= getOrderStatusLabel($order['statut']) ?>
                                </span>
                            </div>
                            <p class="text-xs text-gray-400">
                                <?= $order['nb_jus'] ?> jus • <?= formatPrice($order['total']) ?> • <?= formatDate($order['created_at']) ?>
                            </p>
                        </div>
                        <svg class="w-5 h-5 text-gray-300 group-hover:text-green-500 group-hover:translate-x-1 transition-all flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Tab: Security -->
        <div x-show="activeTab === 'securite'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="display:none;">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8">
                <h2 class="text-lg font-bold font-display text-gray-900 mb-6 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-orange-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    Changer le mot de passe
                </h2>
                <form method="POST" class="space-y-5 max-w-md">
                    <input type="hidden" name="change_password" value="1">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Mot de passe actuel</label>
                        <input type="password" name="current_password" required class="input-modern w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nouveau mot de passe</label>
                        <input type="password" name="new_password" required minlength="6" class="input-modern w-full">
                        <p class="text-xs text-gray-400 mt-1">Minimum 6 caractères</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Confirmer le nouveau mot de passe</label>
                        <input type="password" name="confirm_password" required class="input-modern w-full">
                    </div>
                    <button type="submit"
                            class="px-8 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white font-bold text-sm
                                   rounded-xl shadow-lg shadow-orange-200/50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
                        Changer le mot de passe
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
