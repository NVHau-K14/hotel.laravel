# KẾ HOẠCH CHUYỂN `hotel_php` SANG LARAVEL (`hotel.laravel`)

> File này là chỉ thị chính cho AI agent. Đọc toàn bộ file trước khi làm bất cứ việc gì.
> Mỗi khi bắt đầu phiên làm việc mới, đọc lại file này và `docs/CHECKLIST.md` để biết đang ở giai đoạn nào.

---

## 1. Bối cảnh

Đây là bài tập nhóm môn Lập trình Web PHP + Laravel. Dự án **Quản lý khách sạn** ban đầu viết bằng C# (MVC), sau đó đã được chuyển sang **PHP thuần theo mô hình MVC tự viết** trong thư mục `hotel_php`. Nhiệm vụ: chuyển `hotel_php` sang **Laravel**, giữ nguyên chức năng và giao diện, viết theo đúng cách làm của Laravel.

**Môi trường (Windows, Laragon):**
- PHP 8.3.30, Laravel Framework 13.35.0 (đã kiểm tra bằng `php artisan --version`)
- MySQL 8.4.3, cổng 3306, user `root`, mật khẩu rỗng
- Apache 2.4 (Laragon), quản lý DB bằng HeidiSQL
- Terminal: PowerShell trong VS Code
- Project Laravel: `C:\laragon\www\hotel.laravel` (mới tạo, gần như trống, đang dùng SQLite mặc định)
- Code nguồn tham chiếu: `C:\laragon\www\hotel_php` (nằm trong cùng workspace VS Code; nếu đường dẫn khác thì tìm và báo lại)
- File SQL gốc: `hotel_php/QuanLyKhachSan.sql`

**Yêu cầu của giảng viên:** bắt buộc dùng **Migration, Eloquent, Blade, Form Request validation**. Sản phẩm là một website hoàn chỉnh.

**Tình trạng `hotel_php`:** đã được chạy thử với file SQL trên MySQL. Toàn bộ 34 trang admin và 12 trang khách hàng chạy không lỗi PHP. Luồng nghiệp vụ đầy đủ (khách đặt → duyệt → nhận phòng → thêm dịch vụ → trả phòng → thanh toán) cho kết quả đúng. **Hãy coi `hotel_php` là bản chuẩn về hành vi**: Laravel phải cho ra cùng kết quả.

---

## 2. Quy tắc làm việc (bắt buộc)

1. **KHÔNG sửa, xóa, di chuyển bất kỳ file nào trong `hotel_php`.** Chỉ đọc.
2. Làm **từng giai đoạn** theo mục 6. Hết mỗi giai đoạn thì:
   - chạy các bước kiểm tra của giai đoạn đó và ghi kết quả thật (không ghi "chắc là chạy được");
   - cập nhật `docs/CHECKLIST.md`;
   - `git add -A` rồi `git commit -m "Giai đoạn N: ..."`;
   - **DỪNG LẠI**, báo cáo ngắn gọn bằng tiếng Việt (đã làm gì, kiểm tra ra sao, tôi cần tự kiểm tra gì trên trình duyệt) và **chờ tôi gõ "tiếp"**.
3. Trước khi viết lại một chức năng, **đọc đủ controller + model + view tương ứng trong `hotel_php`**. Không đoán.
4. **Giữ nguyên mọi quy tắc nghiệp vụ.** Trong `hotel_php` có **53 chỗ `throw new DomainException(...)`**. Mỗi chỗ là một quy tắc (ví dụ: không xóa tài khoản đang đăng nhập, không đặt trùng lịch phòng, ngày trả phải sau ngày nhận…). Ở Giai đoạn 0, liệt kê **tất cả** vào `docs/CHECKLIST.md`. Mỗi quy tắc phải được đánh dấu đã chuyển sang đâu (Form Request, model, service…).
5. Giữ nguyên **thông báo tiếng Việt** (flash, lỗi) như bản gốc.
6. Không cài thêm package Composer/npm nếu không thật cần. Nếu cần thì hỏi tôi trước. **Không dùng Breeze/Jetstream/Fortify.**
7. Nếu phát hiện lỗi trong bản gốc thì ghi vào mục "Lỗi phát hiện trong bản gốc" của `docs/CHECKLIST.md` và hỏi tôi trước khi đổi hành vi. Ngoại lệ: các lỗi đã liệt kê ở mục 5 thì sửa luôn.
8. Nếu `CLAUDE.md` / `AGENTS.md` của project có hướng dẫn trái với file này thì **file này được ưu tiên** về các quyết định ở mục 3. Có gì không chắc thì hỏi tôi.
9. Code viết theo phong cách Laravel chuẩn, dễ đọc cho sinh viên. Chú thích ngắn bằng tiếng Việt ở những chỗ khó.

