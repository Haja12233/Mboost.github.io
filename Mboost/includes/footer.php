<!-- ╔══════════════════════════════════════════════════════════╗ -->
<!-- ║  FOOTER — M'Boost Premium Footer                        ║ -->
<!-- ╚══════════════════════════════════════════════════════════╝ -->
</main><!-- /main-content opened in header.php -->

<footer class="relative bg-gradient-to-b from-gray-900 via-gray-900 to-gray-950 text-gray-300 mt-auto overflow-hidden border-t border-white/10">
    <!-- Decorative top gradient line -->
    <div class="h-0.5 bg-gradient-to-r from-green-500 via-emerald-400 to-orange-400"></div>

    <!-- Floating decorative circles -->
    <div class="absolute top-12 -left-16 w-40 h-40 bg-green-500/5 rounded-full blur-3xl"></div>
    <div class="absolute bottom-6 -right-20 w-52 h-52 bg-emerald-500/5 rounded-full blur-3xl"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-5 relative z-10">
        <!-- ── Main grid ──────────────────────────────────── -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
            <!-- Brand & Social -->
            <div class="lg:col-span-1 flex flex-col items-start">
                <a href="<?= APP_URL ?>" class="inline-flex items-center mb-4">
                    <img src="<?= APP_URL ?>/assets/images/logo.jpg" alt="<?= APP_NAME ?>" class="h-14 w-auto object-contain rounded-xl shadow-sm">
                </a>
                <p class="text-gray-400 text-sm leading-relaxed max-w-xs mb-3">
                    Votre bar à jus naturels en ligne. Jus personnalisés, ingrédients frais & locaux. 🌿
                </p>
                <div class="flex gap-2 mt-2">
                    <a href="#" aria-label="Facebook" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/5 hover:bg-green-500 text-gray-400 hover:text-white transition-all"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.77,7.46H14.5v-1.9c0-.9.6-1.1,1-1.1h3V.5h-4.33C10.24.5,9.5,3.44,9.5,5.32v2.15h-3v4h3v12h5v-12h3.85l.42-4Z"/></svg></a>
                    <a href="#" aria-label="Instagram" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/5 hover:bg-gradient-to-br hover:from-pink-500 hover:to-orange-500 text-gray-400 hover:text-white transition-all"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12,2.16c3.2,0,3.58.01,4.85.07,3.25.15,4.77,1.69,4.92,4.92.06,1.27.07,1.65.07,4.85s-.01,3.58-.07,4.85c-.15,3.23-1.66,4.77-4.92,4.92-1.27.06-1.64.07-4.85.07s-3.58-.01-4.85-.07c-3.26-.15-4.77-1.7-4.92-4.92-.06-1.27-.07-1.64-.07-4.85s.01-3.58.07-4.85C2.38,3.92,3.9,2.38,7.15,2.23,8.42,2.18,8.8,2.16,12,2.16ZM12,0C8.74,0,8.33.01,7.05.07,2.7.27.27,2.7.07,7.05.01,8.33,0,8.74,0,12s.01,3.67.07,4.95c.2,4.36,2.62,6.78,6.98,6.98C8.33,23.99,8.74,24,12,24s3.67-.01,4.95-.07c4.35-.2,6.78-2.62,6.98-6.98C23.99,15.67,24,15.26,24,12s-.01-3.67-.07-4.95c-.2-4.35-2.62-6.78-6.98-6.98C15.67.01,15.26,0,12,0Zm0,5.84A6.16,6.16,0,1,0,18.16,12,6.16,6.16,0,0,0,12,5.84ZM12,16a4,4,0,1,1,4-4A4,4,0,0,1,12,16ZM18.41,4.15a1.44,1.44,0,1,0,1.44,1.44A1.44,1.44,0,0,0,18.41,4.15Z"/></svg></a>
                    <a href="#" aria-label="WhatsApp" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/5 hover:bg-green-600 text-gray-400 hover:text-white transition-all"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.47,14.38,15.3,13.26a1.48,1.48,0,0,0-1.52.11l-.7.53a6.34,6.34,0,0,1-3-3l.53-.7a1.48,1.48,0,0,0,.11-1.52L9.62,6.53A1.49,1.49,0,0,0,8.14,5.7l-1.3.37a1.49,1.49,0,0,0-1,1.62A13.83,13.83,0,0,0,17.31,19.17a1.49,1.49,0,0,0,1.62-1l.37-1.3A1.49,1.49,0,0,0,17.47,14.38ZM12,0A12,12,0,0,0,1.82,18.43L.18,23.28a.5.5,0,0,0,.63.63L5.65,22.28A12,12,0,1,0,12,0Z"/></svg></a>
                </div>
            </div>

            <!-- Navigation Column -->
            <div>
                <h3 class="text-white font-semibold font-display text-xs uppercase tracking-[0.14em] mb-3">Navigation</h3>
                <ul class="space-y-2">
                    <li><a href="<?= APP_URL ?>" class="text-sm text-gray-400 hover:text-green-400 transition-colors duration-200 flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-gray-600"></span> Accueil</a></li>
                    <li><a href="<?= APP_URL ?>/client/creer-jus.php" class="text-sm text-gray-400 hover:text-green-400 transition-colors duration-200 flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-gray-600"></span> Créer mon jus</a></li>
                    <li><a href="<?= APP_URL ?>/client/conseils-sante.php" class="text-sm text-gray-400 hover:text-green-400 transition-colors duration-200 flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-gray-600"></span> Conseils Santé</a></li>
                    <li><a href="<?= APP_URL ?>/client/panier.php" class="text-sm text-gray-400 hover:text-green-400 transition-colors duration-200 flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-gray-600"></span> Mon Panier</a></li>
                </ul>
            </div>

            <!-- Account Column -->
            <div>
                <h3 class="text-white font-semibold font-display text-xs uppercase tracking-[0.14em] mb-3">Mon Compte</h3>
                <ul class="space-y-2">
                    <li><a href="<?= APP_URL ?>/client/profil.php" class="text-sm text-gray-400 hover:text-green-400 transition-colors duration-200 flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-gray-600"></span> Mon Profil</a></li>
                    <li><a href="<?= APP_URL ?>/client/commande.php" class="text-sm text-gray-400 hover:text-green-400 transition-colors duration-200 flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-gray-600"></span> Mes Commandes</a></li>
                    <li><a href="<?= APP_URL ?>/auth/login.php" class="text-sm text-gray-400 hover:text-green-400 transition-colors duration-200 flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-gray-600"></span> Connexion</a></li>
                    <li><a href="<?= APP_URL ?>/auth/register.php" class="text-sm text-gray-400 hover:text-green-400 transition-colors duration-200 flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-gray-600"></span> Inscription</a></li>
                </ul>
            </div>

            <!-- Contact Column -->
            <div>
                <h3 class="text-white font-semibold font-display text-xs uppercase tracking-[0.14em] mb-3">Contact</h3>
                <ul class="space-y-2.5">
                    <li class="flex items-start gap-3">
                        <svg class="w-4 h-4 mt-0.5 text-green-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="text-sm text-gray-400">Mahajanga, Madagascar</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-green-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <a href="mailto:<?= defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'contact@mboost.mg' ?>" class="text-sm text-gray-400 hover:text-green-400 transition-colors">
                            <?= defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'contact@mboost.mg' ?>
                        </a>
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-green-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span class="text-sm text-gray-400">+261 34 00 000 00</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- ── Bottom bar ──────────────────────────────────── -->
        <div class="border-t border-white/10 pt-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-gray-500">
                &copy; <?= date('Y') ?> <span class="text-gray-400 font-medium"><?= APP_NAME ?></span>. Tous droits réservés.
            </p>
            <div class="flex items-center gap-4 text-xs text-gray-500">
                <a href="#" class="hover:text-gray-300 transition-colors">Politique de confidentialité</a>
                <span class="text-gray-700">•</span>
                <a href="#" class="hover:text-gray-300 transition-colors">CGU</a>
                <span class="text-gray-700">•</span>
                <a href="#" class="hover:text-gray-300 transition-colors">Aide</a>
            </div>
        </div>
    </div>
