-- ============================================
-- STEP 3: T-SQL - Stored Procedures, Functions, Triggers
-- Run each block separately in SSMS
-- ============================================

-- ============================================
-- STORED PROCEDURE 1: Enroll a Student
-- Checks if class is full before enrolling
-- ============================================
IF OBJECT_ID('EnrollStudent', 'P') IS NOT NULL DROP PROCEDURE EnrollStudent;
GO

CREATE PROCEDURE EnrollStudent
    @p_student_id INT,
    @p_class_id   INT,
    @p_message    VARCHAR(200) OUTPUT
AS
BEGIN
    DECLARE @v_current_count INT;
    DECLARE @v_max_students  INT;
    DECLARE @v_class_name    VARCHAR(100);
    DECLARE @v_student_name  VARCHAR(100);
    DECLARE @already_enrolled INT = 0;

    -- Get class info
    SELECT @v_class_name = class_name, @v_max_students = max_students
    FROM classes WHERE class_id = @p_class_id;

    -- Get student name
    SELECT @v_student_name = full_name
    FROM students WHERE student_id = @p_student_id;

    -- Count current enrollments
    SELECT @v_current_count = COUNT(*)
    FROM enrollments WHERE class_id = @p_class_id;

    -- Check already enrolled
    SELECT @already_enrolled = COUNT(*)
    FROM enrollments
    WHERE student_id = @p_student_id AND class_id = @p_class_id;

    IF @already_enrolled > 0
    BEGIN
        SET @p_message = 'ERROR: Student is already enrolled in this class.';
    END
    ELSE IF @v_current_count >= @v_max_students
    BEGIN
        SET @p_message = 'ERROR: Class "' + @v_class_name + '" is full (' + CAST(@v_max_students AS VARCHAR) + ' students max).';
    END
    ELSE
    BEGIN
        INSERT INTO enrollments (student_id, class_id, enroll_date, payment_status)
        VALUES (@p_student_id, @p_class_id, CAST(GETDATE() AS DATE), 'pending');
        SET @p_message = 'SUCCESS: ' + @v_student_name + ' enrolled in "' + @v_class_name + '" successfully!';
    END
END
GO

-- according to the ssms error ist not support output parameter in stored procedure, so we will return the message as a result set instead of output parameter.
ALTER PROCEDURE EnrollStudent
    @p_student_id INT,
    @p_class_id INT
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @v_current_count INT;
    DECLARE @v_max_students  INT;
    DECLARE @v_class_name    VARCHAR(100);
    DECLARE @v_student_name  VARCHAR(100);
    DECLARE @already_enrolled INT = 0;

    -- Get class info
    SELECT @v_class_name = class_name, @v_max_students = max_students
    FROM classes WHERE class_id = @p_class_id;

    -- Get student name
    SELECT @v_student_name = full_name
    FROM students WHERE student_id = @p_student_id;

    -- Count current enrollments
    SELECT @v_current_count = COUNT(*)
    FROM enrollments WHERE class_id = @p_class_id;

    -- Check already enrolled
    SELECT @already_enrolled = COUNT(*)
    FROM enrollments
    WHERE student_id = @p_student_id AND class_id = @p_class_id;

    IF @already_enrolled > 0
    BEGIN
        SELECT 'ERROR: Student is already enrolled in this class.' AS message;
    END
    ELSE IF @v_current_count >= @v_max_students
    BEGIN
        SELECT 'ERROR: Class "' + @v_class_name + '" is full (' 
               + CAST(@v_max_students AS VARCHAR) + ' students max).' AS message;
    END
    ELSE
    BEGIN
        INSERT INTO enrollments (student_id, class_id, enroll_date, payment_status)
        VALUES (@p_student_id, @p_class_id, CAST(GETDATE() AS DATE), 'pending');

        SELECT 'SUCCESS: ' + @v_student_name + ' enrolled in "' 
               + @v_class_name + '" successfully!' AS message;
    END
END
-- ============================================
-- STORED PROCEDURE 2: Record a Payment
-- Updates payment status after recording
-- ============================================
IF OBJECT_ID('RecordPayment', 'P') IS NOT NULL DROP PROCEDURE RecordPayment;
GO

