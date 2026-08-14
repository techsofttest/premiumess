<?php

namespace App\Filament\Resources\CuratedDeals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class CuratedDealsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order', 'asc')
            ->columns([
                ImageColumn::make('image')
                    ->label('Cover Image'),

                TextColumn::make('name')
                    ->label('Deal Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subtitle')
                    ->label('Subtitle')
                    ->searchable(),

                TextColumn::make('price')
                    ->label('Price')
                    ->suffix(' AED')
                    ->sortable(),

                TextColumn::make('original_price')
                    ->label('Original Price')
                    ->suffix(' AED')
                    ->sortable(),

                TextColumn::make('badge')
                    ->label('Badge Tag')
                    ->badge(),

                TextColumn::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
