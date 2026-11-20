# Báo cáo kiểm thử chức năng — Website Quản lý Khách sạn

- **Ngày kiểm thử:** 20/11/2026
- **Phiên bản:** nhánh `main`, commit `f2e6fb7`
- **Phạm vi:** toàn bộ chức năng phía khách hàng và phía quản trị: luồng sử dụng, quy tắc nghiệp vụ, phân quyền, kiểm tra dữ liệu, bảo mật cơ bản và giao diện.

## 1. Tóm tắt

| | Số lượng |
|---|---|
| Kịch bản kiểm thử tự động trên trình duyệt (Playwright) | **123** |
| Kịch bản đạt | 89 |
| Kịch bản không đạt | 34 (1 kịch bản được chấp nhận: ô dashboard gộp "Đang thuê / Đã đặt" đã ghi rõ nhãn) |
| Feature/Unit test có sẵn (`php artisan test`) | 24/24 đạt (151 assertion) |

Sau khi gộp các kịch bản trùng nguyên nhân, còn **25 vấn đề**:

| Mức độ | Số vấn đề | Ý nghĩa |
|---|---|---|
| 🔴 Nghiêm trọng | 5 | Lỗ hổng phân quyền, lộ hoặc hỏng dữ liệu khách, chặn luồng nghiệp vụ chính |
| 🟠 Cao | 6 | Sai nghiệp vụ gây thất thu hoặc treo dữ liệu, thiếu bảo vệ đăng nhập |
| 🟡 Trung bình | 7 | Hành vi khó hiểu, dữ liệu không nhất quán |
| ⚪ Thấp | 7 | Kiểm tra dữ liệu thiếu, giao diện, dữ liệu mẫu |

**Đánh giá chung:** luồng chính của khách sạn chạy đúng: đặt → duyệt → nhận phòng → gọi dịch vụ → trả phòng → thanh toán, ra đúng hóa đơn **1.260.000 đ**. Việc chống đặt trùng (kể cả 5 người đặt cùng lúc), phân quyền theo menu, CSRF, chống XSS và chặn xem đơn của người khác đều tốt. Các vấn đề nằm chủ yếu ở **trường hợp biên của nghiệp vụ**: khách vãng lai, xóa loại phòng, khách ở quá hạn, hóa đơn treo. Ngoài ra có **một lỗ hổng leo thang đặc quyền** cần sửa trước khi đưa vào sử dụng.

> **Cập nhật 20/11/2026 — đã sửa.** Toàn bộ 24 lỗi thật đã được sửa trên nhánh `sua-loi-kiem-thu`; BUG-23 là báo sai. Chi tiết cách sửa và kết quả kiểm thử lại ở **mục 7**.

---

## 2. Môi trường và cách kiểm thử

- PHP 8.3, MySQL 8.4.3 (Laragon), Windows 11, Chromium headless (Playwright for Python).
- Kiểm thử trên **database riêng `hotel_laravel_qa`** (đã `migrate:fresh --seed`), server `php artisan serve --port=8001`. Database `hotel_laravel` đang dùng **không bị đụng tới**.
- Mỗi kịch bản mô phỏng đúng thao tác người dùng: mở trang, điền form, bấm nút, chấp nhận hộp thoại xác nhận. Kết quả được đối chiếu với giao diện và với dữ liệu trong database. Các kịch bản "sửa request" gửi form kèm CSRF token hợp lệ trong cùng phiên, giống người dùng sửa HTML hoặc gửi lại request.
- Tài khoản dùng: khách `teo@gmail.com`, `ti@gmail.com`; nhân viên `viet` (Quản lý), `binh` (Lễ tân), `vung` (Bảo vệ), `van` (Tạp vụ).
- Ngày "hôm nay" khi test là 20/11/2026. Mọi đơn trong dữ liệu mẫu đều đã qua ngày trả (tháng 5/2026).

---

## 3. Danh sách vấn đề

### 🔴 Nghiêm trọng

#### BUG-01 — Leo thang đặc quyền: nhân viên có quyền "Nhân viên" chiếm được tài khoản Quản lý
- **Tái hiện:** Quản lý cấp quyền `NHAN_VIEN` cho chức vụ *Tạp vụ*. Đăng nhập `van` (Tạp vụ) → *Nhân viên* → sửa `viet` → nhập mật khẩu mới → Lưu. Đăng nhập `viet` bằng mật khẩu mới: **thành công**. Cùng cách đó, `van` **tạo được tài khoản mới với chức vụ Quản lý** (toàn quyền).
- **Nguyên nhân:** [NhanVienController.php:36](app/Http/Controllers/Admin/NhanVienController.php:36) (`store`) và [:62](app/Http/Controllers/Admin/NhanVienController.php:62) (`update`) chỉ chặn việc *tự đổi chức vụ của chính mình*. Không có gì chặn nhân viên không phải Quản lý: sửa hay đặt lại mật khẩu của Quản lý, gán chức vụ Quản lý, hoặc xóa Quản lý.
- **Hậu quả:** người có quyền quản lý nhân sự (ví dụ lễ tân trưởng) chiếm được toàn quyền hệ thống, xem và sửa doanh thu, hóa đơn.
- **Đề xuất:** chỉ Quản lý mới được tạo, sửa, xóa tài khoản Quản lý và gán chức vụ Quản lý. Không cho đặt lại mật khẩu người khác nếu người đó có quyền cao hơn mình. Áp dụng quy tắc tương tự cho trang *Chức vụ*: người có quyền `CHUC_VU` hiện tự cấp thêm quyền cho chức vụ của mình được.

