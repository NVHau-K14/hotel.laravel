<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng LOẠI PHÒNG. DaXoa là cờ xóa mềm tự viết (không dùng SoftDeletes của Laravel).
     */
    public function up(): void
    {
        Schema::create('LOAIPHONG', function (Blueprint $table) {
            $table->increments('MaLoaiPhong');
            $table->string('TenLoaiPhong', 20)->nullable();
            $table->integer('GiaPhong')->nullable();
            $table->string('MoTa', 255)->nullable();
            $table->boolean('DaXoa')->nullable()->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('LOAIPHONG');
    }
};
