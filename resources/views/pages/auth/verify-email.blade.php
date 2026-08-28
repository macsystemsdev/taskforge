@extends('layouts.auth-simple')

@section('title', __('Email verification'))

@section('content')
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Verify your email')" :description="__('Check your inbox for the verification link to continue.')" />

        @if (session('status') == 'verification-link-sent')
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-400">
                {{ __('A new verification link has been sent to your email address.') }}
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="flex flex-col gap-4">
            @csrf

            <flux:button variant="primary" type="submit" class="w-full">
                {{ __('Resend Verification Email') }}
            </flux:button>
        </form>

        <div class="flex items-center justify-between">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <flux:button variant="ghost" type="submit" size="sm">
                    {{ __('Log Out') }}
                </flux:button>
            </form>
        </div>
    </div>
@endsection
