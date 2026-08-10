<?php

namespace App\Filament\Resources\Testimonials;

use App\Filament\Resources\Testimonials\Pages\CreateTestimonial;
use App\Filament\Resources\Testimonials\Pages\EditTestimonial;
use App\Filament\Resources\Testimonials\Pages\ListTestimonials;
use App\Models\Testimonial;
use BackedEnum;
use UnitEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static string|UnitEnum|null $navigationGroup = 'CMS';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Client Details')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Client Name')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('role')
                                ->label('Designation / Location')
                                ->placeholder('e.g. Verified Client, Abu Dhabi')
                                ->maxLength(255),

                            Select::make('rating')
                                ->label('Rating Stars')
                                ->options([
                                    5 => '5 Stars (★★★★★)',
                                    4 => '4 Stars (★★★★)',
                                    3 => '3 Stars (★★★)',
                                    2 => '2 Stars (★★)',
                                    1 => '1 Star (★)',
                                ])
                                ->default(5)
                                ->required(),

                            TextInput::make('sort_order')
                                ->numeric()
                                ->default(0),

                            FileUpload::make('image')
                                ->label('Avatar / Photo')
                                ->image()
                                ->disk('public')
                                ->directory('testimonials')
                                ->columnSpanFull(),

                            Textarea::make('quote')
                                ->label('Testimonial Quote')
                                ->required()
                                ->rows(4)
                                ->columnSpanFull(),

                            Toggle::make('is_featured')
                                ->label('Featured on Homepage')
                                ->default(true),

                            Toggle::make('is_active')
                                ->label('Active / Visible')
                                ->default(true),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('image')
                    ->label('Photo')
                    ->circular(),

                TextColumn::make('name')
                    ->label('Client Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('role')
                    ->label('Role / Location')
                    ->searchable(),

                TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(fn ($state) => str_repeat('★', (int) $state))
                    ->sortable(),

                TextColumn::make('quote')
                    ->label('Quote Preview')
                    ->limit(60),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTestimonials::route('/'),
            'create' => CreateTestimonial::route('/create'),
            'edit' => EditTestimonial::route('/{record}/edit'),
        ];
    }
}
