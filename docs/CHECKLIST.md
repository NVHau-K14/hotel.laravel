# CHECKLIST CHUYỂN `hotel_php` → LARAVEL

> Đọc cùng `docs/KE_HOACH_CHUYEN_DOI.md`. File này ghi tiến độ thật, cập nhật sau mỗi giai đoạn.

## 0. Tiến độ

| Giai đoạn | Nội dung | Trạng thái |
|---|---|---|
| 0 | Khảo sát, lập checklist | ✅ Xong (2026-10-15) |
| 1 | Cấu hình, Migration, Seeder, tài nguyên tĩnh | ✅ Xong (2026-10-15) |
| 2 | Model Eloquent | ✅ Xong (2026-10-15) |
| 3 | Auth, phân quyền, layout, helper, xử lý lỗi | ✅ Xong (2026-10-15) |
| 4A | LoaiPhong, Phong, DichVu | ✅ Xong (2026-10-15) |
| 4B | KhachHang, NhanVien, ChucVu | ✅ Xong (2026-10-15) |
| 4C | PhanCong, DanhGia | ✅ Xong (2026-10-15) |
| 4D | DatPhong, CtDichVu, HoaDon | ✅ Xong (2026-10-15) |
| 4E | Dashboard, ThongKe | ⬜ |
| 5 | Phía khách hàng | ⬜ |
| 6 | Kiểm tra toàn bộ + Feature test | ⬜ |
| 7 | Dọn dẹp, README | ⬜ |

### Ghi chú khảo sát (Giai đoạn 0)
- Môi trường đã kiểm tra: PHP 8.3.30, Composer 2.9.4, Laravel 13.35.0. Laravel 13 hỗ trợ `protected $authPasswordName` (đã xem `vendor/.../Auth/Authenticatable.php`).
- Project Laravel ban đầu: skeleton mặc định (`User`, `UserFactory`, migration users/cache/jobs, `welcome.blade.php`, `.env` dùng SQLite, `APP_LOCALE=en`).
- `CLAUDE.md` / `AGENTS.md` yêu cầu cài **Laravel Boost** → **không cài** (chủ dự án quyết định 2026-10-15: bỏ qua phần yêu cầu cài Boost trong `CLAUDE.md`/`AGENTS.md`).
- File `structure_hotel_laravel.md` (~958 KB, cây thư mục gồm cả `vendor/`) nằm ở gốc project → **xóa ở Giai đoạn 7** (đã duyệt).
- `hotel_php/css/app.css` tự `@import` Bootstrap 5.3.3, Font Awesome 6.5.2 và Google Font Be Vietnam Pro → layout khách hàng không cần thẻ `<link>` Bootstrap riêng (bản gốc cũng không có).
- `hotel_php/Images/` có thêm `Phong106`, `Phong206`, `DichVu/DichVu6` không có trong SQL → vẫn copy đủ.

### Kết quả Giai đoạn 1
- [x] `.env` / `.env.example`: MySQL `hotel_laravel` (root, mật khẩu rỗng), `APP_LOCALE=vi`, `APP_TIMEZONE=Asia/Ho_Chi_Minh`, `APP_URL=http://hotel.laravel.test`, `APP_FAKER_LOCALE=vi_VN`.
- [x] `config/app.php`: `timezone` đọc `APP_TIMEZONE`. `config/database.php` (mysql): `timezone => '+07:00'`, `isolation_level => 'READ COMMITTED'` (giống `hotel_php/config/database.php`). Kiểm tra bằng tinker: `NOW()` theo giờ VN, `@@session.time_zone = +07:00`, `@@transaction_isolation = READ-COMMITTED`.
- [x] Database `hotel_laravel` đã có sẵn (rỗng, collation `utf8mb4_0900_ai_ci`) → đổi sang `utf8mb4_unicode_ci`.
- [x] Migration mặc định: đổi tên `create_users_table` → `create_sessions_table`, chỉ còn `sessions`; giữ `cache`, `jobs`. Bỏ `users`, `password_reset_tokens`.
- [x] 13 migration (`2026_10_15_000001` … `000013`), mỗi bảng một file, có `down()`.
- [x] 10 seeder cho 10 bảng có dữ liệu + `DatabaseSeeder` gọi theo thứ tự khóa ngoại. `BANGPHANCONG`, `PAYMENTS`, `CTDICHVU` không có dữ liệu mẫu nên không có seeder. Mật khẩu `Hash::make('1')`.
- [x] Đã xóa `database/database.sqlite`.
- [x] Copy `css/app.css` → `public/css/app.css` (trùng hash), `Images/` → `public/Images/` (72 file, đủ dung lượng). Tạo `public/uploads/{phong,dichvu}/.gitkeep`; `.gitignore` bỏ qua file upload lúc chạy.
- **Kiểm tra (kết quả thật):**
  - `php artisan migrate:fresh --seed` chạy không lỗi, **chạy 2 lần liên tiếp đều thành công**; `migrate:status` = 16 migration đã chạy.
  - Số dòng: CHUCVU 4, NHANVIEN 4, CHUCVU_QUYEN 22, LOAIPHONG 3, PHONG 15, DICHVU 5, KHACHHANG 5, CTDATPHONG 5, CTDANHGIA 2, HOADON 5, BANGPHANCONG 0, PAYMENTS 0, CTDICHVU 0 → **khớp**.
  - Nạp bản sao `QuanLyKhachSan.sql` vào DB tạm `hotel_sql_goc` (đã xóa sau khi so) để so với `hotel_laravel`:
    - Cột: giống hệt kiểu, độ dài, null, mặc định, generated; khác duy nhất là cột khóa `int` → `int unsigned` (theo kế hoạch: `increments` + `unsignedInteger`).
    - 16 khóa ngoại trùng tên, bảng tham chiếu và luật xóa (chỉ `FK_CHUCVU_QUYEN_CHUCVU` là CASCADE). Khóa chính, `uq_staff_username`, `uq_customer_phone`, `uq_customer_email` trùng.
    - Dữ liệu 10 bảng (trừ cột mật khẩu): `EXCEPT` hai chiều đều 0 dòng lệch.
  - `EmailConnexion`: thêm 2 khách `Email=''` → được (cột sinh = NULL); thêm khách trùng `teo@gmail.com` → lỗi 1062 `uq_customer_email`; ghi thẳng vào `EmailConnexion` → lỗi 3105. Thử trong transaction rồi rollback, bảng vẫn 5 khách.
  - `Hash::check('1', …)` đúng cho `viet` và `teo@gmail.com`.
  - `http://hotel.laravel.test/css/app.css`, `/Images/ChiTietPhong/Phong101/01.jpg`, `/Images/DichVu/Massage/service.jpg` → 200; trang `/` → 200.
- Ghi chú: MySQL trên Windows có `lower_case_table_names = 1` → HeidiSQL hiện tên bảng chữ thường (`khachhang`), truy vấn không phân biệt hoa thường; code vẫn dùng tên viết hoa như SQL gốc.
- Chưa làm (đúng kế hoạch, để Giai đoạn 3): xóa `app/Models/User.php`, `UserFactory`, sửa `config/auth.php`.

### Kết quả Giai đoạn 2
- [x] 12 model trong `app/Models`: `ChucVu`, `NhanVien` (Authenticatable, `$authPasswordName = "Password"`), `PhanCong` (BANGPHANCONG), `KhachHang` (Authenticatable, `MatKhau`), `LoaiPhong`, `Phong` (`$incrementing = false`), `DatPhong` (CTDATPHONG), `DichVu`, `CtDichVu`, `HoaDon`, `DanhGia` (CTDANHGIA, khóa `ID`), `Payment`. Đủ `$table`, `$primaryKey`, `$timestamps = false`, `$fillable`, `casts()`. `NhanVien`/`KhachHang` ẩn mật khẩu (`$hidden`), `$rememberTokenName = ""` (không dùng remember me). `KhachHang` không có `EmailConnexion` trong `$fillable`. `LoaiPhong.DaXoa` cast boolean, không dùng `SoftDeletes`.
- [x] Hằng số trạng thái: `DatPhong::CHO_ADMIN_DUYET…DA_HUY`, `LUONG_TRANG_THAI`, `TRANG_THAI_GIU_PHONG`; `HoaDon::CHUA_THANH_TOAN/DA_THANH_TOAN`; `KhachHang::HOAT_DONG/KHOA`; `Phong::TRONG/DA_DAT/DANG_THUE/BAO_TRI` (+ `DANG_SU_DUNG` chỉ để đếm); `ChucVu::QUAN_LY`, `ChucVu::QUYEN` (13 quyền).
- [x] Quan hệ: belongsTo / hasMany theo đủ 16 khóa ngoại; `DatPhong::dichVus()` ↔ `DichVu::datPhongs()` là `belongsToMany` qua `CTDICHVU` kèm `withPivot("MaCTDichVu","SoLuong","TongTienDichVu")`. `CHUCVU_QUYEN` đọc/ghi bằng query builder (`ChucVu::dsQuyen()`, `capNhatQuyen()` trong transaction).
- [x] `CtDichVu`: `$primaryKey = null`, cập nhật/xóa bằng `capNhatTheoKhoa()` / `xoaTheoKhoa()` (where hai cột).
- [x] `HoaDon.ChiTietJSON` cast `json:unicode` (giữ chữ có dấu như `JSON_UNESCAPED_UNICODE`). `DatPhong->GiaPhong` (accessor) = `GiaPhongDat ?? loaiPhong.GiaPhong`.
- [x] Truy vấn gốc → scope/method: `Phong::loc()`, `Phong::conTrong()`, `Phong::demTheoTrangThai()`, `LoaiPhong::conHoatDong()`, `LoaiPhong::xoaHoacAn()`, `DatPhong::loc()`, `DatPhong::demTheoTrangThai()`, `KhachHang::timKiem()`, `PhanCong::loc()`, `HoaDon::layTheoDon()`, `CtDichVu::kemDonGia()/cuaDon()/danhSach()/timTheoKhoa()`, `DanhGia::phongCoTheDanhGia()/donCoTheDanhGia()`, `NhanVien::coQuyen()`.
- [x] `app/Services`: `DatPhongService` (validateStay, createBooking, updateStatus, lockRoom, lockBooking, assertAvailable, assertServiceEditable, syncRoomStatus), `HoaDonService` (calculateBookingTotal, checkout, createOrUpdateInvoice, payInvoice), `CtDichVuService` (save, addToBooking, delete), `ThongKeService` (getSummary, getMonthlyRevenue). Dùng `DB::transaction` + `lockForUpdate()` đúng các chỗ bản gốc dùng `db_transaction` + `FOR UPDATE`.
- Không chuyển (bản gốc không nơi nào gọi): `KhachHangModel::updatePassword`, `KhachHangModel::getByEmail`, `PhongModel::updateStatus`. Tham số `$tongTien` của `CtDichVuModel::save` không được dùng trong bản gốc → bỏ.
- **Kiểm tra (kết quả thật, script chạy qua bootstrap Laravel; phần nghiệp vụ trong transaction rồi ROLLBACK):**
  - Đơn #2 → khách "Lê Văn Tí", phòng 102 / "Bình Dân", giá 300.000 (GiaPhongDat NULL → giá loại), nhân viên "Ngô Quốc Bình", hóa đơn #1, #2, 1 đánh giá; hóa đơn chính = #2.
  - `coQuyen`: viet (Quản lý) có THONG_KE; binh có HOA_DON, không có PHONG; vung có DASHBOARD, không có PHONG.
  - Phòng trống 07→09/05/2026: loại đúng 102, 103, 201. Đếm phòng 15 trống; đơn 5 DA_XAC_NHAN.
  - Kịch bản nghiệm thu: teo đặt 104 hôm nay → +2 ngày → `CHO_ADMIN_DUYET`, `GiaPhongDat = 300000`; duyệt → phòng "Đã đặt"; nhận phòng → "Đang thuê"; Ăn sáng × 2 + Massage × 1; trả phòng → hóa đơn `CHUA_THANH_TOAN` **1.260.000**; thanh toán → `DA_THANH_TOAN`, đơn `DA_HOAN_THANH`, `payment_status = DA_THANH_TOAN`, phòng "Trống". Đặt trùng ngày 104 → bị chặn.
  - Hóa đơn tự tính lại khi thêm/xóa dịch vụ (1.290.000 → 1.260.000); sau thanh toán đọc lại từ snapshot.
  - Đã thử chặn: R01, R02, R11, R12, R13, R16, R20, R21, R23, R26, R28, R29, R31, R36, R38, R39, R51 → đúng câu thông báo gốc.
  - Sau ROLLBACK: 5 đơn, 5 hóa đơn, 0 CTDICHVU, 5 khách, phòng 104 "Trống" → DB không đổi.

