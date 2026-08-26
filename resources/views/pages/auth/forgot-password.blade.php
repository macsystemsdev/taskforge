@extends('layouts.auth-simple')

@section('title', __('Forgot password'))

@section('content')
    <div class="flex flex-col gap-6">
        <h1 class="text-2xl font-bold text-center">{{ __('Reset account access') }}</h1>
        <p class="text-sm text-center text-zinc-600">{{ __('Enter your email address to receive a TaskForge password reset link.') }}</p>

        @if (session('status'))
            <div class="text-sm text-green-600">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium">{{ __('Email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus class="mt-1 block w-full rounded-md border-zinc-300 shadow-sm">
                @error('email')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <button type="submit" class="w-full rounded-md bg-blue-600 px-4 py-2 text-white">
                {{ __('Email Password Reset Link') }}
            </button>
        </form>
    </div>
@endsection
