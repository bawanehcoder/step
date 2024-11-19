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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('barcode', 12)->unique();  // بار كود من 12 خانة
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');  // علاقة مع الزبون
            $table->foreignId('order_type_id')->constrained('order_types')->onDelete('cascade');  // علاقة مع نوع الطلب
            $table->enum('delivery_option', ['same day', 'next day', 'custom date']); // خيارات التسليم
            $table->date('custom_delivery_date')->nullable(); // حقل لتاريخ التسليم المخصص
            $table->text('order_description'); // وصف الطلب
            $table->decimal('weight', 8, 2); // الوزن
            $table->integer('number_of_pieces'); // عدد القطع
            $table->string('invoice_number'); // رقم الفاتورة
            $table->decimal('invoice_value', 10, 2); // قيمة الفاتورة
            $table->decimal('cash_required', 10, 2); // قيمة الكاش المطلوبة
            $table->text('order_notes')->nullable(); // ملاحظات الطلب
            $table->enum('order_status', [
                'pending pickup',
                'picked up',
                'ready for delivery',
                'out for delivery',
                'delivered',
                'returned',
                'damaged'
            ])->default('pending pickup'); // حالة الطلب
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
