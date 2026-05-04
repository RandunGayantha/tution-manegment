-- ============================================
-- STEP 2: Insert Sample Data
-- ============================================

-- Sample Teachers
INSERT INTO teachers (full_name, email, phone, subject, salary) VALUES
('Mr. Kamal Perera',   'kamal@tuition.lk',  '0771234567', 'Mathematics', 45000),
('Ms. Nimal Silva',    'nimal@tuition.lk',  '0772345678', 'Science',     42000),
('Mrs. Dilani Fernando','dilani@tuition.lk', '0773456789', 'English',     40000),
('Mr. Suresh Kumar',   'suresh@tuition.lk', '0774567890', 'ICT',         38000);

-- Sample Students

-- Sample Classes
INSERT INTO classes (class_name, subject, teacher_id, schedule, max_students, fee) VALUES
('Maths Grade 10',    'Mathematics', 1, 'Mon/Wed 4pm-6pm', 25, 2500),
('Maths Grade 11',    'Mathematics', 1, 'Tue/Thu 4pm-6pm', 25, 2800),
('Science Grade 10',  'Science',     2, 'Mon/Wed 6pm-8pm', 20, 2200),
('English Spoken',    'English',     3, 'Sat 9am-12pm',    30, 1800),
('ICT Beginners',     'ICT',         4, 'Sun 9am-1pm',     20, 3000);

-- Sample Enrollments
INSERT INTO enrollments (student_id, class_id, payment_status) VALUES
(1, 1, 'paid'),
(2, 2, 'paid'),
(3, 1, 'pending'),
(4, 4, 'paid'),
(5, 3, 'pending'),
(6, 5, 'paid'),
(7, 1, 'overdue'),
(8, 2, 'paid'),
(1, 4, 'paid'),
(2, 5, 'pending');

-- Sample Payments









SELECT 'Sample data inserted!' AS status;