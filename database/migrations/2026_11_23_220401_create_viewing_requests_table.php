<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viewing_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('buyer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('listing_id')
                ->constrained('listings')
                ->cascadeOnDelete();

            $table->date('preferred_date');
            $table->time('preferred_time');

            $table->string('status')->default('pending');
            $table->text('comment')->nullable();

            $table->timestamps();

            $table->index(['status', 'preferred_date']);
            $table->index('listing_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viewing_requests');
    }
};
