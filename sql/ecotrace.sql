-- ============================================================
-- EcoTrace - E-Waste Management System
-- Import this file in phpMyAdmin (Import tab) before running the site.
-- ============================================================

CREATE DATABASE IF NOT EXISTS ecotrace
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecotrace;

-- ---------- Users ----------
CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100)  NOT NULL,
  email         VARCHAR(150)  NOT NULL UNIQUE,
  phone         VARCHAR(20)   DEFAULT NULL,
  password_hash VARCHAR(255)  NOT NULL,
  role          ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- Collection centers ----------
CREATE TABLE IF NOT EXISTS centers (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  name      VARCHAR(150) NOT NULL,
  address   VARCHAR(255) NOT NULL,
  city      VARCHAR(80)  NOT NULL,
  pincode   VARCHAR(10)  NOT NULL,
  phone     VARCHAR(20)  DEFAULT NULL,
  timings   VARCHAR(100) DEFAULT '10:00 AM - 6:00 PM',
  accepts   VARCHAR(255) DEFAULT 'Phones, Laptops, Batteries, Cables',
  is_active TINYINT(1)   NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- ---------- Pickup requests ----------
CREATE TABLE IF NOT EXISTS requests (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  tracking_code  VARCHAR(20)  NOT NULL UNIQUE,
  user_id        INT          NOT NULL,
  device_type    VARCHAR(60)  NOT NULL,
  device_count   INT          NOT NULL DEFAULT 1,
  est_weight     DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  condition_note TEXT,
  pickup_address VARCHAR(255) NOT NULL,
  city           VARCHAR(80)  NOT NULL,
  pincode        VARCHAR(10)  NOT NULL,
  preferred_date DATE         NOT NULL,
  slot           ENUM('Morning','Afternoon','Evening') NOT NULL DEFAULT 'Morning',
  photo          VARCHAR(255) DEFAULT NULL,
  status         ENUM('Pending','Scheduled','Picked Up','Recycled','Cancelled')
                 NOT NULL DEFAULT 'Pending',
  center_id      INT          DEFAULT NULL,
  admin_note     VARCHAR(255) DEFAULT NULL,
  created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_req_user   FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
  CONSTRAINT fk_req_center FOREIGN KEY (center_id) REFERENCES centers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------- Status timeline ----------
CREATE TABLE IF NOT EXISTS status_history (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  request_id INT NOT NULL,
  status     VARCHAR(30) NOT NULL,
  note       VARCHAR(255) DEFAULT NULL,
  changed_by VARCHAR(100) DEFAULT 'system',
  changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_hist_req FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- Contact / enquiry ----------
CREATE TABLE IF NOT EXISTS messages (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  email      VARCHAR(150) NOT NULL,
  body       TEXT NOT NULL,
  is_read    TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- Sample centers ----------
INSERT INTO centers (name, address, city, pincode, phone, timings, accepts) VALUES
('EcoTrace Hub - Kakkanad', 'Seaport-Airport Road, Kakkanad', 'Kochi', '682030', '0484-2200111', '9:00 AM - 6:00 PM', 'Phones, Laptops, TVs, Batteries'),
('GreenCycle Depot',        'MG Road, Near Metro Pillar 210',  'Kochi', '682016', '0484-2200222', '10:00 AM - 7:00 PM', 'Small appliances, Cables, Chargers'),
('Campus Drop Point',       'Block C, College Campus',         'Kochi', '682021', '0484-2200333', '9:00 AM - 4:00 PM', 'Phones, Laptops, Accessories');

-- ---------- Make yourself admin ----------
-- 1) Register normally on the site with your email.
-- 2) Then run the line below (change the email) in phpMyAdmin > SQL:
-- UPDATE users SET role = 'admin' WHERE email = 'you@example.com';
