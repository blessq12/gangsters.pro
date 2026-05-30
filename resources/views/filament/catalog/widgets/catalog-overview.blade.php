<x-filament::section>
    <x-slot name="heading">
        Обзор каталога
    </x-slot>

    @forelse ($categories as $node)
        <div class="mb-6 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <span class="font-medium text-gray-950 dark:text-white">
                    {{ $node['category']['name'] }}
                </span>
                @if (! $node['category']['is_active'])
                    <x-filament::badge color="gray">
                        Скрыта
                    </x-filament::badge>
                @endif
            </div>

            @if (count($node['products']) === 0)
                <p class="text-sm text-gray-500 dark:text-gray-400">Нет товаров в категории.</p>
            @else
                <ul class="list-inside list-disc space-y-1 text-sm text-gray-700 dark:text-gray-300">
                    @foreach ($node['products'] as $product)
                        <li>
                            {{ $product['name'] }}
                            <span class="text-gray-500 dark:text-gray-400">({{ $product['status'] }})</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @empty
        <p class="text-sm text-gray-500 dark:text-gray-400">Категории не найдены.</p>
    @endforelse
</x-filament::section>
