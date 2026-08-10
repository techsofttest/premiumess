<?php

namespace App\Filament\Resources\Products\RelationManagers;


use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Actions\DeleteAction;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $recordTitleAttribute = 'review_name';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('review_name')
                    ->label('Name')
                    ->searchable(),

                TextColumn::make('review_email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('review_rating')
                    ->label('Rating')
                    ->formatStateUsing(fn($state) => str_repeat('⭐', $state))
                    ->sortable(),

                TextColumn::make('review_title')
                    ->label('Title'),

                TextColumn::make('review_content')
                    ->label('Review')
                    ->wrap()
                    ->limit(null),

                ToggleColumn::make('review_status')
                    ->label('Visible'),
            ])
            ->recordActions([
                DeleteAction::make(),
            ]);
    }
}
