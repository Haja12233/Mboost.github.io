<?php
/**
 * header.php — Main layout header for M'Boost
 * Premium redesign with glassmorphism navbar
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ── Cart count helper ───────────────────────────────────────── */
$cartCount = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += (int)($item['quantite'] ?? $item['qty'] ?? 1);
    }
}

$isLoggedIn   = !empty($_SESSION['user_id']);
$userName     = htmlspecialchars($_SESSION['user_prenom'] ?? $_SESSION['user_name'] ?? '', ENT_QUOTES, 'UTF-8');
$currentPath  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$currentPath  = is_string($currentPath) && $currentPath !== '' ? $currentPath : '/';

/* SEO defaults overridable per page */
$seoTitle = (string)($pageTitle ?? APP_NAME);
$seoDescription = (string)($pageDescription ?? "M'Boost - Jus naturels sur mesure, frais et livres a Mahajanga.");
$seoKeywords = (string)($pageKeywords ?? "jus naturel, jus frais, detox, sante, livraison, Mahajanga");
$seoRobots = (string)($pageRobots ?? 'index,follow');
$seoCanonical = (string)($pageCanonical ?? (rtrim(APP_URL, '/') . $currentPath));
$seoOgType = (string)($pageOgType ?? 'website');
$seoImage = (string)($pageOgImage ?? (rtrim(APP_URL, '/') . '/assets/images/logo.jpg'));
$seoTwitterCard = (string)($pageTwitterCard ?? 'summary_large_image');

if (!preg_match('#^https?://#i', $seoCanonical)) {
    $seoCanonical = rtrim(APP_URL, '/') . '/' . ltrim($seoCanonical, '/');
}
if (!preg_match('#^https?://#i', $seoImage)) {
    $seoImage = rtrim(APP_URL, '/') . '/' . ltrim($seoImage, '/');
}

$seoJsonLd = $pageJsonLd ?? [
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => APP_NAME,
    'url' => rtrim(APP_URL, '/'),
    'logo' => rtrim(APP_URL, '/') . '/assets/images/logo.jpg',
];

/* ── Active-link helper ──────────────────────────────────────── */
function navActive(string $path, string $current): string {
    return str_contains($current, $path)
        ? 'text-green-700 font-semibold dark:text-green-400'
        : 'text-gray-600 hover:text-green-600 dark:text-gray-400 dark:hover:text-green-400';
}

function navIsActive(string $path, string $current): bool {
    return str_contains($current, $path);
}
?>
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($seoDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($seoKeywords, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="robots" content="<?= htmlspecialchars($seoRobots, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="theme-color" content="#059669">
    <link rel="canonical" href="<?= htmlspecialchars($seoCanonical, ENT_QUOTES, 'UTF-8') ?>">

    <title><?= htmlspecialchars($seoTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta property="og:locale" content="fr_MG">
    <meta property="og:type" content="<?= htmlspecialchars($seoOgType, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($seoTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($seoDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:url" content="<?= htmlspecialchars($seoCanonical, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image" content="<?= htmlspecialchars($seoImage, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:card" content="<?= htmlspecialchars($seoTwitterCard, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:title" content="<?= htmlspecialchars($seoTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($seoDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($seoImage, ENT_QUOTES, 'UTF-8') ?>">
    <script type="application/ld+json"><?= json_encode($seoJsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>

    <!-- ── Google Fonts ──────────────────────────────────── -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- ── Tailwind CSS CDN ──────────────────────────────── -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                fontFamily: {
                    'display': ['Outfit', 'Inter', 'system-ui', 'sans-serif'],
                    'body': ['Inter', 'system-ui', 'sans-serif'],
                },
                colors: {
                    primary: {
                        50: '#f0fdf4', 100: '#dcfce7', 200: '#bbf7d0', 300: '#86efac',
                        400: '#4ade80', 500: '#22c55e', 600: '#16a34a', 700: '#15803d',
                        800: '#166534', 900: '#14532d',
                    }
                }
            }
        }
    }
    </script>

    <!-- ── Alpine.js CDN ─────────────────────────────────── -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>


    <!-- ── Custom CSS ────────────────────────────────────── -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" />
    <script defer src="<?= APP_URL ?>/assets/js/theme.js"></script>

    <!-- ── Favicon ───────────────────────────────────────── -->
    <link rel="icon" href="<?= APP_URL ?>/assets/images/logo.jpg" type="image/jpeg">
        <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (window.AOS) {
                    AOS.init({
                        once: true,
                        duration: 900,
                        offset: 80,
                        easing: 'ease-in-out',
                    });
                }
            });
        </script>
