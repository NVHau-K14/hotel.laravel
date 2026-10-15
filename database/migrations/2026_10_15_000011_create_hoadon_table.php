<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng HÓA ĐƠN. ChiTietJSON lưu bản chụp chi tiết hóa đơn khi lập/thanh toán.
     */
    public function up(): void
    {
        Schema::create('HOADON', function (Blueprint $table) {
            $table->increments('MaHoaDon');
            $table->date('NgayThuTien')->nullable();
            $table->integer('SoTienThu')->nullable();
            $table->longText('ChiTietJSON')->nullable();
            $table->unsignedInteger('MaCTDatPhong')->nullable();
            $table->unsignedInteger('MaNhanVien')->nullable();
            $table->string('TrangThai', 30)->nullable()->default('CHUA_THANH_TOAN');

            $table->foreign('MaCTDatPhong', 'FK_HOADON_CTDATPHONG')
                ->references('MaCTDatPhong')->on('CTDATPHONG');
            $table->foreign('MaNhanVien', 'FK_HOADON_NHANVIEN')
                ->references('MaNhanVien')->on('NHANVIEN');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('HOADON');
    }
};
