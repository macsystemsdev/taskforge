<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'priority',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'priority',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $value = $priority instanceof \BackedEnum ? $priority->value : (string) $priority;
    $label = str($value)->headline();

    $dots = [
        'low' => 'bg-zinc-400',
        'medium' => 'bg-zinc-700 dark:bg-zinc-300',
        'high' => 'bg-orange-500',
        'urgent' => 'bg-red-500',
    ];
?>

<span <?php echo e($attributes->merge(['class' => 'inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-white px-2.5 py-1 text-xs font-medium text-zinc-700 dark:border-white/10 dark:bg-white/5 dark:text-zinc-300'])); ?>>
    <span class="size-1.5 rounded-full <?php echo e($dots[$value] ?? $dots['medium']); ?>"></span>
    <?php echo e($label); ?>

</span>
<?php /**PATH D:\Code\taskforge\resources\views/components/ui/priority-badge.blade.php ENDPATH**/ ?>