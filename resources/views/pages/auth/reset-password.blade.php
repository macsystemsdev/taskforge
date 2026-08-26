@extends('layouts.auth-simple')

@section('title', __('Reset password'))

@section('content')
    <div class="flex flex-col gap-6">
        <h1 class="text-2xl font-bold text-center">{{ __('Reset password') }}</h1>
        <p class="text-sm text-center text-zinc-600">{{ __('Choose a new password to regain access to TaskForge.') }}</p>

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-4">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <div>
                <label class="block text-sm font-medium">{{ __('Email') }}</label>
                <input type="email" name="email" value="{{ old('email', $request->email) }}" required class="mt-1 block w-full rounded-md border-zinc-300 shadow-sm">
                @error('email')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Password') }}</label>
                <input type="password" name="password" required class="mt-1 block w-full rounded-md border-zinc-300 shadow-sm">
                @error('password')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Confirm Password') }}</label>
                <input type="password" name="password_confirmation" required class="mt-1 block w-full rounded-md border-zinc-300 shadow-sm">
            </div>
            <button type="submit" class="w-full rounded-md bg-blue-600 px-4 py-2 text-white">
                {{ __('Reset Password') }}
            </button>
        </form>
    </div>
@endsection
