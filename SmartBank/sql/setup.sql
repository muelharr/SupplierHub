-- =========================================
-- SmartBank Database Setup
-- smartbank_db
-- =========================================

CREATE DATABASE IF NOT EXISTS smartbank_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smartbank_db;

DROP TABLE IF EXISTS sb_request_logs;
DROP TABLE IF EXISTS sb_loans;
DROP TABLE IF EXISTS sb_ledger;
DROP TABLE IF EXISTS sb_users;

-- =========================================
-- 1. USERS (akun bank)
-- =========================================
CREATE TABLE IF NOT EXISTS sb_users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    saldo       BIGINT NOT NULL DEFAULT 0,
    role        ENUM('user','merchant','admin') NOT NULL DEFAULT 'user',
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================
-- 2. LEDGER (catatan semua transaksi)
-- =========================================
CREATE TABLE IF NOT EXISTS sb_ledger (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    from_user_id    INT DEFAULT NULL,
    to_user_id      INT DEFAULT NULL,
    type            ENUM('debit','credit','fee','tax','loan','loan_repay','bank_fee') NOT NULL,
    amount          BIGINT NOT NULL DEFAULT 0,
    fee_bank        BIGINT NOT NULL DEFAULT 0,
    description     VARCHAR(255) DEFAULT NULL,
    reference_id    VARCHAR(100) DEFAULT NULL UNIQUE,
    source_app      VARCHAR(50) DEFAULT 'SmartBank',
    status          ENUM('success','failed','pending') NOT NULL DEFAULT 'success',
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_user_id) REFERENCES sb_users(id) ON DELETE SET NULL,
    FOREIGN KEY (to_user_id)   REFERENCES sb_users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================
-- 3. LOANS (pinjaman)
-- =========================================
CREATE TABLE IF NOT EXISTS sb_loans (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    amount      BIGINT NOT NULL,
    interest    DECIMAL(5,2) NOT NULL DEFAULT 2.00,
    status      ENUM('pending','approved','repaid','rejected') NOT NULL DEFAULT 'pending',
    due_date    DATE DEFAULT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES sb_users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================
-- 4. REQUEST LOGS (audit trail)
-- =========================================
CREATE TABLE IF NOT EXISTS sb_request_logs (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    endpoint        VARCHAR(100) NOT NULL,
    method          VARCHAR(10) NOT NULL DEFAULT 'POST',
    source_app      VARCHAR(50) DEFAULT NULL,
    user_id         INT DEFAULT NULL,
    request_body    TEXT DEFAULT NULL,
    response_body   TEXT DEFAULT NULL,
    status_code     INT DEFAULT 200,
    ip_address      VARCHAR(45) DEFAULT NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================
-- SEED DATA
-- =========================================

-- password = bcrypt('password123')
INSERT INTO sb_users (id, name, email, password, saldo, role) VALUES
(1, 'SmartBank Admin',    'admin@smartbank.id',     '$2y$10$mWm02z6ClvVWIBal.MbSbOl0b5gCKdALG6LHWXIJlZvEeqaf/WWZW', 999999999, 'admin'),
(2, 'Warung Bu Ani',      'umkm@b2blink.com',       '$2y$10$mWm02z6ClvVWIBal.MbSbOl0b5gCKdALG6LHWXIJlZvEeqaf/WWZW', 10000000,  'user'),
(3, 'SupplierHub B2B',    'supplier@b2blink.com',   '$2y$10$mWm02z6ClvVWIBal.MbSbOl0b5gCKdALG6LHWXIJlZvEeqaf/WWZW', 5000000,   'merchant');

-- Seed ledger: saldo awal Warung Bu Ani
INSERT INTO sb_ledger (from_user_id, to_user_id, type, amount, fee_bank, description, reference_id, source_app) VALUES
(1, 2, 'credit', 10000000, 0, 'Top-up saldo awal UMKM', 'SB-INIT-UMKM-001', 'SmartBank'),
(1, 3, 'credit', 5000000,  0, 'Top-up saldo awal Supplier', 'SB-INIT-SUPP-001', 'SmartBank');
