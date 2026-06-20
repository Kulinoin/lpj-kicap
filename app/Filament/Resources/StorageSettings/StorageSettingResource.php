<?php

namespace App\Filament\Resources\StorageSettings;

use App\Filament\Resources\StorageSettings\Pages\EditStorageSetting;
use App\Filament\Resources\StorageSettings\Pages\ListStorageSettings;
use App\Models\StorageSetting;
use App\Services\AppFileStorageService;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StorageSettingResource extends Resource
{
    protected static ?string $model = StorageSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCloud;

    protected static ?string $navigationLabel = 'Pengaturan Penyimpanan';

    protected static ?string $modelLabel = 'Pengaturan Penyimpanan';

    protected static ?string $pluralModelLabel = 'Pengaturan Penyimpanan';

    protected static \UnitEnum|string|null $navigationGroup = 'Pengaturan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Provider Aktif')
                ->schema([
                    Select::make('provider')
                        ->label('Provider')
                        ->options(StorageSetting::providerOptions())
                        ->required()
                        ->native(false),
                    TextInput::make('root_prefix')
                        ->label('Folder Root')
                        ->helperText('Opsional. Contoh: production/kicap-event'),
                ])
                ->columns(2),
            Section::make('Cloudflare R2')
                ->schema([
                    TextInput::make('r2_account_id')
                        ->label('Account ID'),
                    TextInput::make('r2_access_key_id')
                        ->label('Access Key ID'),
                    TextInput::make('r2_secret_access_key')
                        ->label('Secret Access Key')
                        ->password()
                        ->revealable()
                        ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? $state : null)
                        ->dehydrated(fn (?string $state): bool => filled($state)),
                    TextInput::make('r2_bucket')
                        ->label('Bucket'),
                    TextInput::make('r2_endpoint')
                        ->label('Endpoint')
                        ->placeholder('https://<account-id>.r2.cloudflarestorage.com')
                        ->url(),
                    TextInput::make('r2_public_url')
                        ->label('Public URL')
                        ->placeholder('https://files.example.com')
                        ->url(),
                ])
                ->columns(2),
            Section::make('Optimasi Gambar')
                ->schema([
                    Toggle::make('auto_webp_enabled')
                        ->label('Kompres gambar ke WebP otomatis')
                        ->default(true),
                    TextInput::make('webp_quality')
                        ->label('Kualitas WebP')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(100)
                        ->default(78),
                    TextInput::make('max_image_width')
                        ->label('Lebar Maksimal Gambar')
                        ->numeric()
                        ->minValue(320)
                        ->default(1800),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('provider')
                    ->label('Provider')
                    ->formatStateUsing(fn (string $state): string => StorageSetting::providerOptions()[$state] ?? $state)
                    ->badge(),
                IconColumn::make('r2_configured')
                    ->label('R2 Siap')
                    ->state(fn (StorageSetting $record): bool => $record->isR2Configured())
                    ->boolean(),
                IconColumn::make('auto_webp_enabled')
                    ->label('WebP Aktif')
                    ->boolean(),
                IconColumn::make('gd_webp')
                    ->label('Engine WebP')
                    ->state(fn (): bool => app(AppFileStorageService::class)->webpEngineStatus()['gd_webp'])
                    ->boolean(),
                TextColumn::make('webp_quality')
                    ->label('Quality'),
                TextColumn::make('max_image_width')
                    ->label('Max Width'),
                TextColumn::make('updated_at')
                    ->label('Update')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStorageSettings::route('/'),
            'edit' => EditStorageSetting::route('/{record}/edit'),
        ];
    }
}
