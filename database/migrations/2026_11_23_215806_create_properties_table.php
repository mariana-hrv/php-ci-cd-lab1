<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();

            $table->string('property_type');
            $table->string('city');
            $table->string('address');

            $table->decimal('area', 10, 2);
            $table->unsignedInteger('rooms')->nullable();

            $table->timestamps();

            $table->index(['city', 'property_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
