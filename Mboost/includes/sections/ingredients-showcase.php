<?php
/**
 * ingredients-showcase.php — 4-Row Diagonal Scrolling Grid
 */

if (empty($ingredients)) {
    $ingredients = [
        ['nom' => 'Orange', 'avantage' => 'Renforce le système immunitaire avec une dose massive de Vitamine C.', 'image' => 'https://images.unsplash.com/photo-1547514701-42782101795e?auto=format&fit=crop&w=1600&h=640&q=80'],
        ['nom' => 'Pomme', 'avantage' => 'La pectine aide à réguler le cholestérol et facilite le transit.', 'image' => 'https://images.unsplash.com/photo-1560806887-1e4cd0b6bcd6?auto=format&fit=crop&w=1600&h=640&q=80'],
        ['nom' => 'Carotte', 'avantage' => 'Améliore la vision nocturne et procure un teint éclatant.', 'image' => 'https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?auto=format&fit=crop&w=1600&h=640&q=80'],
        ['nom' => 'Gingembre', 'avantage' => 'Puissant anti-inflammatoire et aide précieuse à la digestion.', 'image' => 'https://images.unsplash.com/photo-1615484477778-ca3b77940c25?auto=format&fit=crop&w=1600&h=640&q=80'],
        ['nom' => 'Citron', 'avantage' => 'Équilibre le pH de l\'organisme et détoxifie le foie naturellement.', 'image' => 'https://images.unsplash.com/photo-1585059895312-5e769bc6f35c?auto=format&fit=crop&w=1600&h=640&q=80'],
        ['nom' => 'Ananas', 'avantage' => 'La bromélaïne aide à décomposer les protéines pour une digestion légère.', 'image' => 'https://images.unsplash.com/photo-1550258114-b8a27a03f1d8?auto=format&fit=crop&w=1600&h=640&q=80'],
        ['nom' => 'Betterave', 'avantage' => 'Augmente l\'endurance physique en améliorant l\'oxygénation sanguine.', 'image' => 'https://images.unsplash.com/photo-1585829365291-1782bd0a3bb6?auto=format&fit=crop&w=1600&h=640&q=80'],
        ['nom' => 'Menthe', 'avantage' => 'Apaise les spasmes digestifs et procure une fraîcheur instantanée.', 'image' => 'https://images.unsplash.com/photo-1533228800806-05658097587c?auto=format&fit=crop&w=1600&h=640&q=80'],
    ];
}

// Ensure enough items for 4 rows
while (count($ingredients) < 16) {
    $ingredients = array_merge($ingredients, $ingredients);
}

// Split into 4 rows
$rows = array_chunk($ingredients, ceil(count($ingredients) / 4));
?>

<section id="ingredients-showcase" 
         class="relative py-24 overflow-hidden dark:bg-slate-950"
         x-data="{ 
            selectedItem: null, 
            openModal(item) { 
                this.selectedItem = item; 
                document.body.classList.add('overflow-hidden'); 
            }, 
            closeModal() { 
                this.selectedItem = null; 
                document.body.classList.remove('overflow-hidden'); 
            } 
         }">
    
    <!-- Section Header (Stable above the diagonal) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-4 relative z-40">
        <div class="text-center">
            <span class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-green-100 text-green-700 text-[10px] font-black uppercase tracking-[0.4em] mb-4 dark:bg-green-900/30 dark:text-green-400">
                Nos Richesses Naturelles
            </span>
            <h2 class="text-3xl sm:text-6xl font-black font-display text-gray-900 dark:text-white mb-4 tracking-tighter uppercase leading-none">
                Le Verger <span class="text-green-600">M'Boost</span>
            </h2>
        </div>
    </div>

    <!-- 4-Row Diagonal Container -->
    <div class="relative overflow-hidden w-full h-[600px] lg:h-[900px] flex items-center justify-center">
        <div class="showcase-diagonal-container">
            <?php foreach ($rows as $idx => $row): 
                $directionClass = ($idx % 2 === 0) ? 'scroll-ltr' : 'scroll-rtl';
                $speed = 40 + ($idx * 10);
            ?>
            <!-- Row <?= $idx + 1 ?> -->
            <div class="diagonal-row <?= $directionClass ?>" style="animation-duration: <?= $speed ?>s">
                <?php for ($r = 0; $r < 3; $r++): // Repeat for infinite loop ?>
                <?php foreach ($row as $item): ?>
                <?php 
                    $imgSrc = !empty($item['image']) 
                        ? (strpos($item['image'], 'http') === 0 ? $item['image'] : APP_URL . '/uploads/ingredients/' . $item['image'])
                        : 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=1600&h=640&q=80';
                ?>
                <div class="showcase-card-small group relative w-56 h-28 lg:w-96 lg:h-48 rounded-xl lg:rounded-[2rem] overflow-hidden shadow-xl bg-white cursor-pointer"
                     @click="openModal(<?= htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8') ?>)">
                    <div class="absolute inset-0">
                        <img src="<?= $imgSrc ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        <div class="absolute inset-0 bg-black/5 group-hover:bg-black/60 transition-all duration-300"></div>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <h3 class="text-white font-black fruit-title-diagonal uppercase tracking-tighter italic">
                            <?= htmlspecialchars($item['nom']) ?>
                        </h3>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endfor; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Simple Info Modal -->
    <template x-if="selectedItem">
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100">
            
            <div class="absolute inset-0 bg-black/90 backdrop-blur-xl" @click="closeModal()"></div>
            
            <div class="relative bg-white dark:bg-slate-900 w-full max-w-2xl rounded-[3rem] overflow-hidden shadow-2xl"
                 @click.away="closeModal()"
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                
                <div class="p-10 lg:p-16 text-center">
                    <h2 class="text-5xl lg:text-7xl font-black text-gray-900 dark:text-white mb-8 uppercase tracking-tighter" x-text="selectedItem.nom"></h2>
                    <div class="p-8 lg:p-12 rounded-[2rem] bg-green-500 text-white">
                        <h4 class="text-[10px] font-black uppercase tracking-widest mb-3 opacity-80">Son Avantage</h4>
                        <p class="text-xl lg:text-3xl font-bold leading-tight" x-text="selectedItem.avantage"></p>
                    </div>
                    <button @click="closeModal()" class="mt-10 text-gray-400 hover:text-green-600 font-black uppercase tracking-[0.2em] text-xs">
                        [ Fermer ]
                    </button>
                </div>
            </div>
        </div>
    </template>

</section>
