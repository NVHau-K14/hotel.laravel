<?php

namespace App\Models;

use DomainException;
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
     * Khôi phục loại phòng đã xóa mềm.
     */
    public function khoiPhuc(): void
    {
        $this->update(['DaXoa' => false]);
    }

    /**
     * Xóa loại phòng: còn phòng dùng loại này thì chỉ đánh dấu DaXoa, không có thì xóa hẳn.
     */
    public function xoaHoacAn(): void
    {
        // Còn đơn chờ duyệt / đã xác nhận / đang ở thì xóa sẽ làm khách không nhận phòng được
        $coDonDangHoatDong = $this->phongs->contains(fn (Phong $phong) => $phong->coDonDangHoatDong());
        if ($coDonDangHoatDong) {
            throw new DomainException('Loại phòng này còn phòng đang có lịch đặt. Hãy xử lý hết các đơn trước khi xóa.');
        }

        if ($this->phongs()->exists()) {
            $this->update(['DaXoa' => true]);

            return;
        }

        $this->delete();
    }
}