---

## 3. Các quyết định đã chốt (không tự ý đổi)

| # | Quyết định |
|---|---|
| 1 | **Giữ nguyên tên bảng và tên cột tiếng Việt** đúng như file SQL (`PHONG`, `MaPhong`, `CTDATPHONG`…). Mỗi Model khai báo `$table`, `$primaryKey`, `$timestamps = false`, `$fillable`. |
| 2 | **Đăng nhập tự viết bằng Laravel Auth với 2 guard:** `admin` (provider Eloquent → model `NhanVien`) và `web` (provider Eloquent → model `KhachHang`). Phân quyền bằng **middleware** kiểm tra mã quyền. |
| 3 | **Database tạo bằng Migration + Seeder.** Lệnh `php artisan migrate:fresh --seed` phải dựng lại đầy đủ DB và dữ liệu mẫu. |
| 4 | **URL kiểu Laravel:** dùng named routes, prefix `/admin` cho trang quản trị, URL tiếng Việt không dấu (vd. `/admin/loai-phong`, `/admin/dat-phong/{id}`, `/phong/{maPhong}`, `/tai-khoan/dang-nhap`). Thao tác làm thay đổi dữ liệu dùng POST/PUT/DELETE, không dùng GET. |
| 5 | **Validation bằng Form Request** (thư mục `app/Http/Requests`), thông báo lỗi tiếng Việt qua `messages()` / `attributes()`. **View dùng Blade** (`@extends`, `@section`, `@include`, `@csrf`, `@method`), giữ nguyên giao diện Bootstrap của bản gốc. |
| 6 | **MySQL, database tên `hotel_laravel`** (dấu gạch dưới, utf8mb4 / utf8mb4_unicode_ci). |
| 7 | **Dùng Git**, commit sau mỗi giai đoạn. |

---

## 4. Những điều đã biết về `hotel_php` (đọc kỹ, đây là các chỗ dễ sai)

### 4.1 Cấu trúc bản gốc
- Router: `index.php` (khách hàng) và `admin/index.php` (quản trị), điều hướng bằng `?c=controller&a=action`.
- `config/auth.php`: class `Auth` (đăng nhập 2 phía, phân quyền). `config/helpers.php`: CSRF, flash, `format_currency`, `format_date`, `upload_image`, `validate_stay`, `validate_customer`, xử lý lỗi. `config/database.php`: PDO, timezone `+07:00`, `db_transaction()`.
- Model là class tĩnh viết SQL bằng PDO. Controller gọi `render_admin_view()` / `render_client_view()` (header + nav + view + footer).
- Giao diện: Bootstrap 5.3.3, Font Awesome 6.5.1, Chart.js, **tất cả qua CDN** → **không cần Node/npm/Vite**. CSS riêng: `hotel_php/css/app.css`.
- **Thư mục `hotel_php/static/` là bản sao thừa, không được code dùng → bỏ qua.**

### 4.2 Database (13 bảng trong file SQL)
`CHUCVU`, `CHUCVU_QUYEN`, `NHANVIEN`, `BANGPHANCONG`, `KHACHHANG`, `LOAIPHONG`, `PHONG`, `CTDATPHONG`, `PAYMENTS`, `CTDANHGIA`, `HOADON`, `DICHVU`, `CTDICHVU`.

