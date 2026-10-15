<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng ĐÁNH GIÁ. Khóa chính tên ID. Đánh giá do admin thêm thì không có khách/đơn.
     */
    public function up(): void
    {
        Schema::create('CTDANHGIA', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('NoiDung', 255)->nullable();
            $table->date('NgayDanhGia')->nullable();
            $table->unsignedInteger('MaPhong')->nullable();
            $table->unsignedInteger('MaKhachHang')->nullable();
            $table->unsignedInteger('MaCTDatPhong')->nullable();
            $table->integer('SoSao')->default(5);

            $table->foreign('MaPhong', 'FK_CTDANHGIA_PHONG')
                ->references('MaPhong')->on('PHONG');
            $table->foreign('MaKhachHang', 'FK_CTDANHGIA_KHACHHANG')
                ->references('MaKhachHang')->on('KHACHHANG');
            $table->foreign('MaCTDatPhong', 'FK_CTDANHGIA_CTDATPHONG')
                ->references('MaCTDatPhong')->on('CTDATPHONG');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CTDANHGIA');
    }
};
