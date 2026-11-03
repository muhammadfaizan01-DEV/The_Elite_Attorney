-- ============================================================
-- Lawyers Website Database Schema
-- Database: lawyer_db
-- ============================================================

CREATE DATABASE IF NOT EXISTS lawyers_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lawyers_db;

-- ============================================================
-- Table: users (Admin & Customers)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)  NOT NULL,
    email       VARCHAR(150)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,
    role        ENUM('admin','customer') DEFAULT 'customer',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin account (password: Admin@123)
INSERT INTO users (name, email, password, role) VALUES
('Super Admin', 'admin@eliteattorney.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- ============================================================
-- Table: lawyers
-- ============================================================
CREATE TABLE IF NOT EXISTS lawyers (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100)  NOT NULL,
    email           VARCHAR(150)  NOT NULL UNIQUE,
    password        VARCHAR(255)  NOT NULL,
    phone           VARCHAR(20),
    specialization  VARCHAR(150),
    experience      INT DEFAULT 0,          -- years
    bio             TEXT,
    photo           VARCHAR(255) DEFAULT 'default.jpg',
    fee             DECIMAL(10,2) DEFAULT 0.00,
    bar_number      VARCHAR(50),
    city            VARCHAR(100),
    is_approved     TINYINT(1) DEFAULT 0,   -- admin approval required
    is_active       TINYINT(1) DEFAULT 1,
    rating          DECIMAL(3,2) DEFAULT 0.00,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- Table: services
-- ============================================================
CREATE TABLE IF NOT EXISTS services (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    icon        VARCHAR(50) DEFAULT 'fa-balance-scale',
    is_active   TINYINT(1) DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample services
INSERT INTO services (name, description, icon) VALUES
('Criminal Law',    'Defense and prosecution in criminal cases',           'fa-gavel'),
('Family Law',      'Divorce, custody, and family disputes',               'fa-home'),
('Corporate Law',   'Business formation, contracts, mergers',              'fa-briefcase'),
('Real Estate',     'Property transactions and disputes',                  'fa-building'),
('Immigration',     'Visa, citizenship and immigration matters',           'fa-globe'),
('Civil Litigation','Court representation in civil matters',              'fa-balance-scale');

-- ============================================================
-- Table: lawyer_services (many-to-many)
-- ============================================================
CREATE TABLE IF NOT EXISTS lawyer_services (
    lawyer_id   INT NOT NULL,
    service_id  INT NOT NULL,
    PRIMARY KEY (lawyer_id, service_id),
    FOREIGN KEY (lawyer_id)  REFERENCES lawyers(id)  ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- ============================================================
-- Table: appointments
-- ============================================================
CREATE TABLE IF NOT EXISTS appointments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    customer_id     INT NOT NULL,
    lawyer_id       INT NOT NULL,
    appt_date       DATE NOT NULL,
    appt_time       TIME NOT NULL,
    message         TEXT,
    status          ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (lawyer_id)   REFERENCES lawyers(id)  ON DELETE CASCADE
);

-- ============================================================
-- Table: reviews
-- ============================================================
CREATE TABLE IF NOT EXISTS reviews (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    lawyer_id   INT NOT NULL,
    rating      TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment     TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (lawyer_id)   REFERENCES lawyers(id) ON DELETE CASCADE
);

-- ============================================================
-- Table: contact_messages
-- ============================================================
CREATE TABLE IF NOT EXISTS contact_messages (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    subject     VARCHAR(200),
    message     TEXT NOT NULL,
    is_read     TINYINT(1) DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