CREATE PROCEDURE RecordPayment
    @p_enroll_id  INT,
    @p_amount     DECIMAL(10,2),
    @p_method     VARCHAR(20),
    @p_message    VARCHAR(200) OUTPUT
AS
BEGIN
    DECLARE @v_fee DECIMAL(10,2);
    DECLARE @v_exists INT = 0;

    SELECT @v_exists = COUNT(*) FROM enrollments WHERE enroll_id = @p_enroll_id;

    IF @v_exists = 0
    BEGIN
        SET @p_message = 'ERROR: Enrollment not found.';
    END
    ELSE
    BEGIN
        -- Get class fee
        SELECT @v_fee = c.fee
        FROM enrollments e
        JOIN classes c ON e.class_id = c.class_id
        WHERE e.enroll_id = @p_enroll_id;

        -- Insert payment
        INSERT INTO payments (enroll_id, amount, payment_date, method)
        VALUES (@p_enroll_id, @p_amount, CAST(GETDATE() AS DATE), @p_method);

        -- Update enrollment payment status
        IF @p_amount >= @v_fee
        BEGIN
            UPDATE enrollments SET payment_status = 'paid' WHERE enroll_id = @p_enroll_id;
            SET @p_message = 'SUCCESS: Full payment recorded. Status updated to PAID.';
        END
        ELSE
        BEGIN
            UPDATE enrollments SET payment_status = 'pending' WHERE enroll_id = @p_enroll_id;
            SET @p_message = 'SUCCESS: Partial payment of Rs.' + CAST(@p_amount AS VARCHAR) + ' recorded.';
        END
    END
END
GO


-- ============================================
-- STORED PROCEDURE 3: Get Student Report
-- Returns result set directly
-- ============================================
IF OBJECT_ID('StudentReport', 'P') IS NOT NULL DROP PROCEDURE StudentReport;
GO

CREATE PROCEDURE StudentReport
    @p_student_id INT
AS
BEGIN
    SELECT c.class_name, c.subject, c.schedule, c.fee, e.payment_status, e.enroll_date
    FROM enrollments e
    JOIN classes c ON e.class_id = c.class_id
    WHERE e.student_id = @p_student_id;
END
GO

-- ============================================
-- STORED PROCEDURE 4: Add Class
-- Inserts new class with validation
-- ============================================
IF OBJECT_ID('AddClass', 'P') IS NOT NULL DROP PROCEDURE AddClass;
GO

CREATE PROCEDURE AddClass
    @p_class_name VARCHAR(100),
    @p_subject    VARCHAR(50),
    @p_teacher_id INT,
    @p_schedule   VARCHAR(100),
    @p_max        INT,
    @p_fee        DECIMAL(10,2)
AS
BEGIN
    SET NOCOUNT ON;

    IF @p_class_name IS NULL OR @p_class_name = ''
    BEGIN
        SELECT 'ERROR: Class name is required' AS message;
        RETURN;
    END

    IF @p_max <= 0
    BEGIN
        SELECT 'ERROR: Max students must be greater than 0' AS message;
        RETURN;
    END

    INSERT INTO classes (class_name, subject, teacher_id, schedule, max_students, fee)
    VALUES (@p_class_name, @p_subject, @p_teacher_id, @p_schedule, @p_max, @p_fee);

    SELECT 'SUCCESS: Class created successfully' AS message;
END
GO


-- ============================================
-- FUNCTION 1: Get Total Fees Paid by a Student
-- ============================================
IF OBJECT_ID('GetStudentTotalPaid', 'FN') IS NOT NULL DROP FUNCTION GetStudentTotalPaid;
GO

CREATE FUNCTION GetStudentTotalPaid(@p_student_id INT)
RETURNS DECIMAL(10,2)
AS
BEGIN
    DECLARE @v_total DECIMAL(10,2) = 0.00;

    SELECT @v_total = ISNULL(SUM(p.amount), 0)
    FROM payments p
    JOIN enrollments e ON p.enroll_id = e.enroll_id
    WHERE e.student_id = @p_student_id;

    RETURN @v_total;
END
GO


