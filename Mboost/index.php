<?php
/**
* M'Boost — Premium Landing Page
* Cinematic hero, popular juices, how-it-works, benefits, testimonials, CTA
*/
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
session_start();
ensureHeroAdsTable($pdo);

// Fetch popular juices (top 6 ordered)
try {
    $stmt = $pdo->query("
        SELECT jp.*, u.prenom as creator_name
        FROM jus_personnalises jp
        LEFT JOIN users u ON jp.user_id = u.id
        ORDER BY jp.created_at DESC
        LIMIT 6
    ");
    $popularJuices = $stmt->fetchAll();
} catch (PDOException $e) {
    $popularJuices = [];
}

// Fetch published health tips (pinned first)
try {
    $stmt = $pdo->query("
        SELECT id, titre, categories, image_url, description
        FROM annonces_sante
        WHERE statut = 'publie'
        ORDER BY epingle DESC, date_publication DESC
        LIMIT 3
    ");
    $healthTips = $stmt->fetchAll();
} catch (PDOException $e) {
    $healthTips = [];
}

// Fetch hero ads (image/video) for marquee slider
try {
    $stmt = $pdo->query("
        SELECT id, titre, description, media_type, media_url, lien_url
        FROM hero_publicites
        WHERE statut = 'publie'
        ORDER BY ordre_affichage ASC, created_at DESC
        LIMIT 10
    ");
    $heroAds = $stmt->fetchAll();
} catch (PDOException $e) {
    $heroAds = [];
}

$pageTitle = "Accueil - Votre bar à jus naturels";
$pageDescription = "M'Boost : composez votre jus naturel sur mesure, frais et livré à Mahajanga.";
$pageKeywords = "jus naturel, jus sur mesure, detox, livraison Mahajanga, Mboost";
$pageCanonical = APP_URL . '/index.php';
$pageOgType = 'website';
$pageOgImage = APP_URL . '/assets/images/logo.jpg';
$pageJsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => APP_NAME,
    'url' => APP_URL,
    'potentialAction' => [
        '@type' => 'SearchAction',
        'target' => APP_URL . '/client/conseils-sante.php',
        'query-input' => 'required name=search_term_string',
    ],
];
include __DIR__ . '/includes/header.php';

// --- Modal Automatique de Bienvenue ---
?>
<div x-data="{ 
        showPromo: false,
        init() {
            // Affichage automatique après 1.5 secondes
            setTimeout(() => { 
                this.showPromo = true; 
                // Fermeture automatique après 3 secondes
                setTimeout(() => { this.showPromo = false; }, 3000);
            }, 1500);
        }
     }"
     x-show="showPromo"
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-hidden"
     x-cloak
     style="display: none;">
    
    <!-- Backdrop avec flou intense -->
    <div x-show="showPromo"
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="showPromo = false"
         class="fixed inset-0 bg-gray-950/80 backdrop-blur-md transition-opacity"></div>

    <!-- Contenu de la Modale - Image Plein Écran -->
    <div x-show="showPromo"
         x-transition:enter="transition ease-out duration-700"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-500"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-90"
         class="relative bg-transparent rounded-[2.5rem] shadow-2xl max-w-4xl w-full overflow-hidden mx-auto">
        
        <!-- Image seule remplissant tout le modale -->
        <img src="<?= APP_URL ?>/assets/images/logo.jpg" 
             alt="M'Boost Promo" 
             class="w-full h-auto object-contain rounded-[2.5rem] shadow-2xl">

        <!-- Barre de progression discrète en bas -->
        <div class="absolute bottom-0 left-0 right-0 h-1.5 bg-black/20 backdrop-blur-sm">
            <div class="h-full bg-green-500 transition-all duration-[3000ms] ease-linear w-full"
                 x-init="setTimeout(() => { $el.style.width = '0%' }, 1500)"></div>
        </div>
    </div>
</div>
<?php

// Sections
include __DIR__ . '/includes/sections/hero.php';
include __DIR__ . '/includes/sections/how-it-works.php';
include __DIR__ . '/includes/sections/about.php';
include __DIR__ . '/includes/sections/ingredients-showcase.php';
include __DIR__ . '/includes/sections/popular-juices.php';
include __DIR__ . '/includes/sections/benefits.php';
include __DIR__ . '/includes/sections/health-tips.php';
include __DIR__ . '/includes/sections/testimonials.php';
include __DIR__ . '/includes/sections/cta-banner.php';

include __DIR__ . '/includes/footer.php';
?>
