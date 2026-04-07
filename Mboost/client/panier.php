<?php
/**
 * M'Boost — Panier (Cart) — Premium Design
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = APP_URL . '/client/panier.php';
    redirect(APP_URL . '/auth/login.php');
}

$userId = $_SESSION['user_id'];
$deliveryFee = (float) getParam('frais_livraison', 'frais_livraison', DELIVERY_FEE_DEFAULT);

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart = $_SESSION['cart'];
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['prix'] * $item['quantite'];
}
$total = $subtotal + $deliveryFee;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCSRFOrAbort();
    if (isset($_POST['update_quantity'])) {
        $key = $_POST['cart_key'] ?? '';
        $quantity = (int) ($_POST['quantity'] ?? 1);
        if (isset($cart[$key])) {
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$key]);
            } else {
                $_SESSION['cart'][$key]['quantite'] = $quantity;
            }
        }
        redirect(APP_URL . '/client/panier.php');
    }

    if (isset($_POST['remove_item'])) {
        $key = $_POST['cart_key'] ?? '';
        if (isset($cart[$key])) {
            unset($_SESSION['cart'][$key]);
        }
        redirect(APP_URL . '/client/panier.php');
    }

    if (isset($_POST['checkout'])) {
        redirect(APP_URL . '/client/commande.php');
    }
}

$pageTitle = "Mon Panier";
include __DIR__ . '/../includes/header.php';
?>

<section class="bg-gradient-to-b from-gray-50/80 to-white min-h-screen py-10 lg:py-14 page-enter">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <a href="<?= APP_URL ?>/client/creer-jus.php" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <nav class="text-sm text-gray-400">
                    <a href="<?= APP_URL ?>" class="hover:text-gray-600">Accueil</a>
                    <span class="mx-1.5">/</span>
                    <span class="text-gray-700 font-medium">Mon Panier</span>
                </nav>
            </div>
            <h1 class="text-3xl font-extrabold font-display text-gray-900 flex items-center gap-3">
                Mon Panier
                <?php if (!empty($cart)): ?>
                <span class="badge badge-success"><?= count($cart) ?> article<?= count($cart) > 1 ? 's' : '' ?></span>
                <?php endif; ?>
            </h1>
        </div>

        <?php if (empty($cart)): ?>
        <!-- Empty Cart -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center max-w-lg mx-auto">
            <div class="w-20 h-20 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-6">
                <span class="text-4xl">🛒</span>
            </div>
            <h2 class="text-xl font-bold font-display text-gray-800 mb-2">Votre panier est vide</h2>
            <p class="text-gray-500 text-sm mb-8">Découvrez nos ingrédients et créez votre jus personnalisé!</p>
            <a href="<?= APP_URL ?>/client/creer-jus.php"
               class="inline-flex items-center gap-2 px-8 py-3.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold text-sm
                      rounded-xl shadow-lg shadow-green-200/50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 btn-shine">
                Créer mon jus
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>

        <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2 space-y-4">
                <?php foreach ($cart as $key => $item): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col sm:flex-row gap-5 card-hover">
                    <!-- Juice Visual -->
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center text-3xl flex-shrink-0 shadow-md shadow-green-200/30">
                        🥤
                    </div>

                    <!-- Details -->
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold font-display text-gray-900"><?= htmlspecialchars($item['nom']) ?></h3>
                        <p class="text-sm text-gray-400 mt-0.5">
                            Taille: <span class="capitalize font-medium text-gray-500"><?= $item['taille'] ?></span>
                            • <?= formatPrice($item['prix']) ?>/unité
                        </p>

                        <!-- Quantity Controls -->
                        <form method="POST" class="mt-3 flex items-center gap-3 flex-wrap">
                            <?= csrfInput() ?>
                            <input type="hidden" name="cart_key" value="<?= htmlspecialchars($key) ?>">
                            <input type="hidden" name="update_quantity" value="1">

                            <div class="flex items-center bg-gray-100 rounded-xl overflow-hidden">
                                <button type="submit" name="quantity" value="<?= $item['quantite'] - 1 ?>"
                                        class="w-9 h-9 flex items-center justify-center text-gray-500 hover:text-green-600 hover:bg-green-50 transition-colors font-bold">−</button>
                                <span class="w-10 text-center text-sm font-bold text-gray-800"><?= $item['quantite'] ?></span>
                                <button type="submit" name="quantity" value="<?= $item['quantite'] + 1 ?>"
                                        class="w-9 h-9 flex items-center justify-center text-gray-500 hover:text-green-600 hover:bg-green-50 transition-colors font-bold">+</button>
                            </div>

                            <button type="submit" name="remove_item" value="1"
                                    class="inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-red-500 transition-colors font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Supprimer
                            </button>
                        </form>
                    </div>

                    <!-- Price -->
                    <div class="text-right flex-shrink-0">
                        <p class="text-lg font-extrabold font-display text-green-600">
                            <?= formatPrice($item['prix'] * $item['quantite']) ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Continue Shopping -->
                <a href="<?= APP_URL ?>/client/creer-jus.php"
                   class="inline-flex items-center gap-2 text-sm font-semibold text-green-600 hover:text-green-700 transition-colors group mt-2">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Ajouter un autre jus
                </a>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="sticky top-24 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 overflow-hidden">
                    <h2 class="text-lg font-bold font-display text-gray-900 mb-5">Récapitulatif</h2>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-gray-500">
                            <span>Sous-total (<?= count($cart) ?> article<?= count($cart) > 1 ? 's' : '' ?>)</span>
                            <span class="font-medium text-gray-700"><?= formatPrice($subtotal) ?></span>
                        </div>
                        <div class="flex justify-between text-gray-500">
                            <span>Frais de livraison</span>
                            <span class="font-medium text-gray-700"><?= formatPrice($deliveryFee) ?></span>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 my-5"></div>

                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-gray-900">Total</span>
                        <span class="text-2xl font-extrabold font-display text-green-600"><?= formatPrice($total) ?></span>
                    </div>

                    <form method="POST" class="mt-6">
                        <?= csrfInput() ?>
                        <button type="submit" name="checkout" value="1"
                                class="w-full py-3.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold text-sm
                                       rounded-xl shadow-lg shadow-green-200/50 hover:shadow-xl hover:shadow-green-300/50
                                       hover:-translate-y-0.5 transition-all duration-300 btn-shine flex items-center justify-center gap-2">
                            Valider la commande
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </button>
                    </form>

                    <div class="mt-4 flex items-center justify-center gap-2 text-xs text-gray-400">
                        <svg class="w-3.5 h-3.5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        Paiement sécurisé par Mobile Money
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
