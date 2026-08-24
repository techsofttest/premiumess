<?php

namespace App\Filament\Resources\ContactSettings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_name')
                    ->label('Company Name')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Phone Number'),

                TextColumn::make('email')
                    ->label('Primary Email'),

                TextColumn::make('working_hours')
                    ->label('Working Hours'),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