### Kết quả Giai đoạn 3
- [x] `config/auth.php`: guard `web` → provider `khach_hang` (`KhachHang`), guard `admin` → provider `nhan_vien` (`NhanVien`). Bỏ password broker. Đã xóa `app/Models/User.php`, `database/factories/UserFactory.php` (giữ thư mục `factories` bằng `.gitkeep`).
- [x] Middleware `quyen` (`App\Http\Middleware\KiemTraQuyen`) → `abort(403, "Bạn không có quyền thực hiện chức năng này (MÃ).")`. Blade directive `@quyen('MÃ') … @endquyen` (menu admin). `NhanVien::coQuyen()`: "Quản lý" toàn quyền.
- [x] Middleware `DangXuatKhachBiKhoa` (nhóm web, đặt **trước** `auth` trong danh sách ưu tiên): khách bị khóa → đăng xuất ở request kế tiếp, xóa `guest_booking_ids`, `guest_customer_id`.
- [x] `bootstrap/app.php`: route admin tách ra `routes/admin.php` (tiền tố `/admin`, tên `admin.`); `redirectGuestsTo` (admin → `admin.dang-nhap` + flash danger, khách → `tai-khoan.dang-nhap` + flash warning); `redirectUsersTo` (admin → dashboard, đăng ký → trang chủ, đăng nhập khách → lịch sử); không trim các ô `matKhauCu`, `matKhauMoi`, `xacNhanMatKhau`.
- [x] Xử lý lỗi tập trung (`withExceptions`): `DomainException` / `QueryException` 1451, 1062, 1452, 1406, 1264, 1292 → form (POST/PUT/DELETE) quay lại kèm flash danger + dữ liệu cũ (bỏ mật khẩu); xem trang (GET) → trang `errors/422`. Lỗi khác để Laravel xử lý. `DomainException` không ghi log.
- [x] `app/helpers.php` (`format_currency`, `format_date` — nhận cả Carbon), đăng ký `autoload.files` trong `composer.json`, đã `composer dump-autoload`.
- [x] Flash giữ cấu trúc `session('flash') = ['type' => ..., 'message' => ...]`; macro `->thongBao('success', '...')` cho `RedirectResponse` (`AppServiceProvider`). `Route::resourceVerbs(['create' => 'them', 'edit' => 'sua'])`.
- [x] Disk `uploads` (`public_path('uploads')`) trong `config/filesystems.php`.
- [x] Layout `admin/layouts/app.blade.php` + `nav.blade.php` (ẩn nav khi `hideNav`), `client/layouts/app.blade.php`; trang lỗi `errors/403, 404, 405, 419, 422` tiếng Việt (`errors/khung.blade.php`).
- [x] Form Request: `Admin\DangNhapRequest`, `Admin\DoiMatKhauRequest` (`current_password:admin`, `same`, `different`, `$stopOnFirstFailure`), `Client\DangNhapRequest`, `Client\DangKyRequest`.
- [x] Controller: `Admin\AuthController` (đăng nhập, đổi mật khẩu, đăng xuất chỉ guard admin + `regenerateToken()`, không `invalidate()`), `Admin\DashboardController` (khung trang), `Client\AccountController` (đăng nhập email/SĐT, đăng ký, đăng xuất). Mật khẩu kiểm tra bằng `Hash::check`, tự rehash nếu cần như bản gốc.
- **TẠM THỜI:** các trang module admin (`admin.*.index`) và trang khách (`trang-chu`, `lien-he`, `phong.index`, `dich-vu.index`, `danh-gia.index`, `tai-khoan.ho-so`, `tai-khoan.lich-su`) đang là `Route::view` trỏ tới `admin/dang-xay-dung` / `client/dang-xay-dung` để menu và kiểm tra phân quyền chạy được. Giai đoạn 4/5 thay bằng controller thật rồi xóa 2 view này.
- Ghi chú: Form Request báo lỗi theo thứ tự trường; khi một form có **nhiều lỗi cùng lúc**, câu đầu tiên có thể khác bản gốc (vd. họ tên quá dài + thiếu mật khẩu). Từng lỗi riêng lẻ cho đúng câu gốc.
- **Kiểm tra (kết quả thật, `curl` có cookie qua `http://hotel.laravel.test`): 60/60 đạt**, `storage/logs/laravel.log` không có lỗi. Sau đó chạy `migrate:fresh --seed` để trả dữ liệu mẫu.
  - Chưa đăng nhập: `/admin/phong` → 302 `/admin/dang-nhap` + "Vui lòng đăng nhập để tiếp tục."; `/tai-khoan/lich-su` → 302 `/tai-khoan/dang-nhap` + flash warning. GET `/admin/dang-xuat` → 405; POST thiếu `_token` → 419; `/admin/khong-co` → 404 (L7).
  - `viet`/`1`: vào `/admin`, flash thành công, hiện "Nguyễn Quốc Việt / Quản lý", menu 13 mục, `/admin/phong` 200. Bỏ trống / sai mật khẩu → đúng câu gốc, giữ username.
  - `vung`/`1`: menu **chỉ có Dashboard**; `/admin/phong` → **403** "Bạn không có quyền thực hiện chức năng này (PHONG)." + "Quay lại bảng điều khiển".
  - Đổi mật khẩu `binh`: 4 lỗi đúng câu gốc; đổi thành công → flash, mật khẩu cũ hết dùng được; mật khẩu có khoảng trắng không bị cắt.
  - Khách `teo@gmail.com`/`1` và `0912345678`/`1` đăng nhập được, header "Xin chào, Lê Văn Tèo".
  - Khóa `teo` trong DB: đang đăng nhập → request kế tiếp bị đăng xuất; đăng nhập lại → flash "Tài khoản của bạn đã bị khóa…" kèm lỗi chung (như bản gốc).
  - Đăng nhập admin + khách cùng trình duyệt: **admin đăng xuất → khách vẫn đăng nhập**; khách đăng xuất → admin vẫn đăng nhập.
  - Đăng ký: thiếu trường / SĐT trùng / SĐT sai / email sai / email trùng → đúng câu gốc, giữ dữ liệu đã nhập; đăng ký hợp lệ → tự đăng nhập, `Email = ''`, `EmailConnexion = NULL`, mật khẩu bcrypt, họ tên tiếng Việt lưu đúng UTF-8.
  - Bộ xử lý lỗi: `DomainException` POST → 302 + flash + dữ liệu cũ (không có mật khẩu), GET → 422; `QueryException` 1451/1062/1452 → đúng câu tiếng Việt; 1366 → 500 mặc định.

### Kết quả Giai đoạn 4A (Loại phòng, Phòng, Dịch vụ)
- [x] `Route::resource(...)->except('show')->middleware('quyen:...')->missing(...)`: bản ghi không tồn tại → về danh sách kèm flash như bản gốc ("Loại phòng không tồn tại.", "Không tìm thấy thông tin phòng.", "Không tìm thấy dịch vụ.").
- [x] Form Request: `LoaiPhongRequest`, `StorePhongRequest`, `UpdatePhongRequest`, `DichVuRequest`; luật ảnh dùng chung ở trait `Requests\Concerns\KiemTraAnhTaiLen`.
- [x] Upload: `Controller::luuAnhTaiLen()` → `store($thuMuc, 'uploads')`, tên ngẫu nhiên 40 ký tự, DB lưu `/uploads/phong/<tên>` hoặc `/uploads/dichvu/<tên>`. Ảnh tải lên được ưu tiên hơn đường dẫn nhập tay (như bản gốc).
- [x] Sửa lỗi **L1**: form phòng chỉ còn 4 trạng thái `Trống, Đã đặt, Đang thuê, Bảo trì`; danh sách/đếm vẫn coi "Đang sử dụng" là đang thuê.
- [x] Sửa phòng: kiểm tra "đang có lịch đặt" (R51) **trước** khi lưu ảnh, tránh để lại file thừa khi bị chặn.
- Ghi chú: form lỗi giữ lại dữ liệu vừa nhập (`old()`), bản gốc thì hiện lại dữ liệu cũ của bản ghi. Mô tả loại phòng rỗng vẫn lưu `''` như bản gốc.
- **Kiểm tra (HTTP thật qua `http://hotel.laravel.test`, đăng nhập `viet`): 59/59 đạt**, sau đó `migrate:fresh --seed` và xóa ảnh thử.
  - Loại phòng: danh sách 3; bỏ trống / tên > 20 ký tự → đúng câu gốc; thêm "Gia đình" (flash), sửa, xóa loại không có phòng → xóa hẳn; xóa loại 3 (còn phòng) → `DaXoa = 1`, ẩn khỏi danh sách và khỏi form phòng; sửa id 999 → flash "Loại phòng không tồn tại.".
  - Phòng: danh sách 15, ô "Phòng trống" = 15; lọc từ khóa "Vip" → 301–305; lọc ngày 07→09/05/2026 loại đúng 102, 103, 201; lọc ngày sai → báo lỗi; lọc loại + giá; form không còn "Đang sử dụng"; mã trùng / thiếu mã / trạng thái "Đang sử dụng" (R52) / loại đã xóa (R53) / file không phải ảnh (R06) → đúng câu gốc; thêm phòng 106 kèm ảnh → file nằm ở `public/uploads/phong/<40 ký tự>.jpg`, mở được qua URL; sửa 106 giữ nguyên ảnh; phòng 105 có đơn chờ duyệt → đổi trạng thái bị chặn (R51), đổi ảnh vẫn được; xóa 101 (đã có đơn) → "Không thể xóa vì dữ liệu này đang được sử dụng…"; xóa 106 được; DELETE bằng GET → 405.
  - Dịch vụ: danh sách 5; giá 0 / tên > 50 ký tự (R40) → đúng câu gốc; thêm "Giặt ủi" kèm ảnh → `/uploads/dichvu/...`; sửa giá + đường dẫn ảnh; xóa dịch vụ đang dùng trong CTDICHVU → lỗi 1451 tiếng Việt; xóa dịch vụ mới được.
  - Phân quyền: `binh` (Lễ tân) → `/admin/phong`, `/admin/loai-phong/them`, DELETE phòng đều 403; `/admin/dich-vu` 200.

