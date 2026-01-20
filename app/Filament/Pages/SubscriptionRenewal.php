<?php

namespace App\Filament\Pages;

use App\Models\Package;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SubscriptionRenewal extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected ?string $subheading = 'Pilih paket yang sesuai dengan kebutuhan bisnis Anda';

    protected static ?string $title = 'Perpanjang Langganan';

    protected static ?string $slug = 'subscription/renew';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.subscription-renewal';

    public $packages;

    public function mount()
    {
        $this->packages = Package::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function processRenewal($packageId)
    {
        $package = Package::findOrFail($packageId);

        // If trial package, auto renew/activate
        if ($package->is_trial) {
            $user = auth()->user();

            $expiresAt = $package->duration_days ? now()->addDays($package->duration_days) : null;

            $user->update([
                'package_id' => $package->id,
                'package_expires_at' => $expiresAt,
            ]);

            Notification::make()
                ->title('Paket Trial Berhasil Diaktifkan')
                ->body('Selamat menikmati masa percobaan Anda.')
                ->success()
                ->send();

            return redirect()->to('/admin');
        }

        $message = "Halo Admin, saya ingin memperpanjang paket langganan ke paket {$package->name}. Mohon infonya.";
        $whatsappUrl = 'https://wa.me/6281234567890?text='.urlencode($message);

        return redirect()->away($whatsappUrl);
    }
}
