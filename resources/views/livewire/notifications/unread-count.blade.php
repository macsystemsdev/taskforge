<div class="absolute -right-0.5 -top-0.5">
    @if ($count > 0)
        <span class="flex size-4 items-center justify-center rounded-full bg-rose-600 text-[10px] font-bold text-white shadow-sm">
            {{ $count > 9 ? '9+' : $count }}
        </span>
    @endif
</div>
