<!-- ╔══════════════════════════════════════════════════════════╗ -->
<!-- ║  HEALTH TIPS PREVIEW                                    ║ -->
<!-- ╚══════════════════════════════════════════════════════════╝ -->
<?php if (!empty($healthTips)): ?>
<section class="py-20 lg:py-28 dark:bg-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-pink-100 text-pink-700 text-xs font-bold uppercase tracking-widest mb-4 dark:bg-pink-900/30 dark:text-pink-400">
                Bien-être
            </span>
            <h2 class="text-3xl sm:text-4xl font-extrabold font-display text-gray-900 dark:text-white">Conseils Santé</h2>
            <p class="mt-4 text-gray-500 max-w-lg mx-auto dark:text-slate-400">Découvrez nos conseils nutritionnels et nos recettes pour une santé optimale.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
            <?php foreach ($healthTips as $tip): ?>
            <a href="<?= APP_URL ?>/client/conseils-sante.php"
               class="group bg-white rounded-2xl border border-gray-100 overflow-hidden card-hover shadow-sm hover:shadow-xl dark:bg-slate-900 dark:border-slate-800">
                <div class="h-40 bg-gradient-to-br from-green-100 to-emerald-50 flex items-center justify-center relative overflow-hidden dark:from-slate-800 dark:to-slate-900">
                    <?php
                    $tipImage = (string) ($tip['image_url'] ?? '');
                    $tipPath = dirname(dirname(__DIR__)) . '/uploads/annonces/' . $tipImage;
                    $hasTipImage = ($tipImage !== '' && is_file($tipPath));
                    ?>
                    <?php if ($hasTipImage): ?>
                        <img src="<?= APP_URL ?>/uploads/annonces/<?= htmlspecialchars($tipImage) ?>"
                             alt="<?= htmlspecialchars($tip['titre']) ?>"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <?php else: ?>
                        <span class="text-5xl opacity-60">🌿</span>
                    <?php endif; ?>
                </div>
                <div class="p-5">
                    <?php
                    $categories = json_decode($tip['categories'] ?? '[]', true) ?: [];
                    if (!empty($categories)): ?>
                    <div class="flex flex-wrap gap-1.5 mb-3">
                        <?php foreach (array_slice($categories, 0, 2) as $cat): ?>
                            <span class="badge badge-success dark:bg-green-900/50 dark:text-green-300"><?= htmlspecialchars($cat) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <h3 class="font-bold font-display text-gray-900 group-hover:text-green-700 transition-colors line-clamp-2 dark:text-white dark:group-hover:text-green-400">
                        <?= htmlspecialchars($tip['titre']) ?>
                    </h3>
                    <p class="text-sm text-gray-500 mt-2 line-clamp-2 dark:text-slate-400"><?= htmlspecialchars(mb_substr(strip_tags($tip['description'] ?? ''), 0, 100)) ?>...</p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-10">
            <a href="<?= APP_URL ?>/client/conseils-sante.php"
               class="inline-flex items-center gap-2 text-sm font-semibold text-green-600 hover:text-green-700 transition-colors group dark:text-green-400 dark:hover:text-green-300">
                Voir tous les conseils
                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>
