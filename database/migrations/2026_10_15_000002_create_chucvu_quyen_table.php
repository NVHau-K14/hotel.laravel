<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng PHÂN QUYỀN CHỨC VỤ. Khóa chính ghép (MaChucVu, MaQuyen);
     * MaQuyen là chuỗi (DASHBOARD, PHONG, ...), không có bảng quyền riêng.
     */
    public function up(): void
    {
        Schema::create('CHUCVU_QUYEN', function (Blueprint $table) {
            $table->unsignedInteger('MaChucVu');
            $table->string('MaQuyen', 50);

            $table->primary(['MaChucVu', 'MaQuyen']);
            // Xóa chức vụ thì xóa luôn quyền của chức vụ đó (ON DELETE CASCADE như file SQL)
            $table->foreign('MaChucVu', 'FK_CHUCVU_QUYEN_CHUCVU')
                ->references('MaChucVu')->on('CHUCVU')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CHUCVU_QUYEN');
    }
};
