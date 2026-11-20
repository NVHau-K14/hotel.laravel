<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tên loại phòng 20 ký tự quá ngắn cho tên thực tế (vd. "Phòng Gia Đình Cao Cấp") → mở rộng lên 50.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('LOAIPHONG', function (Blueprint $table) {
            $table->string('TenLoaiPhong', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('LOAIPHONG', function (Blueprint $table) {
            $table->string('TenLoaiPhong', 20)->nullable()->change();
        });
    }
};
