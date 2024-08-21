<?php

namespace TomatoPHP\FilamentTenancy\Livewire;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use TomatoPHP\FilamentTenancy\Models\Tenant;

class RegisterDemo extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithFormActions;
    use InteractsWithForms;
    use WithRateLimiting;

    public array $data=[];

    public function getRegisterAction(): Action
    {
        return Action::make('getRegisterAction')
            ->label('Get Started')
            ->modalHeading('Welcome to TomatoPHP community Demo')
            ->modalDescription('you will start a SaaS for you with sub-domain to test our plugins')
            ->modalSubmitActionLabel('Register')
            ->form([
                Forms\Components\Grid::make([
                    'sm' => 1,
                    'lg' => 2
                ])->schema([
                    Forms\Components\ToggleButtons::make('loginBy')
                        ->label('Sign Up By')
                        ->inline()
                        ->default('github')
                        ->live()
                        ->columnSpanFull()
                        ->colors([
                            'github' => 'warning',
                            'discord' => 'info',
                            'register' => 'danger',
                        ])
                        ->icons([
                            'github' => 'bxl-github',
                            'discord' => 'bxl-discord',
                            'register' => 'bxl-discord-alt',
                        ])
                        ->options([
                            'github' => 'Github Account',
                            'discord' => 'Discord Account',
                            'register' => 'Discord Username',
                        ]),
                    Forms\Components\TextInput::make('name')
                        ->label('Discord username')
                        ->hidden(fn(Get $get) => $get('loginBy') !== 'register')
                        ->required()
                        ->unique(table:'tenants', ignoreRecord: true)->live(onBlur: true)
                        ->columnSpanFull()
                        ->afterStateUpdated(function(Forms\Set $set, $state) {
                            $set('id', $slug = \Str::of($state)->slug('_')->toString());
                            $set('domain', \Str::of($state)->slug()->toString());
                        }),
                    Forms\Components\TextInput::make('id')
                        ->hidden(fn(Get $get) => $get('loginBy') !== 'register')
                        ->disabled()
                        ->label('Unique ID')
                        ->required()
                        ->unique(table: 'tenants', ignoreRecord: true),
                    Forms\Components\TextInput::make('domain')
                        ->disabled()
                        ->hidden(fn(Get $get) => $get('loginBy') !== 'register')
                        ->label('Sub-Domain')
                        ->required()
                        ->unique(table: 'domains',ignoreRecord: true)
                        ->prefix('https://')
                        ->suffix(".".request()->getHost())
                    ,
                    Forms\Components\TextInput::make('email')
                        ->hidden(fn(Get $get) => $get('loginBy') !== 'register')
                        ->required()
                        ->email(),
                    Forms\Components\TextInput::make('phone')
                        ->hidden(fn(Get $get) => $get('loginBy') !== 'register')
                        ->required()
                        ->tel(),
                    Forms\Components\TextInput::make('password')
                        ->hidden(fn(Get $get) => $get('loginBy') !== 'register')
                        ->label('Password')
                        ->password()
                        ->revealable(filament()->arePasswordsRevealable())
                        ->rule(Password::default())
                        ->autocomplete('new-password')
                        ->dehydrated(fn ($state): bool => filled($state))
                        ->dehydrateStateUsing(fn ($state): string => Hash::make($state))
                        ->live(debounce: 500)
                        ->same('passwordConfirmation'),
                    Forms\Components\TextInput::make('passwordConfirmation')
                        ->hidden(fn(Get $get) => $get('loginBy') !== 'register')
                        ->label('Password Confirmation')
                        ->password()
                        ->revealable(filament()->arePasswordsRevealable())
                        ->required()
                        ->dehydrated(false),
                    Forms\Components\CheckboxList::make('packages')
                        ->searchable()
                        ->label('Plugins')
                        ->hint('Select the plugins you want to install')
                        ->columnSpanFull()
                        ->required()
                        ->default(["filament-users"])
                        ->view('components.packages')
                        ->descriptions(collect(config('app.packages'))->pluck('description', 'key')->toArray())
                        ->options(collect(config('app.packages'))->pluck('label', 'key')->toArray()),
                ])
            ])
            ->action(function (array $data){
                try {
                    $this->rateLimit(5);
                } catch (TooManyRequestsException $exception) {
                    Notification::make()
                        ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                            'seconds' => $exception->secondsUntilAvailable,
                            'minutes' => ceil($exception->secondsUntilAvailable / 60),
                        ]))
                        ->body(array_key_exists('body', __('filament-panels::pages/auth/login.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/login.notifications.throttled.body', [
                            'seconds' => $exception->secondsUntilAvailable,
                            'minutes' => ceil($exception->secondsUntilAvailable / 60),
                        ]) : null)
                        ->danger()
                        ->send();

                    return null;
                }
                if($data['loginBy'] === 'register'){
                    $otp = substr(number_format(time() * rand(), 0, '', ''), 0, 6);
                    $data['id'] = \Str::of($data['name'])->slug('_')->toString();
                    $data['domain'] =  \Str::of($data['name'])->slug()->toString();
                    session()->put('demo_user', json_encode($data));
                    session()->put('demo_otp', $otp);

                    Notification::make()
                        ->title('New Demo User')
                        ->body(collect([
                            'NAME: '.$data['name'],
                            'EMAIL: '.$data['email'],
                            'USERNAME: '.$data['domain'],
                            'OTP: '.$otp,
                            'PACKAGES: '.collect($data['packages'])->implode(','),
                            'URL: '.'https://'.\Str::of($data['name'])->slug()->toString().'.'.config('app.domain'),
                        ])->implode("\n"))
                        ->sendToDiscord();

                    try {
                        $embeds = [];
                        $embeds['description'] = "your OTP is: ". $otp;
                        $embeds['url'] = url('/otp');

                        $params = [
                            'content' => "@" . $data['domain'],
                            'embeds' => [
                                $embeds
                            ]
                        ];

                        Http::post(config('services.discord.otp-webhook'), $params)->json();

                    }catch (\Exception $e){
                        Notification::make()
                            ->title('Something went wrong')
                            ->danger()
                            ->send();
                    }

                    Notification::make()
                        ->title('Check discord server')
                        ->body('We have sent your OTP to our discord server #otp channel')
                        ->success()
                        ->send();


                    return redirect()->route('verify.otp');

                }
                else {
                    session()->put('demo_user', json_encode($data));

                    return redirect()->route('login.provider', ['provider' => $data['loginBy']]);
                }
            });
    }

    public function getLoginAction(): Action
    {
        return Action::make('getLoginAction')
            ->color('info')
            ->label('Demo Login')
            ->modalHeading('Welcome to TomatoPHP community Demo')
            ->modalDescription('please use username or password to login or use social login')
            ->modalSubmitActionLabel('Login')
            ->form([
                Forms\Components\Grid::make([
                    'sm' => 1,
                    'lg' => 2
                ])->schema([
                    Forms\Components\ToggleButtons::make('loginBy')
                        ->label('Sign In By')
                        ->inline()
                        ->default('github')
                        ->live()
                        ->columnSpanFull()
                        ->colors([
                            'github' => 'warning',
                            'discord' => 'info',
                            'register' => 'danger',
                        ])
                        ->icons([
                            'github' => 'bxl-github',
                            'discord' => 'bxl-discord',
                            'register' => 'bxl-discord-alt',
                        ])
                        ->options([
                            'github' => 'Github Account',
                            'discord' => 'Discord Account',
                            'register' => 'Discord Username',
                        ]),
                    Forms\Components\TextInput::make('email')
                        ->hidden(fn(Get $get) => $get('loginBy') !== 'register')
                        ->required()
                        ->email(),
                    Forms\Components\TextInput::make('password')
                        ->required()
                        ->hidden(fn(Get $get) => $get('loginBy') !== 'register')
                        ->label('Password')
                        ->password()
                        ->revealable(filament()->arePasswordsRevealable())
                        ->rule(Password::default())
                        ->autocomplete('new-password'),
                ])
            ])
            ->action(function (array $data){
                try {
                    $this->rateLimit(5);
                } catch (TooManyRequestsException $exception) {
                    Notification::make()
                        ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                            'seconds' => $exception->secondsUntilAvailable,
                            'minutes' => ceil($exception->secondsUntilAvailable / 60),
                        ]))
                        ->body(array_key_exists('body', __('filament-panels::pages/auth/login.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/login.notifications.throttled.body', [
                            'seconds' => $exception->secondsUntilAvailable,
                            'minutes' => ceil($exception->secondsUntilAvailable / 60),
                        ]) : null)
                        ->danger()
                        ->send();

                    return null;
                }
                if($data['loginBy'] === 'register'){
                    $record = Tenant::query()
                        ->where('email', $data['email'])
                        ->first();

                    if($record){
                        if(Hash::check($data['password'], $record->password)){
                            session()->regenerate();

                            $token = tenancy()->impersonate($record, 1, '/app', 'web');

                            return redirect()->to('https://' . $record->domains[0]->domain . '.' . config('app.domain') . '/login/url?token=' . $token->token . '&email=' . $record->email);
                        }
                        else {
                            Notification::make()
                                ->title('Invalid Credentials')
                                ->body('Please check your email and password')
                                ->danger()
                                ->send();
                        }
                    }
                    else {
                        Notification::make()
                            ->title('Invalid Credentials')
                            ->body('Please check your email and password')
                            ->danger()
                            ->send();
                    }

                }
                else {
                    return redirect()->route('login.provider', ['provider' => $data['loginBy']]);
                }
            });
    }


    public function render()
    {
        return view('filament-tenancy::livewire.register-demo');
    }
}