#### BUG-02 — Khách vãng lai dùng chung trình duyệt: người sau ghi đè thông tin và xem được đơn của người trước
- **Tái hiện (máy công cộng, máy ở quầy, quán net):** chưa đăng nhập, đặt phòng 102 với tên *Vãng Lai A* / `0933000001`. Không đóng trình duyệt, người khác đặt phòng 103 với tên *Vãng Lai B* / `0933000002`.
  - Đơn #13 của A giờ mang **tên, SĐT, email của B**. Dashboard admin hiện hai đơn đều tên "Vãng Lai B", khách sạn mất thông tin liên lạc của A.
  - Người B mở `/dat-phong/13/thanh-cong` **xem được đơn của A**.
- **Nguyên nhân:** [BookingController.php:53-54](app/Http/Controllers/Client/BookingController.php:53) lưu `guest_customer_id` vào session. Lần đặt sau trong cùng phiên, [DatPhongService::datPhongTrucTuyen](app/Services/DatPhongService.php:97) dùng lại khách đó và gọi `capNhatThongTin()` ghi đè toàn bộ thông tin.
- **Đề xuất:** chỉ dùng lại khách vãng lai khi SĐT gửi lên **trùng** SĐT của khách đó. Nếu khác thì tạo khách mới. Không bao giờ cập nhật thông tin khách cũ từ form vãng lai.

#### BUG-03 — Khách vãng lai quay lại không đặt được lần 2 và không bao giờ đăng ký được tài khoản
- **Tái hiện:**
  1. Khách vãng lai đặt phòng với SĐT `0933000002`. Hôm sau, ở phiên mới, đặt lại cùng SĐT → *"Số điện thoại hoặc email đã được đăng ký. Vui lòng đăng nhập tài khoản này."* Nhưng khách này **không có mật khẩu nên không đăng nhập được**.
  2. Khách vào *Đăng ký* bằng chính SĐT đó → *"Số điện thoại này đã được đăng ký tài khoản."*
- **Nguyên nhân:** [KhachHang::taoKhachHang](app/Models/KhachHang.php:84) và quy tắc `unique:KHACHHANG,DienThoai` trong [DangKyRequest.php:21](app/Http/Requests/Client/DangKyRequest.php:21) coi khách vãng lai (`MatKhau = NULL`) như tài khoản đã đăng ký.
- **Hậu quả:** khách cũ, tức tệp khách quý nhất, bị khóa ngoài vĩnh viễn trừ khi gọi điện cho khách sạn.
- **Đề xuất:** khi đặt phòng mà SĐT thuộc khách vãng lai, dùng lại bản ghi đó. Khi đăng ký mà SĐT thuộc khách vãng lai, chuyển bản ghi đó thành tài khoản (đặt mật khẩu) thay vì báo trùng. Nên có bước xác minh SĐT/email.

#### BUG-04 — Xóa loại phòng khi còn đơn đã xác nhận làm khách đến không nhận phòng được
- **Tái hiện:** lễ tân tạo đơn #16, phòng 301 (loại *Vip*), nhận phòng hôm nay. Quản lý xóa loại phòng *Vip*: hệ thống chỉ đánh dấu `DaXoa` và báo thành công. Bấm *Nhận phòng* đơn #16 → *"Phòng đang bảo trì hoặc loại phòng đã ngừng sử dụng."* Không có cách nào trên giao diện để check-in, cũng không khôi phục được loại phòng.
- Phía khách: phòng 301–305 **vẫn hiện ở `/phong` và trang chủ với nút "Đặt ngay"**, bấm đặt thì báo lỗi.
- **Nguyên nhân:** [LoaiPhong::xoaHoacAn](app/Models/LoaiPhong.php:46) không kiểm tra đơn đang hoạt động, trong khi [DatPhongService::assertAvailable](app/Services/DatPhongService.php:216) chặn mọi thao tác trên phòng thuộc loại đã xóa. `Phong::scopeLoc` không lọc loại đã xóa.
- **Đề xuất:** chặn xóa loại phòng khi còn phòng có đơn *Chờ duyệt / Đã xác nhận / Đang ở* (giống cách đổi phòng sang *Bảo trì* đang làm). Ẩn phòng thuộc loại đã xóa khỏi trang khách. Thêm chức năng khôi phục loại phòng.

