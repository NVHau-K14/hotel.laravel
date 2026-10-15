<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bảng BANGPHANCONG — phân công công việc cho nhân viên.
 */
class PhanCong extends Model
{
    protected $table = 'BANGPHANCONG';

    protected $primaryKey = 'MaPhanCong';

    public $timestamps = false;

    protected $fillable = ['NgayPhanCong', 'LoaiCongViec', 'MaNhanVien'];

    protected function casts(): array
    {
        return [
            'NgayPhanCong' => 'date',
        ];
    }

    public function nhanVien(): BelongsTo
    {
        return $this->belongsTo(NhanVien::class, 'MaNhanVien', 'MaNhanVien');
    }

    /**
     * Lọc theo từ khóa loại công việc và ngày, mới nhất trước (PhanCongModel::getAll).
     */
    public function scopeLoc(Builder $query, ?string $keyword = null, ?string $ngay = null): void
    {
        if (! empty($keyword)) {
            $query->where('LoaiCongViec', 'like', '%'.trim($keyword).'%');
        }

        if (! empty($ngay)) {
            $query->where('NgayPhanCong', trim($ngay));
        }

        $query->orderByDesc('NgayPhanCong')->orderByDesc('MaPhanCong');
    }
}
