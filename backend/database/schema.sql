-- =====================================================================
-- Service Request & Complaint Management System
-- Database Schema (MySQL 5.7+ / MariaDB 10.3+)
-- =====================================================================

DROP DATABASE IF EXISTS service_request_system;
CREATE DATABASE service_request_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE service_request_system;

-- ---------------------------------------------------------------------
-- Users table (User, Admin, Technician all live here, differentiated by role)
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(120)        NOT NULL,
    email           VARCHAR(150)        NOT NULL UNIQUE,
    password_hash   VARCHAR(255)        NOT NULL,
    role            ENUM('user','admin','technician') NOT NULL DEFAULT 'user',
    phone           VARCHAR(20)         NULL,
    is_active       TINYINT(1)          NOT NULL DEFAULT 1,
    created_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Categories
-- ---------------------------------------------------------------------
CREATE TABLE categories (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(60)         NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Service Requests
-- ---------------------------------------------------------------------
CREATE TABLE service_requests (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_code    VARCHAR(20)         NOT NULL UNIQUE,      -- e.g. SR-000001
    user_id         INT UNSIGNED        NOT NULL,             -- who raised it
    category_id     INT UNSIGNED        NOT NULL,
    subject         VARCHAR(150)        NOT NULL,
    description     TEXT                NOT NULL,
    priority        ENUM('Low','Medium','High','Critical') NOT NULL DEFAULT 'Low',
    status          ENUM('New','Assigned','In Progress','Resolved','Closed') NOT NULL DEFAULT 'New',
    attachment_path VARCHAR(255)        NULL,
    created_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_sr_user     FOREIGN KEY (user_id)     REFERENCES users(id)       ON DELETE CASCADE,
    CONSTRAINT fk_sr_category FOREIGN KEY (category_id) REFERENCES categories(id)  ON DELETE RESTRICT,
    INDEX idx_sr_status (status),
    INDEX idx_sr_priority (priority),
    INDEX idx_sr_user (user_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Request Assignments (current + historical assignment records)
-- ---------------------------------------------------------------------
CREATE TABLE request_assignments (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id      INT UNSIGNED        NOT NULL,
    technician_id   INT UNSIGNED        NOT NULL,
    assigned_by     INT UNSIGNED        NOT NULL,             -- admin user id
    assigned_at     TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_current      TINYINT(1)          NOT NULL DEFAULT 1,

    CONSTRAINT fk_ra_request    FOREIGN KEY (request_id)    REFERENCES service_requests(id) ON DELETE CASCADE,
    CONSTRAINT fk_ra_technician FOREIGN KEY (technician_id) REFERENCES users(id)            ON DELETE CASCADE,
    CONSTRAINT fk_ra_admin      FOREIGN KEY (assigned_by)   REFERENCES users(id)            ON DELETE CASCADE,
    INDEX idx_ra_request (request_id),
    INDEX idx_ra_technician (technician_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Request History (audit trail of every status change)
-- ---------------------------------------------------------------------
CREATE TABLE request_history (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id      INT UNSIGNED        NOT NULL,
    status          VARCHAR(30)         NOT NULL,
    updated_by      INT UNSIGNED        NOT NULL,
    updated_by_role ENUM('user','admin','technician') NOT NULL,
    remarks         VARCHAR(500)        NULL,
    created_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_rh_request FOREIGN KEY (request_id) REFERENCES service_requests(id) ON DELETE CASCADE,
    CONSTRAINT fk_rh_user    FOREIGN KEY (updated_by)  REFERENCES users(id)            ON DELETE CASCADE,
    INDEX idx_rh_request (request_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Remarks (free-form notes added by any role on a request)
-- ---------------------------------------------------------------------
CREATE TABLE remarks (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id      INT UNSIGNED        NOT NULL,
    user_id         INT UNSIGNED        NOT NULL,
    role            ENUM('user','admin','technician') NOT NULL,
    remark          VARCHAR(1000)       NOT NULL,
    created_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_rm_request FOREIGN KEY (request_id) REFERENCES service_requests(id) ON DELETE CASCADE,
    CONSTRAINT fk_rm_user    FOREIGN KEY (user_id)     REFERENCES users(id)            ON DELETE CASCADE,
    INDEX idx_rm_request (request_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Sessions (simple server-side session/token store, in addition to PHP's
-- native session; kept so an API-token style login also works without
-- relying solely on PHP session cookies)
-- ---------------------------------------------------------------------
CREATE TABLE auth_tokens (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED        NOT NULL,
    token           VARCHAR(128)        NOT NULL UNIQUE,
    expires_at      DATETIME            NOT NULL,
    created_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_at_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_at_token (token)
) ENGINE=InnoDB;
