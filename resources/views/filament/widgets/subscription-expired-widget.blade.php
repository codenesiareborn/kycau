<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col items-center justify-center p-6 text-center">
            <div class="mb-4">
                <x-heroicon-o-lock-closed class="w-16 h-16 text-gray-400 mx-auto" />
            </div>

            <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-2xl">
                Akses Terbatas
            </h2>

            <p class="mt-4 text-gray-500 dark:text-gray-400 max-w-lg">
                Mohon maaf, Anda belum memiliki paket aktif atau masa berlaku paket Anda telah habis. 
                Silakan hubungi admin untuk mengaktifkan atau memperpanjang paket langganan Anda agar dapat melihat data dashboard.
            </p>

            <div class="mt-6">
                <x-filament::button
                    tag="a"
                    href="{{ \App\Filament\Pages\SubscriptionRenewal::getUrl() }}"
                    size="lg"
                    color="primary"
                >
                    Perpanjang Paket Sekarang
                </x-filament::button>
            </div>

            @if(auth()->user()->package)
                <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg w-full max-w-md">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Status Paket Anda</div>
                    <div class="mt-1 text-lg font-bold text-primary-600">
                        {{ auth()->user()->package->name }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ auth()->user()->package_status }}
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Terdaftar Sejak:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                {{ auth()->user()->created_at->isoFormat('D MMMM Y') }}
                            </span>
                        </div>
                        
                        @if(auth()->user()->package_expires_at)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Berakhir Pada:</span>
                                <span class="font-medium {{ auth()->user()->isPackageExpired() ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100' }}">
                                    {{ auth()->user()->package_expires_at->isoFormat('D MMMM Y') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
