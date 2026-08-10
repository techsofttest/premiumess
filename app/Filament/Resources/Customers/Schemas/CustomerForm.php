<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->readOnly(),
                TextInput::make('email')
                    ->readOnly(),
                TextInput::make('phone')
                    ->readOnly(),
                TextInput::make('created_at')
                    ->label('Registered At')
                    ->readOnly(),
            ]);
    }
}
