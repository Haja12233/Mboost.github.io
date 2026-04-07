<!-- ╔══════════════════════════════════════════════════════════╗ -->
<!-- ║  POPULAR JUICES                                         ║ -->
<!-- ╚══════════════════════════════════════════════════════════╝ -->
<section id="popular-juices" class="popular-juices-section relative py-20 lg:py-28 overflow-hidden dark:bg-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16">
            <span class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-orange-100 text-orange-700 text-xs font-bold uppercase tracking-widest mb-4 dark:bg-orange-900/30 dark:text-orange-400">
                Tendances
            </span>
            <h2 class="text-3xl sm:text-4xl font-extrabold font-display text-gray-900 dark:text-white">Jus Populaires</h2>
            <p class="mt-4 text-gray-500 max-w-lg mx-auto dark:text-slate-400">Découvrez les créations les plus appréciées par notre communauté.</p>
        </div>
        
        <?php if (!empty($popularJuices)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            <?php
            $juiceColors = [
                ['from-green-400', 'to-emerald-500', 'shadow-green-200'],
                ['from-orange-400', 'to-amber-500', 'shadow-orange-200'],
                ['from-pink-400', 'to-rose-500', 'shadow-pink-200'],
                ['from-purple-400', 'to-violet-500', 'shadow-purple-200'],
                ['from-teal-400', 'to-cyan-500', 'shadow-teal-200'],
                ['from-yellow-400', 'to-orange-500', 'shadow-yellow-200'],
            ];
            foreach ($popularJuices as $i => $juice):
                $color = $juiceColors[$i % count($juiceColors)];
            ?>
            <div class="group bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 dark:bg-slate-900 dark:border-slate-800">
                <div class="h-48 bg-gray-50 dark:bg-slate-800/50 relative flex items-center justify-center overflow-hidden border-b border-gray-50 dark:border-slate-700">
                    <span class="text-7xl group-hover:scale-125 group-hover:rotate-12 transition-transform duration-500 ease-out z-10">🥤</span>
                    <div class="absolute top-4 right-4">
                        <span class="px-3 py-1 rounded-full bg-white text-gray-900 text-[10px] font-black uppercase tracking-wider border border-gray-100 dark:bg-slate-800 dark:text-white dark:border-slate-700">
                            <?= ucfirst($juice['taille'] ?? 'moyen') ?>
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-black font-display text-gray-900 text-xl group-hover:text-green-600 transition-colors dark:text-white dark:group-hover:text-green-400">
                            <?= htmlspecialchars($juice['nom'] ?? 'Jus personnalisé') ?>
                        </h3>
                    </div>
                    <p class="text-xs font-medium text-gray-400 dark:text-slate-500 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                        par <?= htmlspecialchars($juice['creator_name'] ?? 'Anonyme') ?>
                    </p>
                    <div class="mt-6 pt-4 border-t border-gray-50 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex flex-col">
                            <span class="text-[10px] text-gray-400 uppercase font-bold tracking-tighter">Prix total</span>
                            <span class="text-xl font-black font-display text-green-600 dark:text-green-400">
                                <?= formatPrice($juice['prix_total'] ?? 0) ?>
                            </span>
                        </div>
                        <a href="<?= APP_URL ?>/client/creer-jus.php"
                           class="p-3 rounded-xl bg-green-50 text-green-600 hover:bg-green-600 hover:text-white transition-all duration-300 group/btn dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-600 dark:hover:text-white">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            <?php
            $showcaseJuices = [
                ['name' => 'Green Detox', 'emoji' => '🥬', 'desc' => 'Épinard, concombre, pomme, gingembre', 'price' => '8 000 Ar', 'from' => 'from-green-400', 'to' => 'to-emerald-500'],
                ['name' => 'Tropical Boost', 'emoji' => '🥭', 'desc' => 'Mangue, ananas, passion, chia', 'price' => '10 000 Ar', 'from' => 'from-orange-400', 'to' => 'to-amber-500'],
                ['name' => 'Berry Vitamine', 'emoji' => '🫐', 'desc' => 'Myrtille, fraise, banane, lin', 'price' => '9 000 Ar', 'from' => 'from-purple-400', 'to' => 'to-violet-500'],
            ];
            foreach ($showcaseJuices as $j): ?>
            <div class="group bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm hover:shadow-xl dark:bg-slate-900 dark:border-slate-800 transition-all duration-300">
                <div class="h-32 bg-gray-50 dark:bg-slate-800/50 relative flex items-center justify-center border-b border-gray-50 dark:border-slate-700">
                    <?php
                    // Correction des emojis pour compatibilité universelle
                    $emoji = $j['emoji'];
                    if ($j['name'] === 'Berry Vitamine') {
                        $emoji = '🍓'; // Emoji fraise, compatible partout
                    }
                    ?>
                    <span class="text-5xl opacity-90 group-hover:scale-110 transition-transform duration-300"><?= $emoji ?></span>
                </div>
                <div class="p-5">
                    <h3 class="font-bold font-display text-gray-900 text-lg dark:text-white"><?= $j['name'] ?></h3>
                    <p class="text-xs text-gray-400 mt-1 dark:text-slate-500"><?= $j['desc'] ?></p>
                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-lg font-extrabold font-display text-green-600 dark:text-green-400">
                            <?= $j['price'] ?>
                            <?php
                            // Conversion simple pour l'affichage Euro (1€ = 5000Ar)
                            $priceNum = (int) filter_var($j['price'], FILTER_SANITIZE_NUMBER_INT);
                            $eur = $priceNum > 0 ? ' (' . number_format($priceNum / 5000, 2, ',', ' ') . ' €)' : '';
                            echo $eur;
                            ?>
                        </span>
                        <a href="<?= APP_URL ?>/client/creer-jus.php"
                           class="text-xs font-semibold text-green-600 hover:text-green-700 flex items-center gap-1 dark:text-green-400">
                            Créer →
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <div class="text-center mt-12">
            <a href="<?= APP_URL ?>/client/creer-jus.php"
               class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold text-sm
                      rounded-2xl shadow-lg shadow-green-200/50 hover:shadow-xl hover:shadow-green-300/60
                      hover:-translate-y-1 transition-all duration-300 btn-shine">
                Créer mon propre jus
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
    </div>
</section>
