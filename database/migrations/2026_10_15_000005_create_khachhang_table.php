<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng KHÁCH HÀNG. MatKhau = NULL là khách vãng lai (đặt phòng không cần tài khoản).
     */
    public function up(): void
    {
        Schema::create('KHACHHANG', function (Blueprint $table) {
            $table->increments('MaKhachHang');
            $table->string('HoTen', 255)->nullable();
            $table->string('Email', 254)->nullable();
            $table->string('DiaChi', 255)->nullable();
            $table->string('DienThoai', 20)->nullable();
            $table->string('CMND', 20)->nullable();
            $table->string('MatKhau', 255)->nullable();
            $table->string('TrangThai', 30)->nullable()->default('HOAT_DONG');

            // Cột sinh tự động: email rỗng thành NULL, nên nhiều khách có thể để trống email
            // mà UNIQUE vẫn chặn được email trùng. Không bao giờ ghi trực tiếp vào cột này.
            $table->string('EmailConnexion', 254)->nullable()->storedAs("NULLIF(`Email`, '')");

            $table->unique('DienThoai', 'uq_customer_phone');
            $table->unique('EmailConnexion', 'uq_customer_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('KHACHHANG');
    }
};
