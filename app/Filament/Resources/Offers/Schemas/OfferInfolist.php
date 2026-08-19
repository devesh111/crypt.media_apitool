<?php

namespace App\Filament\Resources\Offers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OfferInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Offer Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('offer_name')
                                    ->label('Offer Name'),

                                TextEntry::make('slug')
                                    ->copyable(),

                                TextEntry::make('company'),

                                TextEntry::make('partner'),

                                TextEntry::make('country'),

                                TextEntry::make('operator'),

                                IconEntry::make('active')
                                    ->label('Status')
                                    ->boolean(),

                                TextEntry::make('created_at')
                                    ->label('Created')
                                    ->dateTime('d M Y H:i'),

                                TextEntry::make('updated_at')
                                    ->label('Updated')
                                    ->dateTime('d M Y H:i'),
                            ]),
                    ]),

                Section::make('URLs')
                    ->schema([
                        TextEntry::make('landing_url')
                            ->label('Landing Page')
                            ->state(fn ($record) => url("/{$record->slug}.html"))
                            ->copyable(),

                        TextEntry::make('pin_request_url')
                            ->label('PIN Request')
                            ->state(fn ($record) => url("/{$record->slug}.html/pin_request"))
                            ->copyable(),

                        TextEntry::make('pin_verification_url')
                            ->label('PIN Verification')
                            ->state(fn ($record) => url("/{$record->slug}.html/pin_verification"))
                            ->copyable(),
                    ]),

                Section::make('Technical')
                    ->schema([
                        TextEntry::make('controller')
                            ->label('Controller Class')
                            ->state(fn ($record) => sprintf(
                                'App\\Http\\Controllers\\services\\%s\\%s\\%s\\%s\\%s',
                                $record->company,
                                $record->partner,
                                $record->country,
                                $record->operator,
                                ucfirst(strtolower($record->offer_name))
                            ))
                            ->copyable(),
                    ]),
            ]);
    }
}