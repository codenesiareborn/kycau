<?php

use App\Filament\Pages\SubscriptionRenewal;
use App\Filament\Widgets\DashboardOverview;
use App\Filament\Widgets\SubscriptionExpiredWidget;
use App\Models\Package;
use App\Models\User;
use Database\Seeders\ShieldSeeder;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ShieldSeeder::class);
});

test('user without package sees subscription expired widget with dates', function () {
    $user = User::factory()->create([
        'created_at' => now()->subDays(10),
    ]);
    $user->assignRole('user');

    // Simulate an expired package
    $package = Package::factory()->create();
    $user->update([
        'package_id' => $package->id,
        'package_expires_at' => now()->subDays(1),
    ]);

    // Refresh user to ensure relationships and attributes are up to date
    $user->refresh();

    // Force locale to 'en' for consistent testing since app defaults to en
    app()->setLocale('en');

    $response = $this->actingAs($user)
        ->get('/admin');

    // Debug output if needed
    // dump($user->created_at->isoFormat('D MMMM Y'));

    // dump($response->getContent()); // Debugging line

    $response->assertSuccessful()
        ->assertSeeLivewire(SubscriptionExpiredWidget::class)
        ->assertDontSeeLivewire(DashboardOverview::class);

    // We need to inspect the Livewire component directly to see if the dates are rendered
    // assertSeeLivewire checks if the component is mounted, but the main response might not contain the full inner HTML of the widget
    // if it's lazily loaded or if we need to assert against the component itself.

    // Instead of checking the full page response for the date (which might be inside the Livewire component's shadow or structure),
    // let's try to make a request to the component or verify the component data/view.

    // However, Livewire::test() is better for unit testing components.
    // Here we are doing a full page request.
    // The issue might be that the widget is rendered but the dates are inside the widget's view
    // and assertSee on the page response *should* see them if they are initially rendered.

    // Let's try testing the widget in isolation to verify it renders the dates correctly.
    Livewire::test(SubscriptionExpiredWidget::class)
        ->assertSee($user->created_at->isoFormat('D MMMM Y'))
        ->assertSee($user->package_expires_at->isoFormat('D MMMM Y'))
        ->assertSee('Perpanjang Paket Sekarang');
});

test('user can access subscription renewal page', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    // Create some packages to display
    Package::factory()->count(3)->create(['is_active' => true, 'is_trial' => false]);

    $this->actingAs($user)
        ->get(SubscriptionRenewal::getUrl())
        ->assertSuccessful()
        ->assertSee('Perpanjang Langganan');
});

test('user can auto renew trial package', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $trialPackage = Package::factory()->create([
        'is_active' => true,
        'is_trial' => true,
        'duration_days' => 14,
        'name' => 'Trial',
    ]);

    // Mount the component
    Livewire::actingAs($user)
        ->test(SubscriptionRenewal::class)
        ->call('processRenewal', $trialPackage->id)
        ->assertRedirect('/admin')
        ->assertNotified('Paket Trial Berhasil Diaktifkan');

    // Verify user record updated
    $user->refresh();
    expect($user->package_id)->toBe($trialPackage->id)
        ->and($user->package_expires_at->diffInDays(now()->addDays(14)))->toBeLessThan(1);
});

test('user with active package sees normal dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $package = Package::factory()->create([
        'duration_days' => 30,
    ]);

    $user->update([
        'package_id' => $package->id,
        'package_expires_at' => now()->addDays(30),
    ]);

    $this->actingAs($user)
        ->get('/admin')
        ->assertSuccessful()
        ->assertDontSeeLivewire(SubscriptionExpiredWidget::class)
        ->assertSeeLivewire(DashboardOverview::class);
});

test('admin always sees normal dashboard even without package', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get('/admin')
        ->assertSuccessful()
        ->assertDontSeeLivewire(SubscriptionExpiredWidget::class)
        ->assertSeeLivewire(DashboardOverview::class);
});
