-- Coupons and Tour Availability Tables
-- Run this SQL to add coupons and availability features

USE travel_quest;

-- Coupons table
CREATE TABLE IF NOT EXISTS coupons (
    id VARCHAR(36) PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    discount_type ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
    discount_value DECIMAL(10,2) NOT NULL DEFAULT 0,
    min_amount DECIMAL(10,2) DEFAULT 0,
    max_discount DECIMAL(10,2) DEFAULT NULL,
    usage_limit INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    valid_from DATETIME NOT NULL,
    valid_to DATETIME NOT NULL,
    status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    applicable_tours JSON DEFAULT NULL, -- NULL means all tours, otherwise array of tour IDs
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_status (status),
    INDEX idx_valid_dates (valid_from, valid_to)
);

-- Coupon usage tracking
CREATE TABLE IF NOT EXISTS coupon_usage (
    id VARCHAR(36) PRIMARY KEY,
    coupon_id VARCHAR(36) NOT NULL,
    booking_id VARCHAR(36) NOT NULL,
    user_id VARCHAR(36) NOT NULL,
    discount_amount DECIMAL(10,2) NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES profiles(id) ON DELETE CASCADE,
    INDEX idx_coupon_id (coupon_id),
    INDEX idx_booking_id (booking_id),
    INDEX idx_user_id (user_id)
);

-- Tour availability calendar
CREATE TABLE IF NOT EXISTS tour_availability (
    id VARCHAR(36) PRIMARY KEY,
    tour_id VARCHAR(36) NOT NULL,
    available_date DATE NOT NULL,
    available_slots INT NOT NULL DEFAULT 0,
    booked_slots INT NOT NULL DEFAULT 0,
    price_override DECIMAL(10,2) DEFAULT NULL, -- Override tour price for this date
    status ENUM('available', 'unavailable', 'sold_out') DEFAULT 'available',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tour_date (tour_id, available_date),
    INDEX idx_tour_id (tour_id),
    INDEX idx_available_date (available_date),
    INDEX idx_status (status)
);

-- Add coupon fields to bookings table
ALTER TABLE bookings 
ADD COLUMN coupon_code VARCHAR(50) DEFAULT NULL AFTER payment_method_id,
ADD COLUMN coupon_id VARCHAR(36) DEFAULT NULL AFTER coupon_code,
ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0 AFTER coupon_id,
ADD COLUMN final_price DECIMAL(10,2) DEFAULT NULL AFTER discount_amount,
ADD INDEX idx_coupon_code (coupon_code),
ADD FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL;

-- Update final_price to be calculated automatically if NULL
-- This will be handled in application code

