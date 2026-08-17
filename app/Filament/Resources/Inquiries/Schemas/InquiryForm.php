<?php

namespace App\Filament\Resources\Inquiries\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class InquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Inquiry Information')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('name')
                            ->label('Full Name')
                            ->disabled(),

                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->disabled(),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->disabled(),
                    ]),

                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'unread' => 'Unread',
                            'read' => 'Read',
                            'replied' => 'Replied',
                            'archived' => 'Archived',
                        ])
                        ->required(),

                    Textarea::make('message')
                        ->label('Customer Message')
                        ->rows(6)
                        ->disabled()
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
