<?php
/**
 * M'Boost â€” Admin Annonces (Health Tips) â€” Premium Design
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

if (!isAdminLoggedIn()) {
    redirect(APP_URL . '/auth/admin.php');
}

$success = '';
$error = '';
$actionCsrf = (string) ($_GET['csrf_token'] ?? '');

// Handle delete
if (isset($_GET['delete'])) {
    if (!verifyCSRF($actionCsrf)) {
        http_response_code(419);
        die('Requete invalide (CSRF).');
    }
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM annonces_sante WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $success = 'Annonce supprimÃ©e!';
}

// Handle publish/unpublish
if (isset($_GET['toggle_publie'])) {
    if (!verifyCSRF($actionCsrf)) {
        http_response_code(419);
        die('Requete invalide (CSRF).');
    }
    $id = (int) $_GET['toggle_publie'];
    // Toggle between 'publie' and 'brouillon'
    $stmt = $pdo->prepare("UPDATE annonces_sante SET statut = IF(statut='publie', 'brouillon', 'publie') WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $success = 'Statut mis Ã  jour!';
}

// Handle pin/unpin
if (isset($_GET['toggle_epingle'])) {
    if (!verifyCSRF($actionCsrf)) {
        http_response_code(419);
        die('Requete invalide (CSRF).');
    }
    $id = (int) $_GET['toggle_epingle'];
    $stmt = $pdo->prepare("UPDATE annonces_sante SET epingle = NOT epingle WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $success = 'Statut Ã©pinglÃ© mis Ã  jour!';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCSRFOrAbort();
    $id = $_POST['id'] ?? '';
    $titre = trim($_POST['titre'] ?? '');
    $contenu = trim($_POST['contenu'] ?? ''); // Maps to description
    $categorie = $_POST['categorie'] ?? '';   // Maps to categories (JSON)
    $organe_cible = $_POST['organe_cible'] ?? ''; // Maps to organes_cibles
    $composition = trim($_POST['composition'] ?? ''); // Maps to conseils_consommation (as composition field is complex JSON, we'll use conseils or text)
    // Actually, schema has `composition_jus` (JSON) and `conseils_consommation` (TEXT).
    // Let's store the composition text in `conseils_consommation` for now as a simple workaround, 
    // or properly format it. The user form assumes text.
    
    $publie = isset($_POST['publie']) ? 'publie' : 'brouillon';
    $epingle = isset($_POST['epingle']) ? 1 : 0;

    if (empty($titre) || empty($contenu)) {
        $error = 'Le titre et le contenu sont obligatoires.';
    } else {
        $imageName = null;
        if (!empty($_FILES['image']['tmp_name'])) {
            $upload = uploadFile($_FILES['image'], UPLOAD_DIR . 'annonces/');
            if (!$upload['success']) {
                $error = $upload['error'];
            } else {
                $imageName = $upload['filename'];
            }
        }

        // Convert simple string category to JSON array for schema
        $categoriesJson = json_encode([$categorie]);

        if ($error === '' && $id) {
            $sql = "UPDATE annonces_sante SET titre = :titre, description = :contenu, categories = :categories, organes_cibles = :organe, conseils_consommation = :composition, statut = :statut, epingle = :epingle";
            $params = [
                ':id' => $id, 
                ':titre' => $titre, 
                ':contenu' => $contenu, 
                ':categories' => $categoriesJson, 
                ':organe' => $organe_cible, 
                ':composition' => $composition, 
                ':statut' => $publie, 
                ':epingle' => $epingle
            ];
            if ($imageName) { $sql .= ", image_url = :image"; $params[':image'] = $imageName; }
            $sql .= " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $success = 'Annonce mise Ã  jour avec succÃ¨s!';
        } elseif ($error === '') {
            $stmt = $pdo->prepare("INSERT INTO annonces_sante (titre, description, categories, organes_cibles, conseils_consommation, statut, epingle, image_url, created_by) VALUES (:titre, :contenu, :categories, :organe, :composition, :statut, :epingle, :image, :admin)");
            $stmt->execute([
                ':titre' => $titre, 
                ':contenu' => $contenu, 
                ':categories' => $categoriesJson, 
                ':organe' => $organe_cible, 
                ':composition' => $composition, 
                ':statut' => $publie, 
                ':epingle' => $epingle, 
                ':image' => $imageName, 
                ':admin' => $_SESSION['admin_id'] ?? 1
            ]);
            $success = 'Annonce crÃ©Ã©e avec succÃ¨s!';
        }
    }
}

// Edit mode
$editMode = isset($_GET['edit']);
$editAnnonce = null;
if ($editMode) {
    $stmt = $pdo->prepare("SELECT * FROM annonces_sante WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => (int) $_GET['edit']]);
    $editAnnonce = $stmt->fetch();
}

// Fetch all
$stmt = $pdo->query("SELECT a.*, ad.nom as admin_nom, ad.prenom as admin_prenom FROM annonces_sante a LEFT JOIN admins ad ON a.created_by = ad.id ORDER BY a.epingle DESC, a.created_at DESC");
$annonces = $stmt->fetchAll();

$pageTitle = "Gestion des annonces";
$activePage = "annonces";
include __DIR__ . '/includes/admin-layout-top.php';
?>

<?php if ($success): ?>
<div class="flex items-start gap-3 p-4 rounded-2xl bg-green-50 border border-green-100 animate-scale-in">
    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-green-100 text-green-600 flex-shrink-0"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
    <p class="text-sm text-green-700 font-medium"><?= htmlspecialchars($success) ?></p>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="flex items-start gap-3 p-4 rounded-2xl bg-red-50 border border-red-100 animate-scale-in">
    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-red-100 text-red-600 flex-shrink-0"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></span>
    <p class="text-sm text-red-700 font-medium"><?= htmlspecialchars($error) ?></p>
</div>
<?php endif; ?>

<!-- Stats -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center shadow-md shadow-green-200/40"><span class="text-base">ðŸ“¢</span></div>
            <div><p class="text-xs text-gray-400 font-medium">Total</p><p class="text-xl font-extrabold font-display text-gray-900"><?= count($annonces) ?></p></div>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-400 to-blue-500 flex items-center justify-center shadow-md shadow-blue-200/40"><span class="text-base">âœ…</span></div>
            <div><p class="text-xs text-gray-400 font-medium">PubliÃ©es</p><p class="text-xl font-extrabold font-display text-gray-900"><?= count(array_filter($annonces, fn($a) => $a['statut'] === 'publie')) ?></p></div>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-yellow-400 to-amber-500 flex items-center justify-center shadow-md shadow-yellow-200/40"><span class="text-base">ðŸ“Œ</span></div>
            <div><p class="text-xs text-gray-400 font-medium">Ã‰pinglÃ©es</p><p class="text-xl font-extrabold font-display text-gray-900"><?= count(array_filter($annonces, fn($a) => $a['epingle'])) ?></p></div>
        </div>
    </div>
</div>

<!-- Add/Edit Form -->
<div id="add-form" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <h2 class="text-lg font-bold font-display text-gray-900 mb-5 flex items-center gap-2">
        <div class="w-8 h-8 rounded-xl <?= $editMode ? 'bg-blue-100' : 'bg-green-100' ?> flex items-center justify-center"><span class="text-sm"><?= $editMode ? 'âœï¸' : 'âž•' ?></span></div>
        <?= $editMode ? 'Modifier' : 'Nouvelle' ?> annonce
    </h2>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <?= csrfInput() ?>
        <?php if ($editMode): ?><input type="hidden" name="id" value="<?= $editAnnonce['id'] ?>"><?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Titre *</label>
                <input type="text" name="titre" required value="<?= $editMode ? htmlspecialchars($editAnnonce['titre']) : '' ?>" class="input-modern w-full">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">CatÃ©gorie</label>
                <select name="categorie" class="input-modern w-full">
                    <?php 
                        $currentCat = '';
                        if ($editMode && !empty($editAnnonce['categories'])) {
                            $decoded = json_decode($editAnnonce['categories'], true);
                            $currentCat = is_array($decoded) ? ($decoded[0] ?? '') : '';
                        }
                    ?>
                    <option value="">Aucune</option>
                    <option value="nutrition" <?= $currentCat === 'nutrition' ? 'selected' : '' ?>>ðŸ¥— Nutrition</option>
                    <option value="bien_etre" <?= $currentCat === 'bien_etre' ? 'selected' : '' ?>>ðŸ§˜ Bien-Ãªtre</option>
                    <option value="sante" <?= $currentCat === 'sante' ? 'selected' : '' ?>>ðŸ’Š SantÃ©</option>
                    <option value="recette" <?= $currentCat === 'recette' ? 'selected' : '' ?>>ðŸ¹ Recette</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Organe cible</label>
                <select name="organe_cible" class="input-modern w-full">
                    <option value="">Aucun</option>
                    <?php 
                        $curOrgane = $editMode ? ($editAnnonce['organes_cibles'] ?? '') : '';
                    ?>
                    <option value="coeur" <?= $curOrgane === 'coeur' ? 'selected' : '' ?>>â¤ï¸ CÅ“ur</option>
                    <option value="cerveau" <?= $curOrgane === 'cerveau' ? 'selected' : '' ?>>ðŸ§  Cerveau</option>
                    <option value="foie" <?= $curOrgane === 'foie' ? 'selected' : '' ?>>ðŸ« Foie</option>
                    <option value="peau" <?= $curOrgane === 'peau' ? 'selected' : '' ?>>âœ¨ Peau</option>
                    <option value="os" <?= $curOrgane === 'os' ? 'selected' : '' ?>>ðŸ¦´ Os</option>
                    <option value="yeux" <?= $curOrgane === 'yeux' ? 'selected' : '' ?>>ðŸ‘ï¸ Yeux</option>
                    <option value="estomac" <?= $curOrgane === 'estomac' ? 'selected' : '' ?>>ðŸ«¡ Estomac</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Contenu *</label>
                <textarea name="contenu" required rows="4" class="input-modern w-full resize-none"><?= $editMode ? htmlspecialchars($editAnnonce['description'] ?? '') : '' ?></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Composition / Conseils</label>
                <textarea name="composition" rows="2" class="input-modern w-full resize-none" placeholder="Liste des ingrÃ©dients ou composition..."><?= $editMode ? htmlspecialchars($editAnnonce['conseils_consommation'] ?? '') : '' ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Image</label>
                <input type="file" name="image" accept="image/*" class="input-modern w-full text-sm">
            </div>
            <div class="flex items-center gap-6 pt-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="publie" value="1" <?= (!$editMode || $editAnnonce['statut'] === 'publie') ? 'checked' : '' ?>
                           class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <span class="text-sm font-medium text-gray-700">Publier</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="epingle" value="1" <?= ($editMode && $editAnnonce['epingle']) ? 'checked' : '' ?>
                           class="w-4 h-4 rounded border-gray-300 text-yellow-600 focus:ring-yellow-500">
                    <span class="text-sm font-medium text-gray-700">Ã‰pingler</span>
                </label>
            </div>
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-green-200/50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
                <?= $editMode ? 'Mettre Ã  jour' : 'CrÃ©er' ?>
            </button>
            <?php if ($editMode): ?>
            <a href="annonces.php" class="px-6 py-3 bg-gray-100 text-gray-600 font-semibold text-sm rounded-xl hover:bg-gray-200 transition-colors">Annuler</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Annonces List -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Annonce</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">CatÃ©gorie</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Organe</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Statut</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Date</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($annonces as $annonce): ?>
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <?php if ($annonce['epingle']): ?><span class="text-yellow-500" title="Ã‰pinglÃ©">ðŸ“Œ</span><?php endif; ?>
                            <p class="font-semibold text-gray-800 truncate max-w-[250px]"><?= htmlspecialchars($annonce['titre']) ?></p>
                        </div>
                    </td>
                    <td class="px-5 py-3.5">
                        <?php
                        $decoded = json_decode($annonce['categories'] ?? '[]', true);
                        $cat = is_array($decoded) ? ($decoded[0] ?? '') : '';
                        $catLabels = ['nutrition' => 'ðŸ¥— Nutrition', 'bien_etre' => 'ðŸ§˜ Bien-Ãªtre', 'sante' => 'ðŸ’Š SantÃ©', 'recette' => 'ðŸ¹ Recette'];
                        echo '<span class="text-xs text-gray-500">' . ($catLabels[$cat] ?? 'â€”') . '</span>';
                        ?>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-gray-500"><?= htmlspecialchars($annonce['organes_cibles'] ?? 'â€”') ?></td>
                    <td class="px-5 py-3.5">
                        <div class="flex gap-1.5">
                            <span class="text-[10px] px-2 py-1 rounded-lg font-semibold <?= $annonce['statut'] === 'publie' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                                <?= $annonce['statut'] === 'publie' ? 'PubliÃ©' : 'Brouillon' ?>
                            </span>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-gray-400 text-xs"><?= formatDate($annonce['created_at'], false) ?></td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-1.5">
                            <a href="?toggle_publie=<?= $annonce['id'] ?>&csrf_token=<?= urlencode(generateCSRF()) ?>" class="text-xs font-bold px-2 py-1.5 rounded-lg transition-colors <?= $annonce['statut'] === 'publie' ? 'text-gray-500 bg-gray-50 hover:bg-gray-100' : 'text-green-600 bg-green-50 hover:bg-green-100' ?>">
                                <?= $annonce['statut'] === 'publie' ? 'DÃ©publier' : 'Publier' ?>
                            </a>
                            <a href="?toggle_epingle=<?= $annonce['id'] ?>&csrf_token=<?= urlencode(generateCSRF()) ?>" class="text-xs font-bold px-2 py-1.5 rounded-lg transition-colors <?= $annonce['epingle'] ? 'text-yellow-600 bg-yellow-50 hover:bg-yellow-100' : 'text-gray-500 bg-gray-50 hover:bg-gray-100' ?>">
                                <?= $annonce['epingle'] ? 'DÃ©sÃ©pingler' : 'Ã‰pingler' ?>
                            </a>
                            <a href="?edit=<?= $annonce['id'] ?>#add-form" class="text-xs text-blue-600 font-bold px-2 py-1.5 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">Modifier</a>
                            <a href="?delete=<?= $annonce['id'] ?>&csrf_token=<?= urlencode(generateCSRF()) ?>" onclick="return confirm('Supprimer cette annonce?')" class="text-xs text-red-600 font-bold px-2 py-1.5 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">Suppr</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($annonces)): ?>
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-3"><span class="text-xl">ðŸ“¢</span></div>
                        <p class="text-sm text-gray-400 font-medium">Aucune annonce crÃ©Ã©e</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/admin-layout-bottom.php'; ?>


