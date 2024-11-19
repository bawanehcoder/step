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
            Schema::create('order_logs', function (Blueprint $table) {
                $table->id();
                $table->string('barcode'); // الباركود
                $table->foreignId('customer_id')->constrained()->onDelete('cascade'); // معرف العميل
                $table->foreignId('order_id')->constrained()->onDelete('cascade'); // معرف العميل
                $table->foreignId('order_type_id')->constrained()->onDelete('cascade'); // معرف نوع الطلب
                $table->string('delivery_option'); // خيار التوصيل
                $table->date('custom_delivery_date')->nullable(); // تاريخ التوصيل المخصص
                $table->text('order_description'); // وصف الطلب
                $table->decimal('weight', 8, 2); // الوزن
                $table->integer('number_of_pieces'); // عدد القطع
                $table->string('invoice_number'); // رقم الفاتورة
                $table->decimal('invoice_value', 10, 2); // قيمة الفاتورة
                $table->decimal('cash_required', 10, 2); // المبلغ المطلوب
                $table->text('order_notes')->nullable(); // ملاحظات الطلب
                $table->string('order_status'); // حالة الطلب
                $table->foreignId('company_id')->constrained()->onDelete('cascade'); // معرف الشركة
                $table->timestamps(); // حقول timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_logs');
    }
};
