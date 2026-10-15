<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng NHÂN VIÊN (tài khoản đăng nhập trang quản trị).
     */
    public function up(): void
    {
        Schema::create('NHANVIEN', function (Blueprint $table) {
            $table->increments('MaNhanVien');
            $table->string('Username', 20)->nullable();
            $table->string('Password', 255)->nullable();
            $table->string('TenNhanVien', 100)->nullable();
            $table->date('NgaySinh')->nullable();
            $table->string('CMND', 20)->nullable();
            $table->date('NgayVaoLam')->nullable();
            $table->unsignedInteger('MaChucVu')->nullable();

            $table->unique('Username', 'uq_staff_username');
            $table->foreign('MaChucVu', 'FK_NHANVIEN_CHUCVU')
                ->references('MaChucVu')->on('CHUCVU');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('NHANVIEN');
    }
};
