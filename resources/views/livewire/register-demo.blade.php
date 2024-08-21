<div>
    <div  class="h-screen w-screen flex flex-col justify-center items-center text-center">
        <x-tomato-application-logo />

        <h1 class="text-3xl font-bold my-4">TomatoPHP</h1>
        <p class="w-1/2 text-gray-400">
            TomatoPHP is a community for PHP developers to share their knowledge and experience.
        </p>
        <div class="my-4 flex justify-center items-center gap-4">
            {{ $this->getRegisterAction }}
            {{ $this->getLoginAction }}
        </div>
        <div class="flex justify-center gap-4 my-2">
            <x-filament::link href="https://docs.tomatophp.com" target="_blank">Docs</x-filament::link>
            <x-filament::link href="https://www.github.com/tomatophp" target="_blank">Github</x-filament::link>
            <x-filament::link href="https://discord.gg/vKV9U7gD3c" target="_blank">Support</x-filament::link>
            <x-filament::link href="https://github.com/sponsors/3x1io" target="_blank">Buy Me a Coffee</x-filament::link>
        </div>

    </div>

    <x-filament-actions::modals />

</div>