</head>
<body class="font-body text-gray-800 antialiased"
    x-data="{ 
        mobileOpen: false, 
        userOpen: false,
        auth: {
            open: false,
            mode: 'login',
            role: 'client'
        },
        openLogin(role = 'client') {
            this.auth.mode = 'login';
            this.auth.role = role;
            this.auth.open = true;
        },
        openRegister() {
            this.auth.mode = 'register';
            this.auth.open = true;
        }
    }"
    @keydown.escape="mobileOpen = false; userOpen = false; auth.open = false"
>
    <a href="#main-content" class="skip-link">Aller au contenu</a>
    <div id="global-fruit-stream" class="site-fruit-stream" aria-hidden="true"></div>

<!-- ╔══════════════════════════════════════════════════════════╗ -->
<!-- ║  NAVBAR — Glassmorphism                                 ║ -->
<!-- ╚══════════════════════════════════════════════════════════╝ -->
<header
    class="bg-white border-b border-gray-100 dark:border-slate-800 fixed top-0 left-0 right-0 z-50 shadow-sm"
>
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" role="navigation" aria-label="Main navigation">
        <div class="flex items-center justify-between h-20 lg:h-24">

            <!-- ── Logo ──────────────────────────────────── -->
            <a href="<?= APP_URL ?>" class="flex items-center flex-shrink-0 group">
                <img
                    loading="lazy"
                    decoding="async"
                    src="<?= APP_URL ?>/assets/images/logo.jpg?v=<?= time() ?>"
                    alt="<?= APP_NAME ?>"
                    class="h-12 lg:h-16 w-auto object-contain rounded-xl shadow-sm"
                >
            </a>

            <!-- ── Center: Desktop Nav ────────────────────── -->
            <div class="hidden md:flex items-center gap-1">
                <a href="<?= APP_URL ?>"
                   class="px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 flex items-center gap-2 <?= navActive('index', $currentPath) ?>" <?php if (navIsActive('index', $currentPath)): ?>aria-current="page"<?php endif; ?> title="Accueil">
                    <svg aria-hidden="true" class="w-4 h-4 text-current" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75L12 4l9 5.75V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.75z"/>
                    </svg>
                    <span>Accueil</span>
                </a>
                <a href="<?= APP_URL ?>/client/creer-jus.php"
                   class="px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 flex items-center gap-2 <?= navActive('creer-jus', $currentPath) ?>" <?php if (navIsActive('creer-jus', $currentPath)): ?>aria-current="page"<?php endif; ?> title="Créer mon jus">
                    <svg aria-hidden="true" class="w-4 h-4 text-current" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                    </svg>
                    <span>Créer mon jus</span>
                </a>
                <a href="<?= APP_URL ?>/client/conseils-sante.php"
                   class="px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200 flex items-center gap-2 <?= navActive('conseils', $currentPath) ?>" <?php if (navIsActive('conseils', $currentPath)): ?>aria-current="page"<?php endif; ?> title="Conseils Santé">
                    <svg aria-hidden="true" class="w-4 h-4 text-current" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 18.343l-6.828-6.828a4 4 0 010-5.656z" />
                    </svg>
                    <span>Conseils Santé</span>
                </a>
            </div>

            <!-- ── Right: Cart + Auth ──────────────────────── -->
            <div class="flex items-center gap-2">
                <!-- Cart Icon -->
                <a href="<?= APP_URL ?>/client/panier.php"
                   class="relative p-2.5 rounded-xl text-gray-500 hover:text-green-600 hover:bg-green-50 transition-all duration-200"
                   aria-label="Panier (<?= $cartCount ?> article<?= $cartCount !== 1 ? 's' : '' ?>)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <?php if ($cartCount > 0): ?>
                    <span class="absolute -top-0.5 -right-0.5 bg-gradient-to-r from-orange-500 to-red-500 text-white text-[10px] font-bold
                                 rounded-full min-w-[1.2rem] h-[1.2rem] flex items-center justify-center px-1 leading-none
                                 shadow-md shadow-orange-200/50 animate-scale-in">
                        <?= $cartCount > 99 ? '99+' : $cartCount ?>
                    </span>
                    <?php endif; ?>
                </a>

                <!-- Auth: Guest -->
                <?php if (!$isLoggedIn): ?>
                <div class="hidden md:flex items-center gap-2">
                    <button @click="openLogin('client')"
                       class="px-6 py-2.5 text-sm font-bold bg-gradient-to-r from-green-600 to-emerald-600 text-white
                              rounded-xl shadow-md shadow-green-200/50 hover:shadow-lg hover:shadow-green-300/50
                              transition-all duration-300 hover:-translate-y-0.5 btn-shine flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span>Commencer</span>
                    </button>
                </div>

                <!-- Auth: Logged-in User Dropdown -->
                <?php else: ?>
                <div class="hidden md:block relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-gray-600
                                   hover:bg-green-50 transition-all duration-200 focus:outline-none
                                   focus:ring-2 focus:ring-green-400/30"
                            :aria-expanded="open"
                            aria-haspopup="true">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl
                                     bg-gradient-to-br from-green-500 to-emerald-600 text-white text-xs font-bold uppercase select-none shadow-sm">
                            <?= mb_substr($userName, 0, 1, 'UTF-8') ?>
                        </span>
                        <span class="text-sm font-medium max-w-[100px] truncate"><?= $userName ?></span>
                        <svg class="w-4 h-4 transition-transform duration-200 text-gray-400" :class="open ? 'rotate-180' : ''"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                         class="absolute right-0 mt-2 w-56 rounded-2xl bg-white shadow-xl shadow-black/5
                                ring-1 ring-black/5 divide-y divide-gray-100/50 z-50 overflow-hidden"
                         style="display:none;"
                         role="menu">
                        <div class="px-4 py-3.5 bg-gradient-to-r from-green-50 to-emerald-50">
                            <p class="text-[11px] text-gray-500 font-medium uppercase tracking-wider">Connecté</p>
                            <p class="text-sm font-semibold text-gray-900 truncate mt-0.5"><?= $userName ?></p>
                        </div>
                        <div class="py-1.5" role="none">
                            <a href="<?= APP_URL ?>/client/profil.php"
                               class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-green-50
                                      hover:text-green-700 transition-colors" role="menuitem">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Mon Profil
                            </a>
                            <a href="<?= APP_URL ?>/client/profil.php?tab=commandes"
                               class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-green-50
                                      hover:text-green-700 transition-colors" role="menuitem">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                                             M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Mes Commandes
                            </a>
                        </div>
                        <div class="py-1.5" role="none">
                            <a href="<?= APP_URL ?>/auth/logout.php"
                               class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50
                                      transition-colors" role="menuitem">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7
                                             a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Déconnexion
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Mobile Hamburger -->
                <button @click="mobileOpen = !mobileOpen"
                        class="md:hidden p-2.5 rounded-xl text-gray-500 hover:bg-gray-100 transition-colors
                               focus:outline-none focus:ring-2 focus:ring-green-400/30"
                        :aria-expanded="mobileOpen"
                        aria-controls="mobile-menu"
                        aria-label="Menu">
                    <svg x-show="!mobileOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2" style="display:none;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div><!-- /Right -->
        </div><!-- /flex row -->

        <!-- ── Mobile Menu ─────────────────────────────────── -->
        <div id="mobile-menu"
             x-show="mobileOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="md:hidden pb-4 border-t border-gray-100 mt-1"
             style="display:none;">

            <div class="pt-3 space-y-1">
                <a href="<?= APP_URL ?>"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-700
                          hover:bg-green-50 hover:text-green-700 transition-colors" <?php if (navIsActive('index', $currentPath)): ?>aria-current="page"<?php endif; ?> title="Accueil">
                    <svg aria-hidden="true" class="w-5 h-5 text-current" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75L12 4l9 5.75V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.75z"/>
                    </svg>
                    Accueil
                </a>
                <a href="<?= APP_URL ?>/client/creer-jus.php"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-700
                          hover:bg-green-50 hover:text-green-700 transition-colors" <?php if (navIsActive('creer-jus', $currentPath)): ?>aria-current="page"<?php endif; ?> title="Créer mon jus">
                    <svg aria-hidden="true" class="w-5 h-5 text-current" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                    </svg>
                    Créer mon jus
                </a>
                <a href="<?= APP_URL ?>/client/conseils-sante.php"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-700
                          hover:bg-green-50 hover:text-green-700 transition-colors" <?php if (navIsActive('conseils', $currentPath)): ?>aria-current="page"<?php endif; ?> title="Conseils Santé">
                    <svg aria-hidden="true" class="w-5 h-5 text-current" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 18.343l-6.828-6.828a4 4 0 010-5.656z" />
                    </svg>
                    Conseils Santé
                </a>
            </div>

            <div class="mt-3 pt-3 border-t border-gray-100">
                <?php if (!$isLoggedIn): ?>
                <div class="grid grid-cols-2 gap-2 px-3">
                    <a href="<?= APP_URL ?>/auth/login.php"
                       class="text-center py-2.5 text-sm font-medium text-gray-700 bg-gray-100
                              rounded-xl hover:bg-gray-200 transition-colors">
                        Connexion
                    </a>
                    <a href="<?= APP_URL ?>/auth/register.php"
                       class="text-center py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-green-600 to-emerald-600
                              rounded-xl shadow-md shadow-green-200/50 transition-colors">
                        S'inscrire
                    </a>
                </div>
                <?php else: ?>
                <div class="px-3 space-y-1">
                    <div class="flex items-center gap-3 px-3 py-2.5 bg-green-50 rounded-xl mb-2">
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl
                                     bg-gradient-to-br from-green-500 to-emerald-600 text-white text-sm font-bold uppercase shadow-sm">
                            <?= mb_substr($userName, 0, 1, 'UTF-8') ?>
                        </span>
                        <span class="text-sm font-semibold text-gray-800"><?= $userName ?></span>
                    </div>
                    <a href="<?= APP_URL ?>/client/profil.php"
                       class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-green-50 rounded-xl transition-colors">
                        👤 Mon Profil
                    </a>
                    <a href="<?= APP_URL ?>/client/profil.php?tab=commandes"
                       class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-green-50 rounded-xl transition-colors">
                        📋 Mes Commandes
                    </a>
                    <a href="<?= APP_URL ?>/auth/logout.php"
                       class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 rounded-xl transition-colors">
                        🚪 Déconnexion
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div><!-- /Mobile Menu -->
    </nav>
</header>

<!-- Spacer for fixed navbar -->
<div class="h-16"></div>

<!-- ╔══════════════════════════════════════════════════════════╗ -->
<!-- ║  MAIN CONTENT WRAPPER (opened here, closed in footer)   ║ -->
<!-- ╚══════════════════════════════════════════════════════════╝ -->
<main id="main-content" class="min-h-screen">
