<div {{ $attributes->merge(['class' => 'mb-4 rounded-lg bg-white p-4 shadow dark:bg-gray-800 sm:p-6 xl:p-8']) }}>
    @isset($title)
        <h3 {{ $title->attributes->merge(['class' => 'text-xl font-bold dark:text-white']) }}>
            {{ $title }}
        </h3>
    @endisset

    @isset($description)
        <p {{ $description->attributes->merge(['class' => 'mt-2 text-sm font-normal text-gray-500 dark:text-gray-400']) }}>
            {{ $description }}
        </p>
    @endisset

    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
        <li class="py-4">
            <div class="flex justify-end space-x-4">
                <div class="inline-flex items-center">
                    {{ $action ?? '' }}
                </div>
            </div>
        </li>
    </ul>
</div>
