<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Str;
use App\Models\Category;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([

                Section::make('Category Information')
                    ->schema([

                        Grid::make(1)->schema([

                            Select::make('parent_id')
                                ->label('Parent Category')
                                ->options(function ($record) {
                                    $query = Category::whereNull('parent_id');
                                    if ($record) {
                                        $query->where('id', '!=', $record->id);
                                    }
                                    return $query->pluck('name', 'id');
                                })
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->placeholder('None (Top-level category)'),

                            TextInput::make('name')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('slug', Str::slug($state));
                                }),

                            TextInput::make('slug')
                                ->hidden()
                                ->unique(ignoreRecord: true)
                                ->dehydrated(),

                            Toggle::make('home_featured')
                                ->label('Featured on Home Page')
                                ->helperText('Limit to maximum 2 categories at a time.')
                                ->default(false)
                                ->rules([
                                    function ($get, $record) {
                                        return function ($attribute, $value, $fail) use ($record) {
                                            if ($value) {
                                                $query = Category::where('home_featured', true);
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
                        ]),

                        Textarea::make('description')
                            ->default(null)
                            ->columnSpanFull(),

                        FileUpload::make('image')
                            ->image()
                            ->disk('public'),

                        FileUpload::make('icon')
                            ->image()
                            ->disk('public'),

                    ])->columnSpanFull(),

                Section::make('SEO Settings')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title')
                            ->default(null),
                        Textarea::make('meta_description')
                            ->default(null)
                            ->columnSpanFull()
                    ])->columnSpanFull(),
            ]);
    }
}
