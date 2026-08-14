<?php

namespace App\Filament\Resources\CuratedDeals\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Str;

class CuratedDealForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Curation / Deal Header Details')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Deal / Bundle Name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn ($state, $set) => $set('slug', Str::slug($state))
                            ),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('subtitle')
                            ->label('Subtitle / Tagline (e.g. 5 x 10ml Travel Sprays)')
                            ->maxLength(255),

                        TextInput::make('badge')
                            ->label('Badge Tag (e.g. Special Deal • Save 25%)')
                            ->maxLength(255),

                        FileUpload::make('image')
                            ->label('Banner / Cover Image')
                            ->image()
                            ->directory('deals')
                            ->visibility('public')
                            ->columnSpanFull(),
                    ]),

                    Textarea::make('description')
                        ->label('Curation Description')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),

            Section::make('Pricing, Discount & Stock Inventory')
                ->schema([
                    Grid::make(4)->schema([
                        TextInput::make('price')
                            ->label('Deal Selling Price (AED)')
                            ->numeric()
                            ->required()
                            ->suffix('AED'),

                        TextInput::make('original_price')
                            ->label('Original Value (AED)')
                            ->numeric()
                            ->suffix('AED'),

                        TextInput::make('discount_percent')
                            ->label('Savings Discount (%)')
                            ->numeric()
                            ->suffix('%'),

                        TextInput::make('stock')
                            ->label('Available Stock Quantity')
                            ->numeric()
                            ->default(100)
                            ->required(),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label('Active on Storefront')
                            ->default(true)
                            ->inline(false),
                    ]),
                ]),

            Section::make('Box Contents & Curation Highlights')
                ->schema([
                    Repeater::make('contents')
                        ->label('Package Items Included')
                        ->simple(
                            TextInput::make('item')->placeholder('e.g. 1x 100ml Emperor Oud Pour Homme')
                        )
                        ->columnSpanFull(),

                    Repeater::make('features')
                        ->label('Key Highlights & Features')
                        ->simple(
                            TextInput::make('feature')->placeholder('e.g. Complimentary signature gift wrapping')
                        )
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
