<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Offer;
use Illuminate\Support\Str;

class OfferSeeder extends Seeder
{
    public function run(): void
    {
        Offer::create([
            'slug' => Str::lower(Str::random(12)),
            'company' => 'cryptmedia',
            'partner' => 'numero',
            'country' => 'ae',
            'operator' => 'etisalat',
            'offer_name' => 'dindolearn',
            'active' => true,
        ]);
    }
}