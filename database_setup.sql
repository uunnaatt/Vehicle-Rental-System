DROP DATABASE IF EXISTS vehicle_rental;
CREATE DATABASE vehicle_rental;
USE vehicle_rental;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    phone_or_email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Locations Table
CREATE TABLE IF NOT EXISTS locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Vehicles Table
CREATE TABLE IF NOT EXISTS vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    brand VARCHAR(50) NOT NULL,
    model_year INT NOT NULL,
    category_id INT,
    location_id INT,
    seats INT DEFAULT 4,
    transmission ENUM('Manual', 'Automatic') DEFAULT 'Manual',
    fuel_type ENUM('Petrol', 'Diesel', 'Electric', 'Hybrid') DEFAULT 'Petrol',
    daily_rate DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255),
    status ENUM('available', 'booked', 'maintenance') DEFAULT 'available',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL
);

-- Bookings Table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    pickup_location_id INT NOT NULL,
    dropoff_location_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (pickup_location_id) REFERENCES locations(id),
    FOREIGN KEY (dropoff_location_id) REFERENCES locations(id)
);

-- Reviews Table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);

-- ================================================
-- SEED DATA
-- ================================================

-- Users (admin + regular customers)
-- password for all: 'password123' (hashed)
INSERT INTO users (full_name, phone_or_email, password, role) VALUES 
('Admin Sawari', 'admin@sawari.com', '$2y$12$q4CmB8oYcnZF9jxKq7kIdu3xY2YY5q3k4mV/D6I/ukSYp5inBGDk6', 'admin'),
('Rohan Sharma', 'rohan@gmail.com', '$2y$12$q4CmB8oYcnZF9jxKq7kIdu3xY2YY5q3k4mV/D6I/ukSYp5inBGDk6', 'user'),
('Priya Thapa', 'priya@gmail.com', '$2y$12$q4CmB8oYcnZF9jxKq7kIdu3xY2YY5q3k4mV/D6I/ukSYp5inBGDk6', 'user'),
('Suman Rai', 'suman@gmail.com', '$2y$12$q4CmB8oYcnZF9jxKq7kIdu3xY2YY5q3k4mV/D6I/ukSYp5inBGDk6', 'user'),
('Bina Gurung', 'bina@gmail.com', '$2y$12$q4CmB8oYcnZF9jxKq7kIdu3xY2YY5q3k4mV/D6I/ukSYp5inBGDk6', 'user');

-- Categories
INSERT INTO categories (name, description) VALUES 
('SUV', 'Sport Utility Vehicles – powerful, spacious, and perfect for off-road adventures.'),
('Sedan', 'Sleek 4-door cars ideal for city travel and business trips.'),
('Hatchback', 'Compact and fuel-efficient, perfect for daily city use.'),
('Pickup', 'Heavy-duty trucks for cargo and rugged terrain travel.');

-- Locations
INSERT INTO locations (name, address) VALUES 
('Kathmandu – TIA Airport', 'Tribhuvan International Airport, Kathmandu'),
('Pokhara – Lakeside', 'Lakeside Road, Pokhara, Kaski'),
('Chitwan – Sauraha', 'Sauraha Tourist Hub, Chitwan'),
('Bhaktapur – Durbar Square', 'Durbar Square, Bhaktapur'),
('Lalitpur – Patan', 'Mangalbazar, Patan, Lalitpur'),
('Biratnagar Hub', 'Main Road, Biratnagar, Morang');

-- Vehicles (25 vehicles across all categories and locations)
INSERT INTO vehicles (name, brand, model_year, category_id, location_id, seats, transmission, fuel_type, daily_rate, image_url, status, description) VALUES

