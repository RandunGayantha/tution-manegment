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

INSERT INTO students 
(full_name, email, phone, grade, address, dob, guardian_name, guardian_phone, status, username, password)
VALUES
('Kasun Perera', 'kasun@gmail.com', '0771234567', 'Grade 10', 'Negombo', '2008-05-10', 'Nimal Perera', '0711111111', 'active', 'kasun10', 'pass123'),
('Amaya Silva', 'amaya@gmail.com', '0772345678', 'Grade 11', 'Colombo', '2007-03-22', 'Saman Silva', '0722222222', 'active', 'amaya11', 'pass123'),
('Tharindu Fernando', 'tharindu@gmail.com', '0773456789', 'Grade 10', 'Gampaha', '2008-11-15', 'Kamal Fernando', '0733333333', 'active', 'tharu10', 'pass123'),
('Nimasha Jayasinghe', 'nimasha@gmail.com', '0774567890', 'Grade 12', 'Kandy', '2006-07-01', 'Sunil Jayasinghe', '0744444444', 'active', 'nima12', 'pass123'),
('Dinuka Rathnayake', 'dinuka@gmail.com', '0775678901', 'Grade 11', 'Kurunegala', '2007-09-18', 'Ranjith Rathnayake', '0755555555', 'active', 'dinu11', 'pass123'),
('Sanduni Perera', 'sanduni@gmail.com', '0776789012', 'Grade 10', 'Negombo', '2008-01-25', 'Pradeep Perera', '0766666666', 'active', 'sandu10', 'pass123'),
('Pasindu Madushanka', 'pasindu@gmail.com', '0777890123', 'Grade 12', 'Matara', '2006-12-30', 'Chandana Madushanka', '0777777777', 'active', 'pasindu12', 'pass123'),
('Ishara Wickramasinghe', 'ishara@gmail.com', '0778901234', 'Grade 11', 'Galle', '2007-06-14', 'Ajith Wickramasinghe', '0788888888', 'active', 'isha11', 'pass123'),
('Malith Kumara', 'malith@gmail.com', '0779012345', 'Grade 10', 'Kalutara', '2008-09-09', 'Sunil Kumara', '0799999999', 'inactive', 'mali10', 'pass123'),
('Sachini Fernando', 'sachini@gmail.com', '0771122334', 'Grade 12', 'Colombo', '2006-02-19', 'Dinesh Fernando', '0712233445', 'active', 'sachi12', 'pass123');


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
