CREATE DATABASE IF NOT EXISTS prcsystem_db;
USE prcsystem_db;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Department table --
CREATE TABLE department(
  dept_id INT AUTO_INCREMENT PRIMARY KEY,
  dept_name VARCHAR(45) NOT NULL,
  dept_created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  dept_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- User status table --
CREATE TABLE user_status(
  user_status_id INT AUTO_INCREMENT PRIMARY KEY,
  user_status_name VARCHAR(45) NOT NULL,
  user_status_created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  user_status_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- User role table --
CREATE TABLE user_role(
  user_role_id INT AUTO_INCREMENT PRIMARY KEY,
  role_name VARCHAR(45) NOT NULL,
  role_created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  role_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- User table --
CREATE TABLE user(
  user_id CHAR(36) PRIMARY KEY,
  username VARCHAR(45) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  user_role_id INT NOT NULL,
  user_status_id INT NOT NULL,
  user_created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  user_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_role_id) 
    REFERENCES user_role(user_role_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (user_status_id) 
    REFERENCES user_status(user_status_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- User profile table --
CREATE TABLE user_profile(
  user_id CHAR(36) PRIMARY KEY,
  dept_id INT NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  user_first_name VARCHAR(45) NOT NULL,
  user_middle_name VARCHAR(45),
  user_last_name VARCHAR(45) NOT NULL,
  user_birthdate DATE NOT NULL,
  prof_pic_path VARCHAR(255),
  user_profile_created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  user_profile_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) 
    REFERENCES user(user_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (dept_id) 
    REFERENCES department(dept_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX (dept_id),
  INDEX (user_last_name)
) ENGINE=InnoDB;

-- Permissions table --
CREATE TABLE permissions(
  permission_id INT AUTO_INCREMENT PRIMARY KEY,
  permission_name VARCHAR(45) NOT NULL,
  permission_created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  permission_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Role permissions table --
CREATE TABLE role_permissions(
  user_role_id INT NOT NULL,
  permission_id INT NOT NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  permission_created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  permission_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (user_role_id, permission_id),
  FOREIGN KEY (user_role_id) 
    REFERENCES user_role(user_role_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (permission_id) 
    REFERENCES permissions(permission_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Document Type table --
CREATE TABLE document_type (
    doc_type_id INT AUTO_INCREMENT PRIMARY KEY,
    document_type_name VARCHAR(50) NOT NULL,
    doc_created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    doc_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Document Status table --
CREATE TABLE doc_status (
    doc_status_id INT AUTO_INCREMENT PRIMARY KEY,
    status_name VARCHAR(50) NOT NULL,
    status_created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Document table --
CREATE TABLE document (
    doc_id CHAR(36) PRIMARY KEY,
    current_user_id CHAR(36) NOT NULL,
    current_dept_id INT NOT NULL,
    doc_status_id INT NOT NULL,
    doc_type_id INT NOT NULL,
    ref_no VARCHAR(50),
    uploaded_by CHAR(36) NOT NULL,
    applicant_name VARCHAR(255),
    applicant_license_no VARCHAR(50),
    file_path VARCHAR(255),
    doc_created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    doc_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (current_user_id) 
      REFERENCES user(user_id)
      ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (current_dept_id) 
      REFERENCES department(dept_id)
      ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (doc_status_id) 
      REFERENCES doc_status(doc_status_id)
      ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (doc_type_id) 
      REFERENCES document_type(doc_type_id)
      ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (uploaded_by)
      REFERENCES user(user_id)
      ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX (current_user_id),
    INDEX (current_dept_id),
    INDEX (doc_status_id)
) ENGINE=InnoDB;

-- Transaction Logs table --
CREATE TABLE transaction_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    doc_id CHAR(36) NOT NULL,
    doc_status_id INT NOT NULL,
    current_dept_id INT NOT NULL,
    target_dept_id INT NOT NULL,
    processed_by CHAR(36) NOT NULL,
    remarks TEXT,
    log_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doc_id) 
      REFERENCES document(doc_id)
      ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (doc_status_id) 
      REFERENCES doc_status(doc_status_id)
      ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (current_dept_id) 
      REFERENCES department(dept_id)
      ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (target_dept_id) 
      REFERENCES department(dept_id)
      ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (processed_by) 
      REFERENCES user(user_id)
      ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX (doc_id),
    INDEX (target_dept_id),
    INDEX (processed_by)
) ENGINE=InnoDB;

COMMIT;