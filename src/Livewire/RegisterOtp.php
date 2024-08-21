<?php

namespace TomatoPHP\FilamentTenancy\Livewire;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\SimplePage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use TomatoPHP\FilamentTenancy\Models\Tenant;

class RegisterOtp extends SimplePage
{
    use InteractsWithFormActions;
    use WithRateLimiting;

    protected static string $view = 'filament-tenancy::livewire.register-otp';

    public array $data;
    public \stdClass $user;
    public string $otp;

    public function mount(): void
    {
        if(!session()->has('demo_user') || !session()->has('demo_otp')){
            abort(404);
        }
        else {
            $this->user = json_decode(session()->get('demo_user'));
            $this->otp = session()->get('demo_otp');
        }
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('otp')
                ->label('OTP Code')
                ->numeric()
                ->maxLength(6)
                ->autocomplete('current-password')
                ->required()
                ->extraInputAttributes(['tabindex' => 2])
        ])->statePath('data');
    }

    public function submitAction()
    {
        return Action::make('submitAction')
            ->label('Check')
            ->action(function (){
                $this->authenticate();
            });
    }

    protected function throwFailureOtpException(): never
    {
        throw ValidationException::withMessages([
            'data.otp' => "otp not correct",
        ]);
    }
    public function authenticate()
    {
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

        $data = $this->form->getState();



        if($data['otp'] != $this->otp){
            $this->throwFailureOtpException();
        }

        $record = Tenant::create([
            'name' => $this->user->name,
            'id' => $this->user->id,
            'email' => $this->user->email,
            'phone' => $this->user->phone,
            'packages'=> $this->user->packages,
            'password'=> $this->user->password,
        ]);

        $record->domains()->create(['domain' => $this->user->domain]);

        session()->regenerate();

        $token = tenancy()->impersonate($record, 1, '/app', 'web');

        return redirect()->to('https://'.$record->domains[0]->domain.'.'. config('app.domain') . '/login/url?token='.$token->token .'&email='. $record->email);
    }

    protected function getResendAction(): Action
    {
        return Action::make('getResendAction')
            ->requiresConfirmation()
            ->form([
                TextInput::make('email')
                    ->required()
                    ->email()
                    ->label('Please enter your email'),
            ])
            ->link()
            ->label('Resend OTP')
            ->color('warning')
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

                $otp = substr(number_format(time() * rand(), 0, '', ''), 0, 6);
                session()->put('demo_otp', $otp);
                $data = json_decode(session()->get('demo_user'));

                try {
                    $embeds = [];
                    $embeds['description'] = "your OTP is: ". $otp;
                    $embeds['url'] = url('/otp');

                    $params = [
                        'content' => "@" . $data->domain,
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
            });
    }

}
