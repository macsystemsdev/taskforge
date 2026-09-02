<?php

use App\Models\User;
use Laravel\Fortify\Features;
use Livewire\Livewire;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());
    Features::twoFactorAuthentication(['confirm' => true, 'confirmPassword' => true]);
});

test('security settings page can be rendered', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    session(['auth.password_confirmed_at' => now()->timestamp]);
    Livewire::test('pages::settings.security')->assertOk();
});

test('security settings page requires password confirmation when enabled', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    session(['auth.password_confirmed_at' => null]);
    Livewire::test('pages::settings.security')->assertRedirect(route('password.confirm'));
});

test('security settings page renders without two factor when feature is disabled', function () {
    config(['fortify.features' => []]);
    $user = User::factory()->create();
    $this->actingAs($user);
    session(['auth.password_confirmed_at' => now()->timestamp]);
    Livewire::test('pages::settings.security')->assertSet('canManageTwoFactor', false);
});

test('password can be updated', function () {
    $user = User::factory()->create(['password' => bcrypt('OldPassword123!')]);
    $this->actingAs($user);
    session(['auth.password_confirmed_at' => now()->timestamp]);
    Livewire::test('pages::settings.security')
        ->set('current_password', 'OldPassword123!')
        ->set('password', 'NewPassword123!')
        ->set('password_confirmation', 'NewPassword123!')
        ->call('updatePassword')
        ->assertHasNoErrors();
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create(['password' => bcrypt('OldPassword123!')]);
    $this->actingAs($user);
    session(['auth.password_confirmed_at' => now()->timestamp]);
    Livewire::test('pages::settings.security')
        ->set('current_password', 'WrongPassword123!')
        ->set('password', 'NewPassword123!')
        ->set('password_confirmation', 'NewPassword123!')
        ->call('updatePassword')
        ->assertHasErrors(['current_password']);
});