#### BUG-05 — Đặt phòng khi đã đăng nhập ghi đè hồ sơ tài khoản
- **Tái hiện:** đăng nhập `teo`, đặt phòng với họ tên "Người Khác Đặt Hộ" (đặt cho người thân). Họ tên trong hồ sơ tài khoản của Tèo bị đổi thành "Người Khác Đặt Hộ". Email, địa chỉ, CMND cũng bị ghi đè hoặc xóa trắng nếu để trống.
- **Nguyên nhân:** [DatPhongService.php:109](app/Services/DatPhongService.php:109) luôn gọi `capNhatThongTin()` cho khách đang đăng nhập.
- **Đề xuất:** lưu thông tin người lưu trú trên đơn đặt phòng (cột riêng), không sửa hồ sơ tài khoản. Hoặc ít nhất không ghi đè các trường để trống.

### 🟠 Cao

#### BUG-06 — Khách ở quá ngày trả: phòng vẫn nhận đơn mới, hóa đơn không tính đêm ở thêm
- **Tái hiện:** đơn phòng 202 nhận 17/11, trả 19/11, đang ở và chưa trả phòng (hôm nay 20/11).
  - Khách khác đặt online phòng 202 cho 20/11 → 21/11: **thành công**. Hệ thống chỉ chặn ở bước nhận phòng ("Phòng còn khách chưa trả phòng"), nên khách mới đến nơi mới biết.
  - Trả phòng: hóa đơn **40.000.000 đ (2 đêm)** dù khách ở thực tế 3 đêm.
- **Nguyên nhân:** kiểm tra trùng lịch chỉ dựa vào `NgayNhan`/`NgayTra` ghi trên đơn. [HoaDonService::calculateBookingTotal](app/Services/HoaDonService.php:47) tính số đêm theo đơn, không theo ngày trả thực tế.
- **Đề xuất:** khi trả phòng sau `NgayTra`, tính thêm đêm (hoặc phụ phí) và cập nhật `NgayTra` thực tế. Khi kiểm tra phòng trống, coi đơn *Đang sử dụng* quá hạn là vẫn chiếm phòng. Hiện cảnh báo "khách quá hạn" ở dashboard.

#### BUG-07 — Hóa đơn treo không thể thanh toán, hủy đơn không hủy hóa đơn
- **Tái hiện:**
  - `/admin/hoa-don/1` có trạng thái *Chưa thanh toán* và **nút "Xác nhận thanh toán"**. Bấm → *"Chỉ thanh toán trả phòng cho đơn đang sử dụng."* Hóa đơn này không bao giờ thanh toán hay hủy được.
  - Hủy đơn #2: đơn chuyển *Đã hủy* nhưng 2 hóa đơn #1, #2 vẫn *Chưa thanh toán*.
  - Dữ liệu mẫu có **5 hóa đơn treo**, và đơn #2, #3 mỗi đơn có **2 hóa đơn**.
- **Nguyên nhân:** [DatPhongController::cancel](app/Http/Controllers/Admin/DatPhongController.php) không xử lý hóa đơn. View [hoa-don/show.blade.php:20](resources/views/admin/hoa-don/show.blade.php:20) hiện nút chỉ dựa vào trạng thái hóa đơn. Seeder tạo hóa đơn cho đơn chưa nhận phòng.
- **Đề xuất:** thêm trạng thái hóa đơn *Đã hủy* và tự hủy khi hủy đơn. Chỉ hiện nút thanh toán khi đơn đang *Đang sử dụng*. Sửa dữ liệu mẫu cho nhất quán (mỗi đơn một hóa đơn, trạng thái khớp với đơn).

#### BUG-08 — Không giới hạn số lần đăng nhập sai (cả khách và quản trị)
- **Tái hiện:** nhập sai mật khẩu `teo@gmail.com` 9 lần liên tiếp, và `viet` 9 lần: không bị khóa tạm hay chậm lại.
- **Nguyên nhân:** route `dang-nhap` của cả hai phía không có `throttle`, controller không dùng `RateLimiter`.
- **Đề xuất:** thêm `->middleware('throttle:5,1')` hoặc `RateLimiter` theo tài khoản + IP. Điều này càng quan trọng vì mật khẩu mặc định là `1` (xem BUG-09).

#### BUG-09 — Không có yêu cầu độ mạnh mật khẩu, SĐT kiểm tra quá lỏng
- Đăng ký khách thành công với mật khẩu **`1`**. Nhân viên mới cũng đặt mật khẩu 1 ký tự được.
- SĐT **`0-------`** (không có chữ số thật nào ngoài số 0) vẫn được chấp nhận. Biểu thức [KhachHang.php:22](app/Models/KhachHang.php:22) `/^[+0-9][0-9 .()-]{7,19}$/` cho phép toàn ký tự gạch hoặc ngoặc.
- **Đề xuất:** `Password::min(8)` cho khách và nhân viên. SĐT Việt Nam nên chuẩn hóa (bỏ khoảng trắng/dấu) rồi kiểm tra `^(0|\+84)\d{9}$`.

