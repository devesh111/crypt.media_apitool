<?php

namespace App\Filament\Resources\Offers\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class OfferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Hidden::make('slug')
                    ->default(fn() => strtolower(Str::random(12))),

                TextInput::make('company')
                    ->label('Company')
                    ->required()
                    ->maxLength(100),

                TextInput::make('partner')
                    ->label('Partner')
                    ->required()
                    ->maxLength(100),

                TextInput::make('country')
                    ->label('Country')
                    ->required()
                    ->maxLength(20),

                TextInput::make('operator')
                    ->label('Operator')
                    ->required()
                    ->maxLength(100),

                TextInput::make('offer_name')
                    ->label('Offer Name')
                    ->required()
                    ->maxLength(100),

                Toggle::make('active')
                    ->label('Active')
                    ->default(true)
                    ->required(),
            ]);
    }
}