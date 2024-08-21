<div class="fi-simple-page">
    <section class="grid auto-cols-fr gap-y-6">
        <x-filament-panels::header.simple
            :heading="$this->getHeading()"
            :logo="true"
        >
            <x-slot name="subheading">
                Please check our <x-filament::link target="_blank" href="https://discord.gg/vKV9U7gD3c">discord server</x-filament::link> and check <b>#otp</b> channel for the OTP.
            </x-slot>
        </x-filament-panels::header.simple>

        <x-filament-panels::form wire:submit.prevent="authenticate">
            {{ $this->form }}

            {{ $this->submitAction }}
        </x-filament-panels::form>

        <div class="text-center">
            <span class="text-gray-400">Don't get the code? please {{ $this->getResendAction }}</span>
            <x-filament-actions::modals />
        </div>
    </section>
</div>
