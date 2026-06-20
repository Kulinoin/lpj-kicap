<?php

namespace App\Filament\Resources\Lpjs;

use App\Filament\Resources\Lpjs\Pages\CreateLpj;
use App\Filament\Resources\Lpjs\Pages\EditLpj;
use App\Filament\Resources\Lpjs\Pages\ListLpjs;
use App\Filament\Resources\Lpjs\Schemas\LpjForm;
use App\Filament\Resources\Lpjs\Tables\LpjsTable;
use App\Models\Lpj;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LpjResource extends Resource
{
    protected static ?string $model = Lpj::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Semua Event';

    protected static ?string $modelLabel = 'Event';

    protected static ?string $pluralModelLabel = 'Event';

    public static function form(Schema $schema): Schema
    {
        return LpjForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LpjsTable::configure($table);
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
            'index' => ListLpjs::route('/'),
            'create' => CreateLpj::route('/create'),
            'edit' => EditLpj::route('/{record}/edit'),
        ];
    }
}
