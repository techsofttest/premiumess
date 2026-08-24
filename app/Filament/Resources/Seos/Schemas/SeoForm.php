<?php

namespace App\Filament\Resources\Seos\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SeoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Page Name / Title')
                    ->placeholder('e.g. Home Page or About Us')
                    ->required(),
                TextInput::make('page_slug')
                    ->label('Page Slug / Route Key')
                    ->placeholder('e.g. home, about-us, contact-us, shop, brands, faqs, journals')
                    ->required(),
                TextInput::make('meta_title')
                    ->label('Meta Title')
                    ->placeholder('Custom SEO Meta Title')
                    ->default(null),
                \Filament\Forms\Components\Textarea::make('meta_description')
                    ->label('Meta Description')
                    ->placeholder('Custom SEO Meta Description')
                    ->rows(3)
                    ->default(null),
                TextInput::make('meta_keywords')
                    ->label('Meta Keywords')
                    ->placeholder('e.g. perfumes, luxury fragrances, UAE, Dubai, Oud')
                    ->default(null),
            ]);
    }
}
