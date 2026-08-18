<div class="inline-flex">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($count > 0): ?>
        <span class="ml-2 inline-flex items-center justify-center rounded-full bg-rose-600 px-2 py-0.5 text-[10px] font-semibold text-white">
            <?php echo e($count); ?>

        </span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH /var/www/html/resources/views/livewire/notifications/unread-count.blade.php ENDPATH**/ ?>