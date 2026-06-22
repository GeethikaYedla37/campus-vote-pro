CREATE DATABASE IF NOT EXISTS campus_vote_pro
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE campus_vote_pro;

CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  email_verification_enabled TINYINT(1) NOT NULL DEFAULT 1,
  email_code_hash VARCHAR(255) NULL,
  email_code_expires_at DATETIME NULL,
  email_code_sent_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS students (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  roll_number VARCHAR(30) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  branch VARCHAR(40) NOT NULL,
  study_year TINYINT UNSIGNED NOT NULL,
  semester TINYINT UNSIGNED NOT NULL,
  status ENUM('pending','activated','deactivated') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS election_categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  description VARCHAR(500) NULL,
  starts_at DATETIME NOT NULL,
  ends_at DATETIME NOT NULL,
  status ENUM('draft','active','closed') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS candidates (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  roll_number VARCHAR(30) NULL,
  branch VARCHAR(40) NOT NULL,
  manifesto VARCHAR(700) NOT NULL,
  photo_path VARCHAR(255) NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_candidates_category
    FOREIGN KEY (category_id) REFERENCES election_categories(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS votes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  candidate_id INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_votes_student
    FOREIGN KEY (student_id) REFERENCES students(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_votes_candidate
    FOREIGN KEY (candidate_id) REFERENCES candidates(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_votes_category
    FOREIGN KEY (category_id) REFERENCES election_categories(id)
    ON DELETE CASCADE,
  CONSTRAINT uq_one_vote_per_category
    UNIQUE (student_id, category_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  actor_type ENUM('admin','student','system') NOT NULL,
  actor_id INT UNSIGNED NULL,
  action VARCHAR(120) NOT NULL,
  details VARCHAR(700) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS login_attempts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  attempt_type ENUM('admin','admin_verification','student') NOT NULL,
  identifier VARCHAR(180) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  successful TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_login_attempts_lookup (attempt_type, identifier, ip_address, created_at)
) ENGINE=InnoDB;
