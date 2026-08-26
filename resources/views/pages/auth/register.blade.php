@extends('layouts.auth-simple')

@section('title', __('Register'))

@section('content')
    <div class="flex flex-col gap-6">
        <h1 class="text-2xl font-bold text-center">{{ __('Create your TaskForge account') }}</h1>
        <p class="text-sm text-center text-zinc-600">{{ __('Start organizing your team, projects, and tasks.') }}</p>

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium">{{ __('Name') }}</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus class="mt-1 block w-full rounded-md border-zinc-300 shadow-sm">
                @error('name')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 block w-full rounded-md border-zinc-300 shadow-sm">
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
                {{ __('Register') }}
            </button>
        </form>

        @if (Route::has('login'))
            <div class="text-sm text-center">
                {{ __('Already have an account?') }}
                <a href="{{ route('login') }}" class="text-blue-600">{{ __('Log in') }}</a>
            </div>
        @endif
    </div>
@endsection