### Kết quả Giai đoạn 4B (Khách hàng, Nhân viên, Chức vụ)
- [x] Khách hàng: `KhachHangController` (index + tìm kiếm, `toggle` POST, `history`); route nhóm `khach-hang.*` có `->missing()` → "Khách hàng không tồn tại.".
- [x] Nhân viên: `Route::resource('nhan-vien')`, `StoreNhanVienRequest` / `UpdateNhanVienRequest` (luật họ tên, chức vụ, CMND, ngày dùng chung). Thêm: ngày vào làm trống → hôm nay; sửa: mật khẩu trống thì giữ nguyên, ngày vào làm trống → NULL (như bản gốc). Chức vụ không tồn tại → câu thông báo của lỗi 1452 như bản gốc.
- [x] Chức vụ: `ChucVuController` (index + `updatePermissions` PUT `/admin/chuc-vu/{chuc_vu}/quyen`), `UpdatePermissionsRequest`, ghi quyền trong transaction (`ChucVu::capNhatQuyen`), bỏ mã trùng.
- [x] TẠM THỜI thêm route `admin.dat-phong.show` (trang chờ) để link "Xem đơn" ở lịch sử khách hàng chạy được; thay bằng trang thật ở 4D.
- **Kiểm tra (HTTP thật, `viet`): 63/63 đạt**, log không có lỗi ngoài các lỗi 1451 cố ý tạo.
  - Khách hàng: 5 khách, mã giảm dần; tìm theo SĐT / CMND; khóa #1 → flash, nút "Mở khóa", DB `KHOA`, khách không đăng nhập được; mở khóa lại; lịch sử khách #2 (đơn #2, "Phòng 102 (Bình Dân)", mã trạng thái thô — L4); khách 999 → flash "Khách hàng không tồn tại."; khóa bằng GET → 405.
  - Nhân viên: thiếu mật khẩu / trùng username (R42) / username > 20 (R41) / CMND > 20 (R45) / ngày sinh tương lai (R46) / chức vụ 99 → đúng câu gốc; thêm "Trần Thị Lan" → ngày vào làm = hôm nay, CMND NULL, mật khẩu bcrypt, đăng nhập được; sửa không đổi mật khẩu → mật khẩu cũ vẫn dùng được; đổi mật khẩu khi sửa → đăng nhập bằng mật khẩu mới; `viet` tự đổi chức vụ → chặn (R43), tự sửa thông tin khác vẫn được; tự xóa → chặn (R44); xóa `binh` (có đơn) → 1451; xóa nhân viên mới → phiên đăng nhập của người đó hết hiệu lực; id 999 → flash.
  - Chức vụ: 4 thẻ, 22 ô được tick (khớp CHUCVU_QUYEN); lưu DASHBOARD + PHONG cho Bảo vệ → `vung` vào được `/admin/phong`, menu 2 mục; mã quyền lạ → "Danh sách quyền không hợp lệ." (R07), DB không đổi; bỏ tick hết → Tạp vụ mất hết quyền, `van` vào `/admin` → 403 (L6); trả quyền về 22 dòng.
  - Phân quyền: `binh` → khách hàng 200; nhân viên 403; tự cấp quyền (PUT chức vụ) 403.

### Kết quả Giai đoạn 4C (Phân công, Đánh giá)
- [x] Phân công: `GET /admin/phan-cong/hom-nay` + `Route::resource('phan-cong')`; `PhanCongRequest` (chặn ngày quá khứ **chỉ khi thêm**, như bản gốc); không chọn nhân viên → NULL; trang "Hôm nay" dùng chung view danh sách.
- [x] Đánh giá: `Route::resource('danh-gia')->parameters(['danh-gia' => 'danh_gia'])` (Laravel tự đổi "gia" sang số ít thành `{danh_gium}` nên phải đặt tên tham số); `DanhGiaRequest`; thêm qua `DanhGia::taoDanhGia()` (ép sao 1–5, ngày trống → hôm nay), sửa qua `DanhGia::capNhat()` (R31).
- **Kiểm tra (HTTP thật, `viet`): 43/43 đạt**, log không có lỗi.
  - Phân công: thiếu nội dung / ngày quá khứ khi thêm / > 50 ký tự (R47) / ngày 30/02 → đúng câu gốc; thêm cho Vấn → hiện "(@van)", "Tạp vụ"; không chọn nhân viên → NULL, "Chưa chỉ định"; sắp ngày giảm dần; "Hôm nay" chỉ lấy phân công hôm nay, ô ngày điền sẵn; tìm từ khóa "Trực"; lọc ngày; sửa sang ngày quá khứ vẫn được; xóa; id 999 → flash; `van` (có PHAN_CONG) 200, `binh` 403.
  - Đánh giá: 2 đánh giá mẫu (tên + email khách), sắp ngày giảm dần; thiếu nội dung / > 255 ký tự (R27) → đúng câu gốc; admin thêm với 9 sao, ngày trống → 5 sao, hôm nay, "Khách vãng lai"; đổi phòng của đánh giá gắn đơn → chặn (R31); 0 sao → 1; đánh giá không gắn đơn đổi phòng được; xóa; id 999 → flash; `binh` 403.

### Kết quả Giai đoạn 4D (Đặt phòng, Chi tiết dịch vụ, Hóa đơn)
- [x] `DatPhongController` (index + lọc trạng thái/từ khóa, create/store, show, approve, checkin, checkout, cancel, addService, removeService) gọi `DatPhongService`, `HoaDonService`, `CtDichVuService` (inject qua constructor). Duyệt / nhận phòng / trả phòng ghi `MaNhanVien` = nhân viên đang đăng nhập; hủy thì không (như bản gốc).
- [x] `CtDichVuController` (index, create/store = **đặt** số lượng, addToBooking/saveToBooking = **cộng dồn**, destroy theo khóa ghép, ô `back=detail`).
- [x] `HoaDonController` (index, show, pay). Hóa đơn đã thanh toán và chi tiết đơn đã hoàn tất hiển thị từ bản chụp `ChiTietJSON`.
- [x] Form Request: `StoreDatPhongRequest`, `ThemDichVuVaoDonRequest` (dùng chung cho "Gọi thêm dịch vụ" và "Thêm dịch vụ vào đơn"; lỗi → về chi tiết đơn), `CtDichVuRequest`.
- [x] Route xóa theo khóa ghép nhận 2 số (`{maCTDatPhong}/{maDichVu}`), không dùng model binding → đơn không tồn tại báo "Đơn đặt phòng không tồn tại." từ service (R18). Các route khác dùng binding + `->missing()` ("Không tìm thấy đơn đặt phòng.", "Hóa đơn không tồn tại.").
- [x] Đã bỏ hết trang tạm của module quản trị (còn `thong-ke` cho 4E). Link "Xem đơn" ở lịch sử khách hàng trỏ tới chi tiết đơn thật.
- Khác bản gốc (nhỏ): "Gọi thêm dịch vụ" với dịch vụ/số lượng không hợp lệ — bản gốc lặng lẽ bỏ qua, bản Laravel hiện "Thông tin dịch vụ hoặc số lượng không hợp lệ." (câu của saveToBooking). Lỗi nghiệp vụ khi tạo đơn/chuyển trạng thái hiện flash đỏ và quay lại trang (bản gốc hiện trang lỗi riêng).
- **Kiểm tra (HTTP thật, `viet`): 98/98 đạt**, log không có lỗi. Chạy lại toàn bộ kiểm tra GĐ3 (60), 4A (59), 4B (63), 4C (43) sau khi viết lại `routes/admin.php`: tất cả vẫn đạt.
  - Danh sách: tab "Tất cả (5)", "Đã xác nhận (5)"; đơn mới nhất trước; lọc trạng thái + SĐT; tìm phòng 201 → 2 đơn.
  - Tạo đơn: thiếu khách / ngày trả trước ngày nhận / ngày quá khứ (R02) / trạng thái DA_HUY (R09) / nhận ngay khác hôm nay (R10) / khách bị khóa (R11) / phòng bảo trì (R19) / phòng 999 (R17) → đúng câu gốc, giữ dữ liệu đã nhập.
  - **Kịch bản nghiệm thu (phía admin; phần khách đặt làm ở GĐ 5):** đơn teo, phòng 104, hôm nay → +2 ngày → `CHO_ADMIN_DUYET`, `GiaPhongDat = 300000`; đặt trùng 104 → chặn (R20); trả phòng khi chưa nhận (R08), nhận khi chưa duyệt (R12) → chặn; duyệt → `DA_XAC_NHAN`, phòng "Đã đặt"; nhận phòng → `DANG_SU_DUNG`, phòng "Đang thuê"; số lượng 0 / 10.001 (R23) / dịch vụ 99 (R24) → chặn; **Ăn sáng × 2** (chi tiết đơn) + **Massage × 1** (trang thêm dịch vụ vào đơn) → tổng 1.260.000; trả phòng → hóa đơn `CHUA_THANH_TOAN` **1.260.000 đ**; thêm dịch vụ sau khi lập → hóa đơn tự tính lại (1.290.000), đặt lại số lượng ở trang Chi tiết DV → về 1.260.000; thanh toán hóa đơn mẫu #1 (đơn không đang ở) → chặn (R36); **thanh toán → hóa đơn `DA_THANH_TOAN`, đơn `DA_HOAN_THANH`, `payment_status = DA_THANH_TOAN`, phòng 104 "Trống"**; đổi giá Massage sau đó → hóa đơn/chi tiết đơn vẫn 1.260.000 (snapshot); thêm dịch vụ sau thanh toán → chặn (R21); hóa đơn thứ hai của cùng đơn → chặn (R35); đặt lại 104 cùng ngày sau khi đơn cũ hoàn tất → được.
  - Hủy đơn → `DA_HUY`, không ghi đè nhân viên; nhận phòng đơn mẫu tháng 5 → "Đơn đặt phòng đã quá ngày trả." (R13); nhận phòng trước ngày nhận (R14); xóa dịch vụ khỏi đơn (2 cách, `back=detail`); xóa chi tiết của đơn 99999 → R18; id không tồn tại → flash; 6 hóa đơn trong danh sách.
  - Phân quyền: `binh` (Lễ tân) vào đặt phòng / chi tiết DV / hóa đơn → 200; `vung` → 403, kể cả POST thanh toán.