-- SUVs (category_id=1)
('Land Cruiser Prado', 'Toyota', 2022, 1, 1, 7, 'Automatic', 'Diesel', 18000.00, '../assets/images/land_cruiser.png', 'available', 'A luxurious, capable 7-seater SUV ideal for Nepal''s mountainous terrain.'),
('Fortuner GR Sport', 'Toyota', 2023, 1, 1, 7, 'Automatic', 'Diesel', 15000.00, '../assets/images/land_cruiser.png', 'available', 'Premium SUV with sport-tuned suspension and panoramic sunroof.'),
('Creta N-Line', 'Hyundai', 2023, 1, 2, 5, 'Automatic', 'Petrol', 9000.00, '../assets/images/land_cruiser.png', 'available', 'Sporty compact SUV with futuristic interior and advanced driver aids.'),
('Tucson Hybrid', 'Hyundai', 2023, 1, 2, 5, 'Automatic', 'Hybrid', 11000.00, '../assets/images/land_cruiser.png', 'available', 'Eco-friendly mid-size SUV with adaptive cruise control and 360 camera.'),
('XUV700', 'Mahindra', 2022, 1, 3, 7, 'Automatic', 'Diesel', 10000.00, '../assets/images/mahindra_thar.png', 'available', 'Award-winning 7-seater with ADAS suite and panoramic sunroof.'),
('Compass Limited', 'Jeep', 2023, 1, 4, 5, 'Automatic', 'Petrol', 13500.00, '../assets/images/mahindra_thar.png', 'available', 'Iconic Jeep DNA in a premium compact SUV format.'),
('Defender 110', 'Land Rover', 2022, 1, 1, 5, 'Automatic', 'Diesel', 25000.00, '../assets/images/mahindra_thar.png', 'available', 'The ultimate luxury off-roader, engineered for any terrain.'),

-- Sedans (category_id=2)
('Model 3 Performance', 'Tesla', 2023, 2, 1, 5, 'Automatic', 'Electric', 14000.00, '../assets/images/tesla_model_3.png', 'available', 'Fully electric sedan with ludicrous acceleration and autopilot.'),
('Civic RS', 'Honda', 2022, 2, 2, 5, 'Manual', 'Petrol', 7500.00, '../assets/images/honda_civic.png', 'available', 'Sporty and reliable sedan, best-seller in its class.'),
('Camry Hybrid', 'Toyota', 2023, 2, 3, 5, 'Automatic', 'Hybrid', 9500.00, '../assets/images/honda_civic.png', 'available', 'Premium hybrid sedan with exceptional fuel efficiency and comfort.'),
('Elantra N', 'Hyundai', 2023, 2, 1, 5, 'Manual', 'Petrol', 8500.00, '../assets/images/honda_civic.png', 'available', 'High-performance version of the popular Elantra sedan.'),
('Model S Plaid', 'Tesla', 2023, 2, 5, 5, 'Automatic', 'Electric', 22000.00, '../assets/images/tesla_model_3.png', 'available', 'World''s fastest sedan with over 1,000 hp and 600 km range.'),
('3 Series M340i', 'BMW', 2022, 2, 4, 5, 'Automatic', 'Petrol', 19000.00, '../assets/images/bmw-white.png', 'available', 'Ultimate driving machine with inline-6 turbocharged engine.'),
('City ZX', 'Honda', 2022, 2, 6, 5, 'Automatic', 'Petrol', 6500.00, '../assets/images/honda_civic.png', 'available', 'Comfortable family sedan with excellent boot space.'),

-- Hatchbacks (category_id=3)
('Thar Rock', 'Mahindra', 2023, 3, 3, 4, 'Manual', 'Diesel', 8000.00, '../assets/images/mahindra_thar.png', 'available', 'Iconic off-road compact with fold-down roof for the adventure seekers.'),
('Swift ZXi', 'Maruti', 2023, 3, 2, 5, 'Automatic', 'Petrol', 4500.00, '../assets/images/vw_polo_gti.png', 'available', 'Nepal''s most loved hatchback for city commuting.'),
('i20 N Line', 'Hyundai', 2023, 3, 1, 5, 'Manual', 'Petrol', 5500.00, '../assets/images/vw_polo_gti.png', 'available', 'Sporty hatchback with dual-tone paint and sport seats.'),
('Polo GTI', 'Volkswagen', 2022, 3, 5, 5, 'Automatic', 'Petrol', 6500.00, '../assets/images/vw_polo_gti.png', 'available', 'Hot hatch with 200+ HP in a compact footprint.'),
('Grand i10 Nios', 'Hyundai', 2022, 3, 6, 5, 'Manual', 'Petrol', 4000.00, '../assets/images/vw_polo_gti.png', 'available', 'Budget-friendly, spacious hatchback perfect for families.'),
('Mazda 2', 'Mazda', 2022, 3, 4, 5, 'Automatic', 'Petrol', 5000.00, '../assets/images/mazda-blue.png', 'available', 'Stylish Japanese hatchback with KODO soul-in-motion design.'),

