<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bảng PAYMENTS (VietQR). Có trong file SQL nhưng bản gốc chưa dùng; giữ model cho đủ schema.
 */
class Payment extends Model
{
    protected $table = 'PAYMENTS';

    protected $primaryKey = 'id';

    /** Bảng chỉ có created_at (MySQL tự điền), không có updated_at */
    public $timestamps = false;

    protected $fillable = [
        'booking_id',
        'amount',
        'method',
        'status',
        'qr_url',
        'transfer_content',
        'payment_requested_at',
        'expired_at',
        'paid_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'created_at' => 'datetime',
            'payment_requested_at' => 'datetime',
            'expired_at' => 'datetime',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function datPhong(): BelongsTo
    {
        return $this->belongsTo(DatPhong::class, 'booking_id', 'MaCTDatPhong');
    }
}