#### BUG-10 — Không giới hạn giữ phòng: một khách giữ được nhiều phòng, rất dài, rất xa
- **Tái hiện:** khách `teo` đặt liền **5/5 phòng Thương Gia** cùng ngày. Đặt được đơn **400 đêm bắt đầu sau 400 ngày**. Không cần đặt cọc, không giới hạn.
- Đơn *Chờ duyệt* không tự hết hạn: đơn đã qua ngày trả vẫn nằm trong "Đơn chờ duyệt" ở dashboard, bấm *Duyệt* thì báo "Đơn đặt phòng đã quá ngày trả." Trong lúc chờ, đơn vẫn **giữ phòng**, khách khác không đặt được.
- **Đề xuất:** giới hạn số đơn đang chờ duyệt trên mỗi khách/SĐT, số đêm tối đa và khoảng đặt trước tối đa. Tự hủy (hoặc ẩn) đơn chờ duyệt quá hạn, ví dụ qua lịch `schedule` hằng ngày.

#### BUG-11 — Nhân viên không có quyền Dashboard đăng nhập xong gặp trang 403
- **Tái hiện:** ở *Chức vụ*, bỏ quyền *Dashboard* của *Bảo vệ*, chỉ giữ *Phân công*. Đăng nhập `vung` → trang **"403 – Truy cập bị từ chối (DASHBOARD)"**, liên kết "Quay lại bảng điều khiển" cũng dẫn về 403.
- **Nguyên nhân:** [AuthController.php:43](app/Http/Controllers/Admin/AuthController.php:43) luôn chuyển về `admin.dashboard`. Sau khi đổi mật khẩu ([:59](app/Http/Controllers/Admin/AuthController.php:59)) cũng vậy.
- **Đề xuất:** sau khi đăng nhập, chuyển tới trang đầu tiên nhân viên có quyền. Hoặc bắt buộc mọi chức vụ có quyền Dashboard.

### 🟡 Trung bình

#### BUG-12 — Thông báo **thành công** ở trang quản trị hiện màu đỏ như báo lỗi
- **Tái hiện:** mọi thao tác thành công ("Đã tạo hóa đơn…", "Đăng nhập quản trị thành công!") đều hiện hộp nền hồng, chữ đỏ.
- **Nguyên nhân:** [public/css/app.css:932](public/css/app.css:932) định nghĩa `.alert { background:#fef2f2; color:#991b1b }`, được nạp sau Bootstrap nên ghi đè `alert-success`. Lớp `.alert.success` ([:942](public/css/app.css:942)) không khớp với `alert-success` mà view dùng.
- **Đề xuất:** đổi `.alert.success` thành `.alert-success`, hoặc bỏ màu nền/màu chữ trong `.alert`.

#### BUG-13 — Trạng thái phòng "Đã đặt" ngay khi có đơn cho tương lai xa
- **Tái hiện:** tạo đơn phòng 204 cho 2 tháng sau → phòng 204 đổi ngay sang **"Đã đặt"** dù hôm nay trống. Dashboard đếm vào ô *Đang thuê / Đã đặt*. Admin **không đổi được loại hay trạng thái** phòng (ví dụ cho bảo trì 1 ngày) vì "Phòng đang có lịch đặt".
- **Nguyên nhân:** [DatPhongService::syncRoomStatus](app/Services/DatPhongService.php:257) xét mọi đơn đã xác nhận có `NgayTra > hôm nay`, không xét `NgayNhan`.
- **Đề xuất:** chỉ "Đã đặt" khi `NgayNhan` là hôm nay hoặc sắp tới (ví dụ trong 1 ngày). Hoặc tính trạng thái theo ngày khi hiển thị thay vì lưu cứng. Cho đổi sang *Bảo trì* nếu khoảng bảo trì không trùng lịch.

#### BUG-14 — Admin đặt tay được trạng thái "Đang thuê"/"Đã đặt" cho phòng không có đơn
- **Tái hiện:** thêm phòng 501 với trạng thái *Đang thuê* → lưu thành công. Số liệu "đang thuê" sai, và trạng thái chỉ được tính lại khi có đơn mới.
- **Đề xuất:** form phòng chỉ cho chọn *Trống* / *Bảo trì*. Hai trạng thái còn lại do hệ thống tự tính.

#### BUG-15 — Khách trả phòng sớm không đánh giá được tới ngày trả trên đơn
- **Tái hiện:** đơn phòng 104 (20/11 → 22/11) trả phòng và thanh toán ngay hôm nay, đơn *Hoàn tất*. Ở `/danh-gia` khách không thấy phòng 104 để đánh giá, phải đợi tới 22/11.
- **Nguyên nhân:** [DanhGia::locDonCoTheDanhGia](app/Models/DanhGia.php) yêu cầu đồng thời `TrangThai = DA_HOAN_THANH` **và** `NgayTra <= hôm nay`.
- **Đề xuất:** đơn đã hoàn tất là đủ điều kiện, bỏ điều kiện ngày trả.