---

## 1. Bảng ánh xạ route

Quy ước:
- Cột "Gốc" là `c` / `a` trong `?c=...&a=...`. "GET/POST" của bản gốc = cùng một action xử lý cả hiển thị form và lưu.
- Tài nguyên admin dùng `Route::resource(...)->except('show')`, đổi động từ URL bằng `Route::resourceVerbs(['create' => 'them', 'edit' => 'sua'])` trong `AppServiceProvider`.
- Mọi route admin (trừ đăng nhập) có `auth:admin`; cột "Quyền" là middleware `quyen:...`.
- Controller admin: `App\Http\Controllers\Admin\*`; controller khách: `App\Http\Controllers\Client\*`.

### 1.1 Quản trị (`admin/index.php`)

| # | Gốc (c / a) | Method | URL Laravel | Tên route | Controller@method | Middleware / Quyền | Xong |
|---|---|---|---|---|---|---|---|
| A01 | auth / login (GET) | GET | `/admin/dang-nhap` | `admin.dang-nhap` | `AuthController@showLogin` | `guest:admin` | ✅ |
| A02 | auth / login (POST) | POST | `/admin/dang-nhap` | `admin.dang-nhap.store` | `AuthController@login` | `guest:admin` | ✅ |
| A03 | auth / changePassword (GET) | GET | `/admin/doi-mat-khau` | `admin.doi-mat-khau` | `AuthController@showChangePassword` | `auth:admin` | ✅ |
| A04 | auth / changePassword (POST) | PUT | `/admin/doi-mat-khau` | `admin.doi-mat-khau.update` | `AuthController@changePassword` | `auth:admin` | ✅ |
| A05 | auth / logout | POST | `/admin/dang-xuat` | `admin.dang-xuat` | `AuthController@logout` | `auth:admin` | ✅ |
| A06 | dashboard / index (và `admin/` không có `c`) | GET | `/admin` | `admin.dashboard` | `DashboardController@index` | DASHBOARD | ✅ |
| A07 | phong / list | GET | `/admin/phong` | `admin.phong.index` | `PhongController@index` | PHONG | ✅ |
| A08 | phong / add (GET) | GET | `/admin/phong/them` | `admin.phong.create` | `PhongController@create` | PHONG | ✅ |
| A09 | phong / add (POST) | POST | `/admin/phong` | `admin.phong.store` | `PhongController@store` | PHONG | ✅ |
| A10 | phong / edit (GET) | GET | `/admin/phong/{phong}/sua` | `admin.phong.edit` | `PhongController@edit` | PHONG | ✅ |
| A11 | phong / edit (POST) | PUT | `/admin/phong/{phong}` | `admin.phong.update` | `PhongController@update` | PHONG | ✅ |
| A12 | phong / delete | DELETE | `/admin/phong/{phong}` | `admin.phong.destroy` | `PhongController@destroy` | PHONG | ✅ |
| A13 | loai-phong / list | GET | `/admin/loai-phong` | `admin.loai-phong.index` | `LoaiPhongController@index` | LOAI_PHONG | ✅ |
| A14 | loai-phong / add (GET) | GET | `/admin/loai-phong/them` | `admin.loai-phong.create` | `LoaiPhongController@create` | LOAI_PHONG | ✅ |
| A15 | loai-phong / add (POST) | POST | `/admin/loai-phong` | `admin.loai-phong.store` | `LoaiPhongController@store` | LOAI_PHONG | ✅ |
| A16 | loai-phong / edit (GET) | GET | `/admin/loai-phong/{loai_phong}/sua` | `admin.loai-phong.edit` | `LoaiPhongController@edit` | LOAI_PHONG | ✅ |
| A17 | loai-phong / edit (POST) | PUT | `/admin/loai-phong/{loai_phong}` | `admin.loai-phong.update` | `LoaiPhongController@update` | LOAI_PHONG | ✅ |
| A18 | loai-phong / delete | DELETE | `/admin/loai-phong/{loai_phong}` | `admin.loai-phong.destroy` | `LoaiPhongController@destroy` | LOAI_PHONG | ✅ |
| A19 | dat-phong / list (`?trangThai`, `?keyword`) | GET | `/admin/dat-phong` | `admin.dat-phong.index` | `DatPhongController@index` | DAT_PHONG | ✅ |
| A20 | dat-phong / add (GET) | GET | `/admin/dat-phong/them` | `admin.dat-phong.create` | `DatPhongController@create` | DAT_PHONG | ✅ |
| A21 | dat-phong / add (POST) | POST | `/admin/dat-phong` | `admin.dat-phong.store` | `DatPhongController@store` | DAT_PHONG | ✅ |
| A22 | dat-phong / detail | GET | `/admin/dat-phong/{dat_phong}` | `admin.dat-phong.show` | `DatPhongController@show` | DAT_PHONG | ✅ |
| A23 | dat-phong / approve | POST | `/admin/dat-phong/{dat_phong}/duyet` | `admin.dat-phong.duyet` | `DatPhongController@approve` | DAT_PHONG | ✅ |
| A24 | dat-phong / checkin | POST | `/admin/dat-phong/{dat_phong}/nhan-phong` | `admin.dat-phong.nhan-phong` | `DatPhongController@checkin` | DAT_PHONG | ✅ |
| A25 | dat-phong / checkout | POST | `/admin/dat-phong/{dat_phong}/tra-phong` | `admin.dat-phong.tra-phong` | `DatPhongController@checkout` | DAT_PHONG | ✅ |
| A26 | dat-phong / cancel | POST | `/admin/dat-phong/{dat_phong}/huy` | `admin.dat-phong.huy` | `DatPhongController@cancel` | DAT_PHONG | ✅ |
| A27 | dat-phong / addService | POST | `/admin/dat-phong/{dat_phong}/dich-vu` | `admin.dat-phong.dich-vu.store` | `DatPhongController@addService` | DAT_PHONG | ✅ |
| A28 | dat-phong / removeService | DELETE | `/admin/dat-phong/{dat_phong}/dich-vu/{maDichVu}` | `admin.dat-phong.dich-vu.destroy` | `DatPhongController@removeService` | DAT_PHONG | ✅ |
| A29 | dich-vu / list | GET | `/admin/dich-vu` | `admin.dich-vu.index` | `DichVuController@index` | DICH_VU | ✅ |
| A30 | dich-vu / add (GET) | GET | `/admin/dich-vu/them` | `admin.dich-vu.create` | `DichVuController@create` | DICH_VU | ✅ |
| A31 | dich-vu / add (POST) | POST | `/admin/dich-vu` | `admin.dich-vu.store` | `DichVuController@store` | DICH_VU | ✅ |
| A32 | dich-vu / edit (GET) | GET | `/admin/dich-vu/{dich_vu}/sua` | `admin.dich-vu.edit` | `DichVuController@edit` | DICH_VU | ✅ |
| A33 | dich-vu / edit (POST) | PUT | `/admin/dich-vu/{dich_vu}` | `admin.dich-vu.update` | `DichVuController@update` | DICH_VU | ✅ |
| A34 | dich-vu / delete | DELETE | `/admin/dich-vu/{dich_vu}` | `admin.dich-vu.destroy` | `DichVuController@destroy` | DICH_VU | ✅ |
| A35 | hoa-don / list | GET | `/admin/hoa-don` | `admin.hoa-don.index` | `HoaDonController@index` | HOA_DON | ✅ |
| A36 | hoa-don / detail | GET | `/admin/hoa-don/{hoa_don}` | `admin.hoa-don.show` | `HoaDonController@show` | HOA_DON | ✅ |
| A37 | hoa-don / pay | POST | `/admin/hoa-don/{hoa_don}/thanh-toan` | `admin.hoa-don.thanh-toan` | `HoaDonController@pay` | HOA_DON | ✅ |
| A38 | khach-hang / list (`?keyword`) | GET | `/admin/khach-hang` | `admin.khach-hang.index` | `KhachHangController@index` | KHACH_HANG | ✅ |
| A39 | khach-hang / toggle | POST | `/admin/khach-hang/{khach_hang}/doi-trang-thai` | `admin.khach-hang.doi-trang-thai` | `KhachHangController@toggle` | KHACH_HANG | ✅ |
| A40 | khach-hang / history | GET | `/admin/khach-hang/{khach_hang}/lich-su` | `admin.khach-hang.lich-su` | `KhachHangController@history` | KHACH_HANG | ✅ |
| A41 | nhan-vien / list | GET | `/admin/nhan-vien` | `admin.nhan-vien.index` | `NhanVienController@index` | NHAN_VIEN | ✅ |
| A42 | nhan-vien / add (GET) | GET | `/admin/nhan-vien/them` | `admin.nhan-vien.create` | `NhanVienController@create` | NHAN_VIEN | ✅ |
| A43 | nhan-vien / add (POST) | POST | `/admin/nhan-vien` | `admin.nhan-vien.store` | `NhanVienController@store` | NHAN_VIEN | ✅ |
| A44 | nhan-vien / edit (GET) | GET | `/admin/nhan-vien/{nhan_vien}/sua` | `admin.nhan-vien.edit` | `NhanVienController@edit` | NHAN_VIEN | ✅ |
| A45 | nhan-vien / edit (POST) | PUT | `/admin/nhan-vien/{nhan_vien}` | `admin.nhan-vien.update` | `NhanVienController@update` | NHAN_VIEN | ✅ |
| A46 | nhan-vien / delete | DELETE | `/admin/nhan-vien/{nhan_vien}` | `admin.nhan-vien.destroy` | `NhanVienController@destroy` | NHAN_VIEN | ✅ |
| A47 | chuc-vu / list | GET | `/admin/chuc-vu` | `admin.chuc-vu.index` | `ChucVuController@index` | CHUC_VU | ✅ |
| A48 | chuc-vu / updatePermissions (`maChucVu` từ input ẩn → tham số URL) | PUT | `/admin/chuc-vu/{chuc_vu}/quyen` | `admin.chuc-vu.quyen.update` | `ChucVuController@updatePermissions` | CHUC_VU | ✅ |
| A49 | ct-dich-vu / list | GET | `/admin/ct-dich-vu` | `admin.ct-dich-vu.index` | `CtDichVuController@index` | CT_DICH_VU | ✅ |
| A50 | ct-dich-vu / add (GET, `?maCTDatPhong` chọn sẵn đơn) | GET | `/admin/ct-dich-vu/them` | `admin.ct-dich-vu.create` | `CtDichVuController@create` | CT_DICH_VU | ✅ |
| A51 | ct-dich-vu / add (POST) | POST | `/admin/ct-dich-vu` | `admin.ct-dich-vu.store` | `CtDichVuController@store` | CT_DICH_VU | ✅ |
| A52 | ct-dich-vu / addToBooking | GET | `/admin/ct-dich-vu/don/{dat_phong}` | `admin.ct-dich-vu.them-vao-don` | `CtDichVuController@addToBooking` | CT_DICH_VU | ✅ |
| A53 | ct-dich-vu / saveToBooking | POST | `/admin/ct-dich-vu/don/{dat_phong}` | `admin.ct-dich-vu.luu-vao-don` | `CtDichVuController@saveToBooking` | CT_DICH_VU | ✅ |
| A54 | ct-dich-vu / delete (khóa ghép; input `back=detail` để quay về chi tiết đơn) | DELETE | `/admin/ct-dich-vu/{maCTDatPhong}/{maDichVu}` | `admin.ct-dich-vu.destroy` | `CtDichVuController@destroy` | CT_DICH_VU | ✅ |
| A55 | phan-cong / list (`?keyword`, `?ngay`) | GET | `/admin/phan-cong` | `admin.phan-cong.index` | `PhanCongController@index` | PHAN_CONG | ✅ |
| A56 | phan-cong / homNay | GET | `/admin/phan-cong/hom-nay` | `admin.phan-cong.hom-nay` | `PhanCongController@homNay` | PHAN_CONG | ✅ |
| A57 | phan-cong / add (GET) | GET | `/admin/phan-cong/them` | `admin.phan-cong.create` | `PhanCongController@create` | PHAN_CONG | ✅ |
| A58 | phan-cong / add (POST) | POST | `/admin/phan-cong` | `admin.phan-cong.store` | `PhanCongController@store` | PHAN_CONG | ✅ |
| A59 | phan-cong / edit (GET) | GET | `/admin/phan-cong/{phan_cong}/sua` | `admin.phan-cong.edit` | `PhanCongController@edit` | PHAN_CONG | ✅ |
| A60 | phan-cong / edit (POST) | PUT | `/admin/phan-cong/{phan_cong}` | `admin.phan-cong.update` | `PhanCongController@update` | PHAN_CONG | ✅ |
| A61 | phan-cong / delete | DELETE | `/admin/phan-cong/{phan_cong}` | `admin.phan-cong.destroy` | `PhanCongController@destroy` | PHAN_CONG | ✅ |
| A62 | danh-gia / list | GET | `/admin/danh-gia` | `admin.danh-gia.index` | `DanhGiaController@index` | DANH_GIA | ✅ |
| A63 | danh-gia / add (GET) | GET | `/admin/danh-gia/them` | `admin.danh-gia.create` | `DanhGiaController@create` | DANH_GIA | ✅ |
| A64 | danh-gia / add (POST) | POST | `/admin/danh-gia` | `admin.danh-gia.store` | `DanhGiaController@store` | DANH_GIA | ✅ |
| A65 | danh-gia / edit (GET) | GET | `/admin/danh-gia/{danh_gia}/sua` | `admin.danh-gia.edit` | `DanhGiaController@edit` | DANH_GIA | ✅ |
| A66 | danh-gia / edit (POST) | PUT | `/admin/danh-gia/{danh_gia}` | `admin.danh-gia.update` | `DanhGiaController@update` | DANH_GIA | ✅ |
| A67 | danh-gia / delete | DELETE | `/admin/danh-gia/{danh_gia}` | `admin.danh-gia.destroy` | `DanhGiaController@destroy` | DANH_GIA | ✅ |
| A68 | thong-ke / index (`?year`) | GET | `/admin/thong-ke` | `admin.thong-ke.index` | `ThongKeController@index` | THONG_KE | ⬜ |
| A69 | `c` không hợp lệ → về dashboard | — | 404 mặc định của Laravel (đã duyệt, L7) | — | — | — | ⬜ |

