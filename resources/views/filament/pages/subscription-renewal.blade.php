<x-filament::page>
    <div class="max-w-4xl mx-auto">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white mb-2">
                Perpanjang Langganan
            </h1>
            <p class="text-gray-500 dark:text-gray-400">
                Pilih paket yang sesuai dengan kebutuhan bisnis Anda
            </p>
        </div>

        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($packages as $package)
                <div class="relative flex flex-col p-6 bg-white dark:bg-gray-800 border-2 rounded-xl transition-all hover:border-primary-500 hover:shadow-lg group {{ $loop->last ? 'md:col-span-2 lg:col-span-1' : '' }} border-gray-200 dark:border-gray-700">
                    
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ $package->name }}</h3>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $package->duration_label }}</div>
                        </div>
                        @if($package->isLifetime())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                Best Value
                            </span>
                        @endif
                    </div>

                    <div class="mb-6">
                        <span class="text-3xl font-bold text-primary-600 dark:text-primary-400">{{ $package->formatted_price }}</span>
                    </div>

                    <ul class="space-y-3 mb-6 flex-1">
                        @foreach($package->features as $feature)
                        <li class="flex items-start">
                            <x-heroicon-s-check-circle class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" />
                            <span class="text-sm text-gray-600 dark:text-gray-300">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>

                    <div class="mt-auto pt-4 w-full">
                        <x-filament::button 
                            wire:click="processRenewal({{ $package->id }})"
                            size="lg" 
                            class="w-full"
                        >
                            Pilih Paket
                        </x-filament::button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</x-filament::page>
