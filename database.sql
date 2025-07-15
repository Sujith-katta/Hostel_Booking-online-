CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Room types table
CREATE TABLE IF NOT EXISTS room_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    base_price DECIMAL(10,2) NOT NULL,
    laundry_fee DECIMAL(10,2) DEFAULT 0.00,
    mess_veg_fee DECIMAL(10,2) NOT NULL,
    mess_nonveg_fee DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_type_id INT NOT NULL,
    mess_type ENUM('veg', 'non-veg') NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'accepted', 'rejected', 'paid') DEFAULT 'pending',
    payment_id VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_type_id) REFERENCES room_types(id) ON DELETE CASCADE
);

-- UPI details table (for admin payment QR code)
CREATE TABLE IF NOT EXISTS upi_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    upi_id VARCHAR(100) NOT NULL,
    qr_code_path VARCHAR(255) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

DELETE FROM users WHERE username = 'admin';

INSERT INTO users (username, password, role, fullname, email, phone)
VALUES (
    'admin',
    'admin123',  -- plain text password
    'admin',
    'Admin User',
    'admin@hostel.com',
    '1234567890'
);

-- Insert sample room types
INSERT INTO room_types (name, description, base_price, laundry_fee, mess_veg_fee, mess_nonveg_fee) 
VALUES 
('Standard Single', 'Basic single occupancy room with shared bathroom', 5000.00, 500.00, 2000.00, 2500.00),
('Deluxe Single', 'Single room with attached bathroom and basic amenities', 7000.00, 700.00, 2500.00, 3000.00),
('Standard Double', 'Double occupancy room with shared bathroom', 8000.00, 800.00, 3500.00, 4000.00),
('Deluxe Double', 'Double room with attached bathroom and basic amenities', 10000.00, 1000.00, 4000.00, 4500.00);


ALTER TABLE bookings MODIFY COLUMN payment_id VARCHAR(255) AFTER status;