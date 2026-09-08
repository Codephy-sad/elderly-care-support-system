-- =========================================================================
-- Elderly Care & Support Platform — Migration: Family Tables
-- Adds visits and memories tables for the Family / Guardian portal
-- =========================================================================

USE elderly_care;

SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------------------------
-- visits — scheduled visits by family members
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS visits (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    elderly_profile_id INT UNSIGNED NOT NULL,
    family_user_id INT UNSIGNED NOT NULL,
    visit_date DATE NOT NULL,
    visit_time TIME NOT NULL,
    duration_minutes INT UNSIGNED NULL DEFAULT 60,
    status ENUM('pending', 'approved', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_visits_elderly (elderly_profile_id),
    KEY idx_visits_family_user (family_user_id),
    CONSTRAINT fk_visits_elderly
        FOREIGN KEY (elderly_profile_id) REFERENCES elderly_profiles (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_visits_family_user
        FOREIGN KEY (family_user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- memories — photos and memories uploaded by family or staff
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS memories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    elderly_profile_id INT UNSIGNED NOT NULL,
    uploaded_by INT UNSIGNED NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    caption TEXT NULL,
    date_recorded DATE NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_memories_elderly (elderly_profile_id),
    KEY idx_memories_uploaded_by (uploaded_by),
    CONSTRAINT fk_memories_elderly
        FOREIGN KEY (elderly_profile_id) REFERENCES elderly_profiles (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_memories_uploaded_by
        FOREIGN KEY (uploaded_by) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================================
-- Seed data for testing/demo
-- =========================================================================

-- We know user_id=2 (sadat) is an elderly. Let's create a family member.
-- Role ID for Family is 2.
INSERT IGNORE INTO users (id, role_id, name, email, password_hash, active_status)
VALUES (3, 2, 'Hasib Uddin', 'hasib@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- create an approved family connection
INSERT IGNORE INTO family_connections (elderly_profile_id, family_user_id, relationship, status)
SELECT ep.id, 3, 'Son', 'approved'
FROM elderly_profiles ep WHERE ep.user_id = 2;

-- seed visits
INSERT INTO visits (elderly_profile_id, family_user_id, visit_date, visit_time, status, notes)
SELECT ep.id, 3, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '15:00:00', 'approved', 'Bringing some home-cooked food.'
FROM elderly_profiles ep WHERE ep.user_id = 2;

-- seed memories (assume there's a placeholder image in assets/img/placeholder_memory.jpg)
INSERT INTO memories (elderly_profile_id, uploaded_by, file_path, caption, date_recorded)
SELECT ep.id, 3, 'placeholder_memory.jpg', 'Eid day with grandfather', DATE_SUB(CURDATE(), INTERVAL 20 DAY)
FROM elderly_profiles ep WHERE ep.user_id = 2;