</footer>

<!-- ╔══════════════════════════════════════════════════════════╗ -->
<!-- ║  TOAST NOTIFICATIONS                                    ║ -->
<!-- ╚══════════════════════════════════════════════════════════╝ -->
<?php
$toastMessages = [];
if (!empty($_SESSION['flash'])) {
    foreach ($_SESSION['flash'] as $flash) {
        $toastMessages[] = [
            'type'    => $flash['type'] ?? 'info',
            'message' => $flash['message'] ?? '',
        ];
    }
    unset($_SESSION['flash']);
}
?>

<div x-data="toastManager()" class="fixed bottom-6 right-6 z-[9999] flex flex-col gap-3 w-full max-w-sm pointer-events-none">
    <template x-for="(toast, index) in toasts" :key="index">
        <div x-show="toast.show"
             x-transition:enter="transition transform ease-out duration-300"
             x-transition:enter-start="translate-x-full opacity-0"
             x-transition:enter-end="translate-x-0 opacity-100"
             x-transition:leave="transition transform ease-in duration-200"
             x-transition:leave-start="translate-x-0 opacity-100"
             x-transition:leave-end="translate-x-full opacity-0"
             class="pointer-events-auto relative rounded-2xl bg-white shadow-xl shadow-black/5 ring-1 ring-black/5 p-4 flex items-start gap-3 overflow-hidden dark:bg-slate-800 dark:ring-white/10"
             role="alert">

            <!-- Type icon -->
            <div class="flex-shrink-0 mt-0.5">
                <template x-if="toast.type === 'success'">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                </template>
                <template x-if="toast.type === 'error'">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </span>
                </template>
                <template x-if="toast.type === 'info'">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                </template>
                <template x-if="toast.type === 'warning'">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </span>
                </template>
            </div>

            <!-- Message -->
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="toast.message"></p>
            </div>

            <!-- Dismiss -->
            <button @click="dismiss(index)" class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <!-- Progress bar -->
            <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-gray-100">
                <div class="h-full transition-all duration-100 ease-linear"
                     :class="{
                         'bg-green-500': toast.type === 'success',
                         'bg-red-500': toast.type === 'error',
                         'bg-blue-500': toast.type === 'info',
                         'bg-yellow-500': toast.type === 'warning',
                     }"
                     :style="'width:' + toast.progress + '%'">
                </div>
            </div>
        </div>
    </template>
