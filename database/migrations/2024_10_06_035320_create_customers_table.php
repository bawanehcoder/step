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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');  // اسم العميل
            $table->string('phone'); // رقم الهاتف
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade'); // علاقة مع الشركة
            $table->foreignId('city_id')->constrained('cities')->onDelete('cascade');       // علاقة مع المدينة
            $table->foreignId('zone_id')->constrained('zones')->onDelete('cascade');        // علاقة مع الزون
            $table->string('street_name');    // اسم الشارع
            $table->string('building_number'); // رقم العمارة
            $table->string('floor');          // الطابق
            $table->text('additional_details')->nullable();  // تفاصيل إضافية للعنوان
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
