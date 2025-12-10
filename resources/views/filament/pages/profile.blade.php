<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Package Status Section --}}
        <x-filament::section>
            <x-slot name="heading">
                Status Paket Langganan
            </x-slot>
            <x-slot name="description">
                Informasi paket langganan Anda saat ini
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Paket</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ auth()->user()->package?->name ?? 'Tidak ada paket' }}
                    </div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Status</div>
                    <div class="text-lg font-semibold {{ auth()->user()->hasActivePackage() ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ auth()->user()->package_status }}
                    </div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Berakhir Pada</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-white">
                        @if(auth()->user()->package?->isLifetime())
                            Selamanya
                        @elseif(auth()->user()->package_expires_at)
                            {{ auth()->user()->package_expires_at->format('d M Y') }}
                        @else
                            -
                        @endif
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Profile Form --}}
        <x-filament::section>
            <x-slot name="heading">
                Informasi Akun
            </x-slot>
            <x-slot name="description">
                Perbarui informasi profil Anda
            </x-slot>

            <form wire:submit="updateProfile" class="space-y-4">
                {{ $this->profileSchema }}

                <div class="flex justify-end">
                    <x-filament::button type="submit">
                        Simpan Perubahan
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        {{-- Password Form --}}
        <x-filament::section>
            <x-slot name="heading">
                Ubah Password
            </x-slot>
            <x-slot name="description">
                Pastikan password menggunakan kombinasi yang kuat
            </x-slot>

            <form wire:submit="updatePassword" class="space-y-4">
                {{ $this->passwordSchema }}

                <div class="flex justify-end">
                    <x-filament::button type="submit">
                        Ubah Password
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>
    </div>
</x-filament-panels::page>