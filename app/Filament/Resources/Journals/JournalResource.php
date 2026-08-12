<?php

namespace App\Filament\Resources\Journals;

use App\Filament\Resources\Journals\Pages\CreateJournal;
use App\Filament\Resources\Journals\Pages\EditJournal;
use App\Filament\Resources\Journals\Pages\ListJournals;
use App\Models\Journal;
use BackedEnum;
use UnitEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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

class JournalResource extends Resource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Journals & Articles';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Journal Article Details')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->required()
                                ->live(onBlur: true)
                                ->columnSpan(1),

                            TextInput::make('slug')
                                ->required()
                                ->unique(Journal::class, 'slug', ignoreRecord: true)
                                ->columnSpan(1),

                            Select::make('category')
                                ->options([
                                    'Haute Parfumerie' => 'Haute Parfumerie',
                                    'Fragrance Craft' => 'Fragrance Craft',
                                    'Heritage & Notes' => 'Heritage & Notes',
                                    'Luxury Lifestyle' => 'Luxury Lifestyle',
                                    'Olfactory Guides' => 'Olfactory Guides',
                                ])
                                ->default('Haute Parfumerie')
                                ->required(),

                            TextInput::make('author')
                                ->default('Master Perfumer')
                                ->required(),
                        ]),

                        FileUpload::make('image')
                            ->label('Featured Cover Image')
                            ->image()
                            ->disk('public')
                            ->directory('journals')
                            ->imageEditor()
                            ->columnSpanFull(),

                        Textarea::make('excerpt')
                            ->label('Article Excerpt / Summary')
                            ->rows(3)
                            ->columnSpanFull(),

                        RichEditor::make('content')
                            ->label('Full Article Content')
                            ->required()
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            Toggle::make('is_published')
                                ->label('Publish Article')
                                ->default(true),

                            DatePicker::make('published_at')
                                ->label('Publishing Date')
                                ->default(now()),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Cover')
                    ->disk('public')
                    ->circular(),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('category')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('author')
                    ->sortable(),

                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),

                TextColumn::make('published_at')
                    ->label('Date')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJournals::route('/'),
            'create' => CreateJournal::route('/create'),
            'edit' => EditJournal::route('/{record}/edit'),
        ];
    }
}
