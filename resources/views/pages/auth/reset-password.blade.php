@extends('layouts.auth-simple')

@section('title', __('Reset password'))

@section('content')
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Reset password')" :description="__('Choose a new password to regain access to your account.')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-4">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email', $request->email)"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <flux:input
                name="password"
                :label="__('New Password')"
                type="password"
                required
                autocomplete="new-password"
                placeholder="••••••••"
                viewable
            />

            <flux:input
                name="password_confirmation"
                :label="__('Confirm Password')"
                type="password"
                required
                autocomplete="new-password"
                placeholder="••••••••"
                viewable
            />

            <flux:button variant="primary" type="submit" class="w-full">
                {{ __('Reset Password') }}
            </flux:button>
        </form>
    </div>
@endsection
