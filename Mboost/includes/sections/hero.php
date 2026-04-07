<!-- ╔══════════════════════════════════════════════════════════╗ -->
<!-- ║  HERO SECTION                                           ║ -->
<!-- ╚══════════════════════════════════════════════════════════╝ -->
<section class="relative min-h-[600px] lg:min-h-screen flex items-center overflow-hidden pt-20 lg:pt-24 dark:bg-slate-900">
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20 flex flex-col items-center">
        <!-- Logo au centre -->
        <div class="animate-slide-up -mt-36 lg:-mt-44 mb-8 lg:mb-10">
            <img src="<?= APP_URL ?>/assets/images/logo_blanc_sans_arrieplan.webp?v=<?= time() ?>" alt="M'Boost" class="h-28 lg:h-36 w-56 lg:w-[24rem] object-contain object-center">
        </div>

        <div class="flex flex-col lg:flex-row items-center lg:items-start lg:justify-between gap-12 lg:gap-16 xl:gap-20 w-full">
            <div class="flex flex-col w-full lg:w-[58%] max-w-3xl items-center lg:items-start text-center lg:text-left">
                <!-- Badge textuel au dessus du texte (place d'origine) -->
                <div class="animate-slide-up inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white shadow-md border border-gray-100 text-gray-800 text-xs font-bold mb-6 dark:bg-slate-800 dark:border-slate-700 dark:text-white">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
                    100% naturel • Fait à Mahajanga
                </div>

                <h1 class="animate-slide-up delay-100 text-5xl sm:text-7xl lg:text-7xl xl:text-8xl font-black font-display text-gray-900 dark:text-white leading-[0.9] tracking-tighter mb-8" style="max-width: 900px;">
                    Boostez votre 
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-300 via-orange-400 to-red-500 animate-gradient">énergie</span>
                </h1>
                
                <p class="animate-slide-up delay-200 text-lg lg:text-base text-gray-600 dark:text-gray-400 mb-10 leading-relaxed max-w-2xl font-medium xl:whitespace-nowrap">
                    Composez votre cocktail de vitamines idéal. Fraîcheur garantie et livraison express à Mahajanga.
                </p>

                <div class="animate-slide-up delay-300 flex flex-wrap lg:flex-nowrap items-center justify-center lg:justify-start gap-5">
                    <a href="<?= APP_URL ?>/client/creer-jus.php" 
                       class="px-10 py-5 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-extrabold text-base rounded-2xl shadow-2xl shadow-green-500/30 hover:shadow-green-500/50 hover:-translate-y-1.5 transition-all duration-300 btn-shine flex items-center gap-3 whitespace-nowrap">
                        Démarrer ma création
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <a href="#popular-juices" class="px-10 py-5 bg-gray-100 dark:bg-slate-800 text-gray-900 dark:text-white font-bold text-base rounded-2xl hover:bg-gray-200 dark:hover:bg-slate-700 transition-all duration-300 hover:-translate-y-1 whitespace-nowrap">
                        Voir les tendances
                    </a>
                </div>

                <!-- Stats simple et compacte -->
    <div class="animate-slide-up delay-400 mt-12 p-6 rounded-3xl bg-gray-50 dark:bg-slate-800/50 border border-gray-100 dark:border-slate-700 flex items-center justify-around lg:justify-start gap-8 lg:gap-12 w-full lg:w-fit shadow-sm">
        <div class="flex flex-col items-center lg:items-start">
            <span class="text-gray-900 dark:text-white font-black text-3xl font-display leading-none">25+</span>
            <span class="text-green-600 dark:text-green-400 text-[10px] uppercase tracking-widest font-bold mt-1">Ingrédients</span>
        </div>
        <div class="w-px h-10 bg-gray-200 dark:bg-slate-700 hidden sm:block"></div>
        <div class="flex flex-col items-center lg:items-start">
            <span class="text-gray-900 dark:text-white font-black text-3xl font-display leading-none">15'</span>
            <span class="text-green-600 dark:text-green-400 text-[10px] uppercase tracking-widest font-bold mt-1">Livraison</span>
        </div>
        <div class="w-px h-10 bg-gray-200 dark:bg-slate-700 hidden sm:block"></div>
        <div class="flex flex-col items-center lg:items-start">
            <span class="text-gray-900 dark:text-white font-black text-3xl font-display leading-none">4.9/5</span>
            <span class="text-green-600 dark:text-green-400 text-[10px] uppercase tracking-widest font-bold mt-1">Satisfaction</span>
        </div>
    </div>
            </div>

            <!-- Hero Ads Slider (Right side on Desktop) -->
            <?php
            $defaultHeroAds = [
                [
                    'media_type' => 'image',
                    'media_url' => 'hero-slider/hero-1.jpg',
                    'titre' => 'Jus carotte orange',
                    'source' => 'assets',
                ],
                [
                    'media_type' => 'image',
                    'media_url' => 'hero-slider/hero-2.jpg',
                    'titre' => 'Jus fruits frais',
                    'source' => 'assets',
                ],
                [
                    'media_type' => 'image',
                    'media_url' => 'hero-slider/hero-3.jpg',
                    'titre' => 'Assortiment de jus',
                    'source' => 'assets',
                ],
                [
                    'media_type' => 'image',
                    'media_url' => 'hero-slider/hero-4.jpg',
                    'titre' => 'Plateau de fruits',
                    'source' => 'assets',
                ],
            ];

            $dbHeroAds = [];
            if (isset($heroAds) && is_array($heroAds)) {
                foreach ($heroAds as $ad) {
                    if (!is_array($ad)) {
                        continue;
                    }
                    if (empty($ad['media_url'])) {
                        continue;
                    }
                    $dbHeroAds[] = $ad;
                }
            }

            // Use "Publicité Hero" published medias when available, otherwise fallback to defaults.
            $displayHeroAds = !empty($dbHeroAds) ? $dbHeroAds : $defaultHeroAds;
            ?>
            <div class="w-full lg:w-[34%] xl:w-[30%] lg:ml-auto max-w-[340px] animate-slide-in-right delay-200">
                <div id="hero-ads-gradient-section" class="relative rounded-[20px] overflow-hidden shadow-2xl shadow-black/40 border border-white/20 bg-white/5">
                    <div class="hero-ads-marquee relative h-full overflow-hidden">
                        <div class="hero-ads-track flex transition-transform duration-500 ease-out h-full">
                            <?php foreach ($displayHeroAds as $ad): ?>
                            <article class="hero-ad-item flex-shrink-0 w-full h-full min-h-full flex flex-col items-center">
                                <div class="w-full h-full min-h-full relative group/ad">
                                    <?php
                                    $isCustom = (($ad['source'] ?? '') === 'custom');
                                    $isAssets = (($ad['source'] ?? '') === 'assets');
                                    if ($isCustom) {
                                        $mediaBaseUrl = APP_URL . '/assets/images/hero-custom/';
                                    } elseif ($isAssets) {
                                        $mediaBaseUrl = APP_URL . '/assets/images/';
                                    } else {
                                        $mediaBaseUrl = APP_URL . '/uploads/hero-ads/';
                                    }
                                    $rawMediaUrl = (string) ($ad['media_url'] ?? '');
                                    $isAbsolute = (bool) preg_match('~^https?://~i', $rawMediaUrl);
                                    $mediaSrc = $isAbsolute ? $rawMediaUrl : ($mediaBaseUrl . htmlspecialchars($rawMediaUrl));
                                    ?>
                                    <?php if (($ad['media_type'] ?? 'image') === 'video'): ?>
                                        <video class="w-full h-full min-h-full object-cover" src="<?= $mediaSrc ?>" autoplay muted loop playsinline></video>
                                    <?php else: ?>
                                        <img
                                            class="w-full h-full min-h-full object-cover"
                                            src="<?= $mediaSrc ?>"
                                            alt="<?= htmlspecialchars((string)($ad['titre'] ?? 'Jus frais')) ?>"
                                            loading="lazy"
                                            decoding="async"
                                            onerror="this.onerror=null;this.src='<?= APP_URL ?>/assets/images/logo.jpg';"
                                        >
                                    <?php endif; ?>
                                    
                                    <!-- Overlay content inside the image frame -->
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent flex flex-col justify-end p-6 opacity-0 group-hover/ad:opacity-100 transition-opacity duration-300">
                                        <h3 class="text-white font-bold text-lg mb-1 leading-tight"><?= htmlspecialchars($ad['titre']) ?></h3>
                                        <?php if (!empty($ad['description'] ?? '')): ?>
                                            <div class="text-white/90 text-xs line-clamp-2 italic mb-3">
                                                <?= strip_tags((string)($ad['description'] ?? '')) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($ad['lien_url'] ?? '')): ?>
                                            <a class="w-fit px-4 py-1.5 rounded-full bg-green-500 text-white font-bold text-xs hover:bg-green-600 transition shadow-lg" href="<?= htmlspecialchars((string)($ad['lien_url'] ?? '')) ?>" target="_blank" rel="noopener noreferrer">Voir l'offre</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Dots -->
                    <div class="hero-slider-dots absolute bottom-4 left-0 right-0 flex justify-center gap-2 z-20"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function() {
    'use strict';
    document.addEventListener('DOMContentLoaded', function() {
        // ===== GESTION DU DÉGRADÉ =====
        const section = document.getElementById('hero-ads-gradient-section');
        if (section) {
            const savedStyle = localStorage.getItem('heroGradientStyle') || '1';
            applyGradientStyle(savedStyle);
        }
        
        function applyGradientStyle(style) {
            const section = document.getElementById('hero-ads-gradient-section');
            if (!section) return;
            section.classList.remove('gradient-style-1', 'gradient-style-2', 'gradient-style-3');
            section.classList.add('gradient-style-' + style);
        }
        
        window.applyGradientStyle = applyGradientStyle;
        
        // ===== SLIDER HERO =====
        const track = document.querySelector('.hero-ads-track');
        if (!track) return;
        
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const items = Array.from(track.querySelectorAll('.hero-ad-item'));
        if (items.length <= 1) return;

        const dotsContainer = document.querySelector('.hero-slider-dots');
        let dots = [];

        if (dotsContainer) {
            items.forEach((_, i) => {
                const dot = document.createElement('button');
                dot.className = `hero-slider-dot ${i === 0 ? 'active' : ''}`;
                dot.setAttribute('aria-label', `Aller à la diapositive ${i + 1}`);
                dot.addEventListener('click', () => goToIndex(i));
                dotsContainer.appendChild(dot);
                dots.push(dot);
            });
        }
        
        let currentIndex = 0;
        let autoSlideTimer = null;

        function goToIndex(index) {
            if (index < 0) index = items.length - 1;
            if (index >= items.length) index = 0;
            currentIndex = index;
            items.forEach((item, i) => {
                item.classList.toggle('is-active', i === currentIndex);
            });
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === currentIndex);
            });
        }

        function goNext() { goToIndex(currentIndex + 1); }
        
        function startAutoSlide() {
            if (autoSlideTimer || prefersReducedMotion || items.length <= 1) return;
            autoSlideTimer = setInterval(goNext, 5000);
        }
        
        function stopAutoSlide() {
            if (!autoSlideTimer) return;
            clearInterval(autoSlideTimer);
            autoSlideTimer = null;
        }
        
        track.addEventListener('mouseenter', stopAutoSlide);
        track.addEventListener('mouseleave', startAutoSlide);

        goToIndex(0);
        startAutoSlide();
    });

})();
</script>

<style>
#hero-ads-gradient-section.gradient-style-1 {
    background: linear-gradient(90deg, #34d399 0%, #10b981 100%);
    transition: background 0.5s;
}
#hero-ads-gradient-section.gradient-style-2 {
    background: linear-gradient(90deg, #fbbf24 0%, #f59e42 100%);
    transition: background 0.5s;
}
#hero-ads-gradient-section.gradient-style-3 {
    background: linear-gradient(90deg, #f472b6 0%, #fbbf24 100%);
    transition: background 0.5s;
}
</style>
