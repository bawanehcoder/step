<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
        $table->foreignId('company_id')->constrained()->onDelete('cascade'); // ارتباط بشركة
        $table->foreignId('zone_id')->nullable()->constrained()->onDelete('cascade'); // ارتباط بمنطقة (اختياري)
        $table->enum('discount_type', ['fixed', 'percentage']); // نوع الخصم (ثابت أو نسبة مئوية)
        $table->decimal('value', 8, 2); // قيمة الخصم (مبلغ ثابت أو نسبة مئوية)
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
