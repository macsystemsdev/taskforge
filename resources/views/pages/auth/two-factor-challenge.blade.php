@extends('layouts.auth-simple')

@section('title', __('Two-factor authentication'))

@section('content')
    <div class="flex flex-col gap-6">
        <h1 class="text-2xl font-bold text-center">{{ __('Two-Factor Authentication') }}</h1>
        <p class="text-sm text-center text-zinc-600">{{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}</p>

        <form method="POST" action="{{ route('two-factor.login') }}" class="flex flex-col gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium">{{ __('Code') }}</label>
                <input type="text" name="code" inputmode="numeric" autofocus autocomplete="one-time-code" required class="mt-1 block w-full rounded-md border-zinc-300 shadow-sm">
                @error('code')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <button type="submit" class="w-full rounded-md bg-blue-600 px-4 py-2 text-white">
                {{ __('Login') }}
            </button>
        </form>
    </div>
@endsection
