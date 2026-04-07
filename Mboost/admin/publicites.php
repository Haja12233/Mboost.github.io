<?php
/**
 * M'Boost â€” Admin Publicites Hero
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

if (!isAdminLoggedIn()) {
    redirect(APP_URL . '/auth/admin.php');
}

ensureHeroAdsTable($pdo);

$success = '';
$error = '';
$uploadDir = UPLOAD_DIR . 'hero-ads/';
$actionCsrf = (string) ($_GET['csrf_token'] ?? '');

if (isset($_GET['delete'])) {
    if (!verifyCSRF($actionCsrf)) {
        http_response_code(419);
        die('Requete invalide (CSRF).');
    }
    $id = (int) $_GET['delete'];

    $stmt = $pdo->prepare("SELECT media_url FROM hero_publicites WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();

    if ($row && !empty($row['media_url'])) {
        $path = $uploadDir . $row['media_url'];
        if (is_file($path)) {
            @unlink($path);
        }
    }

    $stmt = $pdo->prepare("DELETE FROM hero_publicites WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $success = 'Publicite supprimee.';
}

if (isset($_GET['toggle_publie'])) {
    if (!verifyCSRF($actionCsrf)) {
        http_response_code(419);
        die('Requete invalide (CSRF).');
    }
    $id = (int) $_GET['toggle_publie'];
    $stmt = $pdo->prepare("UPDATE hero_publicites SET statut = IF(statut='publie', 'brouillon', 'publie') WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $success = 'Statut mis a jour.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCSRFOrAbort();
    $id = (int) ($_POST['id'] ?? 0);
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $ordre = (int) ($_POST['ordre_affichage'] ?? 0);
    $lien = trim($_POST['lien_url'] ?? '');
    $statut = isset($_POST['publie']) ? 'publie' : 'brouillon';

    if ($lien !== '' && !filter_var($lien, FILTER_VALIDATE_URL)) {
        $error = 'Le lien doit etre une URL valide (https://...).';
    }

    $uploadedMedia = null;
    $uploadedType = null;

    if ($error === '' && !empty($_FILES['media']['tmp_name'])) {
        if ($_FILES['media']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Erreur lors du televersement du media.';
        } elseif ((int) $_FILES['media']['size'] > 20 * 1024 * 1024) {
            $error = 'Le media depasse 20 Mo.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($_FILES['media']['tmp_name']);

            $allowed = [
                'image/jpeg' => ['ext' => 'jpg', 'type' => 'image'],
                'image/png' => ['ext' => 'png', 'type' => 'image'],
                'image/webp' => ['ext' => 'webp', 'type' => 'image'],
                'image/gif' => ['ext' => 'gif', 'type' => 'image'],
                'video/mp4' => ['ext' => 'mp4', 'type' => 'video'],
                'video/webm' => ['ext' => 'webm', 'type' => 'video'],
                'video/ogg' => ['ext' => 'ogg', 'type' => 'video'],
            ];

            if (!isset($allowed[$mimeType])) {
                $error = 'Type de media non autorise: ' . $mimeType;
            } else {
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                    $error = 'Impossible de creer le dossier de televersement.';
                } else {
                    $uploadedMedia = bin2hex(random_bytes(16)) . '.' . $allowed[$mimeType]['ext'];
                    $uploadedType = $allowed[$mimeType]['type'];
                    $destination = $uploadDir . $uploadedMedia;

                    if (!move_uploaded_file($_FILES['media']['tmp_name'], $destination)) {
                        $error = 'Impossible de sauvegarder le media.';
                    }
                }
            }
        }
    }

    if ($error === '') {
        if ($id > 0) {
            $stmt = $pdo->prepare("SELECT media_url FROM hero_publicites WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $existing = $stmt->fetch();

            $sql = "UPDATE hero_publicites
                    SET titre = :titre,
                        description = :description,
                        lien_url = :lien,
                        statut = :statut,
                        ordre_affichage = :ordre";

            $params = [
                ':id' => $id,
                ':titre' => $titre,
                ':description' => $description !== '' ? $description : null,
                ':lien' => $lien !== '' ? $lien : null,
                ':statut' => $statut,
                ':ordre' => $ordre,
            ];

            if ($uploadedMedia !== null) {
                $sql .= ", media_url = :media_url, media_type = :media_type";
                $params[':media_url'] = $uploadedMedia;
                $params[':media_type'] = $uploadedType;
            }

            $sql .= " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            if ($uploadedMedia !== null && !empty($existing['media_url'])) {
                $oldPath = $uploadDir . $existing['media_url'];
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $success = 'Publicite mise a jour.';
        } else {
            if ($uploadedMedia === null || $uploadedType === null) {
                $error = 'Un media (image ou video) est obligatoire pour une nouvelle publicite.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO hero_publicites
                    (titre, description, media_type, media_url, lien_url, statut, ordre_affichage, created_by)
                    VALUES
                    (:titre, :description, :media_type, :media_url, :lien, :statut, :ordre, :created_by)");

                $stmt->execute([
                    ':titre' => $titre,
                    ':description' => $description !== '' ? $description : null,
                    ':media_type' => $uploadedType,
                    ':media_url' => $uploadedMedia,
                    ':lien' => $lien !== '' ? $lien : null,
                    ':statut' => $statut,
                    ':ordre' => $ordre,
                    ':created_by' => $_SESSION['admin_id'] ?? null,
                ]);

                $success = 'Publicite creee.';
            }
        }
    }
}

$editMode = isset($_GET['edit']);
$editPub = null;
if ($editMode) {
    $stmt = $pdo->prepare("SELECT * FROM hero_publicites WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => (int) $_GET['edit']]);
    $editPub = $stmt->fetch();
}

$stmt = $pdo->query("SELECT hp.*, ad.nom AS admin_nom, ad.prenom AS admin_prenom
                    FROM hero_publicites hp
                    LEFT JOIN admins ad ON hp.created_by = ad.id
                    ORDER BY hp.ordre_affichage ASC, hp.created_at DESC");
$publicites = $stmt->fetchAll();

$pageTitle = 'Publicites Hero';
$activePage = 'publicites';
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

<div id="add-form" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <h2 class="text-lg font-bold font-display text-gray-900 mb-5 flex items-center gap-2">
        <div class="w-8 h-8 rounded-xl <?= $editMode ? 'bg-blue-100' : 'bg-green-100' ?> flex items-center justify-center"><span class="text-sm"><?= $editMode ? 'âœï¸' : 'ðŸŽ¬' ?></span></div>
        <?= $editMode ? 'Modifier' : 'Nouvelle' ?> publicite hero
    </h2>

    <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?= csrfInput() ?>
        <?php if ($editMode && $editPub): ?>
        <input type="hidden" name="id" value="<?= (int) $editPub['id'] ?>">
        <?php endif; ?>

        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Titre (optionnel)</label>
            <input type="text" name="titre" value="<?= $editMode && $editPub ? htmlspecialchars($editPub['titre']) : '' ?>" class="input-modern w-full" placeholder="Ex: Promo detox du jour">
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Description</label>
            <textarea name="description" rows="3" class="input-modern w-full resize-none"><?= $editMode && $editPub ? htmlspecialchars($editPub['description'] ?? '') : '' ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Media (image/video) <?= $editMode ? '' : '*' ?></label>
            <input type="file" name="media" accept="image/*,video/mp4,video/webm,video/ogg" class="input-modern w-full text-sm">
            <p class="text-xs text-gray-400 mt-1">Formats: JPG, PNG, WEBP, GIF, MP4, WEBM, OGG (max 20 Mo)</p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Lien (optionnel)</label>
            <input type="url" name="lien_url" placeholder="https://..." value="<?= $editMode && $editPub ? htmlspecialchars($editPub['lien_url'] ?? '') : '' ?>" class="input-modern w-full">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Ordre d'affichage</label>
            <input type="number" name="ordre_affichage" value="<?= $editMode && $editPub ? (int) $editPub['ordre_affichage'] : 0 ?>" class="input-modern w-full">
        </div>

        <div class="flex items-center gap-6 pt-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="publie" value="1" <?= (!$editMode || ($editPub && $editPub['statut'] === 'publie')) ? 'checked' : '' ?> class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                <span class="text-sm font-medium text-gray-700">Publier</span>
            </label>
        </div>

        <?php if ($editMode && $editPub && !empty($editPub['media_url'])): ?>
        <div class="md:col-span-2 bg-gray-50 rounded-xl p-3 border border-gray-100">
            <p class="text-xs font-semibold text-gray-500 mb-2">Media actuel</p>
            <?php if (($editPub['media_type'] ?? '') === 'video'): ?>
            <video src="<?= UPLOAD_URL . 'hero-ads/' . htmlspecialchars($editPub['media_url']) ?>" class="w-full max-w-sm rounded-lg" controls muted preload="metadata"></video>
            <?php else: ?>
            <img src="<?= UPLOAD_URL . 'hero-ads/' . htmlspecialchars($editPub['media_url']) ?>" alt="Media" class="w-full max-w-sm h-36 object-cover rounded-lg">
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="md:col-span-2 flex gap-3">
            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-green-200/50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
                <?= $editMode ? 'Mettre a jour' : 'Creer' ?>
            </button>
            <?php if ($editMode): ?>
            <a href="publicites.php" class="px-6 py-3 bg-gray-100 text-gray-600 font-semibold text-sm rounded-xl hover:bg-gray-200 transition-colors">Annuler</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Media</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Titre</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Type</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Ordre</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Statut</th>
                    <th class="px-5 py-3.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($publicites as $pub): ?>
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-5 py-3.5">
                        <?php if (!empty($pub['media_url']) && $pub['media_type'] === 'video'): ?>
                        <video src="<?= UPLOAD_URL . 'hero-ads/' . htmlspecialchars($pub['media_url']) ?>" class="w-28 h-16 object-cover rounded-lg" muted preload="metadata"></video>
                        <?php elseif (!empty($pub['media_url'])): ?>
                        <img src="<?= UPLOAD_URL . 'hero-ads/' . htmlspecialchars($pub['media_url']) ?>" alt="media" class="w-28 h-16 object-cover rounded-lg">
                        <?php else: ?>
                        <span class="text-xs text-gray-400">Aucun</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3.5">
                        <p class="font-semibold text-gray-800 truncate max-w-[220px]"><?= htmlspecialchars($pub['titre']) ?></p>
                        <?php if (!empty($pub['description'])): ?>
                        <p class="text-xs text-gray-400 truncate max-w-[240px]"><?= htmlspecialchars($pub['description']) ?></p>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-gray-500 uppercase"><?= htmlspecialchars($pub['media_type']) ?></td>
                    <td class="px-5 py-3.5 text-xs text-gray-500"><?= (int) $pub['ordre_affichage'] ?></td>
                    <td class="px-5 py-3.5">
                        <span class="text-[10px] px-2 py-1 rounded-lg font-semibold <?= $pub['statut'] === 'publie' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                            <?= $pub['statut'] === 'publie' ? 'Publie' : 'Brouillon' ?>
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-1.5">
                            <a href="?toggle_publie=<?= (int) $pub['id'] ?>&csrf_token=<?= urlencode(generateCSRF()) ?>" class="text-xs font-bold px-2 py-1.5 rounded-lg transition-colors <?= $pub['statut'] === 'publie' ? 'text-gray-500 bg-gray-50 hover:bg-gray-100' : 'text-green-600 bg-green-50 hover:bg-green-100' ?>">
                                <?= $pub['statut'] === 'publie' ? 'Depublier' : 'Publier' ?>
                            </a>
                            <a href="?edit=<?= (int) $pub['id'] ?>#add-form" class="text-xs text-blue-600 font-bold px-2 py-1.5 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">Modifier</a>
                            <a href="?delete=<?= (int) $pub['id'] ?>&csrf_token=<?= urlencode(generateCSRF()) ?>" onclick="return confirm('Supprimer cette publicite ?')" class="text-xs text-red-600 font-bold px-2 py-1.5 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">Suppr</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($publicites)): ?>
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center">
                        <p class="text-sm text-gray-400 font-medium">Aucune publicite hero pour le moment</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/admin-layout-bottom.php'; ?>


