-- Elderly Care & Support Platform
-- Schema for database: elderly_care
-- Engine: InnoDB, charset: utf8mb4

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE DATABASE IF NOT EXISTS elderly_care
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE elderly_care;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS service_requests;
DROP TABLE IF EXISTS room_assignments;
DROP TABLE IF EXISTS family_connections;
DROP TABLE IF EXISTS elderly_profiles;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------------
-- roles
-- ---------------------------------------------------------------------------
CREATE TABLE roles (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- users
-- ---------------------------------------------------------------------------
CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    role_id INT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    active_status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role_id (role_id),
    CONSTRAINT fk_users_role
        FOREIGN KEY (role_id) REFERENCES roles (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- elderly_profiles
-- ---------------------------------------------------------------------------
CREATE TABLE elderly_profiles (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    date_of_birth DATE NULL,
    gender VARCHAR(20) NULL,
    blood_group VARCHAR(10) NULL,
    medical_conditions TEXT NULL,
    allergies TEXT NULL,
    dietary_requirements TEXT NULL,
    mobility_notes TEXT NULL,
    emergency_contact_name VARCHAR(120) NULL,
    emergency_contact_phone VARCHAR(30) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_elderly_profiles_user_id (user_id),
    CONSTRAINT fk_elderly_profiles_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- family_connections
-- ---------------------------------------------------------------------------
CREATE TABLE family_connections (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    elderly_profile_id INT UNSIGNED NOT NULL,
    family_user_id INT UNSIGNED NOT NULL,
    relationship VARCHAR(80) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_family_connection (elderly_profile_id, family_user_id),
    KEY idx_family_connections_family_user (family_user_id),
    CONSTRAINT fk_family_connections_elderly
        FOREIGN KEY (elderly_profile_id) REFERENCES elderly_profiles (id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_family_connections_family_user
        FOREIGN KEY (family_user_id) REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- rooms
-- ---------------------------------------------------------------------------
CREATE TABLE rooms (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    room_number VARCHAR(20) NOT NULL,
    floor VARCHAR(20) NULL,
    capacity TINYINT UNSIGNED NOT NULL DEFAULT 1,
    room_type VARCHAR(40) NOT NULL DEFAULT 'standard',
    status ENUM('available', 'occupied', 'maintenance') NOT NULL DEFAULT 'available',
    PRIMARY KEY (id),
    UNIQUE KEY uq_rooms_room_number (room_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- room_assignments
-- ---------------------------------------------------------------------------
CREATE TABLE room_assignments (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    room_id INT UNSIGNED NOT NULL,
    elderly_profile_id INT UNSIGNED NOT NULL,
    assigned_by INT UNSIGNED NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_room_assignments_room (room_id),
    KEY idx_room_assignments_elderly (elderly_profile_id),
    KEY idx_room_assignments_assigned_by (assigned_by),
    CONSTRAINT fk_room_assignments_room
        FOREIGN KEY (room_id) REFERENCES rooms (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_room_assignments_elderly
        FOREIGN KEY (elderly_profile_id) REFERENCES elderly_profiles (id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_room_assignments_assigned_by
        FOREIGN KEY (assigned_by) REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- service_requests
-- ---------------------------------------------------------------------------
CREATE TABLE service_requests (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    elderly_profile_id INT UNSIGNED NOT NULL,
    requested_by INT UNSIGNED NOT NULL,
    assigned_to INT UNSIGNED NULL,
    request_type VARCHAR(50) NOT NULL,
    title VARCHAR(160) NOT NULL,
    description TEXT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
    status ENUM('open', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'open',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_service_requests_elderly (elderly_profile_id),
    KEY idx_service_requests_requested_by (requested_by),
    KEY idx_service_requests_assigned_to (assigned_to),
    KEY idx_service_requests_status (status),
    CONSTRAINT fk_service_requests_elderly
        FOREIGN KEY (elderly_profile_id) REFERENCES elderly_profiles (id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_service_requests_requested_by
        FOREIGN KEY (requested_by) REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_service_requests_assigned_to
        FOREIGN KEY (assigned_to) REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- audit_logs
-- ---------------------------------------------------------------------------
CREATE TABLE audit_logs (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NULL,
    action VARCHAR(80) NOT NULL,
    entity_type VARCHAR(80) NULL,
    entity_id INT UNSIGNED NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_audit_logs_user (user_id),
    KEY idx_audit_logs_entity (entity_type, entity_id),
    CONSTRAINT fk_audit_logs_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Seed: default roles
-- ---------------------------------------------------------------------------
INSERT INTO roles (name) VALUES
    ('Elderly'),
    ('Family'),
    ('Caregiver'),
    ('Kitchen'),
    ('Manager'),
    ('Admin'),
    ('Donor'),
    ('Volunteer');

-- ---------------------------------------------------------------------------
-- Seed: default Admin (password: admin123)
-- Hash is bcrypt via password_hash() / password_verify()
-- ---------------------------------------------------------------------------
INSERT INTO users (role_id, name, email, password_hash, active_status)
SELECT id, 'System Admin', 'admin@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1
FROM roles
WHERE name = 'Admin'
LIMIT 1;
