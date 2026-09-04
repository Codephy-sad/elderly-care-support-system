-- =========================================================================
-- Elderly Care & Support Platform — Migration: Add feature tables
-- 16 new tables for meals, medications, activities, etc.
-- Run AFTER the base elderly_care.sql schema is already in place.
-- =========================================================================

USE elderly_care;

SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------------------------
-- menus — daily meal plan container
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS menus (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    meal_date DATE NOT NULL,
    title VARCHAR(160) NULL,
    notes TEXT NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_menus_date (meal_date),
    KEY idx_menus_created_by (created_by),
    CONSTRAINT fk_menus_created_by
        FOREIGN KEY (created_by) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- meals — individual dishes within a menu
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS meals (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    menu_id INT UNSIGNED NOT NULL,
    meal_type ENUM('breakfast','lunch','dinner','snack') NOT NULL,
    title VARCHAR(160) NOT NULL,
    description TEXT NULL,
    dietary_tags VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_meals_menu (menu_id),
    CONSTRAINT fk_meals_menu
        FOREIGN KEY (menu_id) REFERENCES menus (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- meal_assignments — planned: which resident gets which dish
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS meal_assignments (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    meal_id INT UNSIGNED NOT NULL,
    elderly_profile_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_meal_assignment (meal_id, elderly_profile_id),
    KEY idx_meal_assignments_elderly (elderly_profile_id),
    CONSTRAINT fk_meal_assignments_meal
        FOREIGN KEY (meal_id) REFERENCES meals (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_meal_assignments_elderly
        FOREIGN KEY (elderly_profile_id) REFERENCES elderly_profiles (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- meal_distributions — execution: was the dish actually served?
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS meal_distributions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    meal_id INT UNSIGNED NOT NULL,
    elderly_profile_id INT UNSIGNED NOT NULL,
    status ENUM('pending','served','skipped') NOT NULL DEFAULT 'pending',
    notes TEXT NULL,
    served_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_meal_distribution (meal_id, elderly_profile_id),
    KEY idx_meal_distributions_elderly (elderly_profile_id),
    CONSTRAINT fk_meal_distributions_meal
        FOREIGN KEY (meal_id) REFERENCES meals (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_meal_distributions_elderly
        FOREIGN KEY (elderly_profile_id) REFERENCES elderly_profiles (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- medications — per-resident medication schedule
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS medications (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    elderly_profile_id INT UNSIGNED NOT NULL,
    name VARCHAR(160) NOT NULL,
    dosage VARCHAR(100) NULL,
    frequency VARCHAR(100) NOT NULL,
    time_slot TIME NOT NULL,
    instructions TEXT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_medications_elderly (elderly_profile_id),
    CONSTRAINT fk_medications_elderly
        FOREIGN KEY (elderly_profile_id) REFERENCES elderly_profiles (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- medication_events — each dose occurrence and its outcome
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS medication_events (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    medication_id INT UNSIGNED NOT NULL,
    event_date DATE NOT NULL,
    status ENUM('pending','taken','missed','skipped') NOT NULL DEFAULT 'pending',
    taken_at TIMESTAMP NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_med_event (medication_id, event_date),
    CONSTRAINT fk_med_events_medication
        FOREIGN KEY (medication_id) REFERENCES medications (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- care_plans — personalized wellness guidance
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS care_plans (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    elderly_profile_id INT UNSIGNED NOT NULL,
    title VARCHAR(160) NOT NULL,
    description TEXT NULL,
    category VARCHAR(50) NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_care_plans_elderly (elderly_profile_id),
    KEY idx_care_plans_created_by (created_by),
    CONSTRAINT fk_care_plans_elderly
        FOREIGN KEY (elderly_profile_id) REFERENCES elderly_profiles (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_care_plans_created_by
        FOREIGN KEY (created_by) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- activities — facility-wide activities
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS activities (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(160) NOT NULL,
    description TEXT NULL,
    activity_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NULL,
    location VARCHAR(120) NULL,
    max_participants INT UNSIGNED NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_activities_date (activity_date),
    KEY idx_activities_created_by (created_by),
    CONSTRAINT fk_activities_created_by
        FOREIGN KEY (created_by) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- activity_registrations — elderly registers for activities
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS activity_registrations (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    activity_id INT UNSIGNED NOT NULL,
    elderly_profile_id INT UNSIGNED NOT NULL,
    status ENUM('registered','attended','cancelled') NOT NULL DEFAULT 'registered',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_activity_reg (activity_id, elderly_profile_id),
    KEY idx_activity_reg_elderly (elderly_profile_id),
    CONSTRAINT fk_activity_reg_activity
        FOREIGN KEY (activity_id) REFERENCES activities (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_activity_reg_elderly
        FOREIGN KEY (elderly_profile_id) REFERENCES elderly_profiles (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- emergency_alerts — fast emergency help requests
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS emergency_alerts (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    elderly_profile_id INT UNSIGNED NOT NULL,
    alert_type ENUM('medical','fall','fire','other') NOT NULL DEFAULT 'medical',
    message TEXT NULL,
    status ENUM('active','acknowledged','resolved') NOT NULL DEFAULT 'active',
    resolved_by INT UNSIGNED NULL,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_emergency_elderly (elderly_profile_id),
    KEY idx_emergency_status (status),
    KEY idx_emergency_resolved_by (resolved_by),
    CONSTRAINT fk_emergency_elderly
        FOREIGN KEY (elderly_profile_id) REFERENCES elderly_profiles (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_emergency_resolved_by
        FOREIGN KEY (resolved_by) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- notifications — system-generated alerts
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(160) NOT NULL,
    message TEXT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'info',
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    link VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_notifications_user (user_id),
    KEY idx_notifications_read (is_read),
    CONSTRAINT fk_notifications_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- messages — person-to-person communication
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS messages (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    sender_id INT UNSIGNED NOT NULL,
    receiver_id INT UNSIGNED NOT NULL,
    subject VARCHAR(160) NULL,
    body TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_messages_sender (sender_id),
    KEY idx_messages_receiver (receiver_id),
    CONSTRAINT fk_messages_sender
        FOREIGN KEY (sender_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_messages_receiver
        FOREIGN KEY (receiver_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- invoices — bills generated for elderly residents
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS invoices (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    elderly_profile_id INT UNSIGNED NOT NULL,
    invoice_number VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description VARCHAR(255) NULL,
    due_date DATE NULL,
    status ENUM('unpaid','partial','paid','cancelled') NOT NULL DEFAULT 'unpaid',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_invoice_number (invoice_number),
    KEY idx_invoices_elderly (elderly_profile_id),
    CONSTRAINT fk_invoices_elderly
        FOREIGN KEY (elderly_profile_id) REFERENCES elderly_profiles (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- payments — actual payment records against invoices
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payments (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    invoice_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NULL,
    paid_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_payments_invoice (invoice_id),
    CONSTRAINT fk_payments_invoice
        FOREIGN KEY (invoice_id) REFERENCES invoices (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- transportation_requests
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS transportation_requests (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    elderly_profile_id INT UNSIGNED NOT NULL,
    destination VARCHAR(255) NOT NULL,
    pickup_date DATE NOT NULL,
    pickup_time TIME NOT NULL,
    purpose VARCHAR(160) NULL,
    status ENUM('pending','approved','completed','cancelled') NOT NULL DEFAULT 'pending',
    approved_by INT UNSIGNED NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_transport_elderly (elderly_profile_id),
    KEY idx_transport_approved_by (approved_by),
    CONSTRAINT fk_transport_elderly
        FOREIGN KEY (elderly_profile_id) REFERENCES elderly_profiles (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_transport_approved_by
        FOREIGN KEY (approved_by) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- room_change_requests
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS room_change_requests (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    elderly_profile_id INT UNSIGNED NOT NULL,
    current_room_id INT UNSIGNED NULL,
    preferred_room_id INT UNSIGNED NULL,
    reason TEXT NOT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    reviewed_by INT UNSIGNED NULL,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_room_change_elderly (elderly_profile_id),
    KEY idx_room_change_current (current_room_id),
    KEY idx_room_change_preferred (preferred_room_id),
    KEY idx_room_change_reviewed_by (reviewed_by),
    CONSTRAINT fk_room_change_elderly
        FOREIGN KEY (elderly_profile_id) REFERENCES elderly_profiles (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_room_change_current
        FOREIGN KEY (current_room_id) REFERENCES rooms (id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_room_change_preferred
        FOREIGN KEY (preferred_room_id) REFERENCES rooms (id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_room_change_reviewed_by
        FOREIGN KEY (reviewed_by) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================================
-- Seed data for testing/demo
-- =========================================================================

-- rooms
INSERT INTO rooms (room_number, floor, capacity, room_type, status) VALUES
    ('101', '1', 1, 'standard', 'occupied'),
    ('102', '1', 1, 'standard', 'available'),
    ('103', '1', 2, 'shared',   'available'),
    ('201', '2', 1, 'standard', 'available'),
    ('202', '2', 1, 'premium',  'available'),
    ('301', '3', 1, 'standard', 'maintenance');

-- elderly profile for the test user (user_id = 2, "sadat")
INSERT INTO elderly_profiles (user_id, date_of_birth, gender, blood_group, medical_conditions, allergies, dietary_requirements, mobility_notes, emergency_contact_name, emergency_contact_phone)
VALUES (2, '1955-03-15', 'Male', 'B+', 'Type 2 Diabetes, Mild Hypertension', 'Penicillin', 'Low-sodium, diabetic-friendly', 'Uses a walking cane', 'Karim Uddin', '+880-1700-000001');

-- room assignment for the test elderly user
INSERT INTO room_assignments (room_id, elderly_profile_id, assigned_by, start_date)
SELECT r.id, ep.id, 1, CURDATE()
FROM rooms r, elderly_profiles ep
WHERE r.room_number = '101' AND ep.user_id = 2;

-- today's menu + meals
INSERT INTO menus (meal_date, title, created_by) VALUES
    (CURDATE(), 'Daily Menu', 1);

SET @menu_id = LAST_INSERT_ID();

INSERT INTO meals (menu_id, meal_type, title, description, dietary_tags) VALUES
    (@menu_id, 'breakfast', 'Oatmeal with Banana', 'Warm oatmeal topped with sliced banana and a drizzle of honey.', 'diabetic-friendly, high-fiber'),
    (@menu_id, 'lunch',     'Grilled Fish with Rice', 'Baked tilapia fillet with steamed jasmine rice and mixed vegetables.', 'low-sodium, high-protein'),
    (@menu_id, 'dinner',    'Chicken Soup with Bread', 'Clear chicken broth with vegetables, served with whole wheat bread.', 'low-sodium'),
    (@menu_id, 'snack',     'Fruit Cup', 'Seasonal fresh fruit mix.', 'diabetic-friendly');

-- assign today's meals to the test elderly user
INSERT INTO meal_assignments (meal_id, elderly_profile_id)
SELECT m.id, ep.id
FROM meals m
JOIN elderly_profiles ep ON ep.user_id = 2
WHERE m.menu_id = @menu_id;

-- mark breakfast as served
INSERT INTO meal_distributions (meal_id, elderly_profile_id, status, served_at)
SELECT m.id, ep.id, 'served', DATE_SUB(NOW(), INTERVAL 3 HOUR)
FROM meals m
JOIN elderly_profiles ep ON ep.user_id = 2
WHERE m.menu_id = @menu_id AND m.meal_type = 'breakfast';

-- medications for the test elderly user
INSERT INTO medications (elderly_profile_id, name, dosage, frequency, time_slot, instructions) 
SELECT ep.id, 'Metformin 500mg', '1 tablet', 'Twice daily', '08:00:00', 'Take with food. Do not skip meals.'
FROM elderly_profiles ep WHERE ep.user_id = 2;

INSERT INTO medications (elderly_profile_id, name, dosage, frequency, time_slot, instructions)
SELECT ep.id, 'Amlodipine 5mg', '1 tablet', 'Once daily', '09:00:00', 'Take in the morning.'
FROM elderly_profiles ep WHERE ep.user_id = 2;

INSERT INTO medications (elderly_profile_id, name, dosage, frequency, time_slot, instructions)
SELECT ep.id, 'Vitamin D3', '1 capsule', 'Once daily', '13:00:00', 'Take after lunch.'
FROM elderly_profiles ep WHERE ep.user_id = 2;

-- medication events for today
INSERT INTO medication_events (medication_id, event_date, status, taken_at)
SELECT id, CURDATE(), 'taken', DATE_SUB(NOW(), INTERVAL 4 HOUR)
FROM medications WHERE name = 'Metformin 500mg' LIMIT 1;

INSERT INTO medication_events (medication_id, event_date, status)
SELECT id, CURDATE(), 'pending'
FROM medications WHERE name = 'Amlodipine 5mg' LIMIT 1;

INSERT INTO medication_events (medication_id, event_date, status)
SELECT id, CURDATE(), 'pending'
FROM medications WHERE name = 'Vitamin D3' LIMIT 1;

-- care plans
INSERT INTO care_plans (elderly_profile_id, title, description, category, created_by)
SELECT ep.id, 'Morning Stretch Routine', 'Gentle stretching exercises for 15 minutes after waking up. Focus on shoulders, neck and legs.', 'exercise', 1
FROM elderly_profiles ep WHERE ep.user_id = 2;

INSERT INTO care_plans (elderly_profile_id, title, description, category, created_by)
SELECT ep.id, 'Blood Sugar Monitoring', 'Check fasting blood sugar before breakfast. Log readings in the health diary.', 'health', 1
FROM elderly_profiles ep WHERE ep.user_id = 2;

-- activities (facility-wide)
INSERT INTO activities (title, description, activity_date, start_time, end_time, location, max_participants, created_by) VALUES
    ('Morning Yoga', 'Gentle yoga session suitable for all mobility levels.', CURDATE(), '07:30:00', '08:15:00', 'Garden Hall', 20, 1),
    ('Art & Craft Workshop', 'Painting and paper craft session. Materials provided.', CURDATE(), '10:00:00', '11:30:00', 'Activity Room B', 15, 1),
    ('Movie Afternoon', 'Classic film screening with popcorn.', CURDATE(), '14:00:00', '16:00:00', 'Common Room', NULL, 1),
    ('Evening Walk', 'Guided walk around the garden.', CURDATE(), '17:00:00', '17:45:00', 'Garden', 12, 1);

-- register the test elderly user for yoga
INSERT INTO activity_registrations (activity_id, elderly_profile_id, status)
SELECT a.id, ep.id, 'registered'
FROM activities a, elderly_profiles ep
WHERE a.title = 'Morning Yoga' AND ep.user_id = 2;

-- a sample service request
INSERT INTO service_requests (elderly_profile_id, requested_by, request_type, title, description, priority, status)
SELECT ep.id, 2, 'maintenance', 'Light bulb needs replacing', 'The ceiling light in my room is flickering.', 'medium', 'open'
FROM elderly_profiles ep WHERE ep.user_id = 2;

INSERT INTO service_requests (elderly_profile_id, requested_by, request_type, title, description, priority, status)
SELECT ep.id, 2, 'housekeeping', 'Extra blanket', 'Could I get an extra blanket? It is getting cold at night.', 'low', 'in_progress'
FROM elderly_profiles ep WHERE ep.user_id = 2;

-- a sample notification
INSERT INTO notifications (user_id, title, message, type, link) VALUES
    (2, 'Welcome to Elderly Care!', 'Your profile has been set up. Explore your dashboard to see meals, medications, and activities.', 'info', '../elderly/index.php'),
    (2, 'Medication Reminder', 'It is time to take your Amlodipine 5mg.', 'warning', '../elderly/medications.php');

-- a sample invoice + payment
INSERT INTO invoices (elderly_profile_id, invoice_number, amount, description, due_date, status)
SELECT ep.id, 'INV-2026-0001', 15000.00, 'Room fee — September 2026', '2026-09-30', 'unpaid'
FROM elderly_profiles ep WHERE ep.user_id = 2;

-- a sample transportation request
INSERT INTO transportation_requests (elderly_profile_id, destination, pickup_date, pickup_time, purpose, status)
SELECT ep.id, 'Dhaka Medical College Hospital', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '09:00:00', 'Routine check-up with Dr. Rahman', 'pending'
FROM elderly_profiles ep WHERE ep.user_id = 2;
