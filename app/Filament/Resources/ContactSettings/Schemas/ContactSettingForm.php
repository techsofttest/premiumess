<?php

namespace App\Filament\Resources\ContactSettings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ContactSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Contact & Store Information')
                    ->description('Manage public business details displayed across the storefront header, footer, and contact pages.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('company_name')
                                ->label('Company / Store Name')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('phone')
                                ->label('Mobile Phone Number')
                                ->placeholder('+971 55 723 2010')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('telephone')
                                ->label('2nd Telephone / Landline Number')
                                ->placeholder('02 550 8990')
                                ->maxLength(255),

                            TextInput::make('whatsapp')
                                ->label('WhatsApp Concierge Number')
                                ->placeholder('+971 50 123 4567')
                                ->maxLength(255),

                            TextInput::make('email')
                                ->label('Primary General Email')
                                ->email()
                                ->placeholder('info@premiumessence.ae')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('support_email')
                                ->label('Customer Support Email')
                                ->email()
                                ->placeholder('support@premiumessence.ae')
                                ->maxLength(255),

                            TextInput::make('working_hours')
                                ->label('Business Working Hours')
                                ->placeholder('Mon - Sat: 9:00 AM - 9:00 PM (GST)')
                                ->maxLength(255)
                                ->columnSpanFull(),
                        ]),

                        Textarea::make('address')
                            ->label('Physical Office / Boutique Address')
                            ->rows(3)
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('google_maps_link')
                            ->label('Google Maps Location URL')
                            ->placeholder('https://maps.google.com/?q=Musaffah+M9+Abu+Dhabi+UAE')
                            ->url()
                            ->columnSpanFull(),
                    ]),

                Section::make('Social Media Handles & Links')
                    ->description('Social channel links rendered in the website footer and contact sections (only non-empty links will be shown).')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('facebook_url')
                                ->label('Facebook Page URL')
                                ->placeholder('https://facebook.com/yourpage')
                                ->url(),

                            TextInput::make('instagram_url')
                                ->label('Instagram Profile URL')
                                ->placeholder('https://instagram.com/yourhandle')
                                ->url(),

                            TextInput::make('twitter_url')
                                ->label('X (Twitter) Profile URL')
                                ->placeholder('https://x.com/yourhandle')
                                ->url(),

                            TextInput::make('youtube_url')
                                ->label('YouTube Channel URL')
                                ->placeholder('https://youtube.com/@yourchannel')
                                ->url(),

                            TextInput::make('linkedin_url')
                                ->label('LinkedIn Page URL')
                                ->placeholder('https://linkedin.com/company/yourpage')
                                ->url(),
                        ]),
                    ]),
            ]);
    }
}
