<?php
/**
 * M'Boost — Admin Layout Header (Shared Sidebar + Top Bar)
 * Include this at the top of every admin page after setting $pageTitle and $activePage
 */

// Determine which page is active
$activePage = $activePage ?? '';
$adminName = htmlspecialchars(($_SESSION['admin_prenom'] ?? '') . ' ' . ($_SESSION['admin_nom'] ?? ''));
$adminInitial = mb_strtoupper(mb_substr($_SESSION['admin_prenom'] ?? 'A', 0, 1));
$adminRole = $_SESSION['admin_role'] ?? 'admin';

$adminNavItems = [
    ['href' => 'dashboard.php', 'label' => 'Dashboard', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>'],
    ['href' => 'commandes.php', 'label' => 'Commandes', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>'],
    ['href' => 'ingredients.php', 'label' => 'Ingrédients', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>'],
    ['href' => 'clients.php', 'label' => 'Clients', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>'],
    ['href' => 'annonces.php', 'label' => 'Annonces Santé', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],
    ['href' => 'publicites.php', 'label' => 'Publicités Hero', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2m-1-1v2m0 12v2m-7-7H3m18 0h-2m-2.05-4.95l1.414-1.414M7.05 16.95l-1.414 1.414m0-12.728L7.05 7.05m9.9 9.9l1.414 1.414M12 8a4 4 0 100 8 4 4 0 000-8z"/>'],
    ['href' => 'historique-statuts.php', 'label' => 'Historique Statuts', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
    ['href' => 'paiements.php', 'label' => 'Paiements', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a1 1 0 11-2 0 1 1 0 012 0z"/>'],
    ['href' => 'parametres.php', 'label' => 'Paramètres', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'],
];
?>
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> Admin — <?= $pageTitle ?? 'Dashboard' ?></title>
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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css">
    <link rel="icon" href="<?= APP_URL ?>/assets/images/logo.jpg" type="image/jpeg">
</head>
<body class="font-body antialiased bg-gray-50" x-data="{ sidebarOpen: false }">

    <!-- Mobile Overlay -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false"
         class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 lg:hidden"
         x-transition.opacity></div>

    <!-- Sidebar -->
    <aside class="admin-sidebar fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-slate-900 to-slate-950 text-white transform transition-transform duration-300 overflow-hidden"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

        <!-- Logo -->
        <div class="flex items-center gap-3 px-6 py-5 border-b border-white/[0.06]">
            <img src="<?= APP_URL ?>/assets/images/logo.jpg" alt="<?= APP_NAME ?>"
                 class="h-12 w-36 rounded-lg object-cover brightness-110">
            <span class="text-[10px] text-green-400 font-bold uppercase tracking-wider bg-green-500/10 px-2 py-0.5 rounded-md">Admin</span>
        </div>

        <!-- Nav -->
        <nav class="p-3 space-y-0.5 mt-2 flex-1 overflow-y-auto">
            <?php foreach ($adminNavItems as $item):
                $isActive = basename($item['href'], '.php') === $activePage;
            ?>
            <a href="<?= $item['href'] ?>"
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                      <?= $isActive
                          ? 'bg-green-600/90 text-white shadow-lg shadow-green-500/20'
                          : 'text-gray-400 hover:text-white hover:bg-white/[0.06]' ?>">
                <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?= $item['icon'] ?></svg>
                <?= $item['label'] ?>
            </a>
            <?php endforeach; ?>
        </nav>

        <!-- User card -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-white/[0.06] bg-slate-950/50">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-sm font-bold shadow-md"><?= $adminInitial ?></div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold truncate"><?= $adminName ?></p>
                    <p class="text-[10px] text-gray-500 uppercase tracking-wider"><?= $adminRole ?></p>
                </div>
            </div>
            <a href="<?= APP_URL ?>/auth/logout.php"
               class="flex items-center gap-2 px-3 py-2 text-red-400/70 hover:text-red-300 hover:bg-red-500/10 rounded-lg text-xs font-medium transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Déconnexion
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen">
        <!-- Top Bar -->
        <header class="bg-white/80 backdrop-blur-sm border-b border-gray-100 sticky top-0 z-30">
            <div class="flex items-center justify-between px-6 py-3.5">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 hover:bg-gray-100 rounded-xl transition-colors">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="text-lg font-bold font-display text-gray-900"><?= $pageTitle ?? 'Dashboard' ?></h1>
                </div>
                <div class="flex items-center gap-3">
                    <a href="<?= APP_URL ?>" target="_blank"
                       class="text-xs text-gray-400 hover:text-green-600 transition-colors flex items-center gap-1.5 font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Voir le site
                    </a>
                </div>
            </div>
        </header>

        <div class="p-6 space-y-6">