#### BUG-16 — Admin tạo được đánh giá "giả" lẫn với đánh giá thật của khách
- **Tái hiện:** *Đánh giá → Thêm*: nội dung tùy ý, ngày **20/12/2026 (tương lai)**, số sao **99**. Hệ thống lưu thành 5 sao mà không báo gì. Đánh giá không gắn khách nhưng hiện công khai ở `/danh-gia` như đánh giá thật.
- **Đề xuất:** kiểm tra `soSao` trong khoảng 1–5 và ngày không ở tương lai. Đánh dấu rõ "phản hồi của khách sạn", hoặc bỏ chức năng admin tự thêm đánh giá công khai.

#### BUG-17 — Lọc phòng phía khách cho kết quả gây hiểu lầm
- *Giá từ 5.000.000 đến 1.000.000* → "Không tìm thấy phòng nào", không báo khoảng giá bị ngược.
- Lọc ngày **trong quá khứ** (15/11 → 17/11) vẫn hiện 15 phòng "còn trống" kèm nút *Đặt ngay*, bấm đặt thì bị chặn.
- Trang đặt phòng của phòng **đang bảo trì** (mở bằng URL) vẫn hiện đầy đủ form, gửi đi mới báo lỗi.
- **Đề xuất:** kiểm tra `giaTu ≤ giaDen` và `ngayNhan ≥ hôm nay` ngay khi lọc. Trang đặt phòng ẩn form nếu phòng bảo trì hoặc loại phòng đã xóa.

#### BUG-18 — Chi tiết dịch vụ: mã nhập tay bị trùng, xóa dòng không tồn tại vẫn báo "đã xóa"
- *Chi tiết DV → Thêm* cho nhập tay *Mã CT dịch vụ*. Nhập `1` cho hai dòng khác nhau → **3 dòng cùng mã 1** trong `CTDICHVU`.
- Gửi xóa dịch vụ #5 khỏi đơn #16 (đơn không có dịch vụ này) → *"Đã xóa chi tiết dịch vụ."*
- **Đề xuất:** bỏ ô nhập mã (để hệ thống tự sinh) hoặc thêm `unique`. Kiểm tra số dòng bị xóa và báo lỗi nếu bằng 0.

### ⚪ Thấp

| Mã | Vấn đề | Đề xuất |
|---|---|---|
| BUG-19 | Cho tạo **trùng tên loại phòng** (hai loại "Bình Dân" giá khác nhau → khách khó phân biệt). Tên loại phòng giới hạn **20 ký tự**, quá ngắn cho tên thực tế như "Phòng Gia Đình Cao Cấp" (22 ký tự). | Thêm `unique` theo tên (bỏ qua loại đã xóa), tăng giới hạn lên 50–100 ký tự. |
| BUG-20 | Nhân viên có **ngày vào làm (1990) trước ngày sinh (2000)** vẫn lưu được. | `ngayVaoLam` phải `after:ngaySinh`, và nhân viên phải đủ 18 tuổi khi vào làm. |
| BUG-21 | Ô "Đường dẫn ảnh" của phòng/dịch vụ chấp nhận **URL bên ngoài bất kỳ** (`https://evil.example/track.gif`), có thể dùng để theo dõi người xem hoặc hiện ảnh không phù hợp. | Chỉ chấp nhận đường dẫn bắt đầu bằng `/Images/` hoặc `/uploads/`. |
| BUG-22 | Hóa đơn ghi **"2 ngày"** cho 2 đêm lưu trú ([hoa-don/show.blade.php:58, 75](resources/views/admin/hoa-don/show.blade.php:58)). | Đổi thành "đêm" để khớp cách tính tiền. |
| BUG-23 | **Thanh menu trái** của admin chỉ cao 720px. Trang dài (dashboard, danh sách đơn) để lộ nền trắng bên dưới menu. | Cho sidebar `position: sticky`/`min-height: 100vh`. |
| BUG-24 | Lỗi khóa ngoại (xóa phòng hoặc dịch vụ đã dùng) đã hiện thông báo tiếng Việt thân thiện nhưng **vẫn ghi stack trace vào `laravel.log`** mỗi lần, làm nhiễu log. | Thêm `QueryException` mã 1451/1062 vào `dontReport` hoặc kiểm tra trước khi xóa. |
| BUG-25 | **Dữ liệu mẫu bất thường:** loại *Thương Gia* giá **20.000.000 đ** đắt gấp 5 lần *Vip* (4.000.000 đ), nhiều khả năng nhập thừa một số 0. Các đơn mẫu đều đã qua ngày trả nhưng vẫn *Đã xác nhận* (không thể nhận phòng, chỉ hủy được). | Kiểm tra lại giá trong `LoaiPhongSeeder`. Tạo đơn mẫu theo ngày tương đối (`today()->addDays(...)`) để demo được luôn. |

