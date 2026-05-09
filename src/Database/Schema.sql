-- M365 Tenant Tool — Database Schema
-- Run once during installer

CREATE TABLE IF NOT EXISTS app_config (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key`       VARCHAR(100) NOT NULL,
    value       TEXT,
    is_encrypted TINYINT(1) DEFAULT 0,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_key (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS graph_tokens (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    access_token TEXT NOT NULL,
    expires_at   DATETIME NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cache (
    cache_key    VARCHAR(255) NOT NULL,
    data         LONGTEXT NOT NULL,
    expires_at   DATETIME NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (cache_key),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS audit_log (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action       VARCHAR(255) NOT NULL,
    module       VARCHAR(100),
    ip_address   VARCHAR(45),
    details      TEXT,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_module (module),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