-- ============================================
-- FUNCTION 2: Count Students in a Class
-- ============================================
IF OBJECT_ID('GetClassStudentCount', 'FN') IS NOT NULL DROP FUNCTION GetClassStudentCount;
GO

CREATE FUNCTION GetClassStudentCount(@p_class_id INT)
RETURNS INT
AS
BEGIN
    DECLARE @v_count INT = 0;
    SELECT @v_count = COUNT(*) FROM enrollments WHERE class_id = @p_class_id;
    RETURN @v_count;
END
GO


-- ============================================
-- FUNCTION 3: Check if Class is Full
-- ============================================
IF OBJECT_ID('IsClassFull', 'FN') IS NOT NULL DROP FUNCTION IsClassFull;
GO

CREATE FUNCTION IsClassFull(@p_class_id INT)
RETURNS VARCHAR(5)
AS
BEGIN
    DECLARE @v_current INT;
    DECLARE @v_max     INT;

    SELECT @v_current = COUNT(*) FROM enrollments WHERE class_id = @p_class_id;
    SELECT @v_max = max_students FROM classes WHERE class_id = @p_class_id;

    IF @v_current >= @v_max
        RETURN 'YES';
    
    RETURN 'NO';
END
GO


-- ============================================
-- TRIGGER 1: Log new student registration
-- ============================================
IF OBJECT_ID('trg_after_student_insert', 'TR') IS NOT NULL DROP TRIGGER trg_after_student_insert;
GO

CREATE TRIGGER trg_after_student_insert
ON students
AFTER INSERT
AS
BEGIN
    INSERT INTO activity_log (action_type, table_name, description, action_time)
    SELECT 'INSERT', 'students',
           'New student registered: ' + full_name + ' (ID:' + CAST(student_id AS VARCHAR) + ')',
           GETDATE()
    FROM inserted;
END
GO


-- ============================================
-- TRIGGER 2: Log new enrollment
-- ============================================
IF OBJECT_ID('trg_after_enrollment_insert', 'TR') IS NOT NULL DROP TRIGGER trg_after_enrollment_insert;
GO

CREATE TRIGGER trg_after_enrollment_insert
ON enrollments
AFTER INSERT
AS
BEGIN
    INSERT INTO activity_log (action_type, table_name, description, action_time)
    SELECT 'INSERT', 'enrollments',
           s.full_name + ' enrolled in ' + c.class_name,
           GETDATE()
    FROM inserted i
    JOIN students s ON i.student_id = s.student_id
    JOIN classes c ON i.class_id = c.class_id;
END
GO


-- ============================================
-- TRIGGER 3: Prevent deleting active teacher
-- who still has active classes
-- ============================================
IF OBJECT_ID('trg_before_teacher_delete', 'TR') IS NOT NULL DROP TRIGGER trg_before_teacher_delete;
GO

CREATE TRIGGER trg_before_teacher_delete
ON teachers
INSTEAD OF DELETE
AS
BEGIN
    DECLARE @v_class_count INT;
    SELECT @v_class_count = COUNT(*)
    FROM classes c
    JOIN deleted d ON c.teacher_id = d.teacher_id
    WHERE c.status = 'active';

    IF @v_class_count > 0
    BEGIN
        RAISERROR ('Cannot delete teacher who still has active classes!', 16, 1);
        ROLLBACK TRANSACTION;
        RETURN;
    END

    -- If no error, proceed with delete
    DELETE FROM teachers WHERE teacher_id IN (SELECT teacher_id FROM deleted);
END
GO

SELECT 'All T-SQL objects created successfully!' AS status;
GO


-- ============================================
-- VIEW: Class Details (joins + functions)
-- ============================================
IF OBJECT_ID('vw_ClassDetails', 'V') IS NOT NULL
DROP VIEW vw_ClassDetails;
GO

CREATE VIEW vw_ClassDetails AS
SELECT 
    c.class_id,
    c.class_name,
    c.subject,
    t.full_name AS teacher_name,
    c.schedule,
    c.fee,
    c.max_students,
    c.status,
    dbo.GetClassStudentCount(c.class_id) AS enrolled,
    dbo.IsClassFull(c.class_id) AS is_full
FROM classes c
LEFT JOIN teachers t ON c.teacher_id = t.teacher_id;
GO



