---

## 4. Những phần đã kiểm thử và hoạt động đúng

**Phía khách hàng**
- Các trang công khai (trang chủ, phòng, chi tiết phòng, dịch vụ, đánh giá, liên hệ, đăng nhập, đăng ký) tải đúng: không lỗi 500, không lỗi JS, không liên kết hay ảnh hỏng.
- Lọc phòng theo loại, theo khoảng giá. Ngày trả trước ngày nhận bị báo lỗi. Tham số rác, mã phòng không tồn tại hoặc sai định dạng được xử lý êm.
- Đăng ký: chặn trùng SĐT, trùng email, họ tên toàn khoảng trắng.
- Đăng nhập bằng email hoặc SĐT. Sai mật khẩu bị từ chối. Đã đăng nhập mà mở trang đăng nhập thì được chuyển đi.
- Đặt phòng: đơn ở trạng thái *Chờ duyệt* và lưu giá tại thời điểm đặt. Chặn trùng lịch, ngày quá khứ, ngày trả = ngày nhận, phòng bảo trì, lấy SĐT của tài khoản khác.
- **5 khách đặt cùng phòng, cùng ngày, cùng lúc → chỉ 1 đơn được tạo** (khóa dòng hoạt động tốt).
- Lịch sử và chi tiết đơn. **Không xem được đơn hay trang "đặt thành công" của người khác** (IDOR).
- Đánh giá: chỉ hiện phòng đủ điều kiện. Gửi đánh giá giả (sửa request) bị chặn.
- Khách bị khóa đang đăng nhập bị đăng xuất ngay ở request kế tiếp và không đăng nhập lại được.
- POST thiếu CSRF token bị từ chối (419).
- Giao diện điện thoại (375px) không bị tràn ngang ở các trang chính.

**Phía quản trị**
- Ma trận phân quyền đúng cho cả 4 tài khoản mẫu (22 URL × 3 nhân viên). Chưa đăng nhập thì được chuyển về trang đăng nhập.
- Tất cả trang quản trị mở được bằng tài khoản Quản lý, không lỗi.
- Tên khách chứa mã HTML/JS được hiển thị an toàn (không chạy script).
- **Luồng chuẩn:** Duyệt (phòng → *Đã đặt*) → Nhận phòng (phòng → *Đang thuê*) → thêm Ăn sáng ×2 + Massage ×1 → Trả phòng → hóa đơn **1.260.000 đ** → thêm dịch vụ sau khi lập hóa đơn thì hóa đơn tự tính lại (1.295.000 đ) → Thanh toán → đơn *Hoàn tất*, phòng *Trống* → không thêm dịch vụ được nữa → đổi giá dịch vụ không làm đổi hóa đơn đã thanh toán → doanh thu tháng hiện ở *Thống kê* → khách thấy đơn *Hoàn tất* kèm tổng tiền.
- Dịch vụ: chặn số lượng 0, > 10.000, dịch vụ không tồn tại, thêm vào đơn chờ duyệt.
- Tạo đơn ở quầy: chặn khách bị khóa, đơn *Đang sử dụng* cho ngày mai, ngày quá khứ, phòng không tồn tại.
- Phòng: chặn trùng mã. Chặn đổi sang bảo trì khi có đơn. Xóa phòng hay dịch vụ đã dùng thì báo thân thiện.
- Nhân viên: không tự đổi chức vụ, không tự xóa mình. Đổi mật khẩu: chặn sai mật khẩu cũ, xác nhận không khớp.
- Phân công: chặn ngày quá khứ, hiển thị đúng ở "Hôm nay".
- Thống kê: tham số năm bất thường (`abc`, `0`, `99999`, `-1`) không gây lỗi.
- Tìm kiếm khách theo tên, tìm đơn theo SĐT.
- Đăng xuất admin không đăng xuất phiên khách trong cùng trình duyệt.

---

## 5. Thứ tự ưu tiên sửa đề xuất

1. **BUG-01** (leo thang đặc quyền), **BUG-08** (giới hạn đăng nhập), **BUG-09** (mật khẩu): bảo mật, sửa nhanh.
2. **BUG-02, BUG-03, BUG-05**: cùng nằm ở `DatPhongService::datPhongTrucTuyen` và `KhachHang::taoKhachHang`, nên sửa một lần.
3. **BUG-04, BUG-06, BUG-07**: nghiệp vụ phòng và hóa đơn ảnh hưởng vận hành hằng ngày.
4. **BUG-12**: sửa một dòng CSS, lợi ích lớn về trải nghiệm.
5. Các mục còn lại.

## 6. Ghi chú

