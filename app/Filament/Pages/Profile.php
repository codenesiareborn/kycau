<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

use BackedEnum;

class Profile extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    protected static ?string $navigationLabel = 'Profil Saya';

    protected static ?string $title = 'Profil Saya';

    protected static ?string $slug = 'profile';

    protected static ?int $navigationSort = 99;

    protected string $view = 'filament.pages.profile';

    public ?array $profileData = [];
    public ?array $passwordData = [];

    public function mount(): void
    {
        $user = auth()->user();

        $this->profileData = [
            'name' => $user->name,
            'email' => $user->email,
        ];

        $this->passwordData = [];
    }

    protected function getSchemas(): array
    {
        return [
            'profileSchema',
            'passwordSchema',
        ];
    }

    public function profileSchema(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique('users', 'email', ignoreRecord: true),
            ])
            ->columns(2)
            ->statePath('profileData');
    }

    public function passwordSchema(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('current_password')
                    ->label('Password Saat Ini')
                    ->password()
                    ->required()
                    ->currentPassword(),
                TextInput::make('password')
                    ->label('Password Baru')
                    ->password()
                    ->required()
                    ->rule(Password::min(8))
                    ->confirmed(),
                TextInput::make('password_confirmation')
                    ->label('Konfirmasi Password Baru')
                    ->password()
                    ->required(),
            ])
            ->columns(1)
            ->statePath('passwordData');
    }

    public function updateProfile(): void
    {
        try {
            $data = $this->profileSchema->getState();

            $user = auth()->user();
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
            ]);

            Notification::make()
                ->success()
                ->title('Profil berhasil diperbarui')
                ->send();
        } catch (Halt $exception) {
            return;
        }
    }

    public function updatePassword(): void
    {
        try {
            $data = $this->passwordSchema->getState();

            $user = auth()->user();
            $user->update([
                'password' => Hash::make($data['password']),
            ]);

            $this->passwordData = [];

            Notification::make()
                ->success()
                ->title('Password berhasil diperbarui')
                ->send();
        } catch (Halt $exception) {
            return;
        }
    }
}
