<!-- ╔══════════════════════════════════════════════════════════╗ -->
<!-- ║  BENEFITS SECTION (with Alpine.js tabs)                 ║ -->
<!-- ╚══════════════════════════════════════════════════════════╝ -->
<section class="benefits-section py-20 lg:py-28 dark:bg-slate-900" data-aos="fade-up" data-aos-duration="1200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16" data-aos="fade-down" data-aos-delay="100">
            <span class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold uppercase tracking-widest mb-4 dark:bg-emerald-900/30 dark:text-emerald-400 animate__animated animate__fadeInDown">
                Bienfaits
            </span>
            <h2 class="text-3xl sm:text-4xl font-extrabold font-display text-gray-900 dark:text-white animate__animated animate__fadeInUp animate__delay-1s">Pourquoi choisir M'Boost?</h2>
            <p class="mt-4 text-gray-500 max-w-lg mx-auto dark:text-slate-400 animate__animated animate__fadeInUp animate__delay-2s">Des jus naturels conçus pour booster votre santé et votre bien-être au quotidien.</p>
        </div>
        
        <div x-data="{ tab: 'sante' }" class="max-w-4xl mx-auto">
            <!-- Tab Buttons -->
            <div class="flex flex-wrap justify-center gap-2 mb-10" data-aos="zoom-in" data-aos-delay="200">
                <button @click="tab = 'sante'"
                        :class="tab === 'sante' ? 'bg-green-600 text-white shadow-lg shadow-green-200/50' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700 dark:hover:bg-slate-700'"
                        class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300">
                    💚 Santé
                </button>
                <button @click="tab = 'qualite'"
                        :class="tab === 'qualite' ? 'bg-green-600 text-white shadow-lg shadow-green-200/50' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700 dark:hover:bg-slate-700'"
                        class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300">
                    ⭐ Qualité
                </button>
                <button @click="tab = 'pratique'"
                        :class="tab === 'pratique' ? 'bg-green-600 text-white shadow-lg shadow-green-200/50' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700 dark:hover:bg-slate-700'"
                        class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300">
                    🚀 Pratique
                </button>
            </div>
            
            <!-- Tab Content: Santé -->
            <div x-show="tab === 'sante'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm dark:bg-slate-800 dark:border-slate-700 transition-all duration-300 hover:shadow-2xl hover:-translate-y-2 animate__animated animate__fadeInUp" data-aos="fade-up" data-aos-delay="100">
                        <div class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center text-xl mb-4 dark:bg-slate-700">🏋️</div>
                        <h3 class="font-bold font-display text-gray-900 dark:text-white">Boost d'énergie</h3>
                        <p class="text-sm text-gray-500 mt-2 leading-relaxed dark:text-slate-400">Nos jus naturels apportent vitamines et minéraux essentiels pour une énergie durable.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm dark:bg-slate-800 dark:border-slate-700 transition-all duration-300 hover:shadow-2xl hover:-translate-y-2 animate__animated animate__fadeInUp" data-aos="fade-up" data-aos-delay="200">
                        <div class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center text-xl mb-4 dark:bg-slate-700">🛡️</div>
                        <h3 class="font-bold font-display text-gray-900 dark:text-white">Immunité renforcée</h3>
                        <p class="text-sm text-gray-500 mt-2 leading-relaxed dark:text-slate-400">Riche en antioxydants et vitamine C pour renforcer vos défenses naturelles.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm dark:bg-slate-800 dark:border-slate-700 transition-all duration-300 hover:shadow-2xl hover:-translate-y-2 animate__animated animate__fadeInUp" data-aos="fade-up" data-aos-delay="300">
                        <div class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center text-xl mb-4 dark:bg-slate-700">✨</div>
                        <h3 class="font-bold font-display text-gray-900 dark:text-white">Détox naturel</h3>
                        <p class="text-sm text-gray-500 mt-2 leading-relaxed dark:text-slate-400">Éliminez les toxines et régénérez votre corps grâce à nos recettes détox.</p>
                    </div>
                </div>
            </div>
            
            <!-- Tab Content: Qualité -->
            <div x-show="tab === 'qualite'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="display:none;">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm dark:bg-slate-800 dark:border-slate-700 transition-all duration-300 hover:shadow-lg">
                        <div class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center text-xl mb-4 dark:bg-slate-700">🌾</div>
                        <h3 class="font-bold font-display text-gray-900 dark:text-white">Ingrédients locaux</h3>
                        <p class="text-sm text-gray-500 mt-2 leading-relaxed dark:text-slate-400">Nous sélectionnons des fruits et légumes frais auprès de producteurs locaux.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm dark:bg-slate-800 dark:border-slate-700 transition-all duration-300 hover:shadow-lg">
                        <div class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center text-xl mb-4 dark:bg-slate-700">🚫</div>
                        <h3 class="font-bold font-display text-gray-900 dark:text-white">Zéro additif</h3>
                        <p class="text-sm text-gray-500 mt-2 leading-relaxed dark:text-slate-400">Aucun sucre ajouté, aucun conservateur — juste des ingrédients purs et naturels.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm dark:bg-slate-800 dark:border-slate-700 transition-all duration-300 hover:shadow-lg">
                        <div class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center text-xl mb-4 dark:bg-slate-700">🔬</div>
                        <h3 class="font-bold font-display text-gray-900 dark:text-white">Dosage précis</h3>
                        <p class="text-sm text-gray-500 mt-2 leading-relaxed dark:text-slate-400">Chaque jus est préparé avec un dosage précis pour un goût et des bienfaits optimaux.</p>
                    </div>
                </div>
            </div>
            
            <!-- Tab Content: Pratique -->
            <div x-show="tab === 'pratique'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="display:none;">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm dark:bg-slate-800 dark:border-slate-700 transition-all duration-300 hover:shadow-lg">
                        <div class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center text-xl mb-4 dark:bg-slate-700">📱</div>
                        <h3 class="font-bold font-display text-gray-900 dark:text-white">Commande en ligne</h3>
                        <p class="text-sm text-gray-500 mt-2 leading-relaxed dark:text-slate-400">Commandez depuis votre téléphone ou ordinateur en quelques clics.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm dark:bg-slate-800 dark:border-slate-700 transition-all duration-300 hover:shadow-lg">
                        <div class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center text-xl mb-4 dark:bg-slate-700">💳</div>
                        <h3 class="font-bold font-display text-gray-900 dark:text-white">Paiement Mobile Money</h3>
                        <p class="text-sm text-gray-500 mt-2 leading-relaxed dark:text-slate-400">Orange Money ou MVola — payez facilement depuis votre mobile.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm dark:bg-slate-800 dark:border-slate-700 transition-all duration-300 hover:shadow-lg">
                        <div class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center text-xl mb-4 dark:bg-slate-700">🛵</div>
                        <h3 class="font-bold font-display text-gray-900 dark:text-white">Livraison express</h3>
                        <p class="text-sm text-gray-500 mt-2 leading-relaxed dark:text-slate-400">Livraison rapide dans tout Mahajanga pour une fraîcheur garantie.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
