<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng PHÒNG. MaPhong do người dùng nhập (101, 102, ...) nên KHÔNG tự tăng.
     */
    public function up(): void
    {
        Schema::create('PHONG', function (Blueprint $table) {
            $table->unsignedInteger('MaPhong')->primary();
            $table->unsignedInteger('MaLoaiPhong')->nullable();
            $table->string('HinhAnhDaiDien', 255)->nullable();
            $table->string('TrangThai', 30)->nullable()->default('Trống');

            $table->foreign('MaLoaiPhong', 'FK_PHONG_LOAIPHONG')
                ->references('MaLoaiPhong')->on('LOAIPHONG');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PHONG');
    }
};
