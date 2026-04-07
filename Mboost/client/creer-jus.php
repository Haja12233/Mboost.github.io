<?php
/**
 * M'Boost — Créer mon jus (Juice Builder)
 * Clients select ingredients by category, adjust quantities, pick size, see live total.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

/* ── Fetch active ingredients grouped by category ───────────── */
try {
    $stmt = $pdo->query("SELECT id, nom, categorie, description, prix, emoji, image FROM ingredients WHERE actif = 1 ORDER BY categorie, nom");
    $allIngredients = $stmt->fetchAll();
} catch (PDOException $e) {
    $allIngredients = [];
}

$grouped = [];
foreach ($allIngredients as $ing) {
    $grouped[$ing['categorie']][] = $ing;
}

$catLabels = [
    'fruits'  => ['label' => 'Fruits',  'icon' => '🍎'],
    'legumes' => ['label' => 'Légumes', 'icon' => '🥬'],
    'graines' => ['label' => 'Graines', 'icon' => '🌰'],
];

/* ── Sizes from config ──────────────────────────────────────── */
$sizes = JUICE_SIZES; // ['petit'=>[..], 'moyen'=>[..], 'grand'=>[..]]

$pageTitle = "Créer mon jus";
include __DIR__ . '/../includes/header.php';
?>

