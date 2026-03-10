<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. ตาราง driver (ตัวตนของคนขับ ผูกกับ LINE ID)
        Schema::create('emp_drivers', function (Blueprint $table) {
            $table->id();
            $table->string('line_user_id', 100)->unique();
            $table->string('line_display_name', 200)->nullable();
            $table->string('driver_name', 100);
            $table->string('phone', 20);
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
        });

        // 2. junction: driver ขับรถคันไหนได้บ้าง (many-to-many)
        Schema::create('emp_driver_buses', function (Blueprint $table) {
            $table->id();
            $table->string('line_user_id', 100);
            $table->unsignedBigInteger('bus_id');
            $table->unique(['line_user_id', 'bus_id']);
        });

        // 3. เพิ่ม line_user_id ใน Bus_In_Out_Log เพื่อรู้ว่าใครขับเที่ยวนี้
        Schema::table('Bus_In_Out_Log', function (Blueprint $table) {
            $table->string('line_user_id', 100)->nullable()->after('bus_id');
        });

        // 4. Reset log (ข้อมูลเก่าใช้ driver model เดิม ใช้ด้วยกันไม่ได้)
        DB::statement('DELETE FROM Bus_In_Out_Log');
    }

    public function down(): void
    {
        Schema::dropIfExists('emp_driver_buses');
        Schema::dropIfExists('emp_drivers');
        Schema::table('Bus_In_Out_Log', function (Blueprint $table) {
            $table->dropColumn('line_user_id');
        });
    }
};
