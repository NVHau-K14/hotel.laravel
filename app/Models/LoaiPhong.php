<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Bảng LOAIPHONG. DaXoa là cờ xóa mềm tự viết (không dùng trait SoftDeletes).
 */
class LoaiPhong extends Model
{
    protected $table = 'LOAIPHONG';

    protected $primaryKey = 'MaLoaiPhong';

    public $timestamps = false;

    protected $fillable = ['TenLoaiPhong', 'GiaPhong', 'MoTa', 'DaXoa'];

    protected function casts(): array
    {
        return [
            'GiaPhong' => 'integer',
            'DaXoa' => 'boolean',
        ];
    }

    public function phongs(): HasMany
    {
        return $this->hasMany(Phong::class, 'MaLoaiPhong', 'MaLoaiPhong');
    }

    /**
     * Chỉ lấy loại phòng chưa bị xóa mềm.
     */
    public function scopeConHoatDong(Builder $query): void
    {
        $query->where(fn (Builder $q) => $q->where('DaXoa', false)->orWhereNull('DaXoa'));
    }

    /**
     * Xóa loại phòng: còn phòng dùng loại này thì chỉ đánh dấu DaXoa, không có thì xóa hẳn.
     */
    public function xoaHoacAn(): void
    {
        if ($this->phongs()->exists()) {
            $this->update(['DaXoa' => true]);

            return;
        }

        $this->delete();
    }
}
