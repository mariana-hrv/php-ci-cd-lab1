<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('property_id')
                ->constrained('properties')
                ->cascadeOnDelete();

            $table->foreignId('seller_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->decimal('price', 12, 2);
            $table->text('description')->nullable();
            $table->string('status')->default('draft');

            $table->timestamps();

            $table->index(['status', 'price']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
