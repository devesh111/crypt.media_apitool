<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();

            // Public URL
            $table->string('slug', 32)->unique();

            $table->string('company');
            $table->string('partner');
            $table->string('country');
            $table->string('operator');
            $table->string('offer_name');

            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->index([
                'company',
                'partner',
                'country',
                'operator',
                'offer_name'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};