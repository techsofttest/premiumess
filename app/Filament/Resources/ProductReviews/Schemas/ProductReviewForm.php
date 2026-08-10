<?php

namespace App\Filament\Resources\ProductReviews\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('review_product_id')
                    ->label('Product')
                    ->options(Product::pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Select::make('review_rating')
                    ->label('Rating')
                    ->options([
                        1 => '★ (1 Star)',
                        2 => '★★ (2 Stars)',
                        3 => '★★★ (3 Stars)',
                        4 => '★★★★ (4 Stars)',
                        5 => '★★★★★ (5 Stars)',
                    ])
                    ->default(5)
                    ->required(),
                TextInput::make('review_name')
                    ->label('Reviewer Name')
                    ->required(),
                TextInput::make('review_email')
                    ->label('Reviewer Email')
                    ->email()
                    ->required(),
                TextInput::make('review_title')
                    ->label('Review Title')
                    ->columnSpanFull(),
                Textarea::make('review_content')
                    ->label('Review Content')
                    ->rows(4)
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('review_status')
                    ->label('Approved / Published')
                    ->default(true),
            ]);
    }
}
