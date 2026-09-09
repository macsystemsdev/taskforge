<?php

use App\Models\User;

test('two factor challenge redirects to login when not authenticated', function () {
    $this->get(route('two-factor.login'))
        ->assertRedirect(route('login'));
});

test('two factor challenge can be rendered', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->withSession(['login.id' => $user->id]);

    $this->get(route('two-factor.login'))
        ->assertOk();
});
