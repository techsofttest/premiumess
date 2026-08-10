<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\CheckboxList;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\RichEditor;

use Illuminate\Support\Str;
use App\Models\Category;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            /* ================= COLLECTIONS ================= */

            Section::make('Homepage & Promotional Collections')
                ->description('Assign this product to one or more homepage / featured collections.')
                ->schema([
                    CheckboxList::make('collections')
                        ->relationship('collections', 'name')
                        ->columns(3)
                        ->gridDirection('row')
                        ->bulkToggleable()
                        ->columnSpanFull(),
                ])->columnSpanFull(),

            /* ================= BASIC INFO ================= */

            Section::make('Basic Information')
                ->schema([

                    Grid::make(3)->schema([

                        Grid::make(4)->schema([

                            Toggle::make('is_active')
                                ->label('Active')
                                ->default(true)
                                ->inline(false),

                            // Toggle::make('is_featured')
                            //     ->label('Featured')
                            //     ->inline(false),

                            Toggle::make('requires_direct_delivery')
                                ->label('Direct Delivery')
                                ->inline(false),

                            Toggle::make('allows_courier')
                                ->label('Courier Delivery')
                                ->inline(false),

                        ])->columnSpanFull(),

                        // Select::make('category_id')
                        //     ->label('Category')
                        //     ->options(function () {
                        //         $categories = Category::with('children')->whereNull('parent_id')->get();
                        //         $options = [];
                        //         foreach ($categories as $parent) {
                        //             // Group: parent name, options: children
                        //             if ($parent->children->count() > 0) {
                        //                 foreach ($parent->children as $child) {
                        //                     $options[$parent->name][$child->id] = $child->name;
                        //                 }
                        //             } else {
                        //                 $options[$parent->id] = $parent->name;
                        //             }
                        //         }
                        //         return $options;
                        //     })
                        //     ->searchable()
                        //     ->preload()
                        //     ->required(),

                          Select::make('gender')
                            ->options([
                                'men' => 'Men',
                                'women' => 'Women',
                                'unisex' => 'Unisex',
                            ])
                            ->required(),
                            
                        TextInput::make('name')
                            ->label('Product Name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn($state, $set) =>
                                $set('slug', Str::slug($state))
                            ),

                        TextInput::make('sku')
                            ->label('Product SKU'),

                        TextInput::make('slug')
                            ->hidden()
                            ->unique(ignoreRecord: true)
                            ->dehydrated(),

                        Select::make('brand_id')
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Brand'),

                      

                        Select::make('fragrance_family_id')
                            ->relationship('fragranceFamily', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Fragrance Family'),

                        Select::make('fragrance_concentration_id')
                            ->relationship('fragranceConcentration', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Concentration'),

                        // TextInput::make('supplier_code')
                        //     ->label('Supplier Code'),

                    ])->columnSpanFull(),

                ])->columnSpanFull(),

            /* ================= VARIANTS (REPEATER) ================= */

            Section::make('Product Variants')
                ->description('Every product must have at least one variant. Prices are set per variant.')
                ->schema([
                    Repeater::make('variants')
                        ->relationship()
                        ->schema([
                            Grid::make(4)->schema([
                                TextInput::make('sku')
                                    ->label('Variant SKU'),

                                TextInput::make('unit')
                                    ->label('Unit')
                                    ->placeholder('e.g. kg, pcs, ltr'),

                                TextInput::make('size')
                                    ->label('Size')
                                    ->placeholder('e.g. 1, 500g, XL'),

                                TextInput::make('stock')
                                    ->label('Stock')
                                    ->numeric()
                                    ->default(0),

                                TextInput::make('buying_price')
                                    ->label('Buying Price')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('₹')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $get, $set) {
                                        $margin = floatval($get('margin'));
                                        $buying = floatval($state);
                                        if ($buying > 0 && $margin > 0) {
                                            $set('selling_price', round($buying + ($buying * $margin / 100), 2));
                                        }
                                    }),

                                // TextInput::make('margin')
                                //     ->label('Margin %')
                                //     ->numeric()
                                //     ->default(0)
                                //     ->suffix('%')
                                //     ->live(onBlur: true)
                                //     ->afterStateUpdated(function ($state, $get, $set) {
                                //         $buying = floatval($get('buying_price'));
                                //         $margin = floatval($state);
                                //         if ($buying > 0 && $margin > 0) {
                                //             $set('selling_price', round($buying + ($buying * $margin / 100), 2));
                                //         }
                                //     }),

                                // TextInput::make('tax_percentage')
                                //     ->label('Tax %')
                                //     ->numeric()
                                //     ->default(0)
                                //     ->minValue(0)
                                //     ->maxValue(100)
                                //     ->suffix('%')
                                //     ->live(onBlur: true)
                                //     ->afterStateUpdated(function ($state, $get, $set) {
                                //         $buying = floatval($get('buying_price'));
                                //         $margin = floatval($get('margin'));
                                //         $tax = floatval($state);
                                //         if ($buying > 0 && ($margin > 0 || $tax > 0)) {
                                //             $priceAfterTax = $buying * (1 + ($tax / 100));
                                //             $sellingPrice = $priceAfterTax * (1 + ($margin / 100));
                                //             $set('selling_price', round($sellingPrice, 2));
                                //         }
                                //     }),

                                // DatePicker::make('expiry_date')
                                //     ->label('Expiry Date')
                                //     ->nullable(),

                                TextInput::make('selling_price')
                                    ->label('Selling Price')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('₹'),
                            ]),
                        ])
                        ->addActionLabel('Add Variant')
                        ->defaultItems(1)
                        ->minItems(1)
                        ->reorderable(false)
                        ->collapsible(),
                ])->columnSpanFull(),

            /* ================= MEDIA ================= */

            Section::make('Product Media')
                ->schema([
                    FileUpload::make('featured_image')
                        ->label('Featured Image')
                        ->disk('public')
                        ->directory('products')
                        ->image()
                        ->preserveFilenames(),

                ])->columnSpanFull(),

            /* ================= DETAILS ================= */

            Section::make('Product Details')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('top_notes')
                            ->label('Top Notes')
                            ->placeholder('e.g. citrus, floral, woody'),

                        TextInput::make('middle_notes')
                            ->label('Middle Notes')
                            ->placeholder('e.g. citrus, floral, woody'),

                        TextInput::make('base_notes')
                            ->label('Base Notes')
                            ->placeholder('e.g. citrus, floral, woody'),
                    ]),

                    RichEditor::make('key_features')
                        ->label('Key Features')
                        ->columnSpanFull(),

                    RichEditor::make('description')
                        ->label('Product Description')
                        ->columnSpanFull(),

                ])->columnSpanFull(),

            /* ================= SEO ================= */

            Section::make('SEO Settings')
                ->collapsible()
                ->collapsed()
                ->schema([
                    TextInput::make('meta_title')
                        ->label('SEO Title'),

                    Textarea::make('meta_description')
                        ->label('SEO Description')
                        ->columnSpanFull(),
                ])->columnSpanFull(),
        ]);
    }
}
