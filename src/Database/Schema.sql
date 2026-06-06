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

-- Stale Account Actions: log of license removals / disables on inactive accounts
CREATE TABLE IF NOT EXISTS stale_account_log (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    VARCHAR(255) NOT NULL,
    user_upn   VARCHAR(255),
    action     VARCHAR(50) NOT NULL,  -- license_removed|account_disabled|skipped
    details    JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user    (user_id),
    INDEX idx_created (created_at)
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

-- Cron job schedule + state (one row per job, upserted by CronRunner on first run)
CREATE TABLE IF NOT EXISTS cron_jobs (
    job_key          VARCHAR(100) PRIMARY KEY,
    label            VARCHAR(255) NOT NULL,
    description      TEXT,
    enabled          TINYINT(1) DEFAULT 1,
    interval_minutes INT UNSIGNED DEFAULT 60,
    last_run_at      DATETIME,
    last_run_status  VARCHAR(20),        -- success|error
    last_run_log     TEXT,
    last_run_seconds DECIMAL(6,2),
    next_run_at      DATETIME,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Async job queue: Graph API write operations (license changes, bulk actions)
CREATE TABLE IF NOT EXISTS job_queue (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_type     VARCHAR(100) NOT NULL,
    payload      JSON NOT NULL,
    status       VARCHAR(20) DEFAULT 'pending',  -- pending|processing|done|failed
    attempts     TINYINT UNSIGNED DEFAULT 0,
    max_attempts TINYINT UNSIGNED DEFAULT 3,
    last_error   TEXT,
    available_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    processed_at DATETIME,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_queue_pick (status, available_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────────────
-- Runtime / app tables. These are ALSO created idempotently on the first web
-- request (index.php) as an upgrade safety net; listed here so a fresh install
-- (and a cron-first start before any web request) has the complete schema.
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS m365_users (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    azure_object_id  VARCHAR(100) DEFAULT NULL,
    upn              VARCHAR(255) NOT NULL,
    display_name     VARCHAR(255) DEFAULT NULL,
    role             ENUM('operator','admin') NOT NULL DEFAULT 'operator',
    is_active        TINYINT(1) NOT NULL DEFAULT 1,
    last_login       DATETIME DEFAULT NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_upn (upn),
    UNIQUE KEY uq_oid (azure_object_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_notes (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_azure_id VARCHAR(100) NOT NULL,
    note          TEXT NOT NULL,
    created_by    VARCHAR(255) NOT NULL DEFAULT '',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_azure_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS access_reviews (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(255) NOT NULL,
    type         VARCHAR(50) NOT NULL DEFAULT 'guests',
    status       ENUM('open','completed') DEFAULT 'open',
    created_by   VARCHAR(255) NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS access_review_items (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    review_id    INT NOT NULL,
    user_id      VARCHAR(100) NOT NULL,
    user_upn     VARCHAR(255) NOT NULL,
    user_name    VARCHAR(255) NOT NULL,
    last_signin  DATETIME DEFAULT NULL,
    decision     ENUM('pending','approve','revoke') DEFAULT 'pending',
    decided_by   VARCHAR(255) DEFAULT NULL,
    decided_at   DATETIME DEFAULT NULL,
    FOREIGN KEY (review_id) REFERENCES access_reviews(id) ON DELETE CASCADE,
    INDEX idx_review (review_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS login_attempts (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    ip_address   VARCHAR(45) NOT NULL,
    attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time (ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Throttles repeated invalid REST API key attempts (per source IP).
CREATE TABLE IF NOT EXISTS api_auth_failures (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    ip_address   VARCHAR(45) NOT NULL,
    attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time (ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS app_audit_log (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    actor      VARCHAR(255) NOT NULL DEFAULT '',
    action     VARCHAR(255) NOT NULL,
    module     VARCHAR(100) NOT NULL DEFAULT '',
    detail     TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created (created_at),
    INDEX idx_actor (actor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS app_notifications (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    category   VARCHAR(64) NOT NULL DEFAULT 'info',
    severity   ENUM('info','success','warn','critical') NOT NULL DEFAULT 'info',
    title      VARCHAR(255) NOT NULL,
    body       TEXT,
    link       VARCHAR(255) DEFAULT NULL,
    dedupe_key VARCHAR(128) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_dedupe (dedupe_key),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS app_notification_seen (
    actor      VARCHAR(255) NOT NULL,
    last_seen  DATETIME NOT NULL,
    PRIMARY KEY (actor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS app_tenant_snapshots (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    kind       VARCHAR(64) NOT NULL,
    payload    LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_kind_created (kind, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS app_metric_history (
    metric     VARCHAR(64) NOT NULL,
    day        DATE NOT NULL,
    value      DECIMAL(14,4) NOT NULL,
    PRIMARY KEY (metric, day)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS app_api_keys (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120) NOT NULL,
    key_hash   CHAR(64) NOT NULL,
    scopes     VARCHAR(255) NOT NULL DEFAULT 'read',
    created_by VARCHAR(255) NOT NULL DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_used  DATETIME DEFAULT NULL,
    revoked_at DATETIME DEFAULT NULL,
    UNIQUE KEY uq_hash (key_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS app_workflows (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(160) NOT NULL,
    trigger_key VARCHAR(80)  NOT NULL,
    trigger_cfg TEXT,
    actions     LONGTEXT     NOT NULL,
    enabled     TINYINT(1)   NOT NULL DEFAULT 1,
    created_by  VARCHAR(255) NOT NULL DEFAULT '',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_run    DATETIME DEFAULT NULL,
    last_status VARCHAR(20)  DEFAULT NULL,
    last_msg    TEXT,
    INDEX idx_trigger (trigger_key, enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS app_workflow_runs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    workflow_id INT NOT NULL,
    ran_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status      VARCHAR(20) NOT NULL,
    target      VARCHAR(255) DEFAULT NULL,
    detail      TEXT,
    INDEX idx_workflow (workflow_id, ran_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
