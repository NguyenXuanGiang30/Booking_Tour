# Hướng dẫn sử dụng tính năng mới

## 1. Email Notifications

### Cấu hình Email

Mở file `config.php` và cập nhật các thông tin email:

```php
// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com'); // SMTP server
define('SMTP_PORT', 587); // SMTP port
define('SMTP_USER', 'your-email@gmail.com'); // Email của bạn
define('SMTP_PASS', 'your-app-password'); // App password (cho Gmail)
define('SMTP_FROM_EMAIL', 'noreply@travelquest.com'); // Email gửi đi
define('SMTP_FROM_NAME', 'TravelQuest'); // Tên người gửi
define('SMTP_SECURE', 'tls'); // 'tls' hoặc 'ssl'

// Email Settings
define('EMAIL_ENABLED', true); // Bật/tắt email
define('EMAIL_ADMIN', 'admin@travelquest.com'); // Email admin
```

### Gmail Setup

1. **Bật 2-Step Verification** trong Google Account
2. **Tạo App Password:**
   - Vào: https://myaccount.google.com/apppasswords
   - Chọn "Mail" và "Other"
   - Copy App Password và dán vào `SMTP_PASS`

### Các loại email được gửi

1. **Booking Confirmation** - Khi user đặt tour
2. **Payment Success** - Khi thanh toán thành công
3. **Booking Status Update** - Khi admin thay đổi trạng thái booking

### Test Email

Trong development, email sẽ được log vào error log thay vì gửi thực tế (nếu `EMAIL_ENABLED = false`).

## 2. Search Nâng Cao

### Full-Text Search Index

Để sử dụng full-text search, chạy SQL sau:

```sql
USE travel_quest;
ALTER TABLE tours ADD FULLTEXT INDEX ft_search (title, description, location);
```

**Lưu ý:**
- Full-text search yêu cầu MySQL 5.6+ với InnoDB hoặc MyISAM
- Nếu không có index, hệ thống sẽ tự động fallback về LIKE search

### Tính năng Search

1. **Search Box trong Navbar:**
   - Tìm kiếm real-time với autocomplete
   - Gợi ý khi gõ (sau 2 ký tự)
   - Hiển thị title và location

2. **Search Results:**
   - Full-text search trong title, description, location
   - Sắp xếp theo relevance
   - Kết hợp với filters (location, price, category)

3. **Search Suggestions API:**
   - Endpoint: `/api/search/suggestions?q=query`
   - Trả về JSON với suggestions

### Cách sử dụng

1. **Tìm kiếm từ Navbar:**
   - Gõ vào search box
   - Chọn suggestion hoặc nhấn Enter
   - Xem kết quả trên trang tours

2. **Tìm kiếm từ URL:**
   ```
   /tours?search=Bali
   /tours?search=beach&category=beach&minPrice=100
   ```

3. **Kết hợp với filters:**
   - Search + Location
   - Search + Price Range
   - Search + Category

### Fallback

Nếu full-text index chưa được tạo, hệ thống sẽ tự động sử dụng LIKE search:
- Tìm trong: title, description, location
- Vẫn hoạt động tốt nhưng chậm hơn với dữ liệu lớn

## Cấu hình

### Bật/Tắt Email

Trong `config.php`:
```php
define('EMAIL_ENABLED', true); // Bật email
define('EMAIL_ENABLED', false); // Tắt email (chỉ log)
```

### Test Email

1. Đặt tour mới → Nhận email xác nhận
2. Thanh toán thành công → Nhận email thanh toán
3. Admin cập nhật status → Nhận email cập nhật

### Test Search

1. Gõ vào search box trong navbar
2. Xem suggestions hiển thị
3. Chọn một suggestion hoặc nhấn Enter
4. Xem kết quả tìm kiếm

## Troubleshooting

### Email không gửi được

1. **Kiểm tra SMTP settings** trong `config.php`
2. **Kiểm tra App Password** (nếu dùng Gmail)
3. **Kiểm tra firewall** không chặn port 587/465
4. **Xem error log** để biết lỗi cụ thể

### Search không hoạt động

1. **Kiểm tra full-text index:**
   ```sql
   SHOW INDEX FROM tours WHERE Key_name = 'ft_search';
   ```

2. **Nếu chưa có index:**
   - Chạy file `database_fulltext_index.sql`
   - Hoặc hệ thống sẽ tự động dùng LIKE search

3. **Kiểm tra database engine:**
   ```sql
   SHOW CREATE TABLE tours;
   ```
   - Phải là InnoDB (MySQL 5.6+) hoặc MyISAM

## Lưu ý

- Email trong development sẽ chỉ log, không gửi thực tế
- Full-text search cần index để hoạt động tốt nhất
- Search suggestions chỉ hiển thị sau 2 ký tự
- Email templates hỗ trợ đa ngôn ngữ (Vi/En)