Những điểm đặc biệt phải xử lý đúng:
- **Không bảng nào có `created_at/updated_at`** (trừ `PAYMENTS.created_at`), nên mọi model đặt `$timestamps = false`.
- **Kiểu khóa:** SQL gốc dùng `INT`. Trong migration dùng `increments('Ma...')` cho khóa tự tăng và `unsignedInteger(...)` cho khóa ngoại để kiểu khớp nhau. **Không dùng `id()` / `foreignId()`** vì chúng tạo BIGINT. Riêng `CTDANHGIA` có khóa chính tên `ID`; `PAYMENTS` có khóa chính tên `id`.
- **`PHONG.MaPhong` KHÔNG tự tăng** (do người dùng nhập: 101, 102…), nên model `Phong` đặt `$incrementing = false`.
- **Khóa chính ghép:**
  - `CHUCVU_QUYEN (MaChucVu, MaQuyen)`: `MaQuyen` là chuỗi, không có bảng quyền riêng. Đọc/ghi bằng query builder hoặc quan hệ `hasMany`. Không dùng `save()` trên bản ghi đơn lẻ.
  - `CTDICHVU (MaDichVu, MaCTDatPhong)`: `MaCTDichVu` chỉ là số hiển thị, bản gốc tự tính `MAX(MaCTDichVu)+1`. Eloquent không hỗ trợ khóa ghép, nên **cập nhật/xóa bằng `where('MaDichVu',…)->where('MaCTDatPhong',…)`**. Có thể dùng `belongsToMany` giữa `DatPhong` và `DichVu` qua bảng `CTDICHVU` kèm `withPivot('MaCTDichVu','SoLuong','TongTienDichVu')`.
- **`KHACHHANG.EmailConnexion`** là **cột sinh tự động (generated column)** `NULLIF(Email,'')` có UNIQUE. Migration dùng `->storedAs("NULLIF(`Email`, '')")`. **Tuyệt đối không đưa cột này vào `$fillable` hay ghi vào nó.**
- **`LOAIPHONG.DaXoa`** là cờ xóa mềm tự viết (BOOLEAN). **Không dùng trait `SoftDeletes`** của Laravel. Giữ đúng logic gốc.
- **Khóa ngoại:** giữ đúng như SQL. `CHUCVU_QUYEN → CHUCVU` có `ON DELETE CASCADE`, các khóa khác không có cascade.
- **Giá trị mặc định:** `PHONG.TrangThai='Trống'`, `KHACHHANG.TrangThai='HOAT_DONG'`, `CTDATPHONG.TrangThai='CHO_ADMIN_DUYET'`, `CTDATPHONG.payment_status='CHUA_TAO'`, `HOADON.TrangThai='CHUA_THANH_TOAN'`, `CTDANHGIA.SoSao=5`, `PAYMENTS.method='VIETQR'`, `PAYMENTS.status='CHO_THANH_TOAN'`, `LOAIPHONG.DaXoa=false`.
- **Bảng `PAYMENTS` và các cột `payment_requested_at`, `payment_expired_at`** (VietQR) có trong SQL **nhưng code PHP không dùng**. Vẫn tạo migration cho đủ schema, không cần làm chức năng VietQR. `payment_status` thì **có dùng** (khi thanh toán hóa đơn).
- **Trạng thái đơn đặt phòng (`CTDATPHONG.TrangThai`):** `CHO_ADMIN_DUYET → DA_XAC_NHAN → DANG_SU_DUNG → DA_HOAN_THANH`, hoặc `DA_HUY`. Hóa đơn: `CHUA_THANH_TOAN` / `DA_THANH_TOAN`. Khách hàng: `HOAT_DONG` / `KHOA`. Phòng: chuỗi tiếng Việt có dấu (`Trống`, `Đã đặt`, `Đang thuê`, `Bảo trì`). Nên gom các giá trị này thành hằng số (const) trong model tương ứng.
- **`CTDATPHONG.GiaPhongDat`** lưu giá phòng tại thời điểm đặt. Khi hiển thị, bản gốc dùng `COALESCE(GiaPhongDat, GiaPhong của loại phòng)`.
- **`HOADON.ChiTietJSON`** lưu bản chụp (snapshot) chi tiết hóa đơn dạng JSON khi tạo/thanh toán. Hóa đơn đã thanh toán thì hiển thị từ snapshot. Giữ đúng logic trong `HoaDonModel.php`. Trong model cast `'ChiTietJSON' => 'array'` nếu phù hợp.
- **Đường dẫn ảnh trong DB** có dạng `/Images/ChiTietPhong/Phong101/01.jpg` và `/Images/DichVu/...`. Ảnh upload mới có dạng `/uploads/phong/<tên>.jpg` và `/uploads/dichvu/...`.

