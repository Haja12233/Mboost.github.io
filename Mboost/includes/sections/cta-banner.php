<!-- ╔══════════════════════════════════════════════════════════╗ -->
<!-- ║  CTA BANNER                                             ║ -->
<!-- ╚══════════════════════════════════════════════════════════╝ -->
<section class="relative overflow-hidden py-20 lg:py-24 dark:bg-slate-900">
    <div class="relative z-10 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black font-display text-gray-900 dark:text-white leading-tight uppercase tracking-tighter">
            Prêt à booster<br><span class="text-green-600">votre santé?</span>
        </h2>
        <p class="mt-6 text-lg text-gray-600 max-w-xl mx-auto dark:text-slate-400 font-medium italic">
            "Commencez dès maintenant en créant votre premier jus personnalisé. C'est simple, rapide et délicieux."
        </p>
        
        <div class="mt-10 flex flex-wrap justify-center gap-4">
            <a href="<?= APP_URL ?>/client/creer-jus.php"
               class="group px-10 py-5 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold text-sm rounded-2xl shadow-xl shadow-green-500/20
                      hover:shadow-2xl hover:shadow-green-500/40 hover:-translate-y-1.5 transition-all duration-300 btn-shine flex items-center gap-2">
                Commencer maintenant
                <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
            <?php if (!isLoggedIn()): ?>
            <a href="<?= APP_URL ?>/auth/register.php"
               class="px-10 py-5 bg-gray-100 dark:bg-slate-800 text-gray-900 dark:text-white font-bold text-sm rounded-2xl
                      hover:bg-gray-200 dark:hover:bg-slate-700 hover:-translate-y-1 transition-all duration-300">
                Créer un compte
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>
