<?php

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Storage\ValidateIncomingFileService;
use App\Domain\Storage\Rules\FileUploadRules;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules;
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public $avatar;
    public $avatarVersion = 0;
    public bool $showAvatarPreview = false;

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    public function updatedAvatar(): void
    {
        $this->showAvatarPreview = true;
    }

    public function cancelAvatarUpload(): void
    {
        $this->reset('avatar');
        $this->showAvatarPreview = false;
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }

    public function updateAvatar(): void
    {
        $this->validate([
            'avatar' => ['required', 'image', 'max:2048'],
        ]);

        $user = Auth::user();

        // Validate incoming file
        $validator = app(ValidateIncomingFileService::class);
        $validator->handle($this->avatar, FileUploadRules::avatar());

        if ($user->avatar_path) {
            Storage::disk('private')->delete($user->avatar_path);
        }

        // Store avatar at user level (no organization required)
        $path = $this->avatar->store('avatars', 'private');
        $user->update(['avatar_path' => $path]);
        $this->avatarVersion++;

        $this->reset('avatar');
        $this->showAvatarPreview = false;

        Flux::toast(variant: 'success', text: __('Avatar updated.'));
    }

    public function removeAvatar(): void
    {
        $user = Auth::user();

        if ($user->avatar_path) {
            Storage::disk('private')->delete($user->avatar_path);
        }

        $user->update(['avatar_path' => null]);
        $this->avatarVersion++;

        Flux::toast(variant: 'success', text: __('Avatar removed.'));
    }

    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));
            return;
        }

        $user->sendEmailVerificationNotification();

        Flux::toast(text: __('A new verification link has been sent to your email address.'));
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && !Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return !Auth::user() instanceof MustVerifyEmail || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="w-full">
    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Manage your personal information')">
        {{-- Avatar Section --}}
        <div
            class="mb-8 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-zinc-900">
            <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                {{-- Avatar (click to view full) --}}
                <button type="button"
                    wire:click="$dispatch('open-avatar-modal', { avatarPath: '{{ auth()->user()->avatar_path }}', name: '{{ auth()->user()->name }}', email: '{{ auth()->user()->email }}', userId: '{{ auth()->user()->id }}' })"
                    class="relative group shrink-0 cursor-pointer" title="View full size">
                    @if (auth()->user()->avatar_path)
                        <img src="{{ route('users.avatar', ['user' => auth()->user()->id, 'v' => $avatarVersion]) }}"
                            alt="{{ auth()->user()->name }}"
                            class="size-24 rounded-full border-2 border-zinc-200 object-cover shadow-sm group-hover:opacity-80 transition dark:border-white/10 sm:size-28" />
                    @else
                        <div
                            class="flex size-24 items-center justify-center rounded-full border-2 border-zinc-200 bg-gradient-to-br from-blue-500 to-indigo-600 text-3xl font-bold text-white shadow-sm group-hover:opacity-80 transition dark:border-white/10 sm:size-28">
                            {{ auth()->user()->initials() }}
                        </div>
                    @endif

                    @if (auth()->user()->avatar_path)
                        <div
                            class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                            <span class="rounded-full bg-black/60 px-3 py-1 text-xs font-medium text-white">View</span>
                        </div>
                    @endif
                </button>

                <div class="flex-1 space-y-3">
                    <div>
                        <h3 class="text-lg font-semibold text-zinc-950 dark:text-white">Profile Photo</h3>
                        <p class="mt-1 text-sm text-zinc-500">Click your photo to view full size. JPG, PNG, or GIF. Max
                            2MB.</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @if ($showAvatarPreview && $avatar)
                            <button type="button" wire:click="updateAvatar"
                                class="inline-flex items-center rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">
                                Save Photo
                            </button>
                            <button type="button" wire:click="cancelAvatarUpload"
                                class="inline-flex items-center rounded-full border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-white/10 dark:text-zinc-300 dark:hover:bg-white/10">
                                Cancel
                            </button>
                        @else
                            <label class="cursor-pointer">
                                <span
                                    class="inline-flex items-center rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">
                                    {{ auth()->user()->avatar_path ? 'Change Photo' : 'Add Photo' }}
                                </span>
                                <input type="file" wire:model="avatar" class="hidden" accept="image/*" />
                            </label>

                            @if (auth()->user()->avatar_path)
                                <button type="button" wire:click="removeAvatar"
                                    class="inline-flex items-center rounded-full border border-red-300 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:text-red-400 dark:hover:bg-red-950/30">
                                    Remove
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            {{-- Preview --}}
            @if ($showAvatarPreview && $avatar)
                <div
                    class="mt-4 rounded-xl border border-blue-200 bg-blue-50/50 p-4 dark:border-blue-800/50 dark:bg-blue-950/20">
                    <div class="flex items-center gap-4">
                        <img src="{{ $avatar->temporaryUrl() }}" alt="Preview"
                            class="size-20 rounded-full object-cover" />
                        <div>
                            <p class="text-sm font-semibold text-zinc-950 dark:text-white">{{ auth()->user()->name }}
                            </p>
                            <p class="text-xs text-zinc-500">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @error('avatar')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Profile Form --}}
        <form wire:submit="updateProfileInformation" class="space-y-6">
            <flux:input wire:model="name" :label="__('Full Name')" type="text" required autofocus
                autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email Address')" type="email" required
                    autocomplete="email" />

                @if ($this->hasUnverifiedEmail)
                    <div class="mt-4">
                        <flux:text>
                            {{ __('Your email address is unverified.') }}
                            <flux:link class="text-sm cursor-pointer"
                                wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" data-test="update-profile-button">
                    {{ __('Save Changes') }}
                </flux:button>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <div class="mt-8 border-t border-zinc-200 pt-6 dark:border-white/10">
                <livewire:pages::settings.delete-user-form />
            </div>
        @endif
    </x-pages::settings.layout>


</section>
