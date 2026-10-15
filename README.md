# Quản lý Khách sạn — Laravel

Website quản lý khách sạn gồm **trang khách hàng** (xem phòng, đặt phòng, đánh giá, tài khoản) và **trang quản trị** (phòng, đặt phòng, dịch vụ, hóa đơn, nhân viên, phân quyền, thống kê).

Dự án được chuyển từ bản **PHP thuần MVC tự viết** (`hotel_php`) sang **Laravel**, giữ nguyên chức năng, giao diện và quy tắc nghiệp vụ. Các phần môn học yêu cầu:

| Yêu cầu | Dùng ở đâu |
|---|---|
| **Migration** | `database/migrations/` — 13 bảng giữ đúng tên bảng/cột tiếng Việt của file SQL gốc |
| **Eloquent** | `app/Models/` — 12 model, quan hệ `belongsTo` / `hasMany` / `belongsToMany`, scope, hằng số trạng thái |
| **Blade** | `resources/views/` — 2 layout (`admin`, `client`), `@extends`, `@section`, `@include`, `@csrf`, `@method` |
| **Form Request** | `app/Http/Requests/` — 18 lớp kiểm tra dữ liệu, thông báo lỗi tiếng Việt qua `messages()` |

---

## 1. Yêu cầu môi trường

- **PHP 8.3** trở lên (đã chạy với 8.3.30), Composer 2
- **MySQL 8** (đã chạy với 8.4.3), cổng 3306
- Laragon (Apache) trên Windows, hoặc bất kỳ máy chủ nào chạy được `php artisan serve`
- **Không cần Node/npm**: Bootstrap 5.3.3, Font Awesome và Chart.js tải qua CDN, CSS riêng ở `public/css/app.css`

## 2. Cài đặt

```bash
# 1. Cài thư viện PHP
composer install

# 2. Tạo file cấu hình rồi sinh khóa ứng dụng
copy .env.example .env          # Windows (macOS/Linux: cp .env.example .env)
php artisan key:generate
```

3. Tạo database trong MySQL (HeidiSQL hoặc dòng lệnh):

```sql
CREATE DATABASE hotel_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

`.env.example` đã cấu hình sẵn: `DB_DATABASE=hotel_laravel`, user `root`, mật khẩu rỗng. Sửa lại nếu máy bạn khác.

4. Dựng bảng và nạp dữ liệu mẫu:

```bash
php artisan migrate:fresh --seed
```

Lệnh này **xóa sạch** database rồi dựng lại từ đầu. Chạy lại bất cứ lúc nào để trả về dữ liệu mẫu.

## 3. Chạy website

- **Laragon:** đặt thư mục ở `C:\laragon\www\hotel.laravel`, bật Apache + MySQL, mở <http://hotel.laravel.test>.
- **Cách khác:** `php artisan serve` rồi mở <http://127.0.0.1:8000> (nhớ sửa `APP_URL` trong `.env`).

| Trang | Đường dẫn |
|---|---|
| Trang khách hàng | `/` |
| Đăng nhập khách hàng | `/tai-khoan/dang-nhap` |
| Đăng nhập quản trị | `/admin/dang-nhap` |

Ảnh tải lên được lưu ở `public/uploads/phong/` và `public/uploads/dichvu/`. Không cần chạy `storage:link`.

## 4. Tài khoản mẫu (mật khẩu đều là `1`)

**Quản trị**

| Tên đăng nhập | Họ tên | Chức vụ | Quyền |
|---|---|---|---|
| `viet` | Nguyễn Quốc Việt | Quản lý | Toàn quyền |
| `binh` | Ngô Quốc Bình | Lễ tân | Dashboard, Đặt phòng, Chi tiết DV, Dịch vụ, Hóa đơn, Khách hàng |
| `vung` | Hoàng Trọng Vững | Bảo vệ | Chỉ Dashboard |
| `van` | Bùi Thị Vấn | Tạp vụ | Dashboard, Phân công |

**Khách hàng:** `teo@gmail.com` (hoặc số điện thoại `0912345678`). Các khách mẫu khác: `ti@gmail.com`, `vana@gmail.com`, `thib@gmail.com`, `minhc@gmail.com`.

Quản lý có thể đổi quyền của từng chức vụ ở trang **Chức vụ**.

## 5. Chạy test

Test chạy trên một **database MySQL riêng** để không đụng dữ liệu đang dùng:

```sql
CREATE DATABASE hotel_laravel_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
php artisan config:clear   # nếu từng chạy config:cache
php artisan test
```

`phpunit.xml` trỏ tới `hotel_laravel_test`. `tests/TestCase.php` tự dừng nếu phát hiện đang trỏ tới database khác. Bộ test gồm:

- `tests/Feature/KichBanNghiemThuTest.php` — kịch bản nghiệm thu:
  1. khách `teo` đặt **phòng 104** (300.000 đ/đêm, 2 đêm);
  2. admin duyệt, rồi nhận phòng;
  3. thêm Ăn sáng × 2 và Massage × 1;
  4. trả phòng → hóa đơn **1.260.000 đ**;
  5. thanh toán → đơn hoàn tất;
  6. đặt trùng ngày → bị chặn.
- `tests/Feature/PhanQuyenTest.php` — `vung` vào `/admin/phong` bị **403**, menu theo quyền, chuyển hướng khi chưa đăng nhập.
- `tests/Feature/DatPhongKhachTest.php` — kiểm tra dữ liệu và quy tắc khi khách đặt phòng, khách vãng lai.
- `tests/Unit/HelperTest.php` — định dạng tiền, ngày.

## 6. Cấu trúc thư mục chính

```
app/
  Http/
    Controllers/Admin/    14 controller trang quản trị
    Controllers/Client/   6 controller trang khách hàng
    Middleware/           KiemTraQuyen (alias "quyen"), DangXuatKhachBiKhoa
    Requests/Admin/       Form Request trang quản trị
    Requests/Client/      Form Request trang khách hàng
  Models/                 12 model Eloquent (tên bảng, cột giữ nguyên tiếng Việt)
  Services/               Nghiệp vụ nhiều bước: đặt phòng, hóa đơn, dịch vụ, thống kê
  helpers.php             format_currency(), format_date()
