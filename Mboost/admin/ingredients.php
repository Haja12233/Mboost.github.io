<?php
/**
 * M'Boost - Admin Ingredients
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCSRFOrAbort();

    $id = (int) ($_POST['id'] ?? 0);
    $nom = trim($_POST['nom'] ?? '');
    $categorie = $_POST['categorie'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $prix = (float) ($_POST['prix'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);
    $emoji = trim($_POST['emoji'] ?? '');
    $actif = isset($_POST['actif']) ? 1 : 0;

    if ($nom === '' || !in_array($categorie, ['legumes', 'fruits', 'graines'], true)) {
        $error = 'Veuillez remplir les champs obligatoires.';
    } else {
        $imageName = null;
        if (!empty($_FILES['image']['tmp_name'])) {
            $upload = uploadFile($_FILES['image'], UPLOAD_DIR . 'ingredients/');
            if (!$upload['success']) {
                $error = $upload['error'];
            } else {
                $imageName = $upload['filename'];
            }
        }

        if ($error === '') {
            if ($id > 0) {
                $sql = 'UPDATE ingredients SET nom = :nom, categorie = :categorie, description = :description, prix = :prix, stock = :stock, emoji = :emoji, actif = :actif';
                $params = [
                    ':id' => $id,
                    ':nom' => $nom,
                    ':categorie' => $categorie,
                    ':description' => $description,
                    ':prix' => $prix,
                    ':stock' => $stock,
                    ':emoji' => $emoji,
                    ':actif' => $actif,
                ];
                if ($imageName !== null) {
                    $sql .= ', image = :image';
                    $params[':image'] = $imageName;
                }
                $sql .= ' WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $success = 'Ingredient mis a jour.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO ingredients (nom, categorie, description, prix, stock, emoji, actif, image) VALUES (:nom, :categorie, :description, :prix, :stock, :emoji, :actif, :image)');
                $stmt->execute([
                    ':nom' => $nom,
                    ':categorie' => $categorie,
                    ':description' => $description,
                    ':prix' => $prix,
                    ':stock' => $stock,
                    ':emoji' => $emoji,
                    ':actif' => $actif,
                    ':image' => $imageName,
                ]);
                $success = 'Ingredient ajoute.';
            }
        }
    }
}

if (isset($_GET['delete'])) {
    if (!verifyCSRF($actionCsrf)) {
        http_response_code(419);
        die('Requete invalide (CSRF).');
    }
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM ingredients WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $success = 'Ingredient supprime.';
}

if (isset($_GET['toggle_actif'])) {
    if (!verifyCSRF($actionCsrf)) {
        http_response_code(419);
        die('Requete invalide (CSRF).');
    }
    $id = (int) $_GET['toggle_actif'];
    $stmt = $pdo->prepare('UPDATE ingredients SET actif = NOT actif WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $success = 'Statut mis a jour.';
}

$editMode = isset($_GET['edit']);
$editIngredient = null;
if ($editMode) {
    $stmt = $pdo->prepare('SELECT * FROM ingredients WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => (int) $_GET['edit']]);
    $editIngredient = $stmt->fetch();
}

$stmt = $pdo->query('SELECT * FROM ingredients ORDER BY categorie, nom');
$ingredients = $stmt->fetchAll();

$categories = ['legumes' => 'Legumes', 'fruits' => 'Fruits', 'graines' => 'Graines'];

$pageTitle = 'Gestion des ingredients';
$activePage = 'ingredients';
include __DIR__ . '/includes/admin-layout-top.php';
?>

<?php if ($success): ?>
<div class="p-3 rounded-xl bg-green-50 border border-green-100 text-green-700 text-sm"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="p-3 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <h2 class="text-lg font-bold mb-4"><?= $editMode ? 'Modifier' : 'Ajouter' ?> un ingredient</h2>
    <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?= csrfInput() ?>
        <?php if ($editMode && $editIngredient): ?>
        <input type="hidden" name="id" value="<?= (int) $editIngredient['id'] ?>">
        <?php endif; ?>
        <input name="nom" required class="input-modern" placeholder="Nom" value="<?= $editMode && $editIngredient ? htmlspecialchars($editIngredient['nom']) : '' ?>">
        <select name="categorie" required class="input-modern">
            <option value="">Categorie</option>
            <?php foreach ($categories as $key => $label): ?>
            <option value="<?= $key ?>" <?= ($editMode && $editIngredient && $editIngredient['categorie'] === $key) ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <input name="prix" type="number" min="0" step="50" required class="input-modern" placeholder="Prix" value="<?= $editMode && $editIngredient ? (float) $editIngredient['prix'] : '' ?>">
        <input name="stock" type="number" min="0" required class="input-modern" placeholder="Stock" value="<?= $editMode && $editIngredient ? (int) $editIngredient['stock'] : 100 ?>">
        <input name="emoji" class="input-modern" placeholder="Emoji" value="<?= $editMode && $editIngredient ? htmlspecialchars($editIngredient['emoji'] ?? '') : '' ?>">
        <input name="image" type="file" accept="image/*" class="input-modern">
        <textarea name="description" class="input-modern md:col-span-2" rows="2" placeholder="Description"><?= $editMode && $editIngredient ? htmlspecialchars($editIngredient['description'] ?? '') : '' ?></textarea>
        <label class="md:col-span-2 text-sm"><input type="checkbox" name="actif" value="1" <?= (!$editMode || ($editIngredient && (int) $editIngredient['actif'] === 1)) ? 'checked' : '' ?>> Actif</label>
        <div class="md:col-span-2 flex gap-3">
            <button class="px-4 py-2 rounded-lg bg-green-600 text-white font-semibold" type="submit"><?= $editMode ? 'Mettre a jour' : 'Ajouter' ?></button>
            <?php if ($editMode): ?><a class="px-4 py-2 rounded-lg bg-gray-100" href="ingredients.php">Annuler</a><?php endif; ?>
        </div>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-6">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="px-4 py-3 text-left">Nom</th>
                    <th class="px-4 py-3 text-left">Categorie</th>
                    <th class="px-4 py-3 text-left">Prix</th>
                    <th class="px-4 py-3 text-left">Stock</th>
                    <th class="px-4 py-3 text-left">Statut</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($ingredients as $ing): ?>
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3"><?= htmlspecialchars(($ing['emoji'] ?? '🥤') . ' ' . $ing['nom']) ?></td>
                    <td class="px-4 py-3"><?= htmlspecialchars($categories[$ing['categorie']] ?? $ing['categorie']) ?></td>
                    <td class="px-4 py-3"><?= formatPrice((float) $ing['prix']) ?></td>
                    <td class="px-4 py-3"><?= (int) $ing['stock'] ?></td>
                    <td class="px-4 py-3"><?= (int) $ing['actif'] === 1 ? 'Actif' : 'Inactif' ?></td>
                    <td class="px-4 py-3 flex gap-2">
                        <a class="text-blue-600" href="?edit=<?= (int) $ing['id'] ?>#add-form">Modifier</a>
                        <a class="text-yellow-600" href="?toggle_actif=<?= (int) $ing['id'] ?>&csrf_token=<?= urlencode(generateCSRF()) ?>">Basculer</a>
                        <a class="text-red-600" href="?delete=<?= (int) $ing['id'] ?>&csrf_token=<?= urlencode(generateCSRF()) ?>" onclick="return confirm('Supprimer cet ingredient ?')">Suppr</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/admin-layout-bottom.php'; ?>
