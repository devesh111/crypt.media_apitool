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

            $table->string('company', 100);
            $table->string('partner', 100);
            $table->string('country', 50);
            $table->string('operator', 100);
            $table->string('offer_name', 150);

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