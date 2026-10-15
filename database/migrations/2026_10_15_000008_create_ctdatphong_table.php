<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng CHI TIẾT ĐẶT PHÒNG (đơn đặt phòng).
     * GiaPhongDat lưu giá phòng tại thời điểm đặt.
     * payment_requested_at, payment_expired_at thuộc VietQR: có trong SQL nhưng code chưa dùng.
     */
    public function up(): void
    {
        Schema::create('CTDATPHONG', function (Blueprint $table) {
            $table->increments('MaCTDatPhong');
            $table->date('NgayThucHien')->nullable();
            $table->unsignedInteger('MaKhachHang')->nullable();
            $table->date('NgayNhan')->nullable();
            $table->date('NgayTra')->nullable();
            $table->unsignedInteger('MaPhong')->nullable();
            $table->unsignedInteger('MaNhanVien')->nullable();
            $table->string('TrangThai', 50)->nullable()->default('CHO_ADMIN_DUYET');
            $table->integer('GiaPhongDat')->nullable();
            $table->string('payment_status', 50)->nullable()->default('CHUA_TAO');
            $table->dateTime('payment_requested_at')->nullable();
            $table->dateTime('payment_expired_at')->nullable();

            $table->foreign('MaKhachHang', 'FK_CTDATPHONG_KHACHHANG')
                ->references('MaKhachHang')->on('KHACHHANG');
            $table->foreign('MaPhong', 'FK_CTDATPHONG_PHONG')
                ->references('MaPhong')->on('PHONG');
            $table->foreign('MaNhanVien', 'FK_CTDATPHONG_NHANVIEN')
                ->references('MaNhanVien')->on('NHANVIEN');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CTDATPHONG');
    }
};
