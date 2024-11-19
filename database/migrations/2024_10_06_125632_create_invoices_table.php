<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // نوع الفاتورة: صادرة أو مستلمة
            $table->string('beneficiary_name'); // اسم المستفيد
            $table->text('description')->nullable(); // وصف الفاتورة
            $table->date('invoice_date'); // تاريخ الفاتورة
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
