@extends('layouts.auth-simple')

@section('title', __('Forgot password'))

@section('content')
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Reset account access')" :description="__('Enter your email address to receive a password reset link.')" />

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-400">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-4">
            @csrf

            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <flux:button variant="primary" type="submit" class="w-full">
                {{ __('Email Password Reset Link') }}
            </flux:button>
        </form>

        <div class="text-center">
            <flux:link :href="route('login')" wire:navigate>{{ __('Back to login') }}</flux:link>
        </div>
    </div>
@endsection
