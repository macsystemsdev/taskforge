@extends('layouts.auth-simple')

@section('title', __('Two-factor authentication'))

@section('content')
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Two-Factor Authentication')" :description="__('Enter the authentication code from your authenticator app.')" />

        <form method="POST" action="{{ route('two-factor.login') }}" class="flex flex-col gap-4">
            @csrf

            <flux:input
                name="code"
                :label="__('Authentication Code')"
                type="text"
                inputmode="numeric"
                autofocus
                autocomplete="one-time-code"
                required
                placeholder="123456"
                class="text-center text-2xl tracking-[0.5em]"
            />

            <flux:button variant="primary" type="submit" class="w-full">
                {{ __('Verify & Login') }}
            </flux:button>
        </form>
    </div>
@endsection
