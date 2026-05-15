<x-filament-panels::page>
    @php($services = $this->getServices())
    @php($sellableProducts = $this->getSellableProducts())
    @php($internalProducts = $this->getInternalProducts())
    @php($refurbishedProducts = $this->getRefurbishedProducts())

    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-filament::section>
                <x-slot name="heading">Services</x-slot>
                <p class="text-3xl font-semibold text-gray-900">{{ number_format(count($services)) }}</p>
                <p class="text-sm text-gray-600">Quoted and sellable service offerings.</p>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Sellable Products</x-slot>
                <p class="text-3xl font-semibold text-gray-900">{{ number_format(count($sellableProducts)) }}</p>
                <p class="text-sm text-gray-600">Products available for direct resale.</p>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Service Materials</x-slot>
                <p class="text-3xl font-semibold text-gray-900">{{ number_format(count($internalProducts)) }}</p>
                <p class="text-sm text-gray-600">Products reserved for internal delivery and job execution.</p>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Refurbished Items</x-slot>
                <p class="text-3xl font-semibold text-gray-900">{{ number_format(count($refurbishedProducts)) }}</p>
                <p class="text-sm text-gray-600">Recovered or refurbished inventory ready for remarketing.</p>
            </x-filament::section>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <x-filament::section>
                <x-slot name="heading">Services</x-slot>
                <div class="space-y-3">
                    @forelse ($services as $service)
                        <div class="rounded-xl border border-gray-200 px-4 py-3 dark:border-white/10">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $service['name'] }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ $service['category'] ?: 'Uncategorized service' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">${{ number_format($service['price'], 2) }}</p>
                                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $service['active'] ? 'Active' : 'Inactive' }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-600 dark:text-gray-300">No services in the catalog yet.</p>
                    @endforelse
                </div>
            </x-filament::section>

            <div class="space-y-6">
                <x-filament::section>
                    <x-slot name="heading">Sellable Products</x-slot>
                    <div class="space-y-3">
                        @forelse ($sellableProducts as $product)
                            <div class="rounded-xl border border-gray-200 px-4 py-3 dark:border-white/10">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $product['name'] }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $product['category'] ?: 'Uncategorized product' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">${{ number_format($product['price'], 2) }}</p>
                                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $product['sku'] ?: 'No SKU' }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-600 dark:text-gray-300">No sellable products yet.</p>
                        @endforelse
                    </div>
                </x-filament::section>

                <x-filament::section>
                    <x-slot name="heading">Service Materials & Refurbished</x-slot>
                    <div class="space-y-3">
                        @foreach (collect($internalProducts)->merge($refurbishedProducts)->take(8) as $product)
                            <div class="rounded-xl border border-gray-200 px-4 py-3 dark:border-white/10">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $product['name'] }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $product['category'] ?: 'Internal catalog item' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">${{ number_format($product['price'], 2) }}</p>
                                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $product['sku'] ?: 'No SKU' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-filament::section>
            </div>
        </div>
    </div>
</x-filament-panels::page>