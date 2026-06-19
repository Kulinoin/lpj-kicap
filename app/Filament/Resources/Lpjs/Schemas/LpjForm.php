<?php

namespace App\Filament\Resources\Lpjs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class LpjForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('lpj_type_id')
                    ->required()
                    ->numeric(),
                TextInput::make('created_by')
                    ->numeric(),
                Select::make('person_in_charge_id')
                    ->relationship('personInCharge', 'name'),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
                TextInput::make('completeness_status')
                    ->required()
                    ->default('belum_lengkap'),
                DatePicker::make('start_date'),
                DatePicker::make('end_date'),
                TextInput::make('location'),
                TextInput::make('funding_source'),
                TextInput::make('assignment_letter_number'),
                TextInput::make('period_label'),
                TextInput::make('external_organizer'),
                Textarea::make('organization_role')
                    ->columnSpanFull(),
                TextInput::make('total_funds_received')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total_valid_expense')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total_remaining_fund')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                DateTimePicker::make('submitted_at'),
                DateTimePicker::make('approved_at'),
                TextInput::make('approved_by')
                    ->numeric(),
                DateTimePicker::make('finalized_at'),
                TextInput::make('finalized_by')
                    ->numeric(),
                DateTimePicker::make('archived_at'),
            ]);
    }
}