<style>
    .ing-card { transition: all .2s ease; border: 2px solid transparent; }
    .ing-card:hover { border-color: #86efac; }
    .ing-card.selected { border-color: #22c55e; background: #f0fdf4; }
    .qty-control { display: inline-flex; align-items: center; border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; }
    .qty-control button { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border: none; background: #f9fafb; cursor: pointer; font-weight: bold; font-size: 16px; }
    .qty-control button:hover { background: #e5e7eb; }
    .qty-control span { width: 32px; text-align: center; font-weight: 700; font-size: 14px; }
    .size-btn { transition: all .2s; }
    .size-btn.active { border-color: #22c55e; background: #f0fdf4; color: #15803d; box-shadow: 0 0 0 1px #22c55e; }
    .summary-row { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; font-size: 14px; }
</style>

<section class="py-10 lg:py-14 min-h-screen"
         style="background-image: linear-gradient(rgba(255,255,255,0.72), rgba(255,255,255,0.78)), url('https://www.coeuretavc.ca/-/media/images/articles/cups-of-juice-1920x1080.jpg?rev=5d25f22395c74de3924b73237cf008b7'); background-size: 100% 100%; background-position: center center; background-repeat: no-repeat;"
         x-data="juiceBuilder()" x-cloak>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Header -->
    <div class="mb-10">
        <h1 class="text-3xl sm:text-4xl font-extrabold font-display text-gray-900">Créez votre jus 🥤</h1>
        <p class="mt-2 text-gray-500 max-w-xl">Cochez vos ingrédients, ajustez les quantités, choisissez la taille et ajoutez au panier.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- ═══════════ LEFT: Ingredient Selection ═══════════ -->
        <div class="lg:col-span-2 space-y-8">

            <?php if (empty($allIngredients)): ?>
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-6 rounded-xl">
                    ⚠️ Aucun ingrédient disponible pour le moment.
                </div>
            <?php endif; ?>

            <?php foreach ($catLabels as $catKey => $catMeta):
                if (!isset($grouped[$catKey])) continue;
                $items = $grouped[$catKey];
            ?>
            <!-- Category: <?= $catMeta['label'] ?> -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-2xl"><?= $catMeta['icon'] ?></span>
                    <h2 class="text-xl font-bold font-display text-gray-900"><?= $catMeta['label'] ?></h2>
                    <span class="text-sm text-gray-400 font-medium">(<?= count($items) ?>)</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <?php foreach ($items as $ing):
                        $ingId = (int) $ing['id'];
                        $prix  = (float) $ing['prix'];
                    ?>
                    <div class="ing-card bg-white rounded-xl p-4 shadow-sm cursor-pointer flex items-center gap-4"
                         :class="{ 'selected': selected[<?= $ingId ?>] }"
                         @click="toggle(<?= $ingId ?>, '<?= addslashes($ing['nom']) ?>', '<?= $ing['emoji'] ?? '🍃' ?>', <?= $prix ?>)">

                        <!-- Checkbox -->
                        <div class="flex-shrink-0 w-6 h-6 rounded-md border-2 flex items-center justify-center transition-all"
                             :class="selected[<?= $ingId ?>] ? 'bg-green-500 border-green-500' : 'border-gray-300'">
                            <svg x-show="selected[<?= $ingId ?>]" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>

                        <!-- Emoji -->
                        <div class="text-3xl flex-shrink-0">
                            <?php
                            $emoji = $ing['emoji'] ?? '🍃';
                            if (mb_strtolower($ing['nom']) === 'gingembre') {
                                $emoji = '🧄'; // Emoji ail pour gingembre
                            } elseif (mb_strtolower($ing['nom']) === 'betterave') {
                                $emoji = '🍠'; // Emoji patate douce pour betterave
                            }
                            echo $emoji;
                            ?>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-800"><?= htmlspecialchars($ing['nom']) ?></p>
                            <?php if (!empty($ing['description'])): ?>
                                <p class="text-xs text-gray-400 truncate"><?= htmlspecialchars($ing['description']) ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Price -->
                        <div class="text-right flex-shrink-0">
                            <p class="font-bold text-green-600 text-sm"><?= formatPrice($prix) ?></p>
                        </div>
                    </div>

                    <!-- Quantity row (visible when selected) -->
                    <template x-if="selected[<?= $ingId ?>]">
                        <div class="sm:col-span-2 -mt-1 ml-10 mr-4 py-2 flex items-center gap-3 animate-fade-in" @click.stop>
                            <span class="text-sm text-gray-500">Quantité :</span>
                            <div class="qty-control">
                                <button @click="adjustQty(<?= $ingId ?>, -1)">−</button>
                                <span x-text="quantities[<?= $ingId ?>] || 1"></span>
                                <button @click="adjustQty(<?= $ingId ?>, 1)">+</button>
                            </div>
                            <span class="text-sm font-medium text-gray-700" x-text="formatAr((quantities[<?= $ingId ?>] || 1) * <?= $prix ?>)"></span>
                            <button @click="remove(<?= $ingId ?>)" class="ml-auto text-red-400 hover:text-red-600 text-xs font-medium">Retirer</button>
                        </div>
                    </template>

                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ═══════════ RIGHT: Summary Panel ═══════════ -->
        <div class="lg:col-span-1">
            <div class="sticky top-24">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-xl overflow-hidden">

                    <!-- Header -->
                    <div class="bg-gradient-to-r from-gray-900 to-gray-800 text-white p-5">
                        <h2 class="font-bold text-lg flex items-center gap-2">🛒 Récapitulatif</h2>
                        <p class="text-sm text-gray-300 mt-1" x-text="countSelected() + ' ingrédient(s) sélectionné(s)'"></p>
                    </div>

                    <div class="p-5 space-y-5">

                        <!-- Size Selection -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Taille du jus</label>
                            <div class="grid grid-cols-3 gap-2">
                                <?php foreach ($sizes as $sKey => $sInfo): ?>
                                <button @click="size = '<?= $sKey ?>'"
                                        :class="size === '<?= $sKey ?>' ? 'active' : ''"
                                        class="size-btn border-2 border-gray-200 rounded-xl py-3 px-1 text-center">
                                    <div class="text-lg"><?= $sKey === 'petit' ? '🥛' : ($sKey === 'moyen' ? '🥤' : '🍹') ?></div>
                                    <div class="text-xs font-bold mt-1"><?= $sInfo['label'] ?></div>
                                    <div class="text-[10px] text-gray-400"><?= $sInfo['ml'] ?>ml</div>
                                </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Selected Ingredients List -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Votre composition</label>

                            <div x-show="countSelected() === 0" class="text-center py-8 text-gray-300 border-2 border-dashed border-gray-100 rounded-xl">
                                <p class="text-3xl mb-2">🥗</p>
                                <p class="text-sm">Sélectionnez des ingrédients à gauche</p>
                            </div>

                            <div class="space-y-1 max-h-48 overflow-y-auto" x-show="countSelected() > 0">
                                <template x-for="item in getSelectedList()" :key="item.id">
                                    <div class="summary-row border-b border-gray-50">
                                        <div class="flex items-center gap-2">
                                            <span x-text="item.emoji" class="text-sm"></span>
                                            <span class="text-gray-700 font-medium" x-text="item.nom"></span>
                                            <span class="text-xs text-gray-400" x-text="'×' + item.qty"></span>
                                        </div>
                                        <span class="font-semibold text-gray-900 text-sm" x-text="formatAr(item.subtotal)"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="bg-gray-50 rounded-xl p-4 space-y-2 border border-gray-100">
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>Sous-total</span>
                                <span x-text="formatAr(subtotal())"></span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>Taille</span>
                                <span class="bg-green-100 text-green-800 text-[10px] px-1.5 py-0.5 rounded font-bold" x-text="'×' + sizeMultiplier()"></span>
                            </div>
                            <div class="border-t border-gray-200 my-1"></div>
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-gray-800">Total</span>
                                <span class="text-2xl font-black text-green-600" x-text="formatAr(totalPrice())"></span>
                            </div>
                        </div>

                        <!-- Add to Cart Button -->
                        <button @click="addToCart()"
                                :disabled="countSelected() === 0 || loading"
                                :class="countSelected() > 0 ? 'bg-gradient-to-r from-green-600 to-emerald-600 hover:shadow-xl hover:-translate-y-0.5' : 'bg-gray-300 cursor-not-allowed'"
                                class="w-full py-4 text-white font-bold text-sm rounded-xl shadow-lg transition-all duration-300 flex items-center justify-center gap-2">
                            <template x-if="!loading">
                                <span>🛒 Ajouter au panier</span>
                            </template>
                            <template x-if="loading">
                                <span class="animate-pulse">Ajout en cours…</span>
                            </template>
                        </button>

                        <!-- Success Message -->
                        <div x-show="successMessage" x-transition
                             class="bg-green-50 border border-green-200 text-green-700 text-sm p-3 rounded-xl text-center font-medium">
                            <span x-text="successMessage"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</section>

<script>
/* ── Juice sizes from PHP config (safe) ─────────────────── */
const JUICE_SIZES = <?= json_encode($sizes, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
const CSRF_TOKEN = '<?= htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8') ?>';

function juiceBuilder() {
    return {
        selected: {},    /* { ingId: true } */
        quantities: {},  /* { ingId: int } */
        names: {},       /* { ingId: 'Carotte' } */
        emojis: {},      /* { ingId: '🥕' } */
        prices: {},      /* { ingId: 300 } */
        size: 'moyen',
        loading: false,
        successMessage: '',

        toggle(id, nom, emoji, prix) {
            if (this.selected[id]) {
                this.remove(id);
            } else {
                this.selected[id] = true;
                this.quantities[id] = 1;
                this.names[id] = nom;
                this.emojis[id] = emoji;
                this.prices[id] = prix;
            }
        },

        remove(id) {
            delete this.selected[id];
            delete this.quantities[id];
            delete this.names[id];
            delete this.emojis[id];
            delete this.prices[id];
            /* Force Alpine reactivity */
            this.selected = { ...this.selected };
        },

        adjustQty(id, delta) {
            let q = (this.quantities[id] || 1) + delta;
            if (q <= 0) {
                this.remove(id);
            } else {
                this.quantities[id] = Math.min(q, 20);
            }
        },

        countSelected() {
            return Object.keys(this.selected).length;
        },

        getSelectedList() {
            return Object.keys(this.selected).map(id => ({
                id: parseInt(id),
                nom: this.names[id],
                emoji: this.emojis[id],
                qty: this.quantities[id] || 1,
                prix: this.prices[id],
                subtotal: (this.quantities[id] || 1) * this.prices[id]
            }));
        },

        subtotal() {
            return this.getSelectedList().reduce((s, i) => s + i.subtotal, 0);
        },

        sizeMultiplier() {
            return JUICE_SIZES[this.size] ? JUICE_SIZES[this.size].multiplier : 1;
        },

        totalPrice() {
            return Math.round(this.subtotal() * this.sizeMultiplier());
        },

        formatAr(amount) {
            return new Intl.NumberFormat('fr-FR').format(Math.round(amount)) + ' Ar';
        },

        async addToCart() {
            if (this.countSelected() === 0) return;
            this.loading = true;
            this.successMessage = '';

            const ingredients = this.getSelectedList().map(i => ({
                id: i.id, nom: i.nom, quantite: i.qty, prix: i.prix
            }));

            try {
                const resp = await fetch('<?= APP_URL ?>/api/cart-add.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        name: 'Jus personnalisé',
                        size: this.size,
                        csrf_token: CSRF_TOKEN,
                        ingredients: ingredients
                    })
                });

                const result = await resp.json();

                if (result.success) {
                    this.successMessage = '✅ Jus ajouté au panier !';
                    this.selected = {};
                    this.quantities = {};
                    this.names = {};
                    this.emojis = {};
                    this.prices = {};

                    /* Update cart badge in header */
                    const badge = document.querySelector('[aria-label*="Panier"]');
                    if (badge) {
                        setTimeout(() => location.reload(), 1200);
                    }
                } else {
                    alert(result.error || 'Erreur lors de l\'ajout.');
                }
            } catch (e) {
                <?php if (!isLoggedIn()): ?>
                if (confirm('Vous devez être connecté. Aller à la page de connexion ?')) {
                    window.location.href = '<?= APP_URL ?>/auth/login.php';
                }
                <?php else: ?>
                alert('Erreur réseau. Veuillez réessayer.');
                <?php endif; ?>
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
