# TravelQuest - PHP Version

Đây là phiên bản PHP thuần của ứng dụng đặt tour du lịch TravelQuest, được chuyển đổi từ React/TypeScript.

## Yêu cầu hệ thống

- PHP 7.4 hoặc cao hơn
- MySQL 5.7 hoặc cao hơn (hoặc MariaDB)
- Apache/Nginx với mod_rewrite
- PDO extension cho PHP

## Cài đặt

1. **Clone hoặc copy dự án vào thư mục web server của bạn**

2. **Tạo database:**
   ```sql
   mysql -u root -p < database.sql
   ```

3. **Cấu hình database trong `config.php`:**
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'travel_quest');
   define('DB_USER', 'root');
   define('DB_PASS', 'your_password');
   ```

4. **Cấu hình URL trong `config.php`:**
   ```php
   define('SITE_URL', 'http://localhost');
   ```

5. **Cấu hình web server:**

   **Apache (.htaccess):**
   ```apache
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ index.php [QSA,L]
   ```

   **Nginx:**
   ```nginx
   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
   ```

## Cấu trúc dự án

```
/
├── config.php              # Cấu hình chính
├── index.php              # Entry point và routing
├── database.sql           # SQL schema
├── create_admin.php       # Script tạo admin mới
├── .htaccess              # Apache URL rewriting
├── README.md              # Tài liệu hướng dẫn
├── includes/              # Core classes
│   ├── Auth.php          # Authentication
│   ├── Database.php      # Database connection
│   └── Router.php        # Routing system
├── functions/            # Data functions (thay thế models)
│   ├── helper_function.php
│   ├── tour_function.php
│   ├── user_function.php
│   ├── admin_function.php
│   ├── booking_function.php
│   ├── review_function.php
│   └── wishlist_function.php
├── handle/              # Process files (thay thế controllers)
│   ├── home_process.php
│   ├── tours_process.php
│   ├── auth_process.php
│   ├── dashboard_process.php
│   ├── booking_process.php
│   ├── wishlist_process.php
│   └── admin_process.php
└── views/                # Views/Templates
    ├── layout.php
    ├── home.php
    ├── tours.php
    ├── tour-detail.php
    ├── login.php
    ├── register.php
    ├── dashboard.php
    ├── about.php
    ├── contact.php
    ├── 404.php
    ├── components/
    │   ├── navbar.php
    │   └── footer.php
    └── admin/
        ├── login.php
        ├── dashboard.php
        ├── tours.php
        ├── tour-form.php
        ├── bookings.php
        └── users.php
```

## Tính năng

### Người dùng
- ✅ Trang chủ với tour nổi bật
- ✅ Danh sách tour với bộ lọc
- ✅ Chi tiết tour
- ✅ Đăng ký/Đăng nhập
- ✅ Dashboard người dùng
- ✅ Quản lý booking
- ✅ Wishlist
- ✅ Reviews
- ✅ Responsive design với Tailwind CSS

### Admin
- ✅ Admin login/logout
- ✅ Admin dashboard với thống kê
- ✅ Quản lý tours (thêm, sửa, xóa)
- ✅ Quản lý bookings (xem, cập nhật trạng thái)
- ✅ Quản lý users (xem danh sách)
- ✅ Giao diện admin chuyên nghiệp

## Sử dụng

### Người dùng
1. Truy cập `http://localhost` trong trình duyệt
2. Đăng ký tài khoản mới hoặc đăng nhập
3. Duyệt và đặt tour

### Admin
1. Truy cập `http://localhost/admin/login`
2. Đăng nhập với:
   - **Username:** `admin`
   - **Password:** `admin123`
3. Quản lý tours, bookings, và users từ admin panel

**Lưu ý:** Sau khi chạy `database.sql`, tài khoản admin mặc định sẽ được tạo tự động. Bạn có thể thay đổi password bằng cách cập nhật trong database hoặc tạo admin mới.

## Lưu ý

- Đảm bảo quyền ghi cho thư mục session (nếu cần)
- Trong môi trường production, tắt `display_errors` trong `config.php`
- Sử dụng HTTPS cho bảo mật
- Hash password được xử lý tự động bằng `password_hash()`

## License

MIT
