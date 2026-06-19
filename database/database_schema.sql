-- Attendance Management System - MySQL Database Schema
-- Created: January 22, 2026

CREATE DATABASE IF NOT EXISTS `attendance_system`;
USE `attendance_system`;

-- ==================== USERS TABLE ====================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `role` ENUM('admin', 'faculty', 'student') NOT NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_username` (`username`),
    INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================== DEPARTMENTS TABLE ====================
CREATE TABLE IF NOT EXISTS `departments` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(100) UNIQUE NOT NULL,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================== COURSES TABLE ====================
CREATE TABLE IF NOT EXISTS `courses` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `dept_id` INT NOT NULL,
    `duration_years` INT DEFAULT 4,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`dept_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
    INDEX `idx_dept_id` (`dept_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================== SUBJECTS TABLE ====================
CREATE TABLE IF NOT EXISTS `subjects` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `code` VARCHAR(20) UNIQUE NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `course_id` INT NOT NULL,
    `semester` INT NOT NULL,
    `credits` INT DEFAULT 3,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
    INDEX `idx_course_id` (`course_id`),
    INDEX `idx_semester` (`semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================== FACULTY TABLE ====================
CREATE TABLE IF NOT EXISTS `faculty` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT UNIQUE NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `dept_id` INT NOT NULL,
    `designation` VARCHAR(50),
    `qualification` VARCHAR(100),
    `contact` VARCHAR(20),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`dept_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_dept_id` (`dept_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================== STUDENTS TABLE ====================
CREATE TABLE IF NOT EXISTS `students` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT UNIQUE,
    `roll_no` VARCHAR(20) UNIQUE NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `dept_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `semester` INT NOT NULL,
    `section` VARCHAR(5),
    `email` VARCHAR(100) UNIQUE,
    `contact` VARCHAR(20),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`dept_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_roll_no` (`roll_no`),
    INDEX `idx_semester` (`semester`),
    INDEX `idx_section` (`section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================== SUBJECT ALLOCATIONS TABLE ====================
CREATE TABLE IF NOT EXISTS `subject_allocations` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `faculty_id` INT NOT NULL,
    `subject_id` INT NOT NULL,
    `semester` INT NOT NULL,
    `section` VARCHAR(5),
    `academic_year` VARCHAR(9),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_allocation` (`faculty_id`, `subject_id`, `semester`, `section`, `academic_year`),
    INDEX `idx_faculty_id` (`faculty_id`),
    INDEX `idx_subject_id` (`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================== ATTENDANCE SESSIONS TABLE ====================
CREATE TABLE IF NOT EXISTS `attendance_sessions` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `faculty_id` INT NOT NULL,
    `subject_id` INT NOT NULL,
    `section` VARCHAR(5),
    `session_date` DATE NOT NULL,
    `session_time` TIME,
    `total_students` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_session` (`faculty_id`, `subject_id`, `section`, `session_date`),
    INDEX `idx_faculty_id` (`faculty_id`),
    INDEX `idx_subject_id` (`subject_id`),
    INDEX `idx_session_date` (`session_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================== ATTENDANCE DETAILS TABLE ====================
CREATE TABLE IF NOT EXISTS `attendance_details` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `session_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `attendance_status` ENUM('present', 'absent', 'late', 'excused') DEFAULT 'absent',
    `remarks` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`session_id`) REFERENCES `attendance_sessions` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_attendance` (`session_id`, `student_id`),
    INDEX `idx_session_id` (`session_id`),
    INDEX `idx_student_id` (`student_id`),
    INDEX `idx_status` (`attendance_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================== INSERT DEFAULT DATA ====================

-- Insert Departments
INSERT INTO `departments` (`id`, `name`, `description`) VALUES
(1, 'Computer Science', 'CS Department'),
(2, 'Mechanical Engineering', 'ME Department'),
(3, 'Electrical Engineering', 'EE Department'),
(4, 'Civil Engineering', 'CE Department');

-- Insert Courses
INSERT INTO `courses` (`id`, `name`, `dept_id`, `duration_years`) VALUES
(1, 'B.Tech CS', 1, 4),
(2, 'B.Tech ME', 2, 4),
(3, 'B.Tech EE', 3, 4),
(4, 'B.Tech CE', 4, 4);

-- Insert Subjects
INSERT INTO `subjects` (`id`, `code`, `name`, `course_id`, `semester`, `credits`) VALUES
(1, 'CS101', 'Programming Fundamentals', 1, 1, 4),
(2, 'CS102', 'Data Structures', 1, 1, 4),
(3, 'CS103', 'Discrete Mathematics', 1, 1, 3),
(4, 'CS104', 'Digital Logic Design', 1, 1, 3);

-- Insert Users (Hashed passwords should be used in production)
INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`) VALUES
(1, 'admin', SHA2('admin', 256), 'admin@college.edu', 'admin'),
(2, 'faculty1', SHA2('faculty', 256), 'fac1@college.edu', 'faculty'),
(3, 'faculty2', SHA2('faculty', 256), 'fac2@college.edu', 'faculty'),
(4, 'faculty3', SHA2('faculty', 256), 'fac3@college.edu', 'faculty'),
(5, 'student1', SHA2('student', 256), 'stu1@college.edu', 'student');

-- Insert Faculty
INSERT INTO `faculty` (`id`, `user_id`, `name`, `dept_id`, `designation`, `qualification`, `contact`) VALUES
(1, 2, 'Dr. Jane Smith', 1, 'Assistant Professor', 'M.Tech, PhD', '098-765-4321'),
(2, 3, 'Prof. Robert Johnson', 1, 'Associate Professor', 'M.Tech, PhD', '087-654-3210'),
(3, 4, 'Ms. Sarah Williams', 1, 'Lecturer', 'M.Tech', '076-543-2109');

-- Insert Students
INSERT INTO `students` (`id`, `user_id`, `roll_no`, `name`, `dept_id`, `course_id`, `semester`, `section`, `email`, `contact`) VALUES
(1, 5, 'CS001', 'John Doe', 1, 1, 1, 'A', 'john@college.edu', '991-234-5678'),
(2, NULL, 'CS002', 'Jane Smith', 1, 1, 1, 'A', 'jane@college.edu', '992-234-5678'),
(3, NULL, 'CS003', 'Alice Johnson', 1, 1, 1, 'A', 'alice@college.edu', '993-234-5678'),
(4, NULL, 'CS004', 'Bob Brown', 1, 1, 1, 'A', 'bob@college.edu', '994-234-5678'),
(5, NULL, 'CS005', 'Carol Davis', 1, 1, 1, 'A', 'carol@college.edu', '995-234-5678'),
(6, NULL, 'CS006', 'David Wilson', 1, 1, 1, 'A', 'david@college.edu', '996-234-5678'),
(7, NULL, 'CS007', 'Emma White', 1, 1, 1, 'A', 'emma@college.edu', '997-234-5678'),
(8, NULL, 'CS008', 'Frank Miller', 1, 1, 1, 'A', 'frank@college.edu', '998-234-5678');

-- Insert Subject Allocations
INSERT INTO `subject_allocations` (`id`, `faculty_id`, `subject_id`, `semester`, `section`, `academic_year`) VALUES
(1, 1, 1, 1, 'A', '2025-26'),
(2, 2, 2, 1, 'A', '2025-26'),
(3, 3, 3, 1, 'A', '2025-26'),
(4, 1, 4, 1, 'A', '2025-26');
