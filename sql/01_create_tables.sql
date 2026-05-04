-- ============================================
-- TUITION CLASS MANAGEMENT SYSTEM
-- STEP 1: Create Tables
-- ============================================

--cerate database and use it
CREATE DATABASE tuition_db;
GO
USE tuition_db;
GO
-- Drop tables if they exist (for fresh start)
IF OBJECT_ID('enrollments', 'U') IS NOT NULL DROP TABLE enrollments;
GO
IF OBJECT_ID('classes', 'U') IS NOT NULL DROP TABLE classes;
Go


-- 1. Students Table

-- 2. Teachers Table
CREATE TABLE teachers (
    teacher_id    INT IDENTITY(1,1) PRIMARY KEY,
    full_name     VARCHAR(100) NOT NULL,
    email         VARCHAR(100) UNIQUE,
    phone         VARCHAR(15),
    subject       VARCHAR(50),
    salary        DECIMAL(10,2) DEFAULT 0.00,
    joined_date   DATE DEFAULT CAST(GETDATE() AS DATE),
    status        VARCHAR(10) DEFAULT 'active' CHECK (status IN ('active', 'inactive'))
);
-- 3. Classes Table
CREATE TABLE classes (
    class_id      INT IDENTITY(1,1) PRIMARY KEY,
    class_name    VARCHAR(100) NOT NULL,
    subject       VARCHAR(50),
    teacher_id    INT,
    schedule      VARCHAR(100),
    max_students  INT DEFAULT 30,
    fee           DECIMAL(10,2) DEFAULT 0.00,
    status        VARCHAR(10) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE SET NULL
);
GO

-- 4. Enrollments Table
CREATE TABLE enrollments (
    enroll_id     INT IDENTITY(1,1) PRIMARY KEY,
    student_id    INT NOT NULL,
    class_id      INT NOT NULL,
    enroll_date   DATE DEFAULT CAST(GETDATE() AS DATE),
    payment_status VARCHAR(10) DEFAULT 'pending' CHECK (payment_status IN ('paid', 'pending', 'overdue')),
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (class_id)   REFERENCES classes(class_id)   ON DELETE CASCADE,
    CONSTRAINT unique_enroll UNIQUE (student_id, class_id)
);
GO

-- 5. Payments Table

-- 6. Activity Log Table (used by triggers)


SELECT 'Tables created successfully!' AS status;