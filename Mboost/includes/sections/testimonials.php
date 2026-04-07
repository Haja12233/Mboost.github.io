<!-- ╔══════════════════════════════════════════════════════════╗ -->
<!-- ║  TESTIMONIALS                                           ║ -->
<!-- ╚══════════════════════════════════════════════════════════╝ -->
<section class="py-20 lg:py-28 dark:bg-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-yellow-100 text-yellow-700 text-xs font-bold uppercase tracking-widest mb-4 dark:bg-yellow-900/30 dark:text-yellow-400">
                Témoignages
            </span>
            <h2 class="text-3xl sm:text-4xl font-extrabold font-display text-gray-900 dark:text-white">Ce qu'en disent nos clients</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
            <?php
            $testimonials = [
                ['name' => 'Rina M.', 'text' => "Depuis que j'ai découvert M'Boost, je commande chaque semaine. Les jus sont incroyablement frais et la livraison est toujours rapide!", 'role' => 'Cliente régulière', 'emoji' => '🌟'],
                ['name' => 'Hery R.', 'text' => "J'adore pouvoir créer mes propres recettes. Le Green Detox est devenu mon rituel matinal. Merci M'Boost!", 'role' => 'Passionné de fitness', 'emoji' => '💪'],
                ['name' => 'Faniry L.', 'text' => "Le paiement via Mobile Money rend tout si simple. Et les conseils santé m'aident à mieux choisir mes ingrédients.", 'role' => 'Nutritionniste', 'emoji' => '🩺'],
            ];
            foreach ($testimonials as $t): ?>
            <div class="bg-white rounded-2xl p-6 border border-gray-100 card-hover shadow-sm relative dark:bg-slate-800 dark:border-slate-700">
                <div class="absolute top-4 right-4 text-2xl opacity-20"><?= $t['emoji'] ?></div>
                <div class="flex gap-0.5 mb-4">
                    <?php for ($s = 0; $s < 5; $s++): ?>
                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <?php endfor; ?>
                </div>
                <p class="text-gray-600 text-sm leading-relaxed italic dark:text-slate-300">"<?= $t['text'] ?>"</p>
                <div class="mt-5 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-white font-bold text-sm">
                        <?= mb_substr($t['name'], 0, 1) ?>
                    </div>
                    <div>
                        <p class="font-bold text-gray-900 text-sm dark:text-white"><?= $t['name'] ?></p>
                        <p class="text-xs text-gray-400 dark:text-slate-500"><?= $t['role'] ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