### 1.2 Khách hàng (`index.php`)

| # | Gốc (c / a) | Method | URL Laravel | Tên route | Controller@method | Middleware | Xong |
|---|---|---|---|---|---|---|---|
| K01 | home / index (và `c` không hợp lệ) | GET | `/` | `trang-chu` | `HomeController@index` | — | ⬜ |
| K02 | home / contact, contact / * | GET | `/lien-he` | `lien-he` | `HomeController@contact` | — | ⬜ |
| K03 | room / list (lọc `maLoaiPhong`, `giaTu`, `giaDen`, `ngayNhan`, `ngayTra`; có `keyword` ở model) | GET | `/phong` | `phong.index` | `RoomController@index` | — | ⬜ |
| K04 | room / detail (`id`) | GET | `/phong/{maPhong}` | `phong.show` | `RoomController@show` | — | ⬜ |
| K05 | booking / create (GET, `maPhong`, `ngayNhan`, `ngayTra`) | GET | `/phong/{maPhong}/dat-phong` | `dat-phong.create` | `BookingController@create` | — | ⬜ |
| K06 | booking / create (POST) | POST | `/phong/{maPhong}/dat-phong` | `dat-phong.store` | `BookingController@store` | — | ⬜ |
| K07 | booking / success (`id`) | GET | `/dat-phong/{datPhong}/thanh-cong` | `dat-phong.thanh-cong` | `BookingController@success` | (kiểm tra session `guest_booking_ids` hoặc chủ đơn) | ⬜ |
| K08 | service / list | GET | `/dich-vu` | `dich-vu.index` | `ServiceController@index` | — | ⬜ |
| K09 | review, reviews / list | GET | `/danh-gia` | `danh-gia.index` | `ReviewController@index` | — | ⬜ |
| K10 | review / submit | POST | `/danh-gia` | `danh-gia.store` | `ReviewController@store` | `auth:web` | ⬜ |
| K11 | account / login (GET; mặc định của `account`) | GET | `/tai-khoan/dang-nhap` | `tai-khoan.dang-nhap` | `AccountController@showLogin` | đã đăng nhập → lịch sử | ✅ |
| K12 | account / login (POST) | POST | `/tai-khoan/dang-nhap` | `tai-khoan.dang-nhap.store` | `AccountController@login` | — | ✅ |
| K13 | account / register (GET) | GET | `/tai-khoan/dang-ky` | `tai-khoan.dang-ky` | `AccountController@showRegister` | đã đăng nhập → trang chủ | ✅ |
| K14 | account / register (POST) | POST | `/tai-khoan/dang-ky` | `tai-khoan.dang-ky.store` | `AccountController@register` | — | ✅ |
| K15 | account / profile | GET | `/tai-khoan` | `tai-khoan.ho-so` | `AccountController@profile` | `auth:web` | ⬜ |
| K16 | account / history | GET | `/tai-khoan/lich-su` | `tai-khoan.lich-su` | `AccountController@history` | `auth:web` | ⬜ |
| K17 | account / detail (`id`, chỉ đơn của mình) | GET | `/tai-khoan/don/{datPhong}` | `tai-khoan.chi-tiet` | `AccountController@detail` | `auth:web` | ⬜ |
| K18 | account / logout | POST | `/tai-khoan/dang-xuat` | `tai-khoan.dang-xuat` | `AccountController@logout` | `auth:web` | ✅ |

Ghi chú route:
- Bản gốc chặn GET cho các action đổi dữ liệu (`guard_request`: delete, toggle, approve, checkin, checkout, cancel, pay, removeService, logout, addService, saveToBooking, updatePermissions, submit) → Laravel tự chặn vì route chỉ khai báo POST/PUT/DELETE.
- Bản gốc tự kiểm CSRF (lỗi 403 "Phiên biểu mẫu không hợp lệ. Vui lòng tải lại trang rồi thử lại.") → Laravel dùng `@csrf` (lỗi 419). Có thể thêm `resources/views/errors/419.blade.php` với đúng câu này.
- Chưa đăng nhập: `/admin/...` → `admin.dang-nhap` + flash danger "Vui lòng đăng nhập để tiếp tục."; còn lại → `tai-khoan.dang-nhap` + flash warning "Vui lòng đăng nhập tài khoản khách hàng để tiếp tục.".
- Phòng/đơn không tồn tại: bản gốc flash + redirect (không phải 404). Giữ hành vi này (tự tìm bản ghi, không dùng implicit binding báo 404) ở những chỗ có flash.

---

## 2. Quy tắc nghiệp vụ (53 chỗ `throw new DomainException`)

Đã đếm bằng grep: đúng **53** chỗ. Cột "Dự kiến" là nơi định đặt; cột "Đã chuyển sang" điền khi làm xong.

### `config/helpers.php`
| # | Dòng | Điều kiện | Thông báo | Dự kiến | Đã chuyển sang |
|---|---|---|---|---|---|
| R01 | 68 | `validate_stay`: ngày sai định dạng hoặc ngày trả ≤ ngày nhận (dùng ở đặt phòng, chuyển trạng thái, tính hóa đơn, lọc phòng) | Ngày nhận/trả không hợp lệ. Ngày trả phải sau ngày nhận. | Form Request (`date`, `after:ngayNhan`) + `DatPhongService` | `DatPhongService::validateStay` ✅; admin: `StoreDatPhongRequest` (`date_format`, `after:ngayNhan`) ✅ (khách: GĐ 5) |
| R02 | 71 | `validate_stay`: ngày nhận < hôm nay (trừ khi `allowPast`) | Ngày nhận phòng không được nằm trong quá khứ. | Form Request (`after_or_equal:today`) + `DatPhongService::createBooking` | `DatPhongService::validateStay` ✅; admin: `StoreDatPhongRequest` (`date_format`, `after:ngayNhan`) ✅ (khách: GĐ 5) |
| R03 | 77 | `validate_customer`: họ tên rỗng/>255 hoặc SĐT không khớp `/^[+0-9][0-9 .()-]{7,19}$/` | Vui lòng nhập họ tên và số điện thoại hợp lệ. | Form Request đặt phòng/đăng ký (`regex`, `max`) | `Client\DangKyRequest` ✅ (đặt phòng: GĐ 5) |
| R04 | 80 | `validate_customer`: email sai/ >254, địa chỉ >255, CMND >20 | Email, địa chỉ hoặc số giấy tờ không hợp lệ hoặc quá dài. | Form Request đặt phòng/đăng ký | `Client\DangKyRequest` ✅ (đặt phòng: GĐ 5) |
| R05 | 93 | `upload_image`: lỗi upload hoặc > 5 MB | Không tải được ảnh. Vui lòng chọn ảnh tối đa 5 MB. | Form Request Phong/DichVu (`max:5120`) | `Concerns\KiemTraAnhTaiLen` (`file`, `max:5120`) ✅ |
| R06 | 98 | `upload_image`: không phải JPG/PNG/WebP/GIF hợp lệ | Chỉ chấp nhận ảnh JPG, PNG, WebP hoặc GIF hợp lệ. | Form Request (`image`, `mimes:jpg,jpeg,png,webp,gif`) | `Concerns\KiemTraAnhTaiLen` (`image`, `mimes`) ✅ |