### 4.3 Đăng nhập và phân quyền (từ `config/auth.php`)
- **Admin:** bảng `NHANVIEN`, đăng nhập bằng `Username` + cột mật khẩu **`Password`**. Sau khi đăng nhập, lấy danh sách `MaQuyen` từ `CHUCVU_QUYEN` theo `MaChucVu`. **Chức vụ có `TenChucVu = 'Quản lý'` có toàn quyền.** 13 mã quyền: `DASHBOARD, NHAN_VIEN, CHUC_VU, KHACH_HANG, LOAI_PHONG, PHONG, DAT_PHONG, DICH_VU, CT_DICH_VU, HOA_DON, PHAN_CONG, DANH_GIA, THONG_KE`. Không có quyền thì trả trang 403 tiếng Việt kèm nút quay lại dashboard. Menu admin chỉ hiện mục mà người dùng có quyền.
- **Khách hàng:** bảng `KHACHHANG`, đăng nhập bằng **Email HOẶC Số điện thoại** + cột mật khẩu **`MatKhau`**. Tài khoản `TrangThai='KHOA'` thì không được đăng nhập (báo "Tài khoản của bạn đã bị khóa…"). Nếu đang đăng nhập mà bị khóa thì tự đăng xuất ở request kế tiếp.
- **Khách vãng lai đặt phòng không cần đăng nhập:** bản gốc tạo/tái dùng `KHACHHANG` không mật khẩu (`MatKhau = NULL`) và lưu `guest_booking_ids`, `guest_customer_id` trong session để khách xem lại trang đặt thành công. Giữ chức năng này (dùng `session()` của Laravel).
- Khách đăng nhập chỉ xem được đơn của chính mình.

Cách làm trong Laravel:
- `NhanVien` và `KhachHang` extends `Illuminate\Foundation\Auth\User` (Authenticatable). Khai báo tên cột mật khẩu bằng `protected $authPasswordName = 'Password'` / `'MatKhau'` (hoặc override `getAuthPassword()` nếu phiên bản yêu cầu). Ẩn mật khẩu bằng `$hidden`. **Không dùng "remember me", không thêm cột `remember_token`.**
- Cấu hình 2 guard + 2 provider trong `config/auth.php`. **Xóa `app/Models/User.php`, `UserFactory`** và các tham chiếu đến chúng.
- Đăng nhập khách bằng Email/SĐT: tự tìm bản ghi, kiểm tra `Hash::check`, kiểm tra trạng thái KHOA, rồi `Auth::guard('web')->login($khachHang)`. Không dùng `Auth::attempt` với điều kiện OR.
- **Cảnh báo về session:** hai guard dùng chung một session. Khi đăng xuất admin chỉ gọi `Auth::guard('admin')->logout()` và `session()->regenerateToken()`. **Không gọi `session()->invalidate()`**, vì như vậy sẽ đăng xuất luôn phía khách hàng (bản gốc chỉ xóa key của phía mình).
- Chuyển hướng khi chưa đăng nhập: cấu hình trong `bootstrap/app.php` (`redirectGuestsTo`). URL bắt đầu bằng `/admin` thì về trang đăng nhập admin, còn lại về trang đăng nhập khách.
- Phân quyền: tạo middleware alias `quyen` (vd. `->middleware('quyen:PHONG')`) và method `NhanVien::coQuyen(string $ma): bool`. Có thể đăng ký thêm Blade directive hoặc dùng `auth('admin')->user()->coQuyen(...)` để ẩn/hiện menu.
- **Mật khẩu:** SQL gốc lưu mật khẩu dạng chữ thường (`'1'`). **Seeder phải lưu bằng `Hash::make('1')`.** Mọi chỗ tạo/đổi mật khẩu đều dùng `Hash::make`. (Lưu ý: `Hash::check` của Laravel báo lỗi nếu chuỗi trong DB không phải bcrypt, nên mọi mật khẩu trong DB phải được hash.)

