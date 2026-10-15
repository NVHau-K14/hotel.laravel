<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng PHÂN CÔNG công việc cho nhân viên.
     */
    public function up(): void
    {
        Schema::create('BANGPHANCONG', function (Blueprint $table) {
            $table->increments('MaPhanCong');
            $table->date('NgayPhanCong')->nullable();
            $table->string('LoaiCongViec', 50)->nullable();
            $table->unsignedInteger('MaNhanVien')->nullable();

            $table->foreign('MaNhanVien', 'FK_BANGPHANCONG_NHANVIEN')
                ->references('MaNhanVien')->on('NHANVIEN');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('BANGPHANCONG');
    }
};
