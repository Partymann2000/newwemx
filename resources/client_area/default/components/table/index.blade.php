@props(['containerClass' => ''])

<div class="relative overflow-x-auto shadow-md sm:rounded-t-lg {{ $containerClass }}">
    <table {{ $attributes->merge(['class' => 'w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400']) }}>
        {{ $slot }}
    </table>
</div>