### 4.4 Xử lý lỗi (từ `handle_request_error` trong `helpers.php`)
Bản gốc ném `DomainException` cho lỗi nghiệp vụ và đổi lỗi MySQL thành câu tiếng Việt:
- 1451 (đang được tham chiếu, không xóa được) → "Không thể xóa vì dữ liệu này đang được sử dụng trong lịch sử đặt phòng, hóa đơn hoặc phân công."
- 1062 (trùng) → "Dữ liệu đã tồn tại. Vui lòng kiểm tra tên đăng nhập, email, số điện thoại hoặc mã bản ghi."
- 1452, 1406, 1264, 1292 → "Dữ liệu không hợp lệ, quá dài hoặc bản ghi liên quan không còn tồn tại. Vui lòng kiểm tra lại biểu mẫu."

Trong Laravel: giữ `DomainException` cho lỗi nghiệp vụ trong model/service. Đăng ký xử lý tập trung trong `bootstrap/app.php` → `withExceptions()`: `DomainException` và `QueryException` (theo mã lỗi ở trên) thì `back()->withInput()` kèm flash lỗi. Lỗi khác để Laravel xử lý mặc định.

### 4.5 Giao dịch và khóa
Bản gốc dùng `db_transaction()` và `SELECT … FOR UPDATE` khi đặt phòng / thêm dịch vụ / thanh toán để tránh đặt trùng. Trong Laravel dùng `DB::transaction(...)` và `->lockForUpdate()` ở đúng các chỗ đó.

### 4.6 Timezone
Bản gốc dùng `Asia/Ho_Chi_Minh` và MySQL `time_zone='+07:00'`. Laravel: `APP_TIMEZONE=Asia/Ho_Chi_Minh` (hoặc `config/app.php`), trong `config/database.php` mục mysql đặt `'timezone' => '+07:00'`. `APP_LOCALE=vi`.

---

## 5. Lỗi đã phát hiện trong bản gốc (sửa luôn khi chuyển)

1. **Trạng thái phòng không khớp:** form `views/admin/phong/form.php` có lựa chọn `'Đang sử dụng'` nhưng `PhongModel` chỉ chấp nhận `['Trống','Đã đặt','Đang thuê','Bảo trì']`. Chọn "Đang sử dụng" rồi lưu sẽ báo "Trạng thái phòng không hợp lệ" (đã thử và xác nhận lỗi). **Cách sửa:** danh sách hợp lệ là 4 giá trị `Trống, Đã đặt, Đang thuê, Bảo trì`, bỏ "Đang sử dụng" khỏi form. Phần thống kê vẫn đếm "Đang sử dụng" như bản gốc để tương thích dữ liệu cũ.

**Ghi chú về dữ liệu mẫu (giữ nguyên, không sửa):** 5 đơn mẫu có ngày tháng 5/2026 với trạng thái `DA_XAC_NHAN`, nên không nhận phòng được (báo "đã quá ngày trả"). Đây là hành vi đúng. Bảng `HOADON` mẫu có 2 hóa đơn cho cùng một đơn #2 và #3, đều `CHUA_THANH_TOAN`. Seeder giữ y nguyên dữ liệu này.

---

## 6. Các giai đoạn

