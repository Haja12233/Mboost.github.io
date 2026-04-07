<?php
/**
 * M'Boost - Client Registration — Premium Split-Screen Design
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

if (isLoggedIn()) {
    redirect(APP_URL . '/client/profil.php');
}

$error = '';
$success = '';
$formData = [
    'nom' => '', 'prenom' => '', 'email' => '',
    'telephone' => '', 'adresse_livraison' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCSRFOrAbort();
    $formData = [
        'nom' => trim($_POST['nom'] ?? ''),
        'prenom' => trim($_POST['prenom'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'telephone' => trim($_POST['telephone'] ?? ''),
        'adresse_livraison' => trim($_POST['adresse_livraison'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'password_confirm' => $_POST['password_confirm'] ?? ''
    ];

    if (empty($formData['nom']) || empty($formData['prenom']) || empty($formData['email']) ||
        empty($formData['telephone']) || empty($formData['password'])) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Veuillez entrer une adresse email valide.';
    } elseif (strlen($formData['password']) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } elseif ($formData['password'] !== $formData['password_confirm']) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $formData['email']]);

        if ($stmt->fetch()) {
            $error = 'Cet email est déjà utilisé. Veuillez vous connecter.';
        } else {
            $hashedPassword = password_hash($formData['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $pdo->prepare("
                INSERT INTO users (nom, prenom, email, telephone, mot_de_passe, adresse_livraison)
                VALUES (:nom, :prenom, :email, :telephone, :mot_de_passe, :adresse_livraison)
            ");
            try {
                $stmt->execute([
                    ':nom' => $formData['nom'],
                    ':prenom' => $formData['prenom'],
                    ':email' => $formData['email'],
                    ':telephone' => $formData['telephone'],
                    ':mot_de_passe' => $hashedPassword,
                    ':adresse_livraison' => $formData['adresse_livraison']
                ]);
                $success = 'Compte créé avec succès! Vous pouvez maintenant vous connecter.';
                $formData = array_fill_keys(array_keys($formData), '');
            } catch (PDOException $e) {
                $error = 'Une erreur est survenue lors de la création du compte. Veuillez réessayer.';
            }
        }
    }
}

$pageTitle = "Inscription";
?>
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> — <?= $pageTitle ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: { 'display': ['Outfit','Inter','system-ui','sans-serif'], 'body': ['Inter','system-ui','sans-serif'] }
            }
        }
    }
    </script>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🥤</text></svg>">
</head>
<body class="font-body antialiased min-h-screen">

<div class="min-h-screen flex">
    <!-- ── LEFT: Branding Side ──────────────────────────────── -->
    <div class="hidden lg:flex lg:w-5/12 gradient-hero-mesh relative overflow-hidden items-center justify-center p-12">
        <div class="absolute inset-0 overflow-hidden pointer-events-none select-none">
            <span class="absolute text-5xl float-1 opacity-20" style="top:10%;left:15%">🥬</span>
            <span class="absolute text-4xl float-2 opacity-20" style="top:25%;right:12%">🍇</span>
            <span class="absolute text-6xl float-3 opacity-15" style="top:50%;left:5%">🥭</span>
            <span class="absolute text-5xl float-4 opacity-20" style="bottom:20%;right:15%">🫐</span>
            <span class="absolute text-4xl float-5 opacity-15" style="bottom:8%;left:25%">🌱</span>
            <span class="absolute text-3xl float-6 opacity-20" style="top:65%;right:30%">🥑</span>
        </div>

        <div class="relative z-10 text-center text-white max-w-md">
            <div class="mx-auto mb-8 inline-flex bg-white/10 backdrop-blur-sm rounded-2xl border border-white/20 shadow-xl shadow-black/10 overflow-hidden">
                <img src="<?= APP_URL ?>/assets/images/logo.jpg" alt="<?= APP_NAME ?>" class="h-20 w-60 xl:h-24 xl:w-72 object-cover">
            </div>
            <h1 class="text-4xl font-extrabold font-display leading-tight mb-4">
                Rejoignez la<br>
                <span class="text-gray-200">communauté</span>
            </h1>
            <p class="text-lg text-gray-100/80 leading-relaxed">
                Inscrivez-vous et commencez à créer vos propres recettes de jus frais, 100% personnalisés.
            </p>

            <!-- Feature list -->
            <div class="mt-10 space-y-3 text-left max-w-xs mx-auto">
                <div class="flex items-center gap-3 text-gray-200/80 text-sm">
                    <span class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center text-base">🎨</span>
                    Créez vos propres recettes
                </div>
                <div class="flex items-center gap-3 text-gray-200/80 text-sm">
                    <span class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center text-base">📦</span>
                    Suivez vos commandes en temps réel
                </div>
                <div class="flex items-center gap-3 text-gray-200/80 text-sm">
                    <span class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center text-base">💚</span>
                    Conseils santé personnalisés
                </div>
            </div>
        </div>
    </div>

    <!-- ── RIGHT: Register Form ─────────────────────────────── -->
    <div class="flex-1 flex items-center justify-center bg-gradient-to-br from-gray-100 via-white to-gray-200/50 px-6 py-10 overflow-y-auto">
        <div class="w-full max-w-lg animate-slide-up">
            <!-- Mobile logo -->
            <div class="lg:hidden text-center mb-6">
                <a href="<?= APP_URL ?>" class="inline-flex items-center gap-2.5">
                    <img src="<?= APP_URL ?>/assets/images/logo.jpg" alt="<?= APP_NAME ?>"
                         class="h-16 w-48 rounded-lg object-cover">
                </a>
            </div>

            <div class="mb-6">
                <h2 class="text-3xl font-extrabold font-display text-gray-900">Créer un compte</h2>
                <p class="mt-2 text-gray-500">Rejoignez-nous et créez vos jus personnalisés</p>
            </div>

            <?php if ($error): ?>
            <div class="mb-5 flex items-start gap-3 p-4 rounded-2xl bg-red-50 border border-red-100 animate-scale-in">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-red-100 text-red-600 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </span>
                <p class="text-sm text-red-700 font-medium"><?= htmlspecialchars($error) ?></p>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="mb-5 p-6 rounded-2xl bg-gray-100 border border-gray-200 text-center animate-scale-in">
                <div class="w-14 h-14 rounded-2xl bg-gray-200 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-1">Compte créé!</h3>
                <p class="text-sm text-gray-700 mb-4"><?= htmlspecialchars($success) ?></p>
                <a href="login.php" class="inline-block px-6 py-2.5 bg-gradient-to-r from-gray-700 to-slate-700 text-white text-sm font-semibold rounded-xl shadow-md hover:shadow-lg transition-all">
                    Se connecter →
                </a>
            </div>
            <?php else: ?>

            <form method="POST" class="space-y-4">
                <?= csrfInput() ?>
                <!-- Name Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nom <span class="text-red-400">*</span></label>
                        <input type="text" name="nom" value="<?= htmlspecialchars($formData['nom']) ?>" required
                               class="input-modern w-full" placeholder="Votre nom">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Prénom <span class="text-red-400">*</span></label>
                        <input type="text" name="prenom" value="<?= htmlspecialchars($formData['prenom']) ?>" required
                               class="input-modern w-full" placeholder="Votre prénom">
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email <span class="text-red-400">*</span></label>
                    <div class="input-icon-wrapper">
                        <input type="email" name="email" value="<?= htmlspecialchars($formData['email']) ?>" required
                               class="input-modern w-full" placeholder="votre@email.com" autocomplete="email">
                        <svg class="input-icon w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Téléphone <span class="text-red-400">*</span></label>
                    <div class="input-icon-wrapper">
                        <input type="tel" name="telephone" value="<?= htmlspecialchars($formData['telephone']) ?>" required
                               class="input-modern w-full" placeholder="034 XX XXX XX">
                        <svg class="input-icon w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                </div>

                <!-- Address -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Adresse de livraison</label>
                    <textarea name="adresse_livraison" rows="2"
                              class="input-modern w-full resize-none"
                              placeholder="Votre adresse de livraison par défaut"><?= htmlspecialchars($formData['adresse_livraison']) ?></textarea>
                </div>

                <!-- Passwords Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Mot de passe <span class="text-red-400">*</span></label>
                        <input type="password" name="password" required minlength="6"
                               class="input-modern w-full" placeholder="6 caractères min" autocomplete="new-password">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Confirmer <span class="text-red-400">*</span></label>
                        <input type="password" name="password_confirm" required
                               class="input-modern w-full" placeholder="Répétez" autocomplete="new-password">
                    </div>
                </div>

                <!-- Terms -->
                <div class="flex items-center gap-2.5 text-sm">
                    <input type="checkbox" required
                           class="w-4 h-4 rounded border-2 border-gray-300 text-gray-700 focus:ring-gray-500 transition-colors">
                    <span class="text-gray-500">
                        J'accepte les <a href="#" class="text-gray-700 hover:underline font-medium">conditions d'utilisation</a>
                    </span>
                </div>

                <!-- Submit -->
                <button type="submit"
                        class="w-full py-3.5 px-6 bg-gradient-to-r from-gray-700 to-slate-700 text-white font-bold text-sm
                               rounded-xl shadow-lg shadow-gray-300/50 hover:shadow-xl hover:shadow-gray-400/50
                               hover:-translate-y-0.5 transition-all duration-300 btn-shine btn-glow">
                    Créer mon compte
                </button>
            </form>

            <!-- Login link -->
            <p class="mt-6 text-center text-sm text-gray-500">
                Déjà un compte?
                <a href="login.php" class="text-gray-700 hover:text-gray-900 font-bold transition-colors">
                    Se connecter →
                </a>
            </p>
            <?php endif; ?>

            <!-- Back to home -->
            <div class="mt-6 pt-6 border-t border-gray-200/60 text-center">
                <a href="<?= APP_URL ?>" class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-gray-600 transition-colors group">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
