<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng THANH TOÁN VIETQR. Có trong file SQL nhưng code PHP gốc không dùng;
     * vẫn tạo để đủ schema.
     */
    public function up(): void
    {
        Schema::create('PAYMENTS', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('booking_id');
            $table->decimal('amount', 12, 2);
            $table->string('method', 30)->nullable()->default('VIETQR');
            $table->string('status', 30)->nullable()->default('CHO_THANH_TOAN');
            $table->text('qr_url')->nullable();
            $table->string('transfer_content', 50)->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('payment_requested_at')->nullable();
            $table->dateTime('expired_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();

            $table->foreign('booking_id', 'FK_PAYMENTS_CTDATPHONG')
                ->references('MaCTDatPhong')->on('CTDATPHONG');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PAYMENTS');
    }
};
