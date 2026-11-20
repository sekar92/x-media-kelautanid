CREATE DATABASE IF NOT EXISTS kelautan_xmedia
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE kelautan_xmedia;

-- 1. Tabel Pengguna (Super Admin, Moderator, User Premium, User Basic)
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) DEFAULT NULL,
    phone_wa VARCHAR(30) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'moderator', 'premium', 'basic') NOT NULL DEFAULT 'basic',
    storage_limit_mb INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_role (role)
) ENGINE=InnoDB;

-- 2. Tabel Metadata Tweet & Riwayat Ekstraksi
CREATE TABLE IF NOT EXISTS tweets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tweet_id VARCHAR(32) NOT NULL UNIQUE,
    user_id INT UNSIGNED DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    original_url VARCHAR(512) NOT NULL,
    author_name VARCHAR(150) DEFAULT NULL,
    author_handle VARCHAR(64) DEFAULT NULL,
    tweet_text TEXT DEFAULT NULL,
    source_engine VARCHAR(32) NOT NULL DEFAULT 'fxtwitter',
    raw_metadata JSON DEFAULT NULL,
    is_flagged TINYINT(1) NOT NULL DEFAULT 0,
    flag_reason VARCHAR(255) DEFAULT NULL,
    moderated_at TIMESTAMP NULL DEFAULT NULL,
    moderated_by_user_id INT UNSIGNED DEFAULT NULL,
    fetched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_author_handle (author_handle),
    INDEX idx_fetched_at (fetched_at),
    INDEX idx_user_id (user_id),
    INDEX idx_is_flagged (is_flagged),
    CONSTRAINT fk_tweet_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_tweet_moderator FOREIGN KEY (moderated_by_user_id)
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 3. Tabel Item Media yang Diekstrak (Video, GIF, Foto)
CREATE TABLE IF NOT EXISTS media_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tweet_record_id BIGINT UNSIGNED NOT NULL,
    media_index TINYINT UNSIGNED NOT NULL DEFAULT 0,
    media_type ENUM('video', 'gif', 'photo') NOT NULL,
    best_direct_url TEXT NOT NULL,
    thumbnail_url TEXT DEFAULT NULL,
    mime_type VARCHAR(64) NOT NULL,
    width INT UNSIGNED DEFAULT NULL,
    height INT UNSIGNED DEFAULT NULL,
    duration_sec DECIMAL(8,2) DEFAULT NULL,
    bitrate INT UNSIGNED DEFAULT NULL,
    variants_json JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_tweet_media (tweet_record_id, media_index),
    CONSTRAINT fk_media_tweet FOREIGN KEY (tweet_record_id)
        REFERENCES tweets(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Tabel Berkas yang Disimpan di Server Lokal
CREATE TABLE IF NOT EXISTS saved_files (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    media_item_id BIGINT UNSIGNED NOT NULL,
    relative_path VARCHAR(255) NOT NULL,
    filename VARCHAR(180) NOT NULL,
    file_size_bytes BIGINT UNSIGNED NOT NULL,
    sha256_hash CHAR(64) NOT NULL,
    saved_by_user_id INT UNSIGNED DEFAULT NULL,
    download_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_saved_media (media_item_id),
    INDEX idx_sha256 (sha256_hash),
    CONSTRAINT fk_saved_media FOREIGN KEY (media_item_id)
        REFERENCES media_items(id) ON DELETE CASCADE,
    CONSTRAINT fk_saved_user FOREIGN KEY (saved_by_user_id)
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 5. Tabel Pelacakan Ekstraksi Tamu (Unregistered Users)
CREATE TABLE IF NOT EXISTS guest_extractions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    tweet_id VARCHAR(32) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_guest_session (session_id),
    INDEX idx_guest_ip (ip_address)
) ENGINE=InnoDB;

-- 6. Tabel Pengaturan Aplikasi (Settings Key-Value)
CREATE TABLE IF NOT EXISTS app_settings (
    setting_key VARCHAR(64) PRIMARY KEY,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 7. Tabel Konten Halaman Dinamis (ToS, Rules, Guidelines)
CREATE TABLE IF NOT EXISTS pages (
    slug VARCHAR(64) PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content LONGTEXT NOT NULL,
    updated_by_user_id INT UNSIGNED DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_page_user FOREIGN KEY (updated_by_user_id)
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
