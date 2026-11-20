<?php

namespace App\Services;

use GdImage;
use Illuminate\Support\Facades\File;

/**
 * Ảnh thu nhỏ WebP cho ảnh phòng/dịch vụ (ảnh gốc rộng tới 6000px, nặng 1–2 MB).
 * Ảnh nhỏ nằm ở public/anh-nho/{rộng}/{đường dẫn gốc}.webp; view dùng qua helper anh_url() / anh_srcset().
 * Chưa tạo ảnh nhỏ thì view tự dùng ảnh gốc, nên trang không bao giờ mất ảnh.
 */
class AnhThuNhoService
{
    /** Các bề rộng tạo sẵn: 640 cho thẻ/ảnh nhỏ, 1600 cho ảnh lớn */
    public const BE_RONG = [640, 1600];

    public const THU_MUC = 'anh-nho';

    private const DUOI_HO_TRO = ['jpg', 'jpeg', 'png', 'webp'];

    private const CHAT_LUONG_WEBP = 78;

    /**
     * Đường dẫn tương đối (trong public) của ảnh gốc, hoặc null nếu không phải ảnh nội bộ hợp lệ.
     */
    public function duongDanGoc(?string $anh): ?string
    {
        if (empty($anh) || str_contains($anh, '..') || preg_match('#^[a-z]+://#i', $anh)) {
            return null;
        }

        $duongDan = ltrim($anh, '/');
        $duoi = strtolower(pathinfo($duongDan, PATHINFO_EXTENSION));

        return in_array($duoi, self::DUOI_HO_TRO, true) ? $duongDan : null;
    }

    /**
     * Đường dẫn tương đối (trong public) của ảnh nhỏ bề rộng $beRong.
     */
    public function duongDanThuNho(string $duongDanGoc, int $beRong): string
    {
        $khongDuoi = preg_replace('/\.[^.\/]+$/', '', $duongDanGoc);

        return self::THU_MUC.'/'.$beRong.'/'.$khongDuoi.'.webp';
    }

    /**
     * Các ảnh nhỏ đã có sẵn trên đĩa, dạng [bề rộng => đường dẫn tương đối].
     *
     * @return array<int, string>
     */
    public function anhCoSan(?string $anh): array
    {
        $duongDanGoc = $this->duongDanGoc($anh);
        if ($duongDanGoc === null) {
            return [];
        }

        $ketQua = [];
        foreach (self::BE_RONG as $beRong) {
            $thuNho = $this->duongDanThuNho($duongDanGoc, $beRong);
            if (is_file(public_path($thuNho))) {
                $ketQua[$beRong] = $thuNho;
            }
        }

        return $ketQua;
    }

    /**
     * Tạo các ảnh nhỏ còn thiếu cho một ảnh. Trả về số ảnh đã tạo mới.
     * Không phóng to: bề rộng lớn hơn ảnh gốc thì bỏ qua.
     */
    public function tao(?string $anh): int
    {
        $duongDanGoc = $this->duongDanGoc($anh);
        if ($duongDanGoc === null || ! is_file(public_path($duongDanGoc))) {
            return 0;
        }

        $thieu = array_filter(
            self::BE_RONG,
            fn (int $beRong) => ! is_file(public_path($this->duongDanThuNho($duongDanGoc, $beRong)))
        );
        if ($thieu === []) {
            return 0;
        }

        $anhGoc = $this->mo(public_path($duongDanGoc));
        if ($anhGoc === null) {
            return 0;
        }

        $soAnh = 0;
        foreach ($thieu as $beRong) {
            if ($beRong >= imagesx($anhGoc)) {
                continue;
            }

            $anhNho = imagescale($anhGoc, $beRong, -1, IMG_BICUBIC);
            if ($anhNho === false) {
                continue;
            }
            imagesavealpha($anhNho, true);

            $dich = public_path($this->duongDanThuNho($duongDanGoc, $beRong));
            File::ensureDirectoryExists(dirname($dich));
            if (imagewebp($anhNho, $dich, self::CHAT_LUONG_WEBP)) {
                $soAnh++;
            }
            imagedestroy($anhNho);
        }
        imagedestroy($anhGoc);

        return $soAnh;
    }

    /**
     * Tạo ảnh nhỏ cho mọi ảnh trong các thư mục public/Images và public/uploads.
     *
     * @return array{anh: int, tao: int}
     */
    public function taoTatCa(): array
    {
        $soAnhGoc = 0;
        $soAnhTao = 0;
        foreach (['Images', 'uploads'] as $thuMuc) {
            if (! File::isDirectory(public_path($thuMuc))) {
                continue;
            }
            foreach (File::allFiles(public_path($thuMuc)) as $file) {
                $tuongDoi = $thuMuc.'/'.str_replace('\\', '/', $file->getRelativePathname());
                if ($this->duongDanGoc($tuongDoi) === null) {
                    continue;
                }
                $soAnhGoc++;
                $soAnhTao += $this->tao($tuongDoi);
            }
        }

        return ['anh' => $soAnhGoc, 'tao' => $soAnhTao];
    }

    private function mo(string $duongDan): ?GdImage
    {
        // Ảnh gốc 6000x4000 cần khoảng 100 MB bộ nhớ khi giải nén
        @ini_set('memory_limit', '512M');

        $anh = match (strtolower(pathinfo($duongDan, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($duongDan),
            'png' => @imagecreatefrompng($duongDan),
            'webp' => @imagecreatefromwebp($duongDan),
            default => false,
        };

        if (! $anh instanceof GdImage) {
            return null;
        }

        // Giữ nền trong suốt của PNG khi thu nhỏ
        imagealphablending($anh, false);
        imagesavealpha($anh, true);

        return $anh;
    }
}
