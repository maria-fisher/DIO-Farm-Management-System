-- Farm Management Database Schema

CREATE DATABASE IF NOT EXISTS farm_management;
USE farm_management;

-- Crops table
CREATE TABLE crops (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    variety VARCHAR(100),
    planting_date DATE,
    harvest_date DATE,
    area_acres DECIMAL(10,2),
    expected_yield DECIMAL(10,2),
    actual_yield DECIMAL(10,2),
    status ENUM('planted', 'growing', 'harvested', 'sold') DEFAULT 'planted',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Livestock table
CREATE TABLE livestock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    breed VARCHAR(100),
    tag_number VARCHAR(50) UNIQUE,
    birth_date DATE,
    gender ENUM('male', 'female'),
    weight DECIMAL(8,2),
    health_status VARCHAR(100) DEFAULT 'healthy',
    purchase_price DECIMAL(10,2),
    purchase_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- finance table
CREATE TABLE finance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_type ENUM('income', 'expense') NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    amount DECIMAL(12,2) NOT NULL,
    transaction_date DATE NOT NULL,
    payment_method VARCHAR(50),
    reference_number VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Soil table
CREATE TABLE soil (
    id INT AUTO_INCREMENT PRIMARY KEY,
    field_name VARCHAR(100) NOT NULL,
    location VARCHAR(200),
    ph_level DECIMAL(3,2),
    nitrogen_level DECIMAL(8,2),
    phosphorus_level DECIMAL(8,2),
    potassium_level DECIMAL(8,2),
    organic_matter DECIMAL(5,2),
    test_date DATE,
    recommendations TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Weather table using international metric units (SI)
CREATE TABLE weather (
    id INT AUTO_INCREMENT PRIMARY KEY,
    record_date DATE NOT NULL,
    
    temperature_high_c DECIMAL(5,2),  -- Celsius
    temperature_low_c DECIMAL(5,2),   -- Celsius
    humidity_percent DECIMAL(5,2),    -- Percent (%)
    rainfall_mm DECIMAL(6,2),         -- Millimeters (mm)
    wind_speed_kph DECIMAL(5,2),      -- Kilometers per hour (km/h)
    
    conditions VARCHAR(100),
    notes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


-- Insert sample data
-- Crops
INSERT INTO crops (name, variety, planting_date, harvest_date, area_acres, expected_yield, actual_yield, status) VALUES
('Corn', 'Sweet Corn', '2024-05-15', '2024-09-15', 25.5, 150.0, 145.0, 'harvested'),
('Wheat', 'Winter Wheat', '2024-10-01', '2025-07-15', 40.0, 60.0, NULL, 'growing'),
('Soybeans', 'Early Maturity', '2024-06-01', '2024-10-30', 30.0, 45.0, 48.0, 'harvested'),
('Barley', 'Spring Barley', '2023-03-20', '2023-07-10', 18.0, 50.0, 47.5, 'harvested'),
('Canola', 'Hybrid', '2023-08-01', '2023-11-25', 22.0, 40.0, 42.0, 'harvested'),
('Oats', 'Black Oats', '2024-03-05', '2024-07-01', 15.0, 35.0, 34.5, 'harvested'),
('Rice', 'Long Grain', '2025-03-15', '2025-07-30', 28.0, 80.0, NULL, 'planted'),
('Sunflower', 'Oilseed', '2025-04-01', '2025-08-20', 20.0, 55.0, NULL, 'planted');

INSERT INTO livestock (type, breed, tag_number, birth_date, gender, weight, health_status, purchase_price, purchase_date) VALUES
('Cattle', 'Angus', 'A001', '2023-03-15', 'female', 1200.00, 'healthy', 1500.00, '2023-04-01'),
('Pig', 'Yorkshire', 'P001', '2024-01-10', 'male', 250.00, 'healthy', 300.00, '2024-02-01'),
('Chicken', 'Rhode Island Red', 'C001', '2024-03-01', 'female', 5.50, 'healthy', 25.00, '2024-03-15'),
('Goat', 'Boer', 'G001', '2023-09-05', 'female', 75.0, 'healthy', 120.00, '2023-10-01'),
('Sheep', 'Merino', 'S001', '2024-02-15', 'male', 80.0, 'injured', 100.00, '2024-03-01'),
('Cattle', 'Hereford', 'A002', '2025-01-10', 'male', 900.0, 'healthy', 1400.00, '2025-02-01'),
('Chicken', 'Leghorn', 'C002', '2025-04-01', 'female', 4.9, 'healthy', 22.00, '2025-04-15');

-- Finance
INSERT INTO finance (transaction_type, category, description, amount, transaction_date, payment_method) VALUES
('income', 'Crop Sales', 'Corn harvest sale', 12500.00, '2024-09-20', 'Bank Transfer'),
('expense', 'Seeds', 'Wheat seeds for winter planting', 800.00, '2024-09-25', 'Credit Card'),
('income', 'Livestock Sales', 'Pig sale to local market', 650.00, '2024-10-15', 'Cash'),
('expense', 'Feed', 'Cattle feed monthly supply', 450.00, '2024-11-01', 'Check'),
('income', 'Crop Sales', 'Barley harvest sale', 9000.00, '2023-07-15', 'Bank Transfer'),
('expense', 'Veterinary', 'Goat vaccination', 75.00, '2023-10-10', 'Cash'),
('expense', 'Equipment', 'New irrigation pump', 1200.00, '2024-05-01', 'Credit Card'),
('income', 'Subsidy', 'Government crop support', 5000.00, '2025-01-10', 'Bank Transfer'),
('expense', 'Fuel', 'Tractor diesel refill', 300.00, '2025-03-05', 'Check'),
('income', 'Livestock Sales', 'Sheep auction', 450.00, '2025-04-01', 'Cash');

INSERT INTO soil (field_name, location, ph_level, nitrogen_level, phosphorus_level, potassium_level, organic_matter, test_date) VALUES
('North Field', 'Section A', 6.8, 45.2, 38.5, 210.0, 3.2, '2024-08-15'),
('South Field', 'Section B', 6.2, 32.1, 42.0, 180.5, 2.8, '2024-08-15'),
('East Pasture', 'Section C', 7.1, 28.5, 35.0, 195.0, 4.1, '2024-08-20'),
('West Plot', 'Section D', 6.5, 40.0, 36.0, 200.0, 3.5, '2023-09-01'),
('Greenhouse', 'Section E', 6.9, 55.0, 48.0, 220.0, 4.8, '2023-11-10'),
('Lowland Field', 'Section F', 6.3, 38.0, 41.0, 175.0, 3.0, '2025-02-15'),
('Upper Ridge', 'Section G', 7.2, 30.0, 33.5, 185.0, 2.6, '2025-04-20');

-- Weather
INSERT INTO weather (
    record_date, temperature_high_c, temperature_low_c,
    humidity_percent, rainfall_mm, wind_speed_kph, conditions, notes
) VALUES
-- Fall 2024
('2024-11-01', 22.5, 7.3, 65.0, 0.0, 13.68, 'Clear', NULL),
('2024-11-02', 20.0, 5.6, 70.0, 6.35, 19.31, 'Partly Cloudy', NULL),
('2024-11-03', 18.6, 3.6, 80.0, 30.48, 24.95, 'Rainy', NULL),
('2024-11-04', 21.1, 6.7, 60.0, 0.0, 9.66, 'Sunny', NULL),
('2024-11-10', 19.3, 4.2, 75.0, 12.5, 20.1, 'Overcast', NULL),
('2024-11-20', 16.0, 2.0, 85.0, 20.0, 28.0, 'Rainy', NULL),

-- Winter 2024–2025
('2024-12-05', 10.5, -1.2, 60.0, 0.0, 14.0, 'Clear', NULL),
('2024-12-15', 7.8, -3.4, 68.0, 5.2, 10.5, 'Snow', NULL),
('2024-12-25', 5.0, -5.0, 72.0, 2.0, 8.0, 'Cloudy', NULL),
('2025-01-05', 3.2, -7.0, 85.0, 8.5, 12.5, 'Snow', NULL),
('2025-01-15', 2.0, -6.5, 90.0, 0.0, 15.0, 'Freezing Fog', NULL),

-- Spring 2025
('2025-03-01', 12.5, 2.5, 60.0, 10.0, 18.0, 'Rain Showers', NULL),
('2025-03-15', 15.0, 5.0, 58.0, 0.0, 22.0, 'Sunny', NULL),
('2025-04-01', 18.5, 8.0, 55.0, 1.0, 16.0, 'Partly Cloudy', NULL),
('2025-04-15', 21.0, 10.2, 50.0, 0.0, 14.3, 'Sunny', NULL),
('2025-05-01', 24.5, 12.1, 65.0, 5.0, 20.0, 'Humid', NULL),

-- Summer 2025
('2025-06-01', 30.0, 18.5, 55.0, 0.0, 15.0, 'Hot', NULL),
('2025-06-15', 33.2, 20.0, 60.0, 2.0, 18.0, 'Hot & Humid', NULL),
('2025-07-01', 35.0, 22.0, 70.0, 8.0, 25.0, 'Thunderstorms', NULL),
('2025-07-15', 36.5, 23.5, 65.0, 0.0, 27.0, 'Clear', NULL),
('2025-08-01', 34.0, 21.0, 68.0, 0.0, 20.0, 'Hot', NULL),

-- Fall 2025
('2025-09-01', 29.0, 17.0, 60.0, 0.0, 15.0, 'Sunny', NULL),
('2025-09-15', 25.0, 14.0, 62.0, 0.0, 18.0, 'Clear', NULL),
('2025-10-01', 22.0, 11.0, 70.0, 3.0, 20.0, 'Light Rain', NULL),
('2025-10-15', 19.5, 8.0, 75.0, 12.0, 22.0, 'Overcast', NULL),
('2025-11-01', 17.0, 6.0, 80.0, 6.5, 19.0, 'Partly Cloudy', NULL);
