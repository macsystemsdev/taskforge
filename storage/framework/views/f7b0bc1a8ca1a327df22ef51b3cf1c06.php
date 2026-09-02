<?php

use Livewire\Component;
use Livewire\Attributes\On;

new class extends Component {
    public bool $show = false;
    public ?string $avatarPath = null;
    public ?string $name = null;
    public ?string $email = null;
    public ?int $avatarUserId = null;

    #[On('open-avatar-modal')]
    public function openAvatarModal($avatarPath = null, $name = null, $email = null, $userId = null): void
    {
        $this->avatarPath = $avatarPath;
        $this->name = $name;
        $this->email = $email;
        $this->avatarUserId = $userId ? (int) $userId : null;
        $this->show = true;
    }

    public function closeModal(): void
    {
        $this->show = false;
        $this->avatarPath = null;
        $this->name = null;
        $this->email = null;
    }

    public function render()
    {
        return view('livewire.ui.avatar-modal');
    }
};
?>

<div>
    <?php if (isset($component)) { $__componentOriginal8cc9d3143946b992b324617832699c5f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8cc9d3143946b992b324617832699c5f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::modal.index','data' => ['wire:model' => 'show','class' => 'max-w-md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model' => 'show','class' => 'max-w-md']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <div class="space-y-6 text-center">
            <div class="flex justify-center">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($avatarUserId): ?>
                    <img src="<?php echo e(route('users.avatar', ['user' => $avatarUserId])); ?>"
                         alt="<?php echo e($name); ?>"
                         class="aspect-square w-64 rounded-full object-cover shadow-2xl sm:w-72"
                         onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect width=%22200%22 height=%22200%22 fill=%22%23999%22/%3E%3Ctext x=%2250%%22 y=%2250%%22 dominant-baseline=%22central%22 text-anchor=%22middle%22 font-size=%2280%22 fill=%22%23fff%22 font-family=%22sans-serif%22%3E<?php echo e(strtoupper(substr($name, 0, 1))); ?>%3C/text%3E%3C/svg%3E';" />
                <?php else: ?>
                    <div class="flex aspect-square w-64 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-6xl font-bold text-white shadow-2xl sm:w-72">
                        <?php echo e(strtoupper(substr($name, 0, 1))); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <p class="text-2xl font-semibold text-zinc-950 dark:text-white"><?php echo e($name); ?></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($email): ?>
                    <p class="mt-1 text-sm text-zinc-500"><?php echo e($email); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8cc9d3143946b992b324617832699c5f)): ?>
<?php $attributes = $__attributesOriginal8cc9d3143946b992b324617832699c5f; ?>
<?php unset($__attributesOriginal8cc9d3143946b992b324617832699c5f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8cc9d3143946b992b324617832699c5f)): ?>
<?php $component = $__componentOriginal8cc9d3143946b992b324617832699c5f; ?>
<?php unset($__componentOriginal8cc9d3143946b992b324617832699c5f); ?>
<?php endif; ?>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/ui/avatar-modal.blade.php ENDPATH**/ ?>