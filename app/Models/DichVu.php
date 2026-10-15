<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Bảng DICHVU.
 */
class DichVu extends Model
{
    protected $table = 'DICHVU';

    protected $primaryKey = 'MaDichVu';

    public $timestamps = false;

    protected $fillable = ['TenDichVu', 'GiaDichVu', 'HinhAnh'];

    protected function casts(): array
    {
        return [
            'GiaDichVu' => 'integer',
        ];
    }

    public function ctDichVus(): HasMany
    {
        return $this->hasMany(CtDichVu::class, 'MaDichVu', 'MaDichVu');
    }

    public function datPhongs(): BelongsToMany
    {
        return $this->belongsToMany(DatPhong::class, 'CTDICHVU', 'MaDichVu', 'MaCTDatPhong')
            ->withPivot('MaCTDichVu', 'SoLuong', 'TongTienDichVu');
    }
}
