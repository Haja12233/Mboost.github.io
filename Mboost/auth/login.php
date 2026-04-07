<?php
/**
 * M'Boost — Unified Login (Client + Admin) — Premium Split-Screen Design
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

// Determine login mode from query parameter or POST
$mode = $_GET['mode'] ?? $_POST['login_mode'] ?? 'client';
if (!in_array($mode, ['client', 'admin'])) {
    $mode = 'client';
}

// Redirect if already logged in
if ($mode === 'admin' && isAdminLoggedIn()) {
    redirect(APP_URL . '/admin/dashboard.php');
}
if ($mode === 'client' && isLoggedIn()) {
    redirect(APP_URL . '/client/profil.php');
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCSRFOrAbort();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $mode = $_POST['login_mode'] ?? 'client';

    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        if ($mode === 'admin') {
            // Admin login
            $stmt = $pdo->prepare("SELECT id, nom, prenom, email, mot_de_passe, role FROM admins WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['mot_de_passe'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_nom'] = $admin['nom'];
                $_SESSION['admin_prenom'] = $admin['prenom'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = $admin['role'];
                redirect(APP_URL . '/admin/dashboard.php');
            } else {
                $error = 'Email ou mot de passe incorrect.';
            }
        } else {
            // Client login
            $stmt = $pdo->prepare("SELECT id, nom, prenom, email, mot_de_passe, statut FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['mot_de_passe'])) {
                if ($user['statut'] === 'bloque') {
                    $error = 'Votre compte a été bloqué. Contactez l\'administration.';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_nom'] = $user['nom'];
                    $_SESSION['user_prenom'] = $user['prenom'];
                    $_SESSION['user_email'] = $user['email'];

                    $redirect = $_SESSION['redirect_after_login'] ?? APP_URL . '/client/profil.php';
                    unset($_SESSION['redirect_after_login']);
                    redirect($redirect);
                }
            } else {
                $error = 'Email ou mot de passe incorrect.';
            }
        }
    }
}

$pageTitle = $mode === 'admin' ? 'Administration' : 'Connexion';
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
    <link rel="icon" href="<?= APP_URL ?>/assets/images/logo.jpg" type="image/jpeg">
</head>
<body class="font-body antialiased min-h-screen" x-data="{ mode: '<?= $mode ?>' }">

<div class="min-h-screen flex">
    <!-- ── LEFT: Branding Side ──────────────────────────────── -->
    <div class="hidden lg:flex lg:w-1/2 gradient-hero-mesh relative overflow-hidden items-center justify-center p-12">
        <!-- Floating fruit decorations -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none select-none">
            <span class="absolute text-5xl float-1 opacity-20" style="top:8%;left:12%">🍊</span>
            <span class="absolute text-4xl float-2 opacity-20" style="top:18%;right:15%">🥝</span>
            <span class="absolute text-6xl float-3 opacity-15" style="top:45%;left:8%">🍎</span>
            <span class="absolute text-5xl float-4 opacity-20" style="bottom:25%;right:10%">🥕</span>
            <span class="absolute text-4xl float-5 opacity-15" style="bottom:10%;left:20%">🍋</span>
            <span class="absolute text-3xl float-6 opacity-20" style="top:70%;right:25%">🌿</span>
            <span class="absolute text-5xl float-7 opacity-15" style="top:35%;right:30%">🍓</span>
        </div>

        <!-- Center Content -->
        <div class="relative z-10 text-center text-white max-w-md">
            <div class="mx-auto mb-8 inline-flex bg-white/10 backdrop-blur-sm rounded-2xl border border-white/20 shadow-xl shadow-black/10 overflow-hidden">
                <img src="<?= APP_URL ?>/assets/images/logo.jpg" alt="<?= APP_NAME ?>" class="h-20 w-60 xl:h-24 xl:w-72 object-cover">
            </div>
            <h1 class="text-4xl font-extrabold font-display leading-tight mb-4">
                Bienvenue sur<br>
                <span class="text-gray-200">M'Boost</span>
            </h1>
            <p class="text-lg text-gray-100/80 leading-relaxed">
                Votre bar à jus naturels en ligne. Créez des jus personnalisés à partir d'ingrédients frais et locaux.
            </p>
            <div class="mt-10 flex items-center justify-center gap-6 text-gray-200/70 text-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    100% Naturel
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Livraison rapide
                </div>
            </div>
        </div>
    </div>

    <!-- ── RIGHT: Login Form ────────────────────────────────── -->
    <div class="flex-1 flex items-center justify-center bg-gradient-to-br from-gray-100 via-white to-gray-200/50 px-6 py-12">
        <div class="w-full max-w-md animate-slide-up">
            <!-- Mobile logo -->
            <div class="lg:hidden text-center mb-8">
                <a href="<?= APP_URL ?>" class="inline-flex items-center gap-2.5 group">
                    <img src="<?= APP_URL ?>/assets/images/logo.jpg" alt="<?= APP_NAME ?>"
                         class="h-16 w-48 rounded-lg object-cover">
                </a>
            </div>

            <!-- ── Role Toggle ──────────────────────────── -->
            <div class="flex bg-gray-100 rounded-xl p-1 mb-8">
                <a href="?mode=client"
                   class="flex-1 py-2.5 text-center text-sm font-bold rounded-lg transition-all duration-300
                          <?= $mode === 'client' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-400 hover:text-gray-600' ?>"
                   @click.prevent="if(mode !== 'client') { mode = 'client'; window.history.replaceState(null, '', '?mode=client'); }">
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Client
                    </span>
                </a>
                <a href="?mode=admin"
                   class="flex-1 py-2.5 text-center text-sm font-bold rounded-lg transition-all duration-300
                          <?= $mode === 'admin' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-400 hover:text-gray-600' ?>"
                   @click.prevent="if(mode !== 'admin') { mode = 'admin'; window.history.replaceState(null, '', '?mode=admin'); }">
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Admin
                    </span>
                </a>
            </div>

            <div class="mb-8">
                <h2 class="text-3xl font-extrabold font-display text-gray-900">
                    <?= $mode === 'admin' ? 'Espace Admin' : 'Connexion' ?>
                </h2>
                <p class="mt-2 text-gray-500">
                    <?= $mode === 'admin' ? 'Connexion sécurisée au tableau de bord' : 'Connectez-vous pour commander vos jus personnalisés' ?>
                </p>
            </div>

            <?php if ($error): ?>
            <div class="mb-6 flex items-start gap-3 p-4 rounded-2xl bg-red-50 border border-red-100 animate-scale-in">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-red-100 text-red-600 flex-shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </span>
                <p class="text-sm text-red-700 font-medium"><?= htmlspecialchars($error) ?></p>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <?= csrfInput() ?>
                <input type="hidden" name="login_mode" value="<?= $mode ?>">

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        <?= $mode === 'admin' ? 'Email administrateur' : 'Adresse email' ?>
                    </label>
                    <div class="input-icon-wrapper">
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required
                               class="input-modern w-full"
                               placeholder="<?= $mode === 'admin' ? 'admin@mboost.mg' : 'votre@email.com' ?>" autocomplete="email">
                        <svg class="input-icon w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">Mot de passe</label>
                    <div class="input-icon-wrapper">
                        <input type="password" id="password" name="password" required
                               class="input-modern w-full"
                               placeholder="••••••••" autocomplete="current-password">
                        <svg class="input-icon w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                </div>

                <?php if ($mode === 'client'): ?>
                <!-- Remember + Forgot (client only) -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" name="remember"
                               class="w-4 h-4 rounded border-2 border-gray-300 text-gray-700 focus:ring-gray-500 focus:ring-offset-0 transition-colors">
                        <span class="text-sm text-gray-500 group-hover:text-gray-700 transition-colors">Se souvenir de moi</span>
                    </label>
                    <a href="#" class="text-sm text-gray-700 hover:text-gray-900 font-semibold transition-colors">Mot de passe oublié?</a>
                </div>
                <?php endif; ?>

                <!-- Submit -->
                <button type="submit"
                        class="w-full py-3.5 px-6 bg-gradient-to-r from-gray-700 to-slate-700 text-white font-bold text-sm
                               rounded-xl shadow-lg shadow-gray-300/50 hover:shadow-xl hover:shadow-gray-400/50
                               hover:-translate-y-0.5 transition-all duration-300 btn-shine btn-glow">
                    <span class="flex items-center justify-center gap-2">
                        <?php if ($mode === 'admin'): ?>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Connexion Admin
                        <?php else: ?>
                        Se connecter
                        <?php endif; ?>
                    </span>
                </button>
            </form>

            <?php if ($mode === 'client'): ?>
            <!-- Register link (client only) -->
            <p class="mt-8 text-center text-sm text-gray-500">
                Pas encore de compte?
                <a href="register.php" class="text-gray-700 hover:text-gray-900 font-bold transition-colors">
                    Créer un compte →
                </a>
            </p>
            <?php endif; ?>

            <?php if ($mode === 'admin'): ?>
            <!-- Security badge (admin mode) -->
            <div class="mt-8 text-center">
                <p class="text-xs text-gray-400 flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                    Connexion sécurisée — <?= APP_NAME ?>
                </p>
            </div>
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
