<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Aanbiedingen (US-12 / UC-14 PDF)
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('discount_amount', 8, 2)->nullable();   // vast bedrag korting
            $table->decimal('discount_percent', 5, 2)->nullable();  // procentuele korting
            $table->date('valid_from');
            $table->date('valid_until');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Koppeltabel product <-> aanbieding
        Schema::create('product_promotion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_promotion');
        Schema::dropIfExists('promotions');
    }
};
