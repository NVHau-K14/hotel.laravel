<?php

namespace App\Services;

use App\Models\DatPhong;
use App\Models\HoaDon;
use App\Models\KhachHang;
use App\Models\NhanVien;
use App\Models\Phong;
use Illuminate\Database\Eloquent\Builder;

/**
 * Số liệu cho Dashboard và trang Thống kê (chuyển từ models/ThongKeModel.php).
 * Doanh thu chỉ tính hóa đơn đã thanh toán.
 */
class ThongKeService
{
    /**
     * @return array<string, int>
     */
    public function getSummary(): array
    {
        $daThanhToan = fn () => HoaDon::where('TrangThai', HoaDon::DA_THANH_TOAN);
        $homNay = now();

        return [
            'tongDoanhThu' => (int) $daThanhToan()->sum('SoTienThu'),
            'doanhThuThangNay' => (int) $daThanhToan()
                ->whereMonth('NgayThuTien', $homNay->month)
                ->whereYear('NgayThuTien', $homNay->year)
                ->sum('SoTienThu'),
            'tongPhong' => Phong::count(),
            'phongTrong' => Phong::where(fn (Builder $q) => $q->where('TrangThai', Phong::TRONG)->orWhereNull('TrangThai'))->count(),
            'phongDangThue' => Phong::whereIn('TrangThai', [Phong::DANG_THUE, Phong::DANG_SU_DUNG, Phong::DA_DAT])->count(),
            'phongBaoTri' => Phong::where('TrangThai', Phong::BAO_TRI)->count(),
            'donChoDuyet' => DatPhong::where('TrangThai', DatPhong::CHO_ADMIN_DUYET)->count(),
            'tongKhachHang' => KhachHang::count(),
            'tongNhanVien' => NhanVien::count(),
        ];
    }

    /**
     * Doanh thu 12 tháng của một năm, khóa 1..12.
     *
     * @return array<int, int>
     */
    public function getMonthlyRevenue(int $nam): array
    {
        $theoThang = HoaDon::query()
            ->selectRaw('MONTH(NgayThuTien) AS thang, COALESCE(SUM(SoTienThu), 0) AS doanhThu')
            ->where('TrangThai', HoaDon::DA_THANH_TOAN)
            ->whereYear('NgayThuTien', $nam)
            ->groupByRaw('MONTH(NgayThuTien)')
            ->pluck('doanhThu', 'thang');

        $ketQua = array_fill(1, 12, 0);
        foreach ($theoThang as $thang => $doanhThu) {
            $ketQua[(int) $thang] = (int) $doanhThu;
        }

        return $ketQua;
    }
}
