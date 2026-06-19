<?php

namespace App\Filament\Resources\OrganizationProfiles;

use App\Filament\Resources\OrganizationProfiles\Pages\CreateOrganizationProfile;
use App\Filament\Resources\OrganizationProfiles\Pages\EditOrganizationProfile;
use App\Filament\Resources\OrganizationProfiles\Pages\ListOrganizationProfiles;
use App\Filament\Resources\OrganizationProfiles\Schemas\OrganizationProfileForm;
use App\Filament\Resources\OrganizationProfiles\Tables\OrganizationProfilesTable;
use App\Models\OrganizationProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OrganizationProfileResource extends Resource
{
    protected static ?string $model = OrganizationProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return OrganizationProfileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrganizationProfilesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrganizationProfiles::route('/'),
            'create' => CreateOrganizationProfile::route('/create'),
            'edit' => EditOrganizationProfile::route('/{record}/edit'),
        ];
    }
}