### Giai đoạn 0: Khảo sát và lập checklist (chưa viết code ứng dụng)
1. Kiểm tra `git status` trong `hotel.laravel`. Nếu chưa có repo thì `git init`. Commit trạng thái hiện tại: "Trạng thái ban đầu".
2. Đọc `CLAUDE.md`, `AGENTS.md` của project. Đọc **toàn bộ** `hotel_php` (bỏ qua `Images/`, `static/`).
3. Tạo `docs/CHECKLIST.md` gồm:
   - **Bảng ánh xạ route:** mọi cặp `c` / `a` trong `index.php` và `admin/index.php` → method HTTP, URL Laravel, tên route, controller@method, quyền cần có.
   - **Danh sách 53 quy tắc nghiệp vụ** (từ `DomainException`) theo file, có cột "Đã chuyển sang" để điền dần.
   - Danh sách view gốc → file Blade mới.
   - Mục "Lỗi phát hiện trong bản gốc".
4. **Dừng, báo cáo, chờ "tiếp".**

### Giai đoạn 1: Cấu hình, Migration, Seeder, tài nguyên tĩnh
1. `.env`: `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=hotel_laravel`, `DB_USERNAME=root`, `DB_PASSWORD=` (rỗng), `APP_LOCALE=vi`, `APP_TIMEZONE=Asia/Ho_Chi_Minh`, `APP_URL=http://hotel.laravel.test`. Cập nhật `.env.example` tương tự (không chứa bí mật). Tạo database `hotel_laravel` nếu chưa có (có thể bằng lệnh `mysql` của Laragon, hoặc nhờ tôi tạo trong HeidiSQL).
2. Migration: **giữ** migration mặc định cho `sessions`, `cache`, `jobs`. **Bỏ** bảng `users` và `password_reset_tokens` khỏi migration mặc định. Tạo migration cho **13 bảng** theo đúng thứ tự khóa ngoại, đúng kiểu, độ dài, null, mặc định, unique, khóa ngoại như mục 4.2. Mỗi bảng một file, có `down()`.
3. Seeder: mỗi bảng một seeder, `DatabaseSeeder` gọi theo thứ tự khóa ngoại. **Dữ liệu y hệt file SQL**, giữ nguyên ID tường minh (KHACHHANG 1–5, CTDATPHONG 1–5, mã phòng 101…305). Mật khẩu `Hash::make('1')`. Xóa `database/database.sqlite` nếu không còn dùng.
4. Tài nguyên tĩnh: copy `hotel_php/css/app.css` → `public/css/app.css`, copy toàn bộ `hotel_php/Images/` → `public/Images/`. Tạo `public/uploads/phong/` và `public/uploads/dichvu/` (có `.gitkeep`).
5. **Kiểm tra:** `php artisan migrate:fresh --seed` chạy không lỗi. Đếm số dòng mỗi bảng phải khớp file SQL: CHUCVU 4, NHANVIEN 4, CHUCVU_QUYEN 22, LOAIPHONG 3, PHONG 15, DICHVU 5, KHACHHANG 5, CTDATPHONG 5, CTDANHGIA 2, HOADON 5, các bảng còn lại 0. Thử `INSERT` 2 khách hàng có `Email=''` để chắc cột `EmailConnexion` cho phép nhiều email rỗng nhưng chặn email trùng.
6. Commit, **dừng, báo cáo, chờ "tiếp".**

