<?php

namespace App\Services;

use App\Models\DatPhong;
use App\Models\HoaDon;
use App\Models\KhachHang;
use App\Models\Phong;
use DateTimeImmutable;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Nghiệp vụ đơn đặt phòng (chuyển từ models/DatPhongModel.php và validate_stay của helpers.php).
 * Các thao tác ghi đều chạy trong DB::transaction và khóa dòng (lockForUpdate) để tránh đặt trùng.
 */
class DatPhongService
{
    /**
     * Chuỗi có đúng dạng ngày Y-m-d và là ngày có thật không (valid_date của bản gốc).
     */
    public static function ngayHopLe(?string $ngay): bool
    {
        if ($ngay === null) {
            return false;
        }

        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $ngay);

        return $parsed !== false && $parsed->format('Y-m-d') === $ngay;
    }

    /**
     * Kiểm tra ngày nhận/trả (validate_stay của bản gốc).
     */
    public function validateStay(string $ngayNhan, string $ngayTra, bool $choPhepQuaKhu = false): void
    {
        if (! self::ngayHopLe($ngayNhan) || ! self::ngayHopLe($ngayTra) || $ngayTra <= $ngayNhan) {
            throw new DomainException('Ngày nhận/trả không hợp lệ. Ngày trả phải sau ngày nhận.');
        }

        if (! $choPhepQuaKhu && $ngayNhan < today()->toDateString()) {
            throw new DomainException('Ngày nhận phòng không được nằm trong quá khứ.');
        }
    }

    /**
     * Tạo đơn đặt phòng, lưu giá phòng tại thời điểm đặt. Trả về mã đơn mới.
     */
    public function createBooking(int $maKhachHang, int $maPhong, string $ngayNhan, string $ngayTra, ?int $maNhanVien = null, string $trangThai = DatPhong::CHO_ADMIN_DUYET): int
    {
        $this->validateStay($ngayNhan, $ngayTra);

        if (! in_array($trangThai, DatPhong::TRANG_THAI_GIU_PHONG, true)) {
            throw new DomainException('Trạng thái đặt phòng không hợp lệ.');
        }
        if ($trangThai === DatPhong::DANG_SU_DUNG && $ngayNhan !== today()->toDateString()) {
            throw new DomainException('Chỉ nhận phòng trong ngày bắt đầu lưu trú.');
        }

        return DB::transaction(function () use ($maKhachHang, $maPhong, $ngayNhan, $ngayTra, $maNhanVien, $trangThai) {
            $phong = $this->lockRoom($maPhong);
            $this->assertAvailable($maPhong, $ngayNhan, $ngayTra);

            $khachHang = KhachHang::find($maKhachHang);
            if (! $khachHang || $khachHang->biKhoa()) {
                throw new DomainException('Khách hàng không tồn tại hoặc đã bị khóa.');
            }

            $datPhong = DatPhong::create([
                'NgayThucHien' => today()->toDateString(),
                'MaKhachHang' => $maKhachHang,
                'NgayNhan' => $ngayNhan,
                'NgayTra' => $ngayTra,
                'MaPhong' => $maPhong,
                'MaNhanVien' => $maNhanVien,
                'TrangThai' => $trangThai,
                'payment_status' => DatPhong::THANH_TOAN_CHUA_TAO,
                'GiaPhongDat' => $phong->GiaPhong,
            ]);

            $this->syncRoomStatus($maPhong);

            return $datPhong->MaCTDatPhong;
        });
    }

    /**
     * Khách (đã đăng nhập) đặt phòng trên website. Trả về mã đơn mới.
     * Thông tin liên hệ lấy từ tài khoản, KHÔNG sửa hồ sơ của khách.
     * Có giới hạn số đêm, khoảng đặt trước và số đơn chờ duyệt để chống giữ chỗ ảo.
     */
    public function datPhongTrucTuyen(KhachHang $khachHang, int $maPhong, string $ngayNhan, string $ngayTra): int
    {
        $this->validateStay($ngayNhan, $ngayTra);

        $soDem = (int) round((strtotime($ngayTra) - strtotime($ngayNhan)) / 86400);
        if ($soDem > DatPhong::SO_DEM_TOI_DA) {
            throw new DomainException('Mỗi lần đặt trực tuyến tối đa '.DatPhong::SO_DEM_TOI_DA.' đêm. Vui lòng liên hệ khách sạn để đặt dài ngày.');
        }
        if ($ngayNhan > today()->addDays(DatPhong::SO_NGAY_DAT_TRUOC_TOI_DA)->toDateString()) {
            throw new DomainException('Chỉ nhận đặt phòng trước tối đa '.DatPhong::SO_NGAY_DAT_TRUOC_TOI_DA.' ngày.');
        }

        return DB::transaction(function () use ($khachHang, $maPhong, $ngayNhan, $ngayTra) {
            // Khóa dòng khách để hai lần gửi cùng lúc không vượt giới hạn số đơn chờ duyệt
            $khach = KhachHang::whereKey($khachHang->MaKhachHang)->lockForUpdate()->first();
            if (! $khach || $khach->biKhoa()) {
                throw new DomainException('Khách hàng không tồn tại hoặc đã bị khóa.');
            }

            $soDonCho = DatPhong::where('MaKhachHang', $khach->MaKhachHang)
                ->where('TrangThai', DatPhong::CHO_ADMIN_DUYET)
                ->count();
            if ($soDonCho >= DatPhong::SO_DON_CHO_DUYET_TOI_DA) {
                throw new DomainException('Bạn đang có '.$soDonCho.' đơn chờ duyệt. Vui lòng chờ khách sạn xác nhận trước khi đặt thêm.');
            }

            return $this->createBooking($khach->MaKhachHang, $maPhong, $ngayNhan, $ngayTra);
        });
    }

    /**
     * Dọn đơn quá hạn (chạy mỗi ngày qua lịch, và khi mở dashboard / danh sách đơn / danh sách phòng):
     *  - Đơn chờ duyệt hoặc đã xác nhận nhưng đã qua ngày trả mà khách không nhận phòng → Đã hủy
     *    (hóa đơn chưa thanh toán đi kèm cũng bị hủy).
     *  - Tính lại trạng thái mọi phòng (trạng thái "Đã đặt" phụ thuộc ngày hôm nay).
     * Trả về số đơn đã hủy.
     */
    public function donDepQuaHan(): int
    {
        $homNay = today()->toDateString();

        $soDon = DB::transaction(function () use ($homNay) {
            $maDons = DatPhong::whereIn('TrangThai', [DatPhong::CHO_ADMIN_DUYET, DatPhong::DA_XAC_NHAN])
                ->where('NgayTra', '<=', $homNay)
                ->lockForUpdate()
                ->pluck('MaCTDatPhong');

            if ($maDons->isNotEmpty()) {
                DatPhong::whereIn('MaCTDatPhong', $maDons)->update(['TrangThai' => DatPhong::DA_HUY]);
                HoaDon::whereIn('MaCTDatPhong', $maDons)
                    ->where('TrangThai', HoaDon::CHUA_THANH_TOAN)
                    ->update(['TrangThai' => HoaDon::DA_HUY]);
            }

            return $maDons->count();
        });

        foreach (Phong::pluck('MaPhong') as $maPhong) {
            $this->syncRoomStatus((int) $maPhong);
        }

        return $soDon;
    }

    /**
     * Chuyển trạng thái đơn theo đúng luồng
     * CHO_ADMIN_DUYET → DA_XAC_NHAN → DANG_SU_DUNG → DA_HOAN_THANH (hoặc DA_HUY).
     * $maNhanVien = null thì giữ nguyên nhân viên đang ghi trên đơn.
     */
    public function updateStatus(int $maCTDatPhong, string $trangThaiMoi, ?int $maNhanVien = null): bool
    {
        return DB::transaction(function () use ($maCTDatPhong, $trangThaiMoi, $maNhanVien) {
            $datPhong = $this->lockBooking($maCTDatPhong);
            if ($datPhong->TrangThai === $trangThaiMoi) {
                return true;
            }

            $duocChuyen = DatPhong::LUONG_TRANG_THAI[$datPhong->TrangThai] ?? [];
            if (! in_array($trangThaiMoi, $duocChuyen, true)) {
                throw new DomainException('Không thể chuyển trạng thái của đơn đặt phòng này.');
            }

            $ngayNhan = $datPhong->NgayNhan?->toDateString() ?? '';
            $ngayTra = $datPhong->NgayTra?->toDateString() ?? '';
            $homNay = today()->toDateString();

            if (in_array($trangThaiMoi, [DatPhong::DA_XAC_NHAN, DatPhong::DANG_SU_DUNG], true)) {
                $this->validateStay($ngayNhan, $ngayTra, true);
                if ($ngayTra <= $homNay) {
                    throw new DomainException('Đơn đặt phòng đã quá ngày trả.');
                }
                $this->assertAvailable((int) $datPhong->MaPhong, $ngayNhan, $ngayTra, $maCTDatPhong);
            }

            if ($trangThaiMoi === DatPhong::DANG_SU_DUNG) {
                if ($ngayNhan > $homNay) {
                    throw new DomainException('Chưa đến ngày nhận phòng.');
                }

                $phongConKhach = DatPhong::where('MaPhong', $datPhong->MaPhong)
                    ->where('TrangThai', DatPhong::DANG_SU_DUNG)
                    ->where('MaCTDatPhong', '<>', $maCTDatPhong)
                    ->exists();
                if ($phongConKhach) {
                    throw new DomainException('Phòng còn khách chưa trả phòng.');
                }
            }

            if ($trangThaiMoi === DatPhong::DA_HOAN_THANH
                && ! $datPhong->hoaDons()->where('TrangThai', HoaDon::DA_THANH_TOAN)->exists()) {
                throw new DomainException('Cần thanh toán hóa đơn trước khi hoàn tất đơn.');
            }

            if ($trangThaiMoi === DatPhong::DA_HUY) {
                $datPhong->hoaDons()->where('TrangThai', HoaDon::CHUA_THANH_TOAN)->update(['TrangThai' => HoaDon::DA_HUY]);
            }

            $datPhong->TrangThai = $trangThaiMoi;
            if ($maNhanVien !== null) {
                $datPhong->MaNhanVien = $maNhanVien;
            }
            $datPhong->save();

            $this->syncRoomStatus((int) $datPhong->MaPhong);

            return true;
        });
    }

    /**
     * Khóa dòng phòng (kèm loại phòng) đến hết transaction. Trả về Phong có thêm GiaPhong, DaXoa.
     */
    public function lockRoom(int $maPhong): Phong
    {
        $phong = Phong::query()
            ->join('LOAIPHONG', 'LOAIPHONG.MaLoaiPhong', '=', 'PHONG.MaLoaiPhong')
            ->where('PHONG.MaPhong', $maPhong)
            ->select('PHONG.*', 'LOAIPHONG.GiaPhong', 'LOAIPHONG.DaXoa')
            ->lockForUpdate()
            ->first();

        if (! $phong) {
            throw new DomainException('Phòng không tồn tại.');
        }

        return $phong;
    }

    /**
     * Khóa đơn đặt phòng (khóa phòng trước rồi mới khóa đơn, cùng thứ tự với bản gốc).
     */
    public function lockBooking(int $maCTDatPhong): DatPhong
    {
        $datPhong = DatPhong::find($maCTDatPhong);
        if (! $datPhong) {
            throw new DomainException('Đơn đặt phòng không tồn tại.');
        }

        $this->lockRoom((int) $datPhong->MaPhong);

        return DatPhong::whereKey($maCTDatPhong)->lockForUpdate()->first();
    }

    /**
     * Phòng phải đang hoạt động và không có đơn khác chồng lên khoảng ngày [ngayNhan, ngayTra).
     */
    public function assertAvailable(int $maPhong, string $ngayNhan, string $ngayTra, int $boQuaMaDon = 0): void
    {
        $phong = $this->lockRoom($maPhong);
        if ($phong->TrangThai === Phong::BAO_TRI || $phong->DaXoa) {
            throw new DomainException('Phòng đang bảo trì hoặc loại phòng đã ngừng sử dụng.');
        }

        $donTrung = DatPhong::where('MaPhong', $maPhong)
            ->where('MaCTDatPhong', '<>', $boQuaMaDon)
            ->whereIn('TrangThai', DatPhong::TRANG_THAI_GIU_PHONG)
            ->where('NgayNhan', '<', $ngayTra)
            ->whereRaw(DatPhong::SQL_NGAY_TRA_THUC_TE.' > ?', [today()->addDay()->toDateString(), $ngayNhan])
            ->lockForUpdate()
            ->first(['MaCTDatPhong']);

        if ($donTrung) {
            throw new DomainException('Phòng đã có lịch đặt trong khoảng ngày này. Vui lòng chọn ngày hoặc phòng khác.');
        }
    }

    /**
     * Chỉ được thêm/sửa/xóa dịch vụ khi đơn đã xác nhận hoặc đang ở, và chưa thanh toán.
     */
    public function assertServiceEditable(int $maCTDatPhong): DatPhong
    {
        $datPhong = $this->lockBooking($maCTDatPhong);

        if (! in_array($datPhong->TrangThai, [DatPhong::DA_XAC_NHAN, DatPhong::DANG_SU_DUNG], true)) {
            throw new DomainException('Chỉ thêm hoặc sửa dịch vụ cho đơn đã xác nhận hoặc đang sử dụng.');
        }
        if ($datPhong->hoaDons()->where('TrangThai', HoaDon::DA_THANH_TOAN)->exists()) {
            throw new DomainException('Không được sửa dịch vụ của hóa đơn đã thanh toán.');
        }

        return $datPhong;
    }

    /**
     * Tính lại trạng thái phòng từ các đơn (không đụng tới phòng đang "Bảo trì"):
     * có khách đang ở → "Đang thuê"; có đơn đã xác nhận đến ngày nhận (khách sắp/đang đến) → "Đã đặt";
     * còn lại → "Trống". Đơn cho những ngày sau KHÔNG làm phòng thành "Đã đặt".
     */
    public function syncRoomStatus(int $maPhong): void
    {
        $homNay = today()->toDateString();
        $donCuaPhong = fn () => DatPhong::where('MaPhong', $maPhong);

        $trangThai = match (true) {
            $donCuaPhong()->where('TrangThai', DatPhong::DANG_SU_DUNG)->exists() => Phong::DANG_THUE,
            $donCuaPhong()->where('TrangThai', DatPhong::DA_XAC_NHAN)
                ->where('NgayNhan', '<=', $homNay)
                ->where('NgayTra', '>', $homNay)->exists() => Phong::DA_DAT,
            default => Phong::TRONG,
        };

        Phong::whereKey($maPhong)
            ->where(fn (Builder $q) => $q->whereNull('TrangThai')->orWhere('TrangThai', '<>', Phong::BAO_TRI))
            ->update(['TrangThai' => $trangThai]);
    }
}
