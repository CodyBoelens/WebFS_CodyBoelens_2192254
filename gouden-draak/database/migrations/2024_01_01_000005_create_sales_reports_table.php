<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dagelijkse verkoop rapportages (UC-19)
        Schema::create('sales_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date')->unique();
            $table->decimal('total_revenue', 10, 2)->default(0);
            $table->integer('total_orders')->default(0);
            $table->string('file_path')->nullable();   // pad naar gegenereerd Excel bestand
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_reports');
    }
};
