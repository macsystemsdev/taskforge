@extends('layouts.auth-simple')

@section('title', __('Confirm password'))

@section('content')
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Confirm Password')" :description="__('This is a secure area. Please confirm your password before continuing.')" />

        <form method="POST" action="{{ route('password.confirm') }}" class="flex flex-col gap-4">
            @csrf

            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autofocus
                autocomplete="current-password"
                placeholder="••••••••"
                viewable
            />

            <flux:button variant="primary" type="submit" class="w-full">
                {{ __('Confirm') }}
            </flux:button>
        </form>
    </div>
@endsection
