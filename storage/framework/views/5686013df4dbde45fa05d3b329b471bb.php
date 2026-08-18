<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => null,
    'initials' => null,
    'size' => 'md',
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
    'name' => null,
    'initials' => null,
    'size' => 'md',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $displayInitials = $initials ?: collect(explode(' ', trim((string) $name)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->implode('');

    $sizes = [
        'sm' => 'size-8 text-xs',
        'md' => 'size-9 text-sm',
        'lg' => 'size-10 text-sm',
    ];
?>

<span <?php echo e($attributes->merge(['class' => ($sizes[$size] ?? $sizes['md']).' inline-flex shrink-0 items-center justify-center rounded-full border border-zinc-200 bg-zinc-100 font-semibold text-zinc-700 dark:border-white/10 dark:bg-white/10 dark:text-zinc-200'])); ?>>
    <?php echo e(strtoupper($displayInitials ?: '?')); ?>

</span>
<?php /**PATH /var/www/html/resources/views/components/ui/avatar.blade.php ENDPATH**/ ?>