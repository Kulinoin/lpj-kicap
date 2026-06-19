<?php

namespace App\Filament\Resources\LpjTypes;

use App\Filament\Resources\LpjTypes\Pages\CreateLpjType;
use App\Filament\Resources\LpjTypes\Pages\EditLpjType;
use App\Filament\Resources\LpjTypes\Pages\ListLpjTypes;
use App\Filament\Resources\LpjTypes\Schemas\LpjTypeForm;
use App\Filament\Resources\LpjTypes\Tables\LpjTypesTable;
use App\Models\LpjType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LpjTypeResource extends Resource
{
    protected static ?string $model = LpjType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return LpjTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LpjTypesTable::configure($table);
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
            'index' => ListLpjTypes::route('/'),
            'create' => CreateLpjType::route('/create'),
            'edit' => EditLpjType::route('/{record}/edit'),
        ];
    }
}
