<?php
/**
 * M'Boost — Conseils Santé — Premium Design
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

$isLoggedIn = isLoggedIn();

$category = $_GET['categorie'] ?? '';
$organ = $_GET['organe'] ?? '';
$search = $_GET['search'] ?? '';

$where = ["statut = 'publie'"];
$params = [];

if ($category) {
    $where[] = "JSON_CONTAINS(categories, :cat)";
    $params[':cat'] = json_encode($category);
}

if ($organ) {
    $where[] = "organes_cibles LIKE :organ";
    $params[':organ'] = "%$organ%";
}

if ($search) {
    $where[] = "(titre LIKE :search OR description LIKE :search)";
    $params[':search'] = "%$search%";
}

$whereClause = implode(' AND ', $where);

$stmt = $pdo->prepare("
    SELECT * FROM annonces_sante
    WHERE $whereClause
    ORDER BY epingle DESC, date_publication DESC
");
$stmt->execute($params);
$annonces = $stmt->fetchAll();

$categories = HEALTH_CATEGORIES;
$organs = HEALTH_ORGANS;

$pageTitle = "Conseils Santé";
include __DIR__ . '/../includes/header.php';
?>

<section class="min-h-screen"
         style="background-image: linear-gradient(rgba(255,255,255,0.72), rgba(255,255,255,0.78)), url('https://www.mairie-elbeuf.fr/wp-content/uploads/2025/01/sante-elbeuf.jpg'); background-size: 100% 100%; background-position: center center; background-repeat: no-repeat;">
    <!-- Hero Mini -->
    <div class="bg-gradient-to-br from-green-600 via-emerald-600 to-teal-600 py-16 relative overflow-hidden">
        <div class="absolute inset-0 overflow-hidden pointer-events-none select-none">
            <span class="absolute text-5xl opacity-10 float-1" style="top:10%;left:5%">🍊</span>
            <span class="absolute text-4xl opacity-10 float-3" style="top:20%;right:10%">🥬</span>
            <span class="absolute text-6xl opacity-8 float-5" style="bottom:5%;left:15%">🥝</span>
        </div>
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <span class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-white/10 text-green-100 text-xs font-bold uppercase tracking-widest mb-4 backdrop-blur-sm">
                💚 Bien-être & Nutrition
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold font-display text-white">Nos Conseils Santé</h1>
            <p class="mt-4 text-green-100/70 max-w-xl mx-auto text-lg">
                Découvrez les bienfaits des jus frais et des combinaisons recommandées pour votre bien-être.
            </p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Filters -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 -mt-10 relative z-10 mb-10" x-data="{ showFilters: true }">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold font-display text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filtres
                </h2>
                <button @click="showFilters = !showFilters" class="text-gray-400 hover:text-gray-600 transition-colors sm:hidden">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>

            <form method="GET" x-show="showFilters" x-transition class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Catégorie</label>
                    <select name="categorie" class="input-modern w-full text-sm">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $key => $data): ?>
                        <option value="<?= $key ?>" <?= $category === $key ? 'selected' : '' ?>><?= $data['emoji'] ?> <?= $data['label'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Organe ciblé</label>
                    <select name="organe" class="input-modern w-full text-sm">
                        <option value="">Tous les organes</option>
                        <?php foreach ($organs as $key => $label): ?>
                        <option value="<?= $key ?>" <?= $organ === $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Recherche</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                           class="input-modern w-full text-sm" placeholder="Rechercher un conseil...">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold text-sm
                                   rounded-xl shadow-lg shadow-green-200/50 hover:shadow-xl transition-all duration-200">
                        Filtrer
                    </button>
                    <?php if ($category || $organ || $search): ?>
                    <a href="<?= APP_URL ?>/client/conseils-sante.php"
                       class="px-4 py-3 bg-gray-100 text-gray-600 font-semibold text-sm rounded-xl hover:bg-gray-200 transition-colors">
                        ✕
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Results count -->
        <div class="mb-6 flex items-center justify-between">
            <p class="text-sm text-gray-500">
                <span class="font-bold text-gray-900"><?= count($annonces) ?></span> conseil<?= count($annonces) > 1 ? 's' : '' ?> trouvé<?= count($annonces) > 1 ? 's' : '' ?>
            </p>
        </div>

        <!-- Articles Grid -->
        <?php if (empty($annonces)): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                <span class="text-3xl">🔍</span>
            </div>
            <h2 class="text-xl font-bold font-display text-gray-800 mb-2">Aucun résultat</h2>
            <p class="text-gray-500 text-sm mb-6">Essayez d'autres critères de recherche ou réinitialisez les filtres.</p>
            <a href="<?= APP_URL ?>/client/conseils-sante.php"
               class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold text-sm rounded-xl shadow-lg hover:-translate-y-0.5 transition-all">
                Voir tous les conseils
            </a>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            <?php foreach ($annonces as $annonce): ?>
            <article class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden card-hover hover:shadow-xl">
                <!-- Image -->
                <div class="h-48 bg-gradient-to-br from-green-100 to-emerald-50 flex items-center justify-center relative overflow-hidden">
                    <?php if ($annonce['image_url']): ?>
                    <img src="<?= UPLOAD_URL . 'annonces/' . htmlspecialchars($annonce['image_url']) ?>"
                         alt="<?= htmlspecialchars($annonce['titre']) ?>"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <?php else: ?>
                    <span class="text-6xl opacity-50">🌿</span>
                    <?php endif; ?>

                    <?php if ($annonce['epingle']): ?>
                    <div class="absolute top-3 left-3">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-yellow-400 text-yellow-900 text-[10px] font-bold rounded-lg shadow-md">
                            📌 Épinglé
                        </span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="p-5">
                    <!-- Categories -->
                    <?php
                    $cats = json_decode($annonce['categories'] ?? '[]', true);
                    if (!empty($cats)): ?>
                    <div class="flex flex-wrap gap-1.5 mb-3">
                        <?php foreach (array_slice($cats, 0, 3) as $cat): ?>
                        <span class="badge badge-success"><?= $categories[$cat]['emoji'] ?? '' ?> <?= $categories[$cat]['label'] ?? $cat ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Title -->
                    <h2 class="text-lg font-bold font-display text-gray-900 mb-2 group-hover:text-green-700 transition-colors line-clamp-2">
                        <?= htmlspecialchars($annonce['titre']) ?>
                    </h2>

                    <!-- Description -->
                    <p class="text-sm text-gray-500 mb-4 leading-relaxed line-clamp-3">
                        <?= htmlspecialchars(mb_substr(strip_tags($annonce['description'] ?? ''), 0, 150)) ?><?= mb_strlen($annonce['description'] ?? '') > 150 ? '...' : '' ?>
                    </p>

                    <!-- Composition Preview -->
                    <?php if ($annonce['composition_jus']):
                        $comp = json_decode($annonce['composition_jus'], true);
                        if ($comp):
                    ?>
                    <div class="bg-green-50 rounded-xl p-3 mb-4">
                        <p class="text-[10px] font-bold text-green-600 uppercase tracking-widest mb-1.5">Composition recommandée</p>
                        <ul class="text-sm text-green-800 space-y-0.5">
                            <?php foreach (array_slice($comp, 0, 3) as $ing): ?>
                            <li class="flex items-center gap-1.5">
                                <span class="w-1 h-1 rounded-full bg-green-400"></span>
                                <?= htmlspecialchars($ing['nom']) ?> (<?= $ing['quantite'] ?>)
                            </li>
                            <?php endforeach; ?>
                            <?php if (count($comp) > 3): ?>
                            <li class="text-green-500 text-xs italic">+ <?= count($comp) - 3 ?> autres...</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <?php endif; endif; ?>

                    <!-- Footer -->
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <span class="text-xs text-gray-400">
                            <?= formatDate($annonce['date_publication'] ?? $annonce['created_at'], false) ?>
                        </span>
                        <?php if ($isLoggedIn): ?>
                        <a href="<?= APP_URL ?>/client/creer-jus.php?recette=<?= $annonce['id'] ?>"
                           class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white text-xs font-bold rounded-xl
                                  shadow-md shadow-orange-200/50 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                            🥤 Créer ce jus
                        </a>
                        <?php else: ?>
                        <a href="<?= APP_URL ?>/auth/login.php?redirect=client/conseils-sante.php"
                           class="text-sm text-green-600 hover:text-green-700 font-semibold transition-colors">
                            Se connecter →
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- CTA Section -->
        <div class="mt-16 relative overflow-hidden rounded-3xl">
            <div class="gradient-hero-mesh py-14 px-8 md:px-14 text-center relative">
                <div class="absolute inset-0 overflow-hidden pointer-events-none select-none">
                    <span class="absolute text-4xl opacity-10 float-1" style="top:10%;left:5%">🍎</span>
                    <span class="absolute text-5xl opacity-10 float-3" style="bottom:10%;right:5%">🥕</span>
                </div>
                <div class="relative z-10">
                    <h2 class="text-2xl md:text-3xl font-extrabold font-display text-white mb-4">Prêt à créer votre propre jus?</h2>
                    <p class="text-green-100/70 mb-8 max-w-xl mx-auto">
                        Utilisez notre configurateur pour créer un jus personnalisé adapté à vos besoins nutritionnels.
                    </p>
                    <a href="<?= APP_URL ?>/client/creer-jus.php"
                       class="inline-flex items-center gap-2 px-8 py-4 bg-white text-green-700 font-bold text-sm rounded-2xl
                              shadow-xl shadow-black/10 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 btn-shine">
                        Créer mon jus maintenant
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
