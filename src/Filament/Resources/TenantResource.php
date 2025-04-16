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

    public static function getNavigationGroup(): ?string
    {
        return trans('filament-tenancy::messages.group');
    }

    public static function getNavigationLabel(): string
    {
        return trans('filament-tenancy::messages.single');
    }

    public static function getPluralLabel(): ?string
    {
        return trans('filament-tenancy::messages.title');
    }

    public static function getLabel(): ?string
    {
        return trans('filament-tenancy::messages.title');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('name')
                        ->label(trans('filament-tenancy::messages.columns.name'))
                        ->required()
                        ->unique(table:'tenants', ignoreRecord: true)->live(onBlur: true)
                        ->afterStateUpdated(function(Forms\Set $set, $state) {
                            $set('id', $slug = \Str::of($state)->slug('_')->toString());
                            $set('domain', \Str::of($state)->slug()->toString());
                        }),
                    Forms\Components\TextInput::make('id')
                        ->label(trans('filament-tenancy::messages.columns.unique_id'))
                        ->required()
                        ->disabled(fn($context) => $context !=='create')
                        ->unique(table: 'tenants', ignoreRecord: true),
                    Forms\Components\TextInput::make('domain')
                        ->columnSpanFull()
                        ->label(trans('filament-tenancy::messages.columns.domain'))
                        ->required()
                        ->visible(fn($context) => $context ==='create')
                        ->unique(table: 'domains',ignoreRecord: true)
                        ->prefix(request()->getScheme()."://")
                        ->suffix(".".request()->getHost())
                    ,
                    Forms\Components\TextInput::make('email')
                        ->label(trans('filament-tenancy::messages.columns.email'))
                        ->required()
                        ->email(),
                    Forms\Components\TextInput::make('phone')
                        ->label(trans('filament-tenancy::messages.columns.phone'))
                        ->tel(),
                    Forms\Components\TextInput::make('password')
                        ->label(trans('filament-tenancy::messages.columns.password'))
                        ->password()
                        ->revealable(filament()->arePasswordsRevealable())
                        ->rule(Password::default())
                        ->autocomplete('new-password')
                        ->dehydrated(fn ($state): bool => filled($state))
                        ->dehydrateStateUsing(fn ($state): string => Hash::make($state))
                        ->live(debounce: 500)
                        ->same('passwordConfirmation'),
                    Forms\Components\TextInput::make('passwordConfirmation')
                        ->label(trans('filament-tenancy::messages.columns.passwordConfirmation'))
                        ->password()
                        ->revealable(filament()->arePasswordsRevealable())
                        ->dehydrated(false),
                    Forms\Components\Toggle::make('is_active')
                        ->label(trans('filament-tenancy::messages.columns.is_active'))
                        ->default(true),
                ])->columns()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(trans('filament-tenancy::messages.columns.id'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(trans('filament-tenancy::messages.columns.name'))
                    ->description(function ($record){
                        return request()->getScheme()."://".$record->domains()->first()?->domain .'.'.config('filament-tenancy.central_domain'). '/app';
                    }),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->sortable()
                    ->label(trans('filament-tenancy::messages.columns.is_active'))
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(trans('filament-tenancy::messages.columns.is_active'))
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label(trans('filament-tenancy::messages.actions.view'))
                    ->tooltip(trans('filament-tenancy::messages.actions.view'))
                    ->iconButton()
                    ->icon('heroicon-s-link')
                    ->url(fn($record) => request()->getScheme()."://".$record->domains()->first()?->domain .'.'.config('filament-tenancy.central_domain'). '/'. filament('filament-tenancy')->panel)
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('login')
                    ->label(trans('filament-tenancy::messages.actions.login'))
                    ->tooltip(trans('filament-tenancy::messages.actions.login'))
                    ->visible(filament('filament-tenancy')->allowImpersonate)
                    ->requiresConfirmation()
                    ->color('warning')
                    ->iconButton()
                    ->icon('heroicon-s-arrow-left-on-rectangle')
                    ->action(function ($record){
                        $token = tenancy()->impersonate($record, 1, '/app', 'web');

                        return redirect()->to(request()->getScheme()."://".$record->domains[0]->domain.'.'. config('filament-tenancy.central_domain') . '/login/url?token='.$token->token .'&email='. urlencode($record->email));
                    }),
                Tables\Actions\Action::make('password')
                    ->label(trans('filament-tenancy::messages.actions.password'))
                    ->tooltip(trans('filament-tenancy::messages.actions.password'))
                    ->requiresConfirmation()
                    ->icon('heroicon-s-lock-closed')
                    ->iconButton()
                    ->color('danger')
                    ->form([
                        Forms\Components\TextInput::make('password')
                            ->label(trans('filament-tenancy::messages.columns.password'))
                            ->password()
                            ->revealable(filament()->arePasswordsRevealable())
                            ->rule(Password::default())
                            ->autocomplete('new-password')
                            ->dehydrated(fn ($state): bool => filled($state))
                            ->live(debounce: 500)
                            ->same('passwordConfirmation'),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label(trans('filament-tenancy::messages.columns.passwordConfirmation'))
                            ->password()
                            ->revealable(filament()->arePasswordsRevealable())
                            ->dehydrated(false),
                    ])
                    ->action(function (array $data, $record) {
                        $record->password = bcrypt($data['password']);
                        $record->save();

                        Notification::make()
                            ->title(trans('filament-tenancy::messages.actions.notificaitons.password.title'))
                            ->body(trans('filament-tenancy::messages.actions.notificaitons.password.body'))
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make()
                    ->label(trans('filament-tenancy::messages.actions.edit'))
                    ->tooltip(trans('filament-tenancy::messages.actions.edit'))
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->label(trans('filament-tenancy::messages.actions.delete'))
                    ->tooltip(trans('filament-tenancy::messages.actions.delete'))
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
