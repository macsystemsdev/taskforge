@extends('layouts.auth-simple')

@section('title', __('Email verification'))

@section('content')
    <div class="flex flex-col gap-6">
        <h1 class="text-2xl font-bold text-center">{{ __('Verify your email') }}</h1>
        <p class="text-sm text-center text-zinc-600">{{ __('Check your inbox for the TaskForge verification link and confirm your email to continue.') }}</p>

        @if (session('status') == 'verification-link-sent')
            <div class="text-sm text-green-600">{{ __('A new verification link has been sent to your email address.') }}</div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="flex flex-col gap-4">
            @csrf
            <button type="submit" class="w-full rounded-md bg-blue-600 px-4 py-2 text-white">
                {{ __('Resend Verification Email') }}
            </button>
        </form>
    </div>
@endsection
