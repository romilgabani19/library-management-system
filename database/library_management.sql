CREATE DATABASE IF NOT EXISTS library_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE library_management;
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS fines;
DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS issues;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 role ENUM('admin','librarian','student') NOT NULL DEFAULT 'student',
 student_no VARCHAR(40) NULL UNIQUE,
 status ENUM('active','inactive') NOT NULL DEFAULT 'active',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_users_role_status(role,status)
) ENGINE=InnoDB;

CREATE TABLE categories (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL UNIQUE,
 description VARCHAR(500) NULL
) ENGINE=InnoDB;

CREATE TABLE books (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 title VARCHAR(200) NOT NULL,
 author VARCHAR(150) NOT NULL,
 category_id INT UNSIGNED NULL,
 isbn VARCHAR(20) NULL UNIQUE,
 publisher VARCHAR(150) NULL,
 publication_year SMALLINT UNSIGNED NULL,
 description TEXT NULL,
 total_quantity INT UNSIGNED NOT NULL DEFAULT 1,
 available_quantity INT UNSIGNED NOT NULL DEFAULT 1,
 cover_image VARCHAR(255) NULL,
 times_issued INT UNSIGNED NOT NULL DEFAULT 0,
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_books_category FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE SET NULL,
 CONSTRAINT chk_book_stock CHECK(available_quantity <= total_quantity),
 INDEX idx_books_title(title),
 INDEX idx_books_author(author),
 INDEX idx_books_active(is_active)
) ENGINE=InnoDB;

CREATE TABLE issues (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 book_id INT UNSIGNED NOT NULL,
 issued_by INT UNSIGNED NULL,
 issue_date DATE NOT NULL,
 due_date DATE NOT NULL,
 return_date DATE NULL,
 status ENUM('issued','returned') NOT NULL DEFAULT 'issued',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_issues_user FOREIGN KEY(user_id) REFERENCES users(id),
 CONSTRAINT fk_issues_book FOREIGN KEY(book_id) REFERENCES books(id),
 CONSTRAINT fk_issues_staff FOREIGN KEY(issued_by) REFERENCES users(id) ON DELETE SET NULL,
 INDEX idx_issues_user_status(user_id,status),
 INDEX idx_issues_due_status(due_date,status)
) ENGINE=InnoDB;

CREATE TABLE reservations (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 book_id INT UNSIGNED NOT NULL,
 status ENUM('pending','approved','rejected','cancelled','fulfilled') NOT NULL DEFAULT 'pending',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_reservations_user FOREIGN KEY(user_id) REFERENCES users(id),
 CONSTRAINT fk_reservations_book FOREIGN KEY(book_id) REFERENCES books(id),
 INDEX idx_reservations_user_status(user_id,status)
) ENGINE=InnoDB;

CREATE TABLE fines (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 issue_id BIGINT UNSIGNED NOT NULL UNIQUE,
 user_id INT UNSIGNED NOT NULL,
 amount DECIMAL(10,2) NOT NULL DEFAULT 0,
 paid_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_fines_issue FOREIGN KEY(issue_id) REFERENCES issues(id),
 CONSTRAINT fk_fines_user FOREIGN KEY(user_id) REFERENCES users(id),
 CONSTRAINT chk_fine_paid CHECK(paid_amount <= amount),
 INDEX idx_fines_user(user_id)
) ENGINE=InnoDB;

CREATE TABLE contact_messages (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 email VARCHAR(190) NOT NULL,
 message VARCHAR(3000) NOT NULL,
 status ENUM('new','read','closed') NOT NULL DEFAULT 'new',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO categories(name,description) VALUES
('Computer Science','Programming, computing and technology'),
('Science','Natural sciences and experiments'),
('Mathematics','Mathematics and statistics'),
('Literature','Fiction, poetry and classic literature'),
('History','History and social studies'),
('Business','Management, finance and entrepreneurship');

-- Sample passwords for all three seeded accounts are: password
-- Change these credentials immediately in any non-demo deployment.
INSERT INTO users(name,email,password_hash,role,student_no) VALUES
('Library Administrator','admin@library.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin',NULL),
('Library Staff','librarian@library.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','librarian',NULL),
('Aarav Patel','student@library.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','student','STU-1001');

INSERT INTO books(title,author,category_id,isbn,publisher,publication_year,description,total_quantity,available_quantity) VALUES
('Clean Code','Robert C. Martin',1,'9780132350884','Prentice Hall',2008,'A practical guide to writing readable, maintainable software. Explore meaningful names, functions, comments, and code structure.',4,4),
('The Pragmatic Programmer','Andrew Hunt, David Thomas',1,'9780135957059','Addison-Wesley',2019,'A collection of practical ideas and techniques for becoming a better programmer and problem solver.',3,3),
('A Brief History of Time','Stephen Hawking',2,'9780553380163','Bantam',1998,'An accessible introduction to cosmology, black holes, time, and the universe.',2,2),
('Introduction to Algorithms','Thomas H. Cormen et al.',1,'9780262046305','MIT Press',2022,'A comprehensive introduction to modern algorithms and their analysis.',2,2),
('The Alchemist','Paulo Coelho',4,'9780061122415','HarperOne',2014,'A philosophical novel about following dreams, discovering purpose, and listening to the world.',5,5),
('The Art of Statistics','David Spiegelhalter',3,'9781541618510','Basic Books',2019,'Learn how to make sense of data and understand statistical claims in everyday life.',2,2),
('Sapiens','Yuval Noah Harari',5,'9780062316097','Harper',2015,'A broad history of humankind, from early humans to modern societies.',3,3),
('The Lean Startup','Eric Ries',6,'9780307887894','Crown Business',2011,'A method for building businesses and products through validated learning and rapid experimentation.',2,2);