### Giai đoạn 2: Model Eloquent
1. Tạo model trong `app/Models`: `ChucVu`, `NhanVien`, `PhanCong` (BANGPHANCONG), `KhachHang`, `LoaiPhong`, `Phong`, `DatPhong` (CTDATPHONG), `DichVu`, `CtDichVu`, `HoaDon`, `DanhGia` (CTDANHGIA), `Payment`. Khai báo đủ `$table`, `$primaryKey`, `$incrementing`, `$keyType`, `$timestamps`, `$fillable`, `$casts` (ngày → `date`, `DaXoa` → `boolean`…).
2. Khai báo quan hệ: `belongsTo` / `hasMany` / `belongsToMany` đúng theo khóa ngoại.
3. Chuyển các truy vấn của model gốc sang Eloquent/Query Builder: scope (vd. `LoaiPhong::scopeConHoatDong`), method nghiệp vụ. Logic nghiệp vụ phức tạp (đặt phòng, hóa đơn, thống kê) có thể đặt ở `app/Services/` nếu giúp controller gọn hơn.
4. **Kiểm tra:** dùng `php artisan tinker` thử vài truy vấn quan hệ (vd. đơn #2 → khách hàng, phòng, loại phòng, hóa đơn). Ghi kết quả vào báo cáo.
5. Commit, **dừng, báo cáo, chờ "tiếp".**

### Giai đoạn 3: Nền tảng chung (Auth, phân quyền, layout, helper, xử lý lỗi)
1. Cấu hình guard/provider, middleware `quyen`, chuyển hướng khi chưa đăng nhập, xử lý lỗi tập trung (mục 4.3, 4.4).
2. Helper: tạo `app/helpers.php` (đăng ký trong `composer.json` → `autoload.files`, chạy `composer dump-autoload`) chứa `format_currency`, `format_date`. Không viết lại những gì Laravel đã có (`e()`, `asset()`, `csrf`…).
3. Flash message: giữ cấu trúc `['type' => 'success|danger|warning|info', 'message' => ...]` trong key session `flash` để layout ít phải sửa.
4. Upload ảnh: tạo disk riêng trong `config/filesystems.php` (vd. `uploads`, root = `public_path('uploads')`, **không cần `storage:link`**). Validate ảnh bằng Form Request (`image`, `mimes:jpg,jpeg,png,webp,gif`, `max:5120`). Lưu tên ngẫu nhiên. Đường dẫn ghi vào DB giữ dạng `/uploads/phong/<tên>`.
5. Layout Blade: `resources/views/admin/layouts/app.blade.php` (từ header + nav + footer gốc) và `resources/views/client/layouts/app.blade.php`. Trang đăng nhập admin không có nav (bản gốc dùng `hideNav`).
6. Đăng nhập/đăng xuất/đổi mật khẩu admin. Đăng nhập/đăng ký/đăng xuất khách hàng. Dashboard admin tạm thời có thể để trống.
7. **Kiểm tra:** đăng nhập admin `viet` / `1` (Quản lý, toàn quyền) và `vung` / `1` (Bảo vệ, chỉ có DASHBOARD). Với `vung`, vào URL `/admin/phong` phải ra 403 và menu chỉ có Dashboard. Đăng nhập khách `teo@gmail.com` / `1` và `0912345678` / `1`. Khóa tài khoản trong DB rồi thử đăng nhập. Đăng nhập cả admin và khách trên cùng trình duyệt, đăng xuất admin thì **khách vẫn còn đăng nhập**.
8. Commit, **dừng, báo cáo, chờ "tiếp".**

### Giai đoạn 4: Các module quản trị
Làm **theo thứ tự**, mỗi module gồm: Form Request + Controller (`app/Http/Controllers/Admin/`) + route có middleware `quyen:...` + view Blade. **Mỗi 3–4 module thì commit một lần.**

- **4A:** `LoaiPhong` (xóa mềm `DaXoa`), `Phong` (mã phòng nhập tay, ảnh đại diện, trạng thái; sửa lỗi ở mục 5), `DichVu` (ảnh).
- **4B:** `KhachHang` (danh sách + tìm kiếm, khóa/mở khóa, lịch sử đặt phòng), `NhanVien` (không tự xóa mình, không tự đổi chức vụ mình, đổi mật khẩu tùy chọn khi sửa), `ChucVu` (màn hình tick quyền theo chức vụ, `updatePermissions` trong transaction).
- **4C:** `PhanCong` (gồm trang "hôm nay" `homNay`), `DanhGia`.
- **4D:** `DatPhong` (danh sách, thêm, chi tiết, duyệt, nhận phòng, trả phòng, hủy, thêm/xóa dịch vụ trên đơn), `CtDichVu` (danh sách, thêm, `addToBooking` / `saveToBooking`, xóa), `HoaDon` (danh sách, chi tiết, thanh toán, snapshot JSON, cập nhật `payment_status`).
- **4E:** `Dashboard` và `ThongKe` (Chart.js qua CDN, số liệu phải khớp bản gốc).

Sau 4D, **dừng, báo cáo, chờ "tiếp"**. Sau 4E cũng vậy.

### Giai đoạn 5: Phía khách hàng
Controller trong `app/Http/Controllers/Client/`. Các trang: trang chủ, danh sách phòng (lọc như bản gốc), chi tiết phòng (ảnh + đánh giá), dịch vụ, đánh giá (gửi đánh giá theo quy tắc trong `DanhGiaModel`), liên hệ, đặt phòng (khách vãng lai và khách đã đăng nhập), đặt thành công, tài khoản (hồ sơ, lịch sử, chi tiết đơn của mình).
**Dừng, báo cáo, chờ "tiếp".**

### Giai đoạn 6: Kiểm tra toàn bộ
1. `php artisan route:list`: đối chiếu với bảng ánh xạ trong `docs/CHECKLIST.md`, không thiếu route nào.
2. Truy cập lần lượt tất cả trang (dùng `php artisan serve` hoặc `http://hotel.laravel.test` + `curl`, kèm phiên đăng nhập), không trang nào lỗi 500.
3. **Kịch bản nghiệm thu (bắt buộc ra đúng số):** chạy `migrate:fresh --seed`, rồi:
   - Khách `teo@gmail.com` đặt **phòng 104** (Bình Dân, 300.000 đ/đêm), nhận **hôm nay**, trả **sau 2 ngày** → đơn mới trạng thái `CHO_ADMIN_DUYET`, `GiaPhongDat = 300000`.
   - Admin `viet`: duyệt → `DA_XAC_NHAN`; nhận phòng → `DANG_SU_DUNG`; thêm dịch vụ **Ăn sáng × 2** và **Massage × 1**; trả phòng → tạo hóa đơn `CHUA_THANH_TOAN`.
   - **Tổng hóa đơn phải bằng 1.260.000 đ** (600.000 tiền phòng + 60.000 ăn sáng + 600.000 massage).
   - Thanh toán → hóa đơn `DA_THANH_TOAN`, đơn `DA_HOAN_THANH`, `payment_status = DA_THANH_TOAN`.
   - Đặt thêm một đơn **trùng ngày** cho phòng 104 → phải bị chặn.
4. Viết **Feature test** (PHPUnit) cho kịch bản trên và cho phân quyền (`vung` bị 403 ở `/admin/phong`). Test chạy trên database MySQL riêng `hotel_laravel_test` (cấu hình trong `phpunit.xml`, vì SQLite mặc định không giống MySQL). Chạy `php artisan test` phải xanh.
5. Rà lại `docs/CHECKLIST.md`: 53 quy tắc đều đã đánh dấu.
6. Commit, **dừng, báo cáo.**

### Giai đoạn 7: Dọn dẹp và hướng dẫn
1. Xóa file thừa (`welcome.blade.php`, route mặc định, `database.sqlite`, `UserFactory`…). Chạy `vendor/bin/pint` để định dạng code (nếu có sẵn).
2. Viết lại `README.md` (tiếng Việt) cho nhóm: yêu cầu môi trường, cách cài (`composer install`, copy `.env.example`, `php artisan key:generate`, tạo DB, `php artisan migrate:fresh --seed`), tài khoản mẫu (`viet/1` Quản lý, `binh/1` Lễ tân, `vung/1` Bảo vệ, `van/1` Tạp vụ; khách `teo@gmail.com/1`), cấu trúc thư mục chính, bảng ánh xạ "file gốc → file Laravel" để giảng viên dễ chấm.
3. Commit cuối, báo cáo tổng kết.