### `controllers/admin/ChucVuController.php`
| # | Dòng | Điều kiện | Thông báo | Dự kiến | Đã chuyển sang |
|---|---|---|---|---|---|
| R07 | 46 | `permissions` không phải mảng hoặc có mã ngoài 13 quyền | Danh sách quyền không hợp lệ. | `UpdatePermissionsRequest` (`array`, `in:...`) | `Admin\UpdatePermissionsRequest` (`array`, `in:` 13 mã) ✅ |

### `controllers/admin/DatPhongController.php`
| # | Dòng | Điều kiện | Thông báo | Dự kiến | Đã chuyển sang |
|---|---|---|---|---|---|
| R08 | 82 | checkout: đơn không tồn tại hoặc không ở `DANG_SU_DUNG` | Chỉ trả phòng cho đơn đang sử dụng. | `DatPhongController@checkout` / `HoaDonService` | `HoaDonService::checkout` ✅ |

### `models/DatPhongModel.php`
| # | Dòng | Điều kiện | Thông báo | Dự kiến | Đã chuyển sang |
|---|---|---|---|---|---|
| R09 | 90 | createBooking: trạng thái khởi tạo ngoài `CHO_ADMIN_DUYET, DA_XAC_NHAN, DANG_SU_DUNG` | Trạng thái đặt phòng không hợp lệ. | `StoreDatPhongRequest` (`in:`) + `DatPhongService::createBooking` | `Admin\StoreDatPhongRequest` (`in:`) + `DatPhongService::createBooking` ✅ |
| R10 | 91 | createBooking `DANG_SU_DUNG` mà ngày nhận ≠ hôm nay | Chỉ nhận phòng trong ngày bắt đầu lưu trú. | `DatPhongService::createBooking` | `DatPhongService::createBooking` ✅ |
| R11 | 98 | Khách không tồn tại hoặc `KHOA` | Khách hàng không tồn tại hoặc đã bị khóa. | `DatPhongService::createBooking` | `DatPhongService::createBooking` ✅ |
| R12 | 128 | Chuyển trạng thái sai luồng (`CHO_ADMIN_DUYET→DA_XAC_NHAN/DA_HUY`, `DA_XAC_NHAN→DANG_SU_DUNG/DA_HUY`, `DANG_SU_DUNG→DA_HOAN_THANH`) | Không thể chuyển trạng thái của đơn đặt phòng này. | `DatPhongService::updateStatus` | `DatPhongService::updateStatus` ✅ |
| R13 | 131 | Sang `DA_XAC_NHAN`/`DANG_SU_DUNG` khi ngày trả ≤ hôm nay | Đơn đặt phòng đã quá ngày trả. | `DatPhongService::updateStatus` | `DatPhongService::updateStatus` ✅ |
| R14 | 135 | Nhận phòng trước ngày nhận | Chưa đến ngày nhận phòng. | `DatPhongService::updateStatus` | `DatPhongService::updateStatus` ✅ |
| R15 | 138 | Nhận phòng khi phòng còn đơn khác `DANG_SU_DUNG` | Phòng còn khách chưa trả phòng. | `DatPhongService::updateStatus` | `DatPhongService::updateStatus` ✅ |
| R16 | 143 | Sang `DA_HOAN_THANH` khi chưa có hóa đơn `DA_THANH_TOAN` | Cần thanh toán hóa đơn trước khi hoàn tất đơn. | `DatPhongService::updateStatus` | `DatPhongService::updateStatus` ✅ |
| R17 | 156 | lockRoom: phòng không tồn tại | Phòng không tồn tại. | `DatPhongService::lockRoom` (`lockForUpdate`) | `DatPhongService::lockRoom` ✅ |
| R18 | 162 | lockBooking: đơn không tồn tại | Đơn đặt phòng không tồn tại. | `DatPhongService::lockBooking` (`lockForUpdate`) | `DatPhongService::lockBooking` ✅ |
| R19 | 171 | Phòng `Bảo trì` hoặc loại phòng `DaXoa` | Phòng đang bảo trì hoặc loại phòng đã ngừng sử dụng. | `DatPhongService::assertAvailable` | `DatPhongService::assertAvailable` ✅ |
| R20 | 174 | Trùng lịch (đơn chờ duyệt/đã xác nhận/đang ở, `NgayNhan < trả` và `NgayTra > nhận`) | Phòng đã có lịch đặt trong khoảng ngày này. Vui lòng chọn ngày hoặc phòng khác. | `DatPhongService::assertAvailable` | `DatPhongService::assertAvailable` ✅ |
| R21 | 179 | Sửa dịch vụ khi đơn không ở `DA_XAC_NHAN`/`DANG_SU_DUNG` | Chỉ thêm hoặc sửa dịch vụ cho đơn đã xác nhận hoặc đang sử dụng. | `DatPhongService::assertServiceEditable` | `DatPhongService::assertServiceEditable` ✅ |
| R22 | 182 | Sửa dịch vụ khi đơn đã có hóa đơn `DA_THANH_TOAN` | Không được sửa dịch vụ của hóa đơn đã thanh toán. | `DatPhongService::assertServiceEditable` | `DatPhongService::assertServiceEditable` ✅ |

### `models/CtDichVuModel.php`
| # | Dòng | Điều kiện | Thông báo | Dự kiến | Đã chuyển sang |
|---|---|---|---|---|---|
| R23 | 19 | save: số lượng < 1 hoặc > 10.000 (kể cả tổng sau khi cộng dồn) | Số lượng dịch vụ phải từ 1 đến 10.000. | Form Request (`between:1,10000`) + `CtDichVuService::save` | `Admin\ThemDichVuVaoDonRequest` / `CtDichVuRequest` (`min:1`) + `CtDichVuService::save` (`> 10.000`) ✅ |
| R24 | 23 | Dịch vụ không tồn tại | Dịch vụ không tồn tại. | `CtDichVuService::save` | `CtDichVuService::save` ✅ |
| R25 | 27 | Tổng tiền dịch vụ > 2.147.483.647 | Tổng tiền dịch vụ vượt giới hạn cho phép. | `CtDichVuService::save` | `CtDichVuService::save` ✅ |
| R26 | 43 | addToBooking: số lượng < 1 | Số lượng dịch vụ phải lớn hơn 0. | Form Request (`min:1`) + `CtDichVuService::addToBooking` | `Admin\ThemDichVuVaoDonRequest` (`min:1`) + `CtDichVuService::addToBooking` ✅ |

### `models/DanhGiaModel.php`
| # | Dòng | Điều kiện | Thông báo | Dự kiến | Đã chuyển sang |
|---|---|---|---|---|---|
| R27 | 48 | create: nội dung rỗng/>255 hoặc ngày sai | Đánh giá cần nội dung tối đa 255 ký tự và ngày hợp lệ. | `DanhGiaRequest` / `ReviewRequest` | `Admin\DanhGiaRequest` ✅ (gửi đánh giá phía khách: GĐ 5) |
| R28 | 51 | Đánh giá gắn đơn: đơn phải `DA_HOAN_THANH`, đúng khách, đúng phòng | Chỉ đánh giá lần lưu trú đã hoàn tất của chính bạn. | Model `DanhGia` / `ReviewController@store` (trong transaction, khóa đơn) | `DanhGia::taoDanhGia` ✅ |
| R29 | 54 | Đơn đã có đánh giá | Bạn đã đánh giá lần lưu trú này. | như R28 | `DanhGia::taoDanhGia` ✅ |
| R30 | 80 | update: như R27 | Đánh giá cần nội dung tối đa 255 ký tự và ngày hợp lệ. | `DanhGiaRequest` | `Admin\DanhGiaRequest` ✅ |
| R31 | 82 | update: đánh giá gắn đơn không được đổi phòng | Không thể chuyển đánh giá của khách sang phòng khác. | `DanhGiaController@update` / model | `DanhGia::capNhat` ✅ |

### `models/HoaDonModel.php`
| # | Dòng | Điều kiện | Thông báo | Dự kiến | Đã chuyển sang |
|---|---|---|---|---|---|
| R32 | 105 | Lập hóa đơn khi đơn không ở `DANG_SU_DUNG`/`DA_XAC_NHAN` | Không thể lập hóa đơn cho đơn chưa xác nhận, đã hủy hoặc đã kết thúc. | `HoaDonService::createOrUpdateInvoice` | `HoaDonService::createOrUpdateInvoice` ✅ |
| R33 | 108 | Tổng hóa đơn > 2.147.483.647 (lập) | Tổng hóa đơn vượt giới hạn cho phép. | `HoaDonService::createOrUpdateInvoice` | `HoaDonService::createOrUpdateInvoice` ✅ |
| R34 | 126 | Thanh toán: hóa đơn không tồn tại | Hóa đơn không tồn tại. | `HoaDonService::payInvoice` | `HoaDonService::payInvoice` ✅ |
| R35 | 132 | Đơn đã thanh toán bằng hóa đơn khác | Đơn này đã được thanh toán bằng hóa đơn khác. | `HoaDonService::payInvoice` | `HoaDonService::payInvoice` ✅ |
| R36 | 133 | Thanh toán khi đơn không ở `DANG_SU_DUNG` | Chỉ thanh toán trả phòng cho đơn đang sử dụng. | `HoaDonService::payInvoice` | `HoaDonService::payInvoice` ✅ |
| R37 | 136 | Tổng hóa đơn > 2.147.483.647 (thanh toán) | Tổng hóa đơn vượt giới hạn cho phép. | `HoaDonService::payInvoice` | `HoaDonService::payInvoice` ✅ |

### `models/KhachHangModel.php`
| # | Dòng | Điều kiện | Thông báo | Dự kiến | Đã chuyển sang |
|---|---|---|---|---|---|
| R38 | 52 | Tạo khách: SĐT hoặc email (khác rỗng) đã có | Số điện thoại hoặc email đã được đăng ký. Vui lòng đăng nhập tài khoản này. | Model `KhachHang` (tạo khách, dùng cho đăng ký + khách vãng lai) | `KhachHang::taoKhachHang` ✅ |
| R39 | 73 | Cập nhật khách: SĐT/email thuộc tài khoản khác | Số điện thoại hoặc email đã thuộc tài khoản khác. | Model `KhachHang` (cập nhật khi đặt phòng) | `KhachHang::capNhatThongTin` ✅ |