-- Pickups (category_id=4)
('Hilux Revo', 'Toyota', 2022, 4, 3, 5, 'Manual', 'Diesel', 14000.00, '../assets/images/ford_ranger.png', 'available', 'Best-in-class pickup truck for both work and adventure.'),
('Ranger Wildtrak', 'Ford', 2023, 4, 6, 5, 'Automatic', 'Diesel', 13500.00, '../assets/images/ford_ranger.png', 'available', 'High-riding pickup truck with rolling load cover and smart tech.'),
('D-Max V-Cross', 'Isuzu', 2023, 4, 1, 5, 'Automatic', 'Diesel', 12000.00, '../assets/images/ford_ranger.png', 'available', 'Adventure-ready pickup, excellent for Himalayan expeditions.'),
('L200 Triton', 'Mitsubishi', 2022, 4, 2, 5, 'Manual', 'Diesel', 11000.00, '../assets/images/ford_ranger.png', 'available', 'Reliable full-sized pickup with legendary 4WD capability.'),
('BT-50', 'Mazda', 2022, 4, 4, 5, 'Manual', 'Diesel', 10500.00, '../assets/images/mazda-blue.png', 'available', 'Tough and dependable pickup with 3-tonne towing capacity.');

-- Sample Bookings
INSERT INTO bookings (user_id, vehicle_id, pickup_location_id, dropoff_location_id, start_date, end_date, total_price, status) VALUES
(2, 1, 1, 2, '2026-03-01', '2026-03-03', 36000.00, 'completed'),
(3, 8, 2, 2, '2026-03-05', '2026-03-07', 28000.00, 'completed'),
(4, 10, 3, 1, '2026-03-10', '2026-03-14', 38000.00, 'completed'),
(5, 3, 1, 1, '2026-03-15', '2026-03-16', 9000.00, 'confirmed'),
(2, 13, 4, 5, '2026-03-20', '2026-03-21', 19000.00, 'pending'),
(3, 21, 3, 3, '2026-04-01', '2026-04-05', 56000.00, 'confirmed'),
(4, 16, 2, 2, '2026-04-02', '2026-04-04', 9000.00, 'pending'),
(5, 7, 1, 1, '2026-04-10', '2026-04-12', 50000.00, 'confirmed');

-- Sample Reviews
INSERT INTO reviews (user_id, vehicle_id, rating, comment) VALUES
(2, 1, 5, 'Absolutely brilliant! Land Cruiser handled the mountain pass like a dream. Will definitely rent again.'),
(3, 8, 5, 'The Tesla Model 3 was a dream to drive. Silent, fast, and Autopilot made highway driving effortless.'),
(4, 10, 4, 'Camry Hybrid is perfect. Fuel efficiency was amazing. Comfortable for a 4-day trip.'),
(5, 3, 4, 'Creta N-Line is super fun. The sunroof was the highlight. Minor pickup delay is the only complaint.'),
(2, 13, 5, 'BMW 3 Series - totally worth it! Premium feel and incredible performance. Love this car!'),
(3, 16, 5, 'Swift is perfect for Pokhara''s streets. Excellent mileage. Best value for money I have found.'),
(4, 21, 4, 'Great pickup truck for Chitwan trip. Handled rough roads with ease. Highly recommended!');
