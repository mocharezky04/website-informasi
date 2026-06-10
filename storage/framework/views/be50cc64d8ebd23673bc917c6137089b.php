<?php $__env->startSection('title', 'Website Informasi'); ?>

<?php $__env->startSection('content'); ?>
<section class="space-y-8 animate-fade-in">
    <div class="bg-neutral-900/45 border border-neutral-800 rounded-2xl p-8 md:p-12 flex flex-col lg:flex-row gap-8 items-center justify-between relative overflow-hidden shadow-lg">
        <div class="absolute top-0 right-0 w-96 h-96 bg-blue-650/5 rounded-full blur-3xl -z-10"></div>
        <div class="flex-1 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-baseline gap-3 sm:gap-6">
                <h1 class="text-5xl sm:text-7xl font-black tracking-tighter leading-none italic uppercase text-neutral-100">Berita</h1>
                <span class="text-blue-500 text-3xl sm:text-5xl font-serif italic tracking-tighter">01</span>
            </div>
            <div class="space-y-3">
                <p class="font-serif text-base sm:text-lg leading-relaxed text-neutral-300 max-w-2xl">
                    Kumpulan artikel keamanan siber, investigasi insiden, hardening, dan forensik digital yang ditulis dalam bahasa Indonesia.
                </p>
                <p class="text-[10px] text-neutral-500 font-mono uppercase tracking-widest leading-none block font-bold">Website Informasi // portal artikel keamanan siber</p>
            </div>
        </div>
        <div class="w-full lg:w-72 border-t lg:border-t-0 lg:border-l border-neutral-800 pt-6 lg:pt-0 lg:pl-10 space-y-6 shrink-0">
            <div>
                <div class="text-[10px] uppercase tracking-widest text-neutral-500 mb-1.5 font-mono font-bold">Artikel Aktif</div>
                <div class="text-3xl font-black text-neutral-150"><?php echo e($articles->count()); ?></div>
                <div class="text-[9px] font-mono text-blue-400 mt-2 uppercase block font-bold">pembaruan sesuai database</div>
            </div>
            <div>
                <div class="text-[10px] uppercase tracking-widest text-neutral-500 mb-1.5 font-mono font-bold">Kategori</div>
                <div class="text-3xl font-black text-neutral-150"><?php echo e(count($categories)); ?></div>
                <div class="text-[9px] font-mono text-blue-400 mt-2 uppercase block font-bold">filter berita tersedia</div>
            </div>
        </div>
    </div>

    <form method="GET" action="<?php echo e(route('home')); ?>" class="bg-neutral-900/60 border border-neutral-800 rounded-2xl p-4 flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-1 md:pb-0">
            <?php $__currentLoopData = ['All', ...$categories]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button name="category" value="<?php echo e($category); ?>" class="py-1.5 px-3 rounded text-[11px] font-bold uppercase tracking-wider whitespace-nowrap transition-all cursor-pointer <?php echo e($selectedCategory === $category ? 'bg-neutral-800 text-blue-400 border border-neutral-700' : 'bg-transparent text-neutral-450 hover:text-neutral-200'); ?>">
                    <?php echo e($category); ?>

                </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="flex items-center bg-neutral-950 border border-neutral-850 rounded px-3 py-1.5 w-full md:w-72">
            <span class="text-neutral-500 text-xs font-mono">SEARCH</span>
            <input name="q" type="text" placeholder="Cari berita atau advis siber..." class="w-full bg-transparent text-neutral-150 placeholder:text-neutral-600 text-xs py-0.5 px-2 focus:outline-none" value="<?php echo e($currentSearch); ?>">
        </div>
    </form>

    <?php if($articles->isEmpty()): ?>
        <div class="bg-neutral-900/40 border border-neutral-800 rounded-2xl p-12 text-center text-neutral-500">
            <p class="text-sm font-semibold text-neutral-400">Tidak ada artikel diinventarisir</p>
            <p class="text-xs text-neutral-600 mt-1">Coba sesuaikan filter kategori Anda atau bersihkan parameter pencarian.</p>
        </div>
    <?php else: ?>
        <?php ($fallbackImage = 'data:image/svg+xml;utf8,'.urlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 675"><rect width="1200" height="675" fill="#0a0a0a"/><rect x="80" y="80" width="1040" height="515" rx="24" fill="#111827" stroke="#1f2937"/><text x="110" y="180" fill="#93c5fd" font-family="Arial, sans-serif" font-size="42" font-weight="700">Website Informasi</text><text x="110" y="240" fill="#9ca3af" font-family="Arial, sans-serif" font-size="24">Gambar artikel tidak tersedia</text></svg>')); ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php $__currentLoopData = $articles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="bg-neutral-900/25 hover:bg-neutral-900/60 border border-neutral-800/80 hover:border-neutral-700 rounded-2xl overflow-hidden shadow-lg transition-all flex flex-col cursor-pointer group" x-data="{ open: false }">
                    <button type="button" @click="open = true" class="text-left">
                        <div class="aspect-[16/9] bg-neutral-950 border-b border-neutral-800/60 overflow-hidden relative">
                            <div class="absolute top-3 left-3 z-10 bg-neutral-950/90 text-neutral-300 text-[9px] border border-neutral-800 px-2.5 py-0.5 rounded font-bold uppercase tracking-wider"><?php echo e($article->category); ?></div>
                            <img
                                src="<?php echo e($article->image_url ?: $fallbackImage); ?>"
                                onerror="this.onerror=null;this.src='<?php echo e($fallbackImage); ?>';"
                                alt="<?php echo e($article->title); ?>"
                                referrerpolicy="no-referrer"
                                loading="lazy"
                                class="w-full h-full object-cover group-hover:scale-103 transition-transform duration-500"
                            >
                        </div>
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div class="space-y-3">
                                <div class="flex items-center gap-2 text-neutral-500 text-[10px] font-mono">
                                    <span class="font-bold uppercase text-neutral-400"><?php echo e($article->author); ?></span>
                                    <span>&bull;</span>
                                    <span><?php echo e($article->published_date?->format('Y-m-d')); ?></span>
                                </div>
                                <h4 class="text-base sm:text-lg font-serif italic text-neutral-100 group-hover:text-blue-400 transition-colors leading-snug line-clamp-2"><?php echo e($article->title); ?></h4>
                                <p class="text-neutral-400 text-xs leading-relaxed line-clamp-3 font-serif"><?php echo e($article->summary); ?></p>
                            </div>
                            <div class="flex flex-wrap gap-1.5 pt-4 border-t border-neutral-900/50 mt-4">
                                <?php $__currentLoopData = array_slice($article->tags ?? [], 0, 3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="text-[9px] font-mono bg-neutral-950 text-neutral-500 border border-neutral-850 rounded px-1.5 py-0.5">#<?php echo e($tag); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </button>
                    <div x-show="open" x-cloak @click.self="open = false" class="fixed inset-0 bg-neutral-950/85 backdrop-blur-md flex justify-center p-4 z-40 overflow-y-auto">
                        <div @click.stop class="w-full max-w-4xl max-h-[85vh] overflow-y-auto overflow-x-hidden bg-neutral-900 border border-neutral-800 rounded-2xl shadow-2xl relative my-auto p-5 sm:p-8 pt-10 sm:pt-12 animate-fade-in">
                            <button type="button" @click="open = false" class="absolute top-4 right-4 z-30 w-8 h-8 flex items-center justify-center bg-neutral-950 text-neutral-300 hover:text-white border border-neutral-700 rounded-full shadow-lg cursor-pointer">X</button>
                            <?php echo $__env->make('articles._detail', ['article' => $article], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/vol15_2/infinityfree.com/if0_42129557/htdocs/resources/views/home.blade.php ENDPATH**/ ?>