<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        {{-- Package Information Section --}}
        @php
            $user = auth()->user();
            $package = $user->package;
        @endphp

        <div class="mb-6 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 bg-zinc-50 dark:bg-zinc-800/50">
            <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-3">{{ __('Paket Berlangganan') }}</h3>

            @if($package)
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Paket') }}</span>
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                            {{ $package->name }}
                            @if($package->is_trial)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                    Trial
                                </span>
                            @endif
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Status') }}</span>
                        @if($user->hasActivePackage())
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                Aktif
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                Kadaluarsa
                            </span>
                        @endif
                    </div>

                    @if($user->package_expires_at)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Berlaku Hingga') }}</span>
                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $user->package_expires_at->translatedFormat('d F Y') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Sisa Waktu') }}</span>
                            <span
                                class="text-sm font-medium {{ $user->isPackageExpired() ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-zinc-100' }}">
                                {{ $user->package_status }}
                            </span>
                        </div>
                    @elseif($package->isLifetime())
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Berlaku Hingga') }}</span>
                            <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                Selamanya (Lifetime)
                            </span>
                        </div>
                    @endif
                </div>
            @else
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('Anda belum memiliki paket berlangganan.') }}
                </p>
            @endif
        </div>

        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                        {{ __('Save') }}
                    </flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>