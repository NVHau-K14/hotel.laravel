<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng CHI TIẾT DỊCH VỤ (dịch vụ dùng trong đơn đặt phòng).
     * Khóa chính thật là (MaDichVu, MaCTDatPhong); MaCTDichVu chỉ là số hiển thị.
     */
    public function up(): void
    {
        Schema::create('CTDICHVU', function (Blueprint $table) {
            $table->integer('MaCTDichVu')->nullable();
            $table->unsignedInteger('MaDichVu');
            $table->integer('SoLuong')->nullable();
            $table->unsignedInteger('MaCTDatPhong');
            $table->integer('TongTienDichVu')->nullable();

            $table->primary(['MaDichVu', 'MaCTDatPhong']);
            $table->foreign('MaDichVu', 'FK_CTDICHVU_DICHVU')
                ->references('MaDichVu')->on('DICHVU');
            $table->foreign('MaCTDatPhong', 'FK_CTDICHVU_CTDATPHONG')
                ->references('MaCTDatPhong')->on('CTDATPHONG');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CTDICHVU');
    }
};