</div>

<script>
function toastManager() {
    return {
        toasts: <?= json_encode($toastMessages, JSON_HEX_APOS | JSON_HEX_QUOT) ?>.map(t => ({...t, show: false, progress: 100})),
        init() {
            this.toasts.forEach((toast, index) => {
                setTimeout(() => { toast.show = true; }, 100 + index * 150);
                const duration = 5000;
                const steps = 50;
                const interval = duration / steps;
                let step = 0;
                const timer = setInterval(() => {
                    step++;
                    toast.progress = 100 - (step / steps * 100);
                    if (step >= steps) {
                        clearInterval(timer);
                        this.dismiss(index);
                    }
                }, interval);
            });
        },
        dismiss(index) {
            if (this.toasts[index]) {
                this.toasts[index].show = false;
            }
        }
    };
}
</script>

</body>
<!-- ╔══════════════════════════════════════════════════════════╗ -->
<!-- ║  AUTH MODALS (Login / Register)                         ║ -->
<!-- ╚══════════════════════════════════════════════════════════╝ -->
<div x-show="auth.open" 
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     style="display: none;">
    
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="auth.open = false"></div>

    <!-- Modal Content -->
    <div class="relative bg-white dark:bg-slate-900 w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden animate-scale-in"
         @click.away="auth.open = false">
        
        <!-- Close Button -->
        <button @click="auth.open = false" 
                class="absolute top-4 right-4 p-2 rounded-full bg-gray-100 dark:bg-slate-800 text-gray-500 hover:text-gray-800 dark:hover:text-white transition-colors z-10">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div class="p-8 sm:p-10">
            <!-- ── LOGIN FORM ────────────────────────────────── -->
            <template x-if="auth.mode === 'login'">
                <div>
                    <div class="mb-8">
                        <div class="flex items-center gap-3 mb-4">
                            <img src="<?= APP_URL ?>/assets/images/logo.jpg" alt="M'Boost" class="h-10 w-auto rounded-lg">
                            <span class="text-xs font-bold uppercase tracking-widest text-green-600">Connexion</span>
                        </div>
                        <h2 class="text-3xl font-black font-display text-gray-900 dark:text-white" x-text="auth.role === 'admin' ? 'Espace Admin' : 'Bon retour !'"></h2>
                        <p class="text-gray-500 dark:text-slate-400 mt-2" x-text="auth.role === 'admin' ? 'Accédez à votre tableau de bord.' : 'Connectez-vous pour commander vos jus.'"></p>
                    </div>

                    <form action="<?= APP_URL ?>/auth/login.php" method="POST" class="space-y-5">
                        <input type="hidden" name="login_mode" :value="auth.role">
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5" x-text="auth.role === 'admin' ? 'Email administrateur' : 'Adresse email'"></label>
                            <div class="input-icon-wrapper">
                                <input type="email" name="email" required class="input-modern w-full" :placeholder="auth.role === 'admin' ? 'admin@mboost.mg' : 'votre@email.com'">
                                <svg class="input-icon w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-sm font-semibold text-gray-700 dark:text-slate-300">Mot de passe</label>
                                <template x-if="auth.role !== 'admin'">
                                    <a href="#" class="text-xs text-green-600 hover:text-green-700 font-bold">Oublié ?</a>
                                </template>
                            </div>
                            <div class="input-icon-wrapper">
                                <input type="password" name="password" required class="input-modern w-full" placeholder="••••••••">
                                <svg class="input-icon w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-4 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold rounded-2xl shadow-xl shadow-green-500/20 hover:shadow-2xl hover:shadow-green-500/40 transition-all duration-300 btn-shine">
                            Se connecter
                        </button>
                    </form>

                    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-slate-800 text-center">
                        <template x-if="auth.role !== 'admin'">
                            <p class="text-sm text-gray-500">Pas encore de compte ? 
                                <button @click="auth.mode = 'register'" class="text-green-600 hover:text-green-700 font-bold">Créer un compte →</button>
                            </p>
                        </template>
                        <template x-if="auth.role === 'admin'">
                            <button @click="auth.role = 'client'" class="text-sm text-gray-500 hover:text-gray-700">Retour à la connexion client</button>
                        </template>
                    </div>
                </div>
            </template>

            <!-- ── REGISTER FORM ─────────────────────────────── -->
            <template x-if="auth.mode === 'register'">
                <div>
                    <div class="mb-6">
                        <div class="flex items-center gap-3 mb-4">
                            <img src="<?= APP_URL ?>/assets/images/logo.jpg" alt="M'Boost" class="h-10 w-auto rounded-lg">
                            <span class="text-xs font-bold uppercase tracking-widest text-orange-600">Rejoignez-nous</span>
                        </div>
                        <h2 class="text-3xl font-black font-display text-gray-900 dark:text-white">Créer un compte</h2>
                        <p class="text-gray-500 dark:text-slate-400 mt-2">Profitez de nos jus personnalisés et conseils santé.</p>
                    </div>

                    <form action="<?= APP_URL ?>/auth/register.php" method="POST" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Nom</label>
                                <input type="text" name="nom" required class="input-modern w-full text-xs" placeholder="Votre nom">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Prénom</label>
                                <input type="text" name="prenom" required class="input-modern w-full text-xs" placeholder="Prénom">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Email</label>
                            <input type="email" name="email" required class="input-modern w-full text-xs" placeholder="votre@email.com">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Téléphone</label>
                            <input type="tel" name="telephone" required class="input-modern w-full text-xs" placeholder="034 XX XXX XX">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Mot de passe</label>
                                <input type="password" name="password" required class="input-modern w-full text-xs" placeholder="••••••••">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Confirmation</label>
                                <input type="password" name="password_confirm" required class="input-modern w-full text-xs" placeholder="Répétez">
                            </div>
                        </div>

                        <button type="submit" class="w-full py-4 bg-gradient-to-r from-orange-500 to-red-500 text-white font-bold rounded-2xl shadow-xl shadow-orange-500/20 hover:shadow-2xl hover:shadow-orange-500/40 transition-all duration-300 btn-shine">
                            S'inscrire gratuitement
                        </button>
                    </form>

                    <div class="mt-6 pt-6 border-t border-gray-100 dark:border-slate-800 text-center">
                        <p class="text-sm text-gray-500">Déjà inscrit ? 
                            <button @click="auth.mode = 'login'" class="text-orange-600 hover:text-orange-700 font-bold">Se connecter →</button>
                        </p>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

</html>
