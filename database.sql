-- TravelQuest Database Schema
-- Run this SQL to create the database structure

CREATE DATABASE IF NOT EXISTS travel_quest;
USE travel_quest;

-- User authentication table
CREATE TABLE IF NOT EXISTS user_auth (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_user_id (user_id)
);

-- Profiles table
CREATE TABLE IF NOT EXISTS profiles (
    id VARCHAR(36) PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL DEFAULT '',
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) DEFAULT '',
    address TEXT,
    birthday DATE,
    avatar_url TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
);

-- Tours table
CREATE TABLE IF NOT EXISTS tours (
    id VARCHAR(36) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    duration INT NOT NULL DEFAULT 1,
    max_guests INT NOT NULL DEFAULT 10,
    images JSON,
    itinerary JSON,
    included JSON,
    excluded JSON,
    rating DECIMAL(3,2) DEFAULT 0,
    total_reviews INT DEFAULT 0,
    featured BOOLEAN DEFAULT FALSE,
    category VARCHAR(50) DEFAULT 'adventure',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_featured (featured),
    INDEX idx_category (category),
    INDEX idx_location (location)
);

-- Payment Methods table (must be created before bookings)
CREATE TABLE IF NOT EXISTS payment_methods (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'card', -- card, bank_account, paypal, etc.
    name VARCHAR(255) NOT NULL, -- Card holder name or account name
    last_four VARCHAR(4) DEFAULT '', -- Last 4 digits of card/account
    expiry_date VARCHAR(7) DEFAULT '', -- MM/YYYY for cards
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES profiles(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
);

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    tour_id VARCHAR(36) NOT NULL,
    booking_date DATE NOT NULL,
    number_of_guests INT NOT NULL DEFAULT 1,
    total_price DECIMAL(10,2) NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    traveler_info JSON,
    payment_method VARCHAR(100) DEFAULT '',
    payment_method_id VARCHAR(36) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_tour_id (tour_id),
    INDEX idx_payment_method_id (payment_method_id)
);

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    tour_id VARCHAR(36) NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE CASCADE,
    INDEX idx_tour_id (tour_id),
    INDEX idx_user_id (user_id)
);

-- Wishlists table
CREATE TABLE IF NOT EXISTS wishlists (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    tour_id VARCHAR(36) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, tour_id),
    INDEX idx_user_id (user_id)
);

-- Admins table
CREATE TABLE IF NOT EXISTS admins (
    id VARCHAR(36) PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
);

-- Insert default admin accounts
-- Admin 1: username='admin', password='admin123' (bcrypt hash)
-- Admin 2: username='superadmin', password='admin' (bcrypt hash)
-- Note: To generate new password hashes, use: php -r "echo password_hash('your_password', PASSWORD_BCRYPT);"
INSERT INTO admins (id, username, email, password, full_name, role, is_active) VALUES 
('admin-001', 'admin', 'admin@travelquest.com', '$2y$10$jSoN44gmv7.66z32W397oOURo2k2U0rntbUlTVK6KvF0qiGSZkzPS', 'Administrator', 'admin', 1),
('admin-002', 'superadmin', 'superadmin@travelquest.com', '$2y$10$zbsSfdsTo810zD3.9dlNJu3ginMDNvs5e7wY0Nv0ugSw/km8Lj832', 'Super Administrator', 'super_admin', 1)
ON DUPLICATE KEY UPDATE 
    password = VALUES(password),
    is_active = 1;

-- Trigger to update tour ratings when reviews are added/updated/deleted
DELIMITER //
CREATE TRIGGER update_tour_rating_after_insert
AFTER INSERT ON reviews
FOR EACH ROW
BEGIN
    UPDATE tours SET 
        rating = (SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE tour_id = NEW.tour_id),
        total_reviews = (SELECT COUNT(*) FROM reviews WHERE tour_id = NEW.tour_id)
    WHERE id = NEW.tour_id;
END//

CREATE TRIGGER update_tour_rating_after_update
AFTER UPDATE ON reviews
FOR EACH ROW
BEGIN
    UPDATE tours SET 
        rating = (SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE tour_id = NEW.tour_id),
        total_reviews = (SELECT COUNT(*) FROM reviews WHERE tour_id = NEW.tour_id)
    WHERE id = NEW.tour_id;
END//

CREATE TRIGGER update_tour_rating_after_delete
AFTER DELETE ON reviews
FOR EACH ROW
BEGIN
    UPDATE tours SET 
        rating = (SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE tour_id = OLD.tour_id),
        total_reviews = (SELECT COUNT(*) FROM reviews WHERE tour_id = OLD.tour_id)
    WHERE id = OLD.tour_id;
END//
DELIMITER ;
