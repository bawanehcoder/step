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
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('beneficiary_name'); // حذف الحقل القديم
            $table->enum('beneficiary_type', ['company', 'driver'])->after('id'); // حقل نوع المستفيد
            $table->foreignId('beneficiary_id')->nullable()->after('beneficiary_type'); // حقل لتخزين الشركة أو السائق
            $table->enum('status', ['paid', 'unpaid'])->default('unpaid')->after('beneficiary_id'); // حقل حالة الفاتورة
            $table->text('additional_notes')->nullable()->after('status'); // ملاحظات إضافية
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            //
        });
    }
};
