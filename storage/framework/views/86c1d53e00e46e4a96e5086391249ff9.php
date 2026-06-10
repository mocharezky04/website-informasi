<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Website Informasi'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Newsreader:ital,opsz,wght@0,6..72,200..800;1,6..72,200..800&family=JetBrains+Mono:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        serif: ['Newsreader', 'Georgia', 'serif'],
                        mono: ['JetBrains Mono', 'ui-monospace', 'SFMono-Regular', 'monospace']
                    },
                    colors: {
                        blue: { 450: '#60a5fa', 650: '#2563eb' },
                        neutral: { 150: '#f5f5f5', 250: '#e5e5e5', 350: '#d4d4d4', 450: '#a3a3a3', 505: '#737373', 550: '#737373', 650: '#525252', 750: '#3f3f46', 850: '#1f1f1f' },
                        slate: { 850: '#172033' }
                    },
                    animation: {
                        'spin-slow': 'spin 1.4s linear infinite',
                        'fade-in': 'fadeIn .25s ease-out'
                    },
                    keyframes: {
                        fadeIn: { from: { opacity: 0, transform: 'translateY(4px)' }, to: { opacity: 1, transform: 'translateY(0)' } }
                    }
                }
            }
        };
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0a0a0a; }
        ::-webkit-scrollbar-thumb { background: #262626; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #404040; }
        [x-cloak] { display: none !important; }
        .terminal-cursor::after { content: '_'; animation: blink 1s step-end infinite; }
        @keyframes blink { from, to { color: transparent } 50% { color: #3b82f6; } }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        .scale-103 { transform: scale(1.03); }
    </style>
    <?php echo $__env->yieldPushContent('head'); ?>
</head>
<body class="min-h-screen bg-neutral-950 text-neutral-100 font-sans selection:bg-blue-650/30 selection:text-blue-450">
<?php ($adminUser = session('admin_user')); ?>
<div class="min-h-screen flex flex-col">
    <div class="bg-neutral-950 border-b border-neutral-800 text-[10px] uppercase tracking-[0.2em] font-semibold text-neutral-500 px-4 py-2.5 flex flex-wrap items-center justify-between gap-2 z-20 font-mono">
        <div class="flex items-center gap-4">
            <span class="flex items-center gap-1.5 text-blue-400">
                <span class="w-2 h-2 rounded-full bg-blue-400 animate-ping"></span>
                <span class="font-black uppercase tracking-widest">System: Operational</span>
            </span>
            <span class="text-neutral-800">|</span>
            <span class="hidden sm:inline text-neutral-550">Network: Latency 14ms</span>
        </div>
        <div class="flex items-center gap-4 text-neutral-500">
            <span>UTC Terminal: <?php echo e(now()->toDateString()); ?></span>
            <span class="hidden md:inline text-neutral-800">|</span>
            <span class="hidden md:inline bg-neutral-900 text-neutral-400 border border-neutral-800 px-2.5 py-0.5 rounded leading-none text-[9.5px]">EXAM ID: UASWEB-2026</span>
        </div>
    </div>

    <header class="bg-neutral-950/80 backdrop-blur-md border-b border-neutral-800 sticky top-0 z-30">
        <div class="w-full max-w-7xl mx-auto px-4 py-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <a href="<?php echo e(route('home')); ?>" class="flex items-center gap-4">
                <div class="bg-blue-600 px-3 py-1 text-xs font-black uppercase tracking-tighter text-white">Website Informasi</div>
                <div class="h-4 w-px bg-neutral-700 hidden sm:block"></div>
                <div class="text-[10px] uppercase tracking-[0.3em] font-medium text-neutral-500 hidden sm:block font-mono">Command Center v4.0.2</div>
            </a>

            <nav class="flex flex-wrap items-center gap-1">
                <a href="<?php echo e(route('home')); ?>" class="flex items-center gap-1.5 px-3 py-1.5 rounded text-xs font-bold uppercase tracking-wider transition-all bg-neutral-800 text-blue-400 border border-neutral-700">
                    <span>Berita</span>
                </a>
                <?php if($adminUser): ?>
                    <a href="<?php echo e(route('admin.index')); ?>" class="px-3 py-1.5 rounded text-xs font-black uppercase tracking-wider transition-all cursor-pointer bg-neutral-900 border border-neutral-800 text-blue-450 hover:bg-neutral-800">Admin CMS</a>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="px-3 py-1.5 rounded text-xs font-black uppercase tracking-wider bg-neutral-950 border border-neutral-800 text-red-400 hover:bg-neutral-900">Logout</button>
                    </form>
                <?php else: ?>
                    <a href="<?php echo e(route('login')); ?>" class="flex items-center gap-1.5 px-3.5 py-1.5 rounded text-xs font-bold uppercase tracking-widest bg-neutral-900 hover:bg-neutral-800 border border-neutral-800 hover:border-neutral-700 transition-all text-neutral-350 hover:text-white">
                        <span class="text-blue-500">LOCK</span>
                        <span>Sign In Admin</span>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <?php if(session('success') || session('error')): ?>
        <div class="w-full max-w-7xl mx-auto px-4 pt-4">
            <div class="<?php echo e(session('success') ? 'bg-blue-500/10 border-blue-500/20 text-blue-400' : 'bg-red-500/10 border-red-500/20 text-red-400'); ?> border text-xs rounded p-3 font-mono">
                <?php echo e(session('success') ?: session('error')); ?>

            </div>
        </div>
    <?php endif; ?>

    <main class="flex-1 w-full max-w-7xl mx-auto px-4 py-8 md:py-10">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <footer class="bg-neutral-950 border-t border-neutral-800 mt-12 py-6 text-neutral-500 font-sans">
        <div class="w-full max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px]">
            <span>&copy; 2026 Website Informasi.</span>
            <span class="font-mono text-neutral-400">Laravel PHP deployment</span>
        </div>
    </footer>
</div>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /home/vol15_2/infinityfree.com/if0_42129557/htdocs/resources/views/layouts/app.blade.php ENDPATH**/ ?>