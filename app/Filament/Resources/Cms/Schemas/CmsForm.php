<?php

namespace App\Filament\Resources\Cms\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Schema;

class CmsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('title')
                    ->required()
                    ->live(onBlur: true),
                
                TextInput::make('slug')
                    ->required()
                    ->hidden(),

                RichEditor::make('content')
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('meta_title')
                    ->columnSpanFull()
                    ->nullable(),

                Textarea::make('description')
                    ->label('Meta Description')
                    ->columnSpanFull()
                    ->nullable(),
            ]);
    }
}
