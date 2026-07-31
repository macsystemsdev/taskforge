@props([
    'columns',
])

<thead class="bg-gray-50 dark:bg-gray-900">

<tr>

@foreach($columns as $column)

    <th
        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300"
    >
        {{ $column }}
    </th>

@endforeach

</tr>

</thead>