<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tafels in het restaurant
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('number', 10);      // tafel 1, 2 etc.
            $table->integer('seats')->default(4);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Bestellingen (zowel tablet als kassa/afhaal)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->nullable()->constrained()->nullOnDelete(); // null = afhaal
            $table->enum('source', ['tablet', 'kassa', 'website'])->default('kassa');
            $table->enum('status', ['open', 'in_behandeling', 'gereed', 'betaald', 'geannuleerd'])->default('open');
            $table->integer('round')->default(1);   // US-1: max 5 rondes
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->decimal('total', 8, 2)->default(0);
            $table->timestamp('last_order_at')->nullable(); // US-1: 1x per 10 min
            $table->timestamps();

            $table->index(['table_id', 'status']);
            $table->index('source');
        });

        // Orderregels
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 8, 2);
            $table->string('rice_choice')->nullable(); // US-11: witte rijst, nasi, bami, mihoen
            $table->text('note')->nullable();          // US-10: opmerking per gerecht
            $table->timestamps();

            $table->index('order_id');
        });

        // Hulpverzoeken van tablet (US-5)
        Schema::create('help_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['open', 'afgemeld'])->default('open');
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('help_requests');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('tables');
    }
};