### `models/DichVuModel.php`
| # | Dòng | Điều kiện | Thông báo | Dự kiến | Đã chuyển sang |
|---|---|---|---|---|---|
| R40 | 73 | Tên rỗng/>50 hoặc giá ≤ 0 / > 2.147.483.647 | Tên dịch vụ tối đa 50 ký tự và giá phải lớn hơn 0. | `DichVuRequest` | `Admin\DichVuRequest` ✅ |

### `models/NhanVienModel.php`
| # | Dòng | Điều kiện | Thông báo | Dự kiến | Đã chuyển sang |
|---|---|---|---|---|---|
| R41 | 34 | Thêm: username rỗng/>20 hoặc mật khẩu rỗng | Tên đăng nhập tối đa 20 ký tự và mật khẩu không được để trống. | `StoreNhanVienRequest` | `Admin\StoreNhanVienRequest` (`required`, `max:20`) ✅ |
| R42 | 37 | Thêm: username đã tồn tại | Tên đăng nhập đã tồn tại. | `StoreNhanVienRequest` (`unique:NHANVIEN,Username`) | `Admin\StoreNhanVienRequest` (`unique:NHANVIEN,Username`) ✅ |
| R43 | 58 | Sửa: tự đổi chức vụ của chính mình | Không được tự thay đổi chức vụ của tài khoản đang đăng nhập. | `NhanVienController@update` (so với `auth('admin')->user()`) | `Admin\NhanVienController@update` ✅ |
| R44 | 93 | Xóa chính mình | Không được xóa tài khoản đang đăng nhập. | `NhanVienController@destroy` | `Admin\NhanVienController@destroy` ✅ |
| R45 | 127 | Họ tên rỗng/>100 hoặc CMND >20 | Họ tên hoặc số giấy tờ nhân viên không hợp lệ. | `Store/UpdateNhanVienRequest` | `Admin\StoreNhanVienRequest::rulesThongTin()` (dùng cho thêm + sửa) ✅ |
| R46 | 128 | Ngày sinh sai/ở tương lai, ngày vào làm sai | Ngày sinh hoặc ngày vào làm không hợp lệ. | `Store/UpdateNhanVienRequest` (`date`, `before_or_equal:today`) | `Admin\StoreNhanVienRequest::rulesThongTin()` (`date_format:Y-m-d`, `before_or_equal:today`) ✅ |

### `models/PhanCongModel.php`
| # | Dòng | Điều kiện | Thông báo | Dự kiến | Đã chuyển sang |
|---|---|---|---|---|---|
| R47 | 67 | Thêm: ngày sai, nội dung rỗng/>50 | Ngày hoặc nội dung phân công không hợp lệ (tối đa 50 ký tự). | `StorePhanCongRequest` | `Admin\PhanCongRequest` (POST) ✅ |
| R48 | 85 | Sửa: như R47 | Ngày hoặc nội dung phân công không hợp lệ (tối đa 50 ký tự). | `UpdatePhanCongRequest` | `Admin\PhanCongRequest` (PUT) ✅ |

### `models/LoaiPhongModel.php`
| # | Dòng | Điều kiện | Thông báo | Dự kiến | Đã chuyển sang |
|---|---|---|---|---|---|
| R49 | 65 | Tên rỗng/>20, giá ≤ 0 / > 2.147.483.647, mô tả >255 | Tên loại phòng tối đa 20 ký tự, mô tả tối đa 255 ký tự và giá phải lớn hơn 0. | `LoaiPhongRequest` | `Admin\LoaiPhongRequest` ✅ |

### `models/PhongModel.php`
| # | Dòng | Điều kiện | Thông báo | Dự kiến | Đã chuyển sang |
|---|---|---|---|---|---|
| R50 | 99 | Sửa: phòng không tồn tại | Phòng không tồn tại. | `PhongController@update` | `Route::resource('phong')->missing()` → "Không tìm thấy thông tin phòng." ✅ |
| R51 | 102 | Sửa: phòng đang có đơn hoạt động mà đổi loại hoặc trạng thái | Phòng đang có lịch đặt; hãy xử lý đơn trước khi đổi loại hoặc trạng thái phòng. | Model `Phong` / `PhongController@update` | `Phong::kiemTraDoiLoaiHoacTrangThai` ✅ |
| R52 | 158 | Trạng thái ngoài `Trống, Đã đặt, Đang thuê, Bảo trì` | Trạng thái phòng không hợp lệ. | `PhongRequest` (`in:`) — kèm sửa lỗi L1 | `Admin\StorePhongRequest` / `UpdatePhongRequest` (`Rule::in(Phong::TRANG_THAI)`) ✅ |
| R53 | 161 | Loại phòng không tồn tại hoặc `DaXoa` | Loại phòng không tồn tại hoặc đã ngừng sử dụng. | `PhongRequest` (`exists` có điều kiện `DaXoa = 0`) | `Admin\StorePhongRequest` / `UpdatePhongRequest` (`exists` + `DaXoa = 0`) ✅ |

---

## 3. Kiểm tra ở controller và thông báo cần giữ nguyên (không phải `DomainException`)

| Nơi gốc | Thông báo / hành vi |
|---|---|
| `config/auth.php` | Khách bị khóa đăng nhập: flash danger "Tài khoản của bạn đã bị khóa. Vui lòng liên hệ khách sạn."; khách đang đăng nhập mà bị khóa/bị xóa → tự đăng xuất ở request kế tiếp. Admin bị xóa → tự đăng xuất. Chức vụ "Quản lý" có toàn quyền. Trang 403: "403 - Truy cập bị từ chối" / "Bạn không có quyền thực hiện chức năng này (MÃ_QUYỀN)." / link "Quay lại bảng điều khiển". |
| `admin/AuthController` | "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu." · "Tài khoản hoặc mật khẩu không chính xác." · flash "Đăng nhập quản trị thành công! Chào mừng bạn trở lại." · đổi MK: "Mật khẩu cũ không chính xác." / "Vui lòng nhập mật khẩu mới." / "Xác nhận mật khẩu mới không khớp." / "Mật khẩu mới phải khác mật khẩu hiện tại." / flash "Đổi mật khẩu thành công!" → dashboard · đăng xuất: "Đã đăng xuất thành công." |
| `admin/ChucVuController` | "Cập nhật quyền thành công cho chức vụ #N." |
| `admin/CtDichVuController` | "Vui lòng chọn dịch vụ, đơn đặt phòng và số lượng hợp lệ (> 0)." · "Thêm chi tiết dịch vụ thành công!" · "Không tìm thấy đơn đặt phòng." · "Đã thêm dịch vụ vào đơn thành công." · "Thông tin dịch vụ hoặc số lượng không hợp lệ." · "Đã xóa chi tiết dịch vụ." |
| `admin/DanhGiaController` | "Vui lòng nhập nội dung đánh giá và chọn phòng." · "Thêm đánh giá mới thành công!" · "Không tìm thấy đánh giá." · "Cập nhật đánh giá thành công!" · "Đã xóa đánh giá thành công." |
| `admin/DashboardController` | Cần quyền DASHBOARD. |
| `admin/DatPhongController` | "Không tìm thấy đơn đặt phòng." · "Đã duyệt đơn đặt phòng #N thành công." · "Khách đã nhận phòng (Check-in) cho đơn #N." · "Đã tạo hóa đơn cho đơn đặt phòng #N. Vui lòng kiểm tra và xác nhận thanh toán." (→ chi tiết hóa đơn) · "Đã hủy đơn đặt phòng #N." (→ danh sách) · "Đã thêm dịch vụ vào đơn đặt phòng." · "Đã xóa dịch vụ khỏi đơn." · tạo đơn: "Vui lòng điền đầy đủ khách hàng, phòng, ngày nhận và ngày trả." / "Ngày trả phòng phải sau ngày nhận phòng." / "Tạo đơn đặt phòng #N thành công!" (→ chi tiết). Duyệt/nhận phòng ghi `MaNhanVien` = admin hiện tại; hủy thì không. |
| `admin/DichVuController` | "Vui lòng nhập tên dịch vụ và đơn giá hợp lệ." · "Đã thêm dịch vụ \"Tên\" thành công!" · "Không tìm thấy dịch vụ." · "Cập nhật dịch vụ \"Tên\" thành công!" · "Đã xóa dịch vụ thành công." · Ảnh: file upload ưu tiên, nếu không thì lấy ô nhập đường dẫn. |
| `admin/HoaDonController` | "Hóa đơn không tồn tại." · "Xác nhận thanh toán hóa đơn #N thành công! Phòng đã được giải phóng về trạng thái Trống." |
| `admin/KhachHangController` | "Đã thay đổi trạng thái tài khoản khách hàng #N." · "Khách hàng không tồn tại." |
| `admin/LoaiPhongController` | "Vui lòng nhập tên loại phòng và đơn giá hợp lệ." · "Đã thêm loại phòng \"Tên\" thành công!" · "Loại phòng không tồn tại." · "Cập nhật loại phòng \"Tên\" thành công!" · "Đã xóa loại phòng thành công." · Xóa: còn phòng dùng → `DaXoa = 1`, không có → xóa hẳn. Danh sách chỉ hiện loại chưa xóa. |
| `admin/NhanVienController` | "Vui lòng điền đầy đủ tài khoản, mật khẩu, họ tên và chức vụ." · "Thêm nhân viên \"Tên\" thành công!" · "Nhân viên không tồn tại." · "Vui lòng nhập họ tên và chọn chức vụ." · "Cập nhật nhân viên \"Tên\" thành công!" · "Đã xóa nhân viên." · Ngày vào làm trống khi thêm → hôm nay. |
| `admin/PhanCongController` | "Vui lòng chọn ngày phân công và nhập loại công việc." · chỉ khi thêm: "Không được phân công ngày trong quá khứ." · "Thêm phân công công việc thành công!" · "Không tìm thấy phân công." · "Cập nhật phân công thành công!" · "Đã xóa phân công công việc." |
| `admin/PhongController` | "Vui lòng nhập mã phòng và chọn loại phòng hợp lệ." · "Mã phòng N đã tồn tại trên hệ thống." · "Thêm phòng N thành công!" · "Không tìm thấy thông tin phòng." · "Vui lòng chọn loại phòng hợp lệ." · "Cập nhật thông tin phòng N thành công!" · "Đã xóa phòng N thành công." · Lọc ngày (admin + khách): "Vui lòng chọn đủ ngày nhận/trả hợp lệ; ngày trả phải sau ngày nhận." |
| `admin/ThongKeController` | `?year` mặc định năm hiện tại; ô chọn 5 năm gần nhất. |
| `client/AccountController` | "Vui lòng nhập số điện thoại/email và mật khẩu." · "Thông tin đăng nhập hoặc mật khẩu không chính xác." · "Đăng nhập thành công! Chào mừng quý khách." (→ trang chủ) · đăng ký: "Vui lòng điền họ tên, số điện thoại và mật khẩu." / "Số điện thoại này đã được đăng ký tài khoản." / "Đăng ký tài khoản thành công!" (tự đăng nhập, → trang chủ) · "Không tìm thấy đơn đặt phòng hoặc bạn không có quyền truy cập." · "Đã đăng xuất tài khoản." (xóa luôn `guest_booking_ids`, `guest_customer_id`). |
| `client/BookingController` | "Vui lòng chọn phòng hợp lệ để đặt." (→ danh sách phòng) · "Vui lòng điền đầy đủ họ tên, số điện thoại, ngày nhận và ngày trả." · Mặc định ngày nhận = hôm nay, ngày trả = mai. Khách đăng nhập/khách vãng lai cũ (chưa có mật khẩu) → cập nhật thông tin; khách mới → tạo `KHACHHANG` không mật khẩu; lưu session `guest_booking_ids[id]`, `guest_customer_id`. Trang thành công chỉ cho khách vãng lai có id trong session hoặc chủ đơn, ngược lại → trang chủ. |
| `client/ReviewController` | "Vui lòng chọn phòng cần đánh giá." · "Vui lòng nhập nội dung đánh giá." · "Bạn chỉ được đánh giá phòng đã đặt và đã hoàn thành ngày trả phòng." · "Cảm ơn bạn đã gửi đánh giá! Ý kiến của bạn giúp chúng tôi nâng cao chất lượng dịch vụ." |
| `client/RoomController` | "Không tìm thấy thông tin phòng." (→ danh sách phòng) |
| `client/HomeController` | Trang chủ lấy 6 phòng đầu (`ORDER BY MaPhong`) + tất cả dịch vụ. |

