<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;


class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order', 'asc')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('parent.name')
                    ->label('Parent')
                    ->placeholder('—')
                    ->sortable(),

                ImageColumn::make('image')
                    ->disk('public')
                    ->size(50)
                    ->square(),

                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),

                ToggleColumn::make('home_featured')
                    ->label('Home Featured')
                    ->sortable()
                    ->rules([
                        function ($record) {
                            return function ($attribute, $value, $fail) use ($record) {
                                if ($value) {
                                    $query = \App\Models\Category::where('home_featured', true);
                                    if ($record) {
                                        $query->where('id', '!=', $record->id);
                                    }
                                    if ($query->count() >= 2) {
                                        $fail('Only 2 categories can be featured on the home page at a time.');
                                    }
                                }
                            };
                        }
                    ]),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
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
