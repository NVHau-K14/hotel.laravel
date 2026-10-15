<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng DỊCH VỤ.
     */
    public function up(): void
    {
        Schema::create('DICHVU', function (Blueprint $table) {
            $table->increments('MaDichVu');
            $table->string('TenDichVu', 50)->nullable();
            $table->integer('GiaDichVu')->nullable();
            $table->string('HinhAnh', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('DICHVU');
    }
};
