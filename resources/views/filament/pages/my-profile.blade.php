<x-filament-panels::page>
    <form wire:submit="updateProfile">
        {{ $this->profileForm }}
        <div class="mt-4 flex justify-end">
            <x-filament::button type="submit">
                Save Profile
            </x-filament::button>
        </div>
    </form>

    <form wire:submit="updatePassword" class="mt-8">
        {{ $this->passwordForm }}
        <div class="mt-4 flex justify-end">
            <x-filament::button type="submit" color="warning">
                Change Password
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>