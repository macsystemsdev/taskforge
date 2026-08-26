@extends('layouts.auth-simple')

@section('title', __('Confirm password'))

@section('content')
    <div class="flex flex-col gap-6">
        <h1 class="text-2xl font-bold text-center">{{ __('Confirm Password') }}</h1>
        <p class="text-sm text-center text-zinc-600">{{ __('This is a secure area of the application. Please confirm your password before continuing.') }}</p>

        <form method="POST" action="{{ route('password.confirm') }}" class="flex flex-col gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium">{{ __('Password') }}</label>
                <input type="password" name="password" required autofocus class="mt-1 block w-full rounded-md border-zinc-300 shadow-sm">
                @error('password')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <button type="submit" class="w-full rounded-md bg-blue-600 px-4 py-2 text-white">
                {{ __('Confirm') }}
            </button>
        </form>
    </div>
@endsection