bootstrap/app.php         Đăng ký route admin, middleware, chuyển hướng khi chưa đăng nhập, xử lý lỗi tập trung
config/auth.php           2 guard: admin (NhanVien) và web (KhachHang)
database/migrations/      13 bảng nghiệp vụ + sessions, cache, jobs
database/seeders/         Dữ liệu mẫu y hệt file SQL gốc (mật khẩu đã băm bcrypt)
resources/views/
  admin/                  Layout + view từng module quản trị
  client/                 Layout + view trang khách hàng
  errors/                 Trang lỗi tiếng Việt (403, 404, 405, 419, 422)
routes/
  web.php                 Route trang khách hàng
  admin.php               Route trang quản trị (tiền tố /admin, tên admin.*)
public/                   css/app.css, Images/ (ảnh mẫu), uploads/ (ảnh tải lên)
tests/                    Feature test + Unit test
docs/                     Kế hoạch chuyển đổi và CHECKLIST tiến độ
```

## 7. Bảng ánh xạ: file gốc (`hotel_php`) → Laravel

### Hạ tầng

| File gốc | Laravel |
|---|---|
| `index.php`, `admin/index.php` (router `?c=...&a=...`) | `routes/web.php`, `routes/admin.php` (named route, URL tiếng Việt không dấu) |
| `config/database.php` (PDO, `db_transaction`) | `.env`, `config/database.php` (timezone `+07:00`), `DB::transaction()` + `lockForUpdate()` |
| `config/auth.php` (class `Auth`) | `config/auth.php` (guard `admin`, `web`), `NhanVien::coQuyen()`, middleware `quyen`, `DangXuatKhachBiKhoa` |
| `config/helpers.php` | `app/helpers.php`; kiểm tra dữ liệu → Form Request; `handle_request_error` → `withExceptions()` trong `bootstrap/app.php`; CSRF → `@csrf`; flash → `->thongBao()` |
| `QuanLyKhachSan.sql` | `database/migrations/*`, `database/seeders/*` |
| `css/app.css`, `Images/` | `public/css/app.css`, `public/Images/` |

### Model

| File gốc | Laravel |
|---|---|
| `models/PhongModel.php` | `app/Models/Phong.php` |
| `models/LoaiPhongModel.php` | `app/Models/LoaiPhong.php` |
| `models/DatPhongModel.php` | `app/Models/DatPhong.php` + `app/Services/DatPhongService.php` |
| `models/HoaDonModel.php` | `app/Models/HoaDon.php` + `app/Services/HoaDonService.php` |
| `models/CtDichVuModel.php` | `app/Models/CtDichVu.php` + `app/Services/CtDichVuService.php` |
| `models/DichVuModel.php` | `app/Models/DichVu.php` |
| `models/KhachHangModel.php` | `app/Models/KhachHang.php` |
| `models/NhanVienModel.php` | `app/Models/NhanVien.php`, `app/Models/ChucVu.php` |
| `models/PhanCongModel.php` | `app/Models/PhanCong.php` |
| `models/DanhGiaModel.php` | `app/Models/DanhGia.php` |
| `models/ThongKeModel.php` | `app/Services/ThongKeService.php` |
| (bảng `PAYMENTS`, code gốc không dùng) | `app/Models/Payment.php` |

### Controller

Controller quản trị giữ **đúng tên** như bản gốc: `controllers/admin/XxxController.php` → `app/Http/Controllers/Admin/XxxController.php`. Có 14 file: Auth, ChucVu, CtDichVu, DanhGia, Dashboard, DatPhong, DichVu, HoaDon, KhachHang, LoaiPhong, NhanVien, PhanCong, Phong, ThongKe.

Controller khách hàng: `controllers/client/XxxController.php` → `app/Http/Controllers/Client/XxxController.php`. Có 6 file: Account, Booking, Home, Review, Room, Service.

### View

| View gốc (`views/...`) | Blade (`resources/views/...`) |
|---|---|
| `admin/layouts/header.php` + `nav.php` + `footer.php` | `admin/layouts/app.blade.php` + `nav.blade.php` |
| `admin/<module>/list.php` | `admin/<module>/index.blade.php` |
| `admin/<module>/form.php` | `admin/<module>/form.blade.php` (đặt phòng, chi tiết DV: `create.blade.php`) |
| `admin/<module>/detail.php` | `admin/<module>/show.blade.php` |
| `admin/khach-hang/history.php`, `admin/ct-dich-vu/them-vao-don.php` | cùng tên, đuôi `.blade.php` |
| `admin/dashboard/index.php`, `admin/thong-ke/index.php`, `admin/auth/*.php` | cùng tên, đuôi `.blade.php` |
| `client/layouts/header.php` + `footer.php` | `client/layouts/app.blade.php` |
| `client/*.php`, `client/account/*.php` | cùng tên, đuôi `.blade.php` |
| (HTML 403 / lỗi CSRF trong code) | `errors/403.blade.php`, `errors/419.blade.php` |

Bảng route đầy đủ (gốc `c`/`a` → method, URL, tên route, controller, quyền) nằm ở `docs/CHECKLIST.md`, mục 1.

## 8. Ghi chú kỹ thuật

- **Giữ nguyên tên bảng/cột** của file SQL (`PHONG.MaPhong`, `CTDATPHONG`…). Model khai báo `$table`, `$primaryKey`, `$timestamps = false`. `PHONG.MaPhong` do người dùng nhập nên `$incrementing = false`. `KHACHHANG.EmailConnexion` là cột MySQL tự sinh, không bao giờ ghi vào.
- **Đăng nhập 2 phía** dùng chung một session nhưng tách guard. Đăng xuất phía nào chỉ đăng xuất phía đó. Khách đăng nhập bằng email **hoặc** số điện thoại. Khách bị khóa sẽ tự đăng xuất ở lần tải trang kế tiếp.
- **Quy tắc nghiệp vụ:** bản gốc có 53 chỗ `DomainException`, tất cả đã chuyển sang Form Request, model hoặc service, giữ nguyên câu thông báo. Danh sách từng quy tắc ở `docs/CHECKLIST.md`, mục 2.
- **Chống đặt trùng:** đặt phòng, thêm dịch vụ và thanh toán chạy trong `DB::transaction` và khóa dòng (`lockForUpdate`).
- **Hóa đơn đã thanh toán** lưu bản chụp `ChiTietJSON`, nên đổi giá dịch vụ sau đó không làm thay đổi hóa đơn cũ.
- **Lỗi của bản gốc đã sửa khi chuyển:**
  - Form phòng có trạng thái "Đang sử dụng" không lưu được → bỏ khỏi form.
  - Trang chi tiết đơn của khách hiện mã trạng thái thô `DA_HOAN_THANH` → hiện "Hoàn tất".
  - Bảng lịch sử thiếu `colspan`.
  - Danh sách đầy đủ ở `docs/CHECKLIST.md`, mục 5.
