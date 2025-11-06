<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Peta Sebaran Customer
        </x-slot>

        <x-slot name="headerEnd">
            <button
                type="button"
                class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-gray fi-btn-color-gray fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-white text-gray-950 hover:bg-gray-50 dark:bg-white/5 dark:text-white dark:hover:bg-white/10 ring-1 ring-gray-950/10 dark:ring-white/20"
                onclick="toggleMapFullscreen()"
                id="fullscreenBtn"
            >
                <svg class="fi-btn-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.75 7h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1 0-1.5ZM13.75 10h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1 0-1.5ZM3.75 7h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1 0-1.5ZM3.75 10h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1 0-1.5ZM14 17v-2.5a.75.75 0 0 0-1.5 0V17a.75.75 0 0 0 1.5 0ZM14 6.5V4a.75.75 0 0 0-1.5 0v2.5a.75.75 0 0 0 1.5 0ZM7.5 17v-2.5a.75.75 0 0 0-1.5 0V17a.75.75 0 0 0 1.5 0ZM7.5 6.5V4a.75.75 0 0 0-1.5 0v2.5a.75.75 0 0 0 1.5 0Z" />
                </svg>
                <span>Fullscreen</span>
            </button>
        </x-slot>

        <div
            id="mapContainer"
            class="relative"
            x-data="{
                customerMapData: @js($this->getCustomerMapData())
            }"
            x-init="
                $nextTick(() => {
                    if (typeof window.initCustomerMap === 'function') {
                        window.customerMapData = customerMapData;
                        window.initCustomerMap();
                    }
                })
            "
            @refresh-map.window="
                window.customerMapData = customerMapData;
                if (typeof window.initCustomerMap === 'function') {
                    window.initCustomerMap();
                }
            "
        >
            <div id="customerMap" wire:ignore style="height: 500px; width: 100%; border-radius: 8px; overflow: hidden;"></div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
