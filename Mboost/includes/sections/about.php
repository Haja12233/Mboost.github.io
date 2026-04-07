<?php
/**
 * about.php — Section À Propos with Hover Animated Subtitle
 */
?>

<section id="about" class="relative py-32 overflow-hidden dark:bg-slate-900 aos-init" data-aos="fade-up" data-aos-duration="1200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col lg:flex-row items-center gap-16 lg:gap-24">
            
            <!-- Left Side: Image with Animated Subtitle -->
            <div class="w-full lg:w-1/2 group">
                <div class="relative rounded-[3rem] overflow-hidden shadow-2xl bg-white aspect-square lg:aspect-[4/5] perspective-1000 parallax-container" data-parallax="scroll" data-image-src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1200&q=80">
                    <!-- Main Image (High quality Orchard/Production) -->
                    <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1200&q=80" 
                        alt="Notre Verger à Mahajanga" 
                        class="w-full h-full object-cover group-hover:scale-110 group-hover:rotate-2 transition-transform duration-1000 ease-in-out will-change-transform">
                    
                    <!-- Gradient Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    
                    <!-- Animated Subtitle (Company Name) -->
                    <div class="about-subtitle-wrapper absolute bottom-0 left-0 w-full p-12 text-center pointer-events-none">
                        <div class="about-subtitle-inner translate-y-10 group-hover:translate-y-0 opacity-0 group-hover:opacity-100 transition-all duration-700 delay-100" data-aos="fade-up" data-aos-delay="300">
                            <span class="text-white text-5xl lg:text-8xl font-black uppercase tracking-[0.2em] italic drop-shadow-2xl inline-block animate__animated animate__fadeInDown">
                                M'Boost
                            </span>
                            <div class="w-24 h-1.5 bg-green-500 mx-auto mt-6 rounded-full scale-x-0 group-hover:scale-x-100 transition-transform duration-1000 delay-300"></div>
                        </div>
                    </div>
                    
                    <!-- Corner Accent -->
                    <div class="absolute top-8 left-8 p-4 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30 text-white font-bold uppercase tracking-widest text-xs translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-500 animate__animated animate__fadeInLeft">
                        Mahajanga, Madagascar
                    </div>
                </div>
            </div>

            <!-- Right Side: Content -->
            <div class="w-full lg:w-1/2 space-y-10 text-center lg:text-left">
                <div class="space-y-4" data-aos="fade-right" data-aos-delay="200">
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-green-100 text-green-700 text-xs font-black uppercase tracking-[0.3em] dark:bg-green-900/40 dark:text-green-400 animate__animated animate__fadeInDown">
                        L'Âme de notre entreprise
                    </span>
                    <h2 class="text-5xl lg:text-7xl font-black text-gray-900 dark:text-white leading-tight uppercase tracking-tighter animate__animated animate__fadeInUp">
                        Une Passion <span class="text-green-600 block">100% Naturelle</span>
                    </h2>
                </div>

                <p class="text-xl text-gray-600 dark:text-slate-400 font-medium leading-relaxed italic animate__animated animate__fadeInUp animate__delay-1s">
                    "Née à Mahajanga, M'Boost est bien plus qu'une marque de jus. C'est un hommage à la terre malgache et à ses fruits généreux."
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                    <div class="p-8 rounded-[2rem] bg-white dark:bg-slate-800 shadow-xl border border-gray-100 dark:border-slate-700 hover:border-green-500 transition-colors duration-500 hover:scale-105 hover:shadow-2xl animate__animated animate__fadeInUp">
                        <div class="w-12 h-12 bg-green-500 rounded-2xl flex items-center justify-center text-white mb-6">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.703 2.703 0 01-3 0 2.704 2.704 0 01-3 0 2.703 2.703 0 01-3 0 2.704 2.704 0 01-1.5-.454M3 6.229V21m0 0l4.312-1.437M3 6.229l4.312-1.437m13.376 0l-4.312 1.437M21 6.229V21m0 0l-4.312-1.437m-8.624 0L3 21m0-14.771l4.312-1.437m8.624 0l4.312 1.437M7.312 4.792v14.771m8.624-14.771v14.771"></path></svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-2 uppercase tracking-tight">Terroir Unique</h4>
                        <p class="text-gray-500 dark:text-slate-400 text-sm leading-relaxed">Nos fruits mûrissent sous le soleil intense de la cité des fleurs, garantissant un goût puissant.</p>
                    </div>

                    <div class="p-8 rounded-[2rem] bg-white dark:bg-slate-800 shadow-xl border border-gray-100 dark:border-slate-700 hover:border-green-500 transition-colors duration-500">
                        <div class="w-12 h-12 bg-green-500 rounded-2xl flex items-center justify-center text-white mb-6">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-2 uppercase tracking-tight">Pure Énergie</h4>
                        <p class="text-gray-500 dark:text-slate-400 text-sm leading-relaxed">Zéro additif, zéro conservateur. Juste la force brute de la nature pour booster votre journée.</p>
                    </div>
                </div>


            </div>
        </div>
    </div>
</section>