- Các vấn đề trên được tái hiện trên database kiểm thử `hotel_laravel_qa`. Database này còn giữ dữ liệu sau kiểm thử và có thể xóa bằng `DROP DATABASE hotel_laravel_qa;`.
- Script kiểm thử (Python + Playwright) và ảnh chụp màn hình được lưu trong thư mục tạm của phiên làm việc, không nằm trong repo.

---

## 7. Kết quả sửa lỗi (nhánh `sua-loi-kiem-thu`)

Ngoài các lỗi ở trên, lần sửa này áp dụng các quyết định đã thống nhất:

- **Đặt phòng bắt buộc đăng nhập.** Khách chưa có tài khoản phải đăng ký; không đăng nhập thì chỉ xem. Bấm "Đặt ngay" khi chưa đăng nhập sẽ chuyển sang trang đăng nhập, đăng nhập xong quay lại đúng phòng. Lễ tân vẫn tạo đơn ở trang quản trị cho khách đến quầy.
- **Form đặt phòng chỉ chọn ngày.** Họ tên, SĐT, email lấy từ hồ sơ tài khoản và không sửa ở form. Thêm trang **Tài khoản → Sửa thông tin / đổi mật khẩu**.
- **Khách vãng lai cũ** (chưa có mật khẩu) đăng ký bằng đúng SĐT cũ thì nhận lại bản ghi và lịch sử đặt phòng.
- **Lọc giá bằng danh sách chọn** (Dưới 500.000 đ · 500.000 – 2 triệu · 2 – 5 triệu · Từ 5 triệu), cả ở trang khách và trang quản trị.
- **Mật khẩu mẫu mới:** `viet` / `Admin@1234`; `binh`, `vung`, `van` / `Nhanvien@1234`; khách / `Khach@1234`. README đã cập nhật.
- Nút "Admin" ở trang khách **giữ nguyên** theo yêu cầu.