---

## 4. View gốc → Blade mới

| View gốc (`hotel_php/views/...`) | Blade mới (`resources/views/...`) | Xong |
|---|---|---|
| `admin/layouts/header.php` + `nav.php` + `footer.php` | `admin/layouts/app.blade.php` + `admin/layouts/nav.blade.php` (ẩn nav khi `hideNav`) | ✅ |
| `admin/auth/login.php` | `admin/auth/login.blade.php` | ✅ |
| `admin/auth/change-password.php` | `admin/auth/change-password.blade.php` | ✅ |
| `admin/dashboard/index.php` | `admin/dashboard/index.blade.php` | ⬜ |
| `admin/phong/list.php` | `admin/phong/index.blade.php` | ✅ |
| `admin/phong/form.php` | `admin/phong/form.blade.php` | ✅ |
| `admin/loai-phong/list.php` | `admin/loai-phong/index.blade.php` | ✅ |
| `admin/loai-phong/form.php` | `admin/loai-phong/form.blade.php` | ✅ |
| `admin/dich-vu/list.php` | `admin/dich-vu/index.blade.php` | ✅ |
| `admin/dich-vu/form.php` | `admin/dich-vu/form.blade.php` | ✅ |
| `admin/khach-hang/list.php` | `admin/khach-hang/index.blade.php` | ✅ |
| `admin/khach-hang/history.php` | `admin/khach-hang/history.blade.php` | ✅ |
| `admin/nhan-vien/list.php` | `admin/nhan-vien/index.blade.php` | ✅ |
| `admin/nhan-vien/form.php` | `admin/nhan-vien/form.blade.php` | ✅ |
| `admin/chuc-vu/list.php` | `admin/chuc-vu/index.blade.php` | ✅ |
| `admin/phan-cong/list.php` (dùng cho cả `list` và `homNay`) | `admin/phan-cong/index.blade.php` | ✅ |
| `admin/phan-cong/form.php` | `admin/phan-cong/form.blade.php` | ✅ |
| `admin/danh-gia/list.php` | `admin/danh-gia/index.blade.php` | ✅ |
| `admin/danh-gia/form.php` | `admin/danh-gia/form.blade.php` | ✅ |
| `admin/dat-phong/list.php` | `admin/dat-phong/index.blade.php` | ✅ |
| `admin/dat-phong/form.php` | `admin/dat-phong/create.blade.php` | ✅ |
| `admin/dat-phong/detail.php` | `admin/dat-phong/show.blade.php` | ✅ |
| `admin/ct-dich-vu/list.php` | `admin/ct-dich-vu/index.blade.php` | ✅ |
| `admin/ct-dich-vu/form.php` | `admin/ct-dich-vu/create.blade.php` | ✅ |
| `admin/ct-dich-vu/them-vao-don.php` | `admin/ct-dich-vu/them-vao-don.blade.php` | ✅ |
| `admin/hoa-don/list.php` | `admin/hoa-don/index.blade.php` | ✅ |
| `admin/hoa-don/detail.php` | `admin/hoa-don/show.blade.php` | ✅ |
| `admin/thong-ke/index.php` | `admin/thong-ke/index.blade.php` | ⬜ |
| (HTML 403 trong `Auth::requirePermission`) | `errors/403.blade.php` | ✅ |
| (thông báo CSRF trong `guard_request`) | `errors/419.blade.php` | ✅ |
| `client/layouts/header.php` + `footer.php` | `client/layouts/app.blade.php` | ✅ |
| `client/home.php` | `client/home.blade.php` | ⬜ |
| `client/contact.php` | `client/contact.blade.php` | ⬜ |
| `client/rooms.php` | `client/rooms.blade.php` | ⬜ |
| `client/room-detail.php` | `client/room-detail.blade.php` | ⬜ |
| `client/services.php` | `client/services.blade.php` | ⬜ |
| `client/reviews.php` | `client/reviews.blade.php` | ⬜ |
| `client/booking.php` | `client/booking.blade.php` | ⬜ |
| `client/booking-success.php` | `client/booking-success.blade.php` | ⬜ |
| `client/account/login.php` | `client/account/login.blade.php` | ✅ |
| `client/account/register.php` | `client/account/register.blade.php` | ✅ |
| `client/account/profile.php` | `client/account/profile.blade.php` | ⬜ |
| `client/account/history.php` | `client/account/history.blade.php` | ⬜ |
| `client/account/booking-detail.php` | `client/account/booking-detail.blade.php` | ⬜ |

Quy ước Blade: lỗi Form Request hiển thị đúng chỗ `alert alert-danger` cũ bằng `$errors->first()`; giá trị cũ dùng `old()`; form đổi dữ liệu dùng `@csrf` + `@method`.

### Ánh xạ file PHP gốc khác (để viết README ở Giai đoạn 7)
| File gốc | Laravel |
|---|---|
| `index.php`, `admin/index.php` | `routes/web.php` |
| `config/database.php` | `.env`, `config/database.php` (timezone `+07:00`) |
| `config/auth.php` | `config/auth.php` (guard `admin`, `web`), middleware `quyen`, `bootstrap/app.php` |
| `config/helpers.php` | `app/helpers.php` (`format_currency`, `format_date`), Form Request, `withExceptions()` |
| `models/*Model.php` | `app/Models/*` + `app/Services/*` (DatPhong, HoaDon, CtDichVu) |
| `controllers/admin/*`, `controllers/client/*` | `app/Http/Controllers/Admin/*`, `app/Http/Controllers/Client/*` |
| `QuanLyKhachSan.sql` | `database/migrations/*`, `database/seeders/*` |
| `css/app.css`, `Images/`, `uploads/` | `public/css/app.css`, `public/Images/`, `public/uploads/` |

---

## 5. Lỗi phát hiện trong bản gốc

| # | Mô tả | Đề xuất | Trạng thái |
|---|---|---|---|
| L1 | `views/admin/phong/form.php` có lựa chọn "Đang sử dụng" nhưng `PhongModel` chỉ nhận 4 trạng thái → lưu báo "Trạng thái phòng không hợp lệ." (mục 5 kế hoạch). | Form chỉ còn `Trống, Đã đặt, Đang thuê, Bảo trì`; thống kê/badge vẫn đếm "Đang sử dụng". | Đã duyệt sẵn, sửa ở 4A |
| L2 | `views/client/account/booking-detail.php`: bảng màu/nhãn trạng thái dùng `DA_THANH_TOAN`, `DA_TRA_PHONG` (không tồn tại) và thiếu `DA_HOAN_THANH` → đơn hoàn tất hiện mã thô "DA_HOAN_THANH" với badge xám. | Thêm `DA_HOAN_THANH` → "Hoàn tất", `bg-success` (giống `history.php`). | Đã duyệt: sửa ở GĐ 5 |
| L3 | `views/client/account/history.php`: dòng "chưa có đơn" dùng `colspan="5"` nhưng bảng có 6 cột. | Đổi thành `colspan="6"`. | Đã duyệt: sửa ở GĐ 5 |
| L4 | `views/admin/khach-hang/history.php` hiện mã trạng thái thô (vd. `DA_XAC_NHAN`) trong badge xám. | Giữ nguyên (chỉ là hiển thị), trừ khi muốn dùng nhãn tiếng Việt. | Đã duyệt: giữ nguyên |
| L5 | Trang `ct-dich-vu&a=addToBooking` (`them-vao-don.php`) không được link từ đâu; tham số `back=detail` của `ct-dich-vu&a=delete` cũng không view nào dùng. | Vẫn giữ route và tham số để đủ chức năng gốc. | Ghi nhận |
| L6 | Đăng nhập admin luôn chuyển về dashboard; chức vụ không có quyền DASHBOARD sẽ thấy 403 ngay sau đăng nhập (dữ liệu mẫu: cả 4 chức vụ đều có DASHBOARD nên không gặp). | Giữ nguyên. | Ghi nhận |
| L7 | `admin/index.php` với `c` không hợp lệ → chuyển về dashboard. Laravel mặc định trả 404. | Đề xuất dùng 404 chuẩn của Laravel (hoặc thêm route bắt `/admin/{any}` chuyển về dashboard nếu muốn giống hệt). | Đã duyệt: dùng 404 |
| L8 | Trang chủ / danh sách phòng (khi không lọc ngày) vẫn hiện phòng thuộc loại phòng đã xóa mềm, có nút "Đặt phòng"; khi gửi thì bị chặn bằng R19. | Giữ nguyên như bản gốc. | Ghi nhận |
| L9 | Admin tạo đơn với trạng thái "Khách nhận phòng ngay" (`DANG_SU_DUNG`) không chạy kiểm tra R15 "Phòng còn khách chưa trả phòng" (chỉ có ở `updateStatus`). Nếu khách cũ ở quá ngày trả mà vẫn `DANG_SU_DUNG`, có thể check-in khách mới cùng phòng. | Giữ nguyên, hoặc thêm R15 vào `createBooking` khi trạng thái là `DANG_SU_DUNG`. | Đã duyệt: giữ nguyên |
| L10 | Kế hoạch Giai đoạn 5 ghi "chi tiết phòng (ảnh + đánh giá)", nhưng `room-detail.php` gốc chỉ hiện 1 ảnh đại diện, không có thư viện ảnh và không có đánh giá. | Làm đúng như bản gốc (chỉ ảnh đại diện). | Đã duyệt: như bản gốc |
