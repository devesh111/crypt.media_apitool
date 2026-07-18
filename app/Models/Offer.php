<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'slug',
        'company',
        'partner',
        'country',
        'operator',
        'offer_name',
        'active'
    ];
}