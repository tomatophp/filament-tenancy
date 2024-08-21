<?php

namespace TomatoPHP\FilamentTenancy\Filament\Resources;

use TomatoPHP\FilamentTenancy\Filament\Resources\TenantResource\Pages;
use TomatoPHP\FilamentTenancy\Filament\Resources\TenantResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use TomatoPHP\FilamentTenancy\Models\Tenant;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->unique(table:'tenants', ignoreRecord: true)->live(onBlur: true)
                        ->afterStateUpdated(function(Forms\Set $set, $state) {
                            $set('id', $slug = \Str::of($state)->slug('_')->toString());
                            $set('domain', \Str::of($state)->slug()->toString());
                        }),
                    Forms\Components\TextInput::make('id')
                        ->label('Unique ID')
                        ->required()
                        ->disabled(fn($context) => $context !=='create')
                        ->unique(table: 'tenants', ignoreRecord: true),
                    Forms\Components\TextInput::make('domain')
                        ->columnSpanFull()
                        ->label('Sub-Domain')
                        ->required()
                        ->visible(fn($context) => $context ==='create')
                        ->unique(table: 'domains',ignoreRecord: true)
                        ->prefix('https://')
                        ->suffix(".".request()->getHost())
                    ,
                    Forms\Components\TextInput::make('email')->required()->email(),
                    Forms\Components\TextInput::make('phone')->tel(),
                    Forms\Components\TextInput::make('password')
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
                        ->label('Password Confirmation')
                        ->password()
                        ->revealable(filament()->arePasswordsRevealable())
                        ->dehydrated(false),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                ])->columns()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->description(function ($record){
                        return "https://".$record->domains()->first()?->domain .'.'.config('filament-tenancy.central_domain'). '/app';
                    }),
            ])
            ->filters([
                //
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Open Tenant')
                    ->tooltip('Open Tenant')
                    ->iconButton()
                    ->icon('heroicon-s-link')
                    ->url(fn($record) => "https://".$record->domains()->first()?->domain .'.'.config('filament-tenancy.central_domain'). '/'. filament('filament-tenancy')->panel)
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('login')
                    ->visible(filament('filament-tenancy')->allowImpersonate)
                    ->requiresConfirmation()
                    ->label('Login To Tenat')
                    ->color('warning')
                    ->tooltip('Login To Tenat')
                    ->iconButton()
                    ->icon('heroicon-s-arrow-left-on-rectangle')
                    ->action(function ($record){
                        $token = tenancy()->impersonate($record, 1, '/app', 'web');

                        return redirect()->to('https://'.$record->domains[0]->domain.'.'. config('filament-tenancy.central_domain') . '/login/url?token='.$token->token .'&email='. $record->email);
                    }),
                Tables\Actions\Action::make('password')
                    ->requiresConfirmation()
                    ->label("Change Password")
                    ->tooltip("Change Password")
                    ->icon('heroicon-s-lock-closed')
                    ->iconButton()
                    ->color('danger')
                    ->form([
                        Forms\Components\TextInput::make('password')
                            ->label("password")
                            ->password()
                            ->required()
                            ->confirmed()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label("password confirmation")
                            ->password()
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (array $data, $record) {
                        $record->password = bcrypt($data['password']);
                        $record->save();

                        Notification::make()
                            ->title('Account Password Changed')
                            ->body('Account password changed successfully')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->tooltip('Edit')
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->label('Delete')
                    ->tooltip('Delete')
                    ->iconButton(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DomainsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'view' => Pages\ViewTenant::route('/{record}'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
