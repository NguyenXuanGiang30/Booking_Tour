# Hướng dẫn sử dụng Coupons và Tour Availability

## 1. Coupons/Discount Codes

### Cài đặt Database

Chạy file SQL để tạo tables:
```sql
-- Chạy file database_coupons_availability.sql
```

### Tạo Coupon

Vào database và insert coupon mới:

```sql
INSERT INTO coupons (id, code, name, description, discount_type, discount_value, min_amount, max_discount, usage_limit, valid_from, valid_to, status)
VALUES (
    UUID(),
    'WELCOME10',  -- Mã coupon
    'Welcome Discount',  -- Tên
    '10% off for new customers',  -- Mô tả
    'percentage',  -- Loại: 'percentage' hoặc 'fixed'
    10,  -- Giá trị: 10% hoặc số tiền cố định
    100000,  -- Số tiền tối thiểu (VND)
    50000,  -- Giảm tối đa (VND) - NULL nếu không giới hạn
    100,  -- Số lần sử dụng tối đa - NULL nếu không giới hạn
    '2024-01-01 00:00:00',  -- Ngày bắt đầu
    '2024-12-31 23:59:59',  -- Ngày kết thúc
    'active'  -- Trạng thái: 'active', 'inactive', 'expired'
);
```

### Các loại Coupon

1. **Percentage Discount** (Giảm theo %):
   - `discount_type = 'percentage'`
   - `discount_value = 10` (10%)
   - Có thể set `max_discount` để giới hạn số tiền giảm tối đa

2. **Fixed Discount** (Giảm số tiền cố định):
   - `discount_type = 'fixed'`
   - `discount_value = 50000` (50,000 VND)

### Coupon áp dụng cho tour cụ thể

Để coupon chỉ áp dụng cho một số tour:
```sql
UPDATE coupons 
SET applicable_tours = JSON_ARRAY('tour-001', 'tour-002')
WHERE code = 'WELCOME10';
```

### Tính năng

- ✅ Validate coupon code real-time
- ✅ Hiển thị discount amount
- ✅ Tự động tính final price
- ✅ Track coupon usage
- ✅ Check min amount, usage limit, validity dates
- ✅ Support percentage và fixed discount

## 2. Tour Availability Calendar

### Cài đặt Database

Đã được tạo trong `database_coupons_availability.sql`

### Quản lý Availability

#### Tạo availability cho một ngày:

```sql
INSERT INTO tour_availability (id, tour_id, available_date, available_slots, booked_slots, price_override, status, notes)
VALUES (
    UUID(),
    'tour-001',
    '2024-12-25',
    20,  -- Số slot có sẵn
    0,  -- Số slot đã đặt
    1500000,  -- Giá override (NULL nếu dùng giá tour mặc định)
    'available',  -- 'available', 'unavailable', 'sold_out'
    'Christmas special'  -- Ghi chú
);
```

#### Cập nhật availability:

```sql
UPDATE tour_availability 
SET available_slots = 30,
    price_override = 1200000,
    status = 'available'
WHERE tour_id = 'tour-001' AND available_date = '2024-12-25';
```

### Tính năng

- ✅ Calendar hiển thị availability
- ✅ Check availability khi chọn date
- ✅ Hiển thị số slots còn lại
- ✅ Price override cho từng ngày
- ✅ Tự động update khi có booking
- ✅ Validate availability trước khi booking

## 3. Sử dụng trong Booking

### Flow Booking với Coupon:

1. User chọn tour, date, số khách
2. User nhập coupon code và click "Apply"
3. System validate coupon:
   - Check code tồn tại
   - Check validity dates
   - Check min amount
   - Check usage limit
   - Check applicable tours
4. Nếu valid, hiển thị discount và update final price
5. Khi booking, coupon được lưu vào booking
6. Payment sử dụng final_price (sau discount)

### Flow Booking với Availability:

1. User chọn date từ calendar
2. System check availability cho date đó
3. Hiển thị số slots còn lại
4. Validate số khách không vượt quá available slots
5. Sử dụng price override nếu có
6. Khi booking thành công, update booked_slots

## 4. API Endpoints

### Validate Coupon
```
POST /api/coupon/validate
Body:
- coupon_code: string
- tour_id: string
- amount: float

Response:
{
    "valid": true/false,
    "message": "string",
    "coupon": {...},
    "discount_amount": float
}
```

### Get Tour Availability
```
GET /api/tour/availability?tour_id={id}&start_date={date}&end_date={date}

Response:
{
    "2024-12-25": {
        "date": "2024-12-25",
        "available_slots": 20,
        "status": "available",
        "price": 1500000,
        "notes": null
    },
    ...
}
```

## 5. Admin Features (Cần implement)

Để quản lý coupons và availability từ admin panel, cần thêm:

1. **Coupon Management:**
   - Create/Edit/Delete coupons
   - View coupon usage statistics
   - Activate/Deactivate coupons

2. **Availability Management:**
   - Set availability cho từng tour
   - Bulk update availability
   - View booking calendar

## 6. Best Practices

### Coupons:
- Tạo coupon codes dễ nhớ (WELCOME10, SUMMER20)
- Set min_amount hợp lý để tránh abuse
- Set max_discount cho percentage coupons
- Monitor usage để tạo campaigns hiệu quả

### Availability:
- Update availability thường xuyên
- Set price_override cho peak seasons
- Track booked_slots để quản lý capacity
- Sử dụng notes để ghi chú đặc biệt

## 7. Testing

### Test Coupon:
1. Tạo coupon test trong database
2. Vào tour detail page
3. Nhập coupon code
4. Verify discount được apply
5. Complete booking và check final_price

### Test Availability:
1. Tạo availability record cho một ngày
2. Chọn date đó trong booking form
3. Verify availability message hiển thị
4. Complete booking và check booked_slots được update

## Lưu ý

- Coupon validation chạy real-time khi user nhập code
- Availability check chạy khi user chọn date
- Final price (sau discount) được dùng cho VNPay payment
- Availability tự động update khi booking thành công
- Coupon usage được track trong `coupon_usage` table