| Mã | Trạng thái | Cách sửa |
|---|---|---|
| BUG-01 | ✅ Đã sửa | Chỉ Quản lý mới tạo, sửa, đặt lại mật khẩu, xóa tài khoản Quản lý hoặc gán chức vụ Quản lý. Người không phải Quản lý không sửa được quyền của chức vụ Quản lý hay chức vụ của chính mình. Form nhân viên ẩn lựa chọn "Quản lý" với người không phải Quản lý. |
| BUG-02 | ✅ Đã sửa | Bỏ hẳn luồng đặt phòng không đăng nhập (không còn `guest_customer_id` trong session). |
| BUG-03 | ✅ Đã sửa | `KhachHang::dangKy()` nhận lại hồ sơ vãng lai theo SĐT/email. |
| BUG-04 | ✅ Đã sửa | Chặn xóa loại phòng còn đơn chờ duyệt / đã xác nhận / đang ở. Phòng thuộc loại đã xóa bị ẩn ở trang khách. Thêm mục "Loại phòng đã ngừng sử dụng" có nút **Khôi phục**. |
| BUG-05 | ✅ Đã sửa | Đặt phòng không còn ghi vào hồ sơ tài khoản. |
| BUG-06 | ✅ Đã sửa | Khách đang ở quá ngày trả vẫn chiếm phòng tới hết hôm nay khi kiểm tra trùng lịch. Hóa đơn tính tới ngày trả thực tế (ghi "trả muộn x đêm"). Khi thanh toán, `NgayTra` cập nhật thành ngày trả thực tế. |
| BUG-07 | ✅ Đã sửa | Thêm trạng thái hóa đơn **Đã hủy**; hủy đơn thì hủy hóa đơn chưa thanh toán. Nút "Xác nhận thanh toán" chỉ hiện khi đơn đang ở. Dữ liệu mẫu mỗi đơn đúng một hóa đơn. |
| BUG-08 | ✅ Đã sửa | Sai 5 lần (theo tài khoản + IP) bị khóa tạm 60 giây, ở cả trang khách và trang quản trị. |
| BUG-09 | ✅ Đã sửa | Mật khẩu ≥ 8 ký tự, có chữ hoa, chữ thường, chữ số, không chứa SĐT/email/tên đăng nhập; đăng ký có ô nhập lại. SĐT chỉ nhận số di động Việt Nam, tự chuẩn hóa `+84`, khoảng trắng, dấu chấm. CMND/CCCD 9 hoặc 12 số. Tên đăng nhập nhân viên 3–20 ký tự `a-z 0-9 _` (để 3 thay vì 4 vì tài khoản mẫu `van` chỉ 3 ký tự). |
| BUG-10 | ✅ Đã sửa | Tối đa 30 đêm/đơn, đặt trước tối đa 365 ngày, tối đa 3 đơn chờ duyệt. Đơn chờ duyệt hoặc đã xác nhận quá ngày trả mà chưa nhận phòng thì tự hủy (lệnh `dat-phong:don-dep` chạy hằng ngày, và mỗi khi mở dashboard, danh sách đơn, danh sách phòng). |
| BUG-11 | ✅ Đã sửa | Sau đăng nhập / đổi mật khẩu / trang 403, nhân viên được đưa tới trang đầu tiên mình có quyền. |
| BUG-12 | ✅ Đã sửa | CSS thêm `.alert-success`, `.alert-warning`, `.alert-info`: thông báo thành công có nền xanh. |
| BUG-13 | ✅ Đã sửa | Phòng chỉ "Đã đặt" khi có đơn đã xác nhận đến ngày nhận (hôm nay hoặc đã qua). Đổi ảnh/thông tin phòng có đơn tương lai vẫn được; chỉ chặn đổi loại phòng hoặc bật/tắt Bảo trì. |
| BUG-14 | ✅ Đã sửa | Form phòng chỉ còn "Hoạt động (tự cập nhật theo đơn)" và "Bảo trì"; server từ chối giá trị khác. |
| BUG-15 | ✅ Đã sửa | Đơn hoàn tất là đánh giá được, không chờ tới ngày trả trên đơn. |
| BUG-16 | ✅ Đã sửa | Số sao 1–5, ngày đánh giá không ở tương lai. Đánh giá do admin thêm hiện tên "Khách sạn Vip (phản hồi)" thay vì "Khách hàng ẩn danh". |
| BUG-17 | ✅ Đã sửa | Lọc giá bằng danh sách chọn; lọc ngày quá khứ có báo lỗi; ô ngày có `min`; trang đặt phòng của phòng bảo trì / loại đã xóa không hiện form. |
| BUG-18 | ✅ Đã sửa | Mã chi tiết dịch vụ do hệ thống cấp (không nhận từ request). Xóa dịch vụ không có trong đơn báo lỗi. |
| BUG-19 | ✅ Đã sửa | Không trùng tên với loại phòng đang hoạt động; tên tối đa 50 ký tự (migration `2026_11_20_000001_mo_rong_ten_loai_phong`). |
| BUG-20 | ✅ Đã sửa | Nhân viên phải đủ 18 tuổi; ngày vào làm phải sau ngày đủ 18 tuổi. |
| BUG-21 | ✅ Đã sửa | Đường dẫn ảnh nhập tay chỉ nhận `/Images/...` hoặc `/uploads/...` với đuôi ảnh. |
| BUG-22 | ✅ Đã sửa | Hóa đơn ghi "đêm". |
| BUG-23 | ⚠️ Báo sai | Thanh menu dùng `position: fixed; height: 100vh` nên luôn phủ hết màn hình trên trình duyệt thật. Hiện tượng chỉ xuất hiện trong **ảnh chụp toàn trang** của công cụ kiểm thử. Không cần sửa. |
| BUG-24 | ✅ Đã sửa | Lỗi MySQL 1451/1062 đã hiện thông báo thân thiện thì không ghi log nữa (`dontReportWhen`). |
| BUG-25 | ✅ Đã sửa | Đơn, hóa đơn, đánh giá mẫu tính ngày theo hôm nay (2 đơn hoàn tất, 1 đang ở, 1 đến hôm nay, 1 chờ duyệt). Giá Thương Gia đổi từ 20.000.000 đ thành **5.000.000 đ** để cao hơn Vip (Thương Gia có thêm phòng làm việc); có thể chỉnh lại trong `LoaiPhongSeeder`. |

### Kiểm thử lại

- `php artisan test`: **41/41 đạt** (240 assertion). Thêm `tests/Feature/SuaLoiKiemThuTest.php` cho từng lỗi; viết lại `DatPhongKhachTest` theo quy tắc bắt buộc đăng nhập.
- Kiểm thử lại trên trình duyệt (Playwright, database `hotel_laravel_qa` dựng mới): **52 kịch bản**, tất cả đạt sau khi xem xét. Hai kịch bản ban đầu báo "không đạt" là do cách viết kịch bản: đặt trước 400 ngày bị **trình duyệt** chặn ngay vì ô ngày có `max`; kịch bản đặt trùng ngày bị chặn sớm hơn bởi quy tắc "tối đa 3 đơn chờ duyệt". Một kịch bản (khách mới đặt phòng còn người ở quá hạn) đã chạy lại riêng để xác nhận.
- Giao diện điện thoại 375px: không tràn ngang ở các trang mới (đặt phòng, sửa hồ sơ, đăng ký).

### Lưu ý khi dùng

- Database đang dùng `hotel_laravel` **chưa được nạp lại dữ liệu mẫu**: nó có 1 đơn và 1 khách tạo thủ công hôm nay nên không tự xóa. Mật khẩu mẫu mới chỉ có hiệu lực sau khi chạy `php artisan migrate:fresh --seed` (lệnh này **xóa sạch** dữ liệu hiện có). Migration mới đã được chạy riêng bằng `php artisan migrate`.
- Khách hoặc nhân viên có mật khẩu cũ yếu vẫn đăng nhập được; quy tắc mới áp dụng khi đăng ký, đổi mật khẩu hoặc tạo/sửa tài khoản.
