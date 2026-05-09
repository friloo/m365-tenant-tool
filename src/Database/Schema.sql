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

-- Sharing Governance: tracked external shares
CREATE TABLE IF NOT EXISTS share_reviews (
    id                   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    drive_id             VARCHAR(255) NOT NULL,
    item_id              VARCHAR(255) NOT NULL,
    permission_id        VARCHAR(255) NOT NULL,
    item_name            VARCHAR(500),
    item_url             TEXT,
    share_scope          VARCHAR(50) DEFAULT 'anonymous',   -- anonymous|users|organization
    owner_upn            VARCHAR(255),
    owner_display_name   VARCHAR(255),
    owner_email          VARCHAR(255),
    site_name            VARCHAR(255),
    first_detected       DATETIME NOT NULL,
    last_reviewed        DATETIME,
    last_review_reason   TEXT,
    next_review_at       DATETIME,
    review_interval_days INT DEFAULT 30,
    status               VARCHAR(30) DEFAULT 'active',      -- active|pending_review|confirmed|revoked|expired
    reminder_sent_at     DATETIME,
    auto_revoke_at       DATETIME,
    revoked_at           DATETIME,
    created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_share (drive_id, item_id, permission_id),
    INDEX idx_status (status),
    INDEX idx_next_review (next_review_at),
    INDEX idx_auto_revoke (auto_revoke_at),
    INDEX idx_owner (owner_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- One-time review tokens (emailed to share owners)
CREATE TABLE IF NOT EXISTS share_review_tokens (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    share_review_id BIGINT UNSIGNED NOT NULL,
    token           VARCHAR(64) NOT NULL,
    used_at         DATETIME,
    expires_at      DATETIME NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_token (token),
    INDEX idx_expires (expires_at),
    FOREIGN KEY (share_review_id) REFERENCES share_reviews(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
