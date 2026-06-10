<?php $__env->startSection('title', $article->title); ?>

<?php $__env->startSection('content'); ?>
<?php ($cameFromAdmin = request('from') === 'admin' || (session('admin_logged_in') && !request()->has('from'))); ?>
<?php ($backUrl = $cameFromAdmin ? route('admin.index') : route('home')); ?>
<div class="max-w-3xl mx-auto bg-neutral-900 border border-neutral-800 rounded-2xl shadow-2xl p-5 sm:p-8 overflow-hidden">
    <a href="<?php echo e($backUrl); ?>" class="inline-block mb-5 text-xs font-mono uppercase tracking-wider text-blue-400 hover:text-blue-300">&larr; <?php echo e($cameFromAdmin ? 'Kembali ke Konsol CMS' : 'Kembali ke Portal'); ?></a>
    <?php echo $__env->make('articles._detail', ['article' => $article], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/vol15_2/infinityfree.com/if0_42129557/htdocs/resources/views/articles/show.blade.php ENDPATH**/ ?>