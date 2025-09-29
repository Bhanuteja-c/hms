-- Healsync Sample Data
-- Run this after schema.sql to populate the database with sample data
-- NOTE: This assumes you already have users with IDs 2-7 in your database

USE healsync;


UPDATE users SET password = '$2y$10$HuRYq6wAcJc6sUuWZZnBmeOAsFmahcu2SeFatRZNTlLWBeUR.2eqO' WHERE email = 'admin@healsync.com';
UPDATE users SET password = '$2y$10$foHr9uiZs5KfZOg.m1.y7.Kkbpl/MJ69I92B4Om7D0mtoTcnQXOr6' WHERE email = 'sabari@healsync.com';
UPDATE users SET password = '$2y$10$PaROvApQaQv9JJX/nlFeAO9BRmo5vVF2ngNzQmkVhOHnD/8zvUd36' WHERE email = 'harith@healsync.com';
UPDATE users SET password = '$2y$10$zZ40UaBhqbDNbr59B9wrYeguvW9gJFJSm4qdF/I.6VfLmlapBMK5O' WHERE email = 'sowmiya@healsync.com';
UPDATE users SET password = '$2y$10$Mv4X9rMIy6glEzPStLabluI27w9ijK8NjgtQI5q.udjqxWKRP07b6' WHERE email = 'bhanuteja@healsync.com';
UPDATE users SET password = '$2y$10$ZSPcbPnYX0v6gNv27Jdk5.gbJ04PGk..GkhRGZMrXYRKbJZC60sJC' WHERE email = 'rahul@healsync.com';


Admin → admin@healsync.com / admin123

Receptionist → sabari@healsync.com / sabari123

Doctor → harith@healsync.com / harith123

Doctor → sowmiya@healsync.com / sowmiya123

Patient → bhanuteja@healsync.com / bhanuteja123

Patient → rahul@healsync.com / rahul123

-- Note: User data is already present in your database with IDs 2-7:
-- ID 2: admin (Admin)
-- ID 3: receptionist (Sabari) 
-- ID 4: doctor (Harith Kumar)
-- ID 5: doctor (Sowmiya R)
-- ID 6: patient (Bhanuteja)
-- ID 7: patient (Rahul Verma)

-- Insert Doctor Details for your existing doctors
INSERT INTO doctors (id, specialty, availability, created_at) VALUES
(4, 'Internal Medicine', '{"monday": {"start": "09:00", "end": "17:00"}, "tuesday": {"start": "09:00", "end": "17:00"}, "wednesday": {"start": "09:00", "end": "17:00"}, "thursday": {"start": "09:00", "end": "17:00"}, "friday": {"start": "09:00", "end": "15:00"}}', '2025-09-28 19:47:58'),
(5, 'Cardiology', '{"monday": {"start": "08:00", "end": "16:00"}, "tuesday": {"start": "08:00", "end": "16:00"}, "wednesday": {"start": "08:00", "end": "16:00"}, "thursday": {"start": "08:00", "end": "16:00"}, "friday": {"start": "08:00", "end": "14:00"}}', '2025-09-28 19:47:58');

-- Insert Patient Details for your existing patients
INSERT INTO patients (id, medical_history, created_at) VALUES
(6, 'No significant medical history. Regular checkups recommended.', '2025-09-28 19:47:58'),
(7, 'Mild asthma condition. Uses inhaler as needed during seasonal changes.', '2025-09-28 19:47:58');

-- Insert Appointments using your existing users
INSERT INTO appointments (patient_id, doctor_id, date_time, reason, status, created_at) VALUES
-- Completed appointments (recent past)
(6, 4, '2025-09-20 10:00:00', 'General health checkup and consultation', 'completed', '2025-09-15 14:30:00'),
(7, 5, '2025-09-21 14:30:00', 'Cardiology consultation for heart health', 'completed', '2025-09-16 09:15:00'),
(6, 5, '2025-09-22 09:00:00', 'Follow-up cardiology appointment', 'completed', '2025-09-17 11:45:00'),
(7, 4, '2025-09-23 11:00:00', 'Internal medicine consultation', 'completed', '2025-09-18 16:20:00'),

-- Approved upcoming appointments
(6, 4, '2025-10-05 15:00:00', 'Follow-up appointment for general health', 'approved', '2025-09-19 10:30:00'),
(7, 5, '2025-10-06 10:30:00', 'Cardiology follow-up consultation', 'approved', '2025-09-20 13:15:00'),
(6, 5, '2025-10-07 14:00:00', 'Heart health monitoring appointment', 'approved', '2025-09-21 08:45:00'),
(7, 4, '2025-10-08 11:30:00', 'General medicine consultation', 'approved', '2025-09-22 15:30:00'),

-- Pending appointments
(6, 5, '2025-10-10 09:30:00', 'Cardiology consultation for heart screening', 'pending', '2025-09-23 12:00:00'),
(7, 4, '2025-10-11 16:00:00', 'Follow-up appointment for asthma management', 'pending', '2025-09-24 14:20:00'),
(6, 4, '2025-10-12 13:00:00', 'General health consultation', 'pending', '2025-09-25 09:30:00'),

-- Cancelled appointments
(7, 5, '2025-09-25 10:00:00', 'Cancelled due to scheduling conflict', 'cancelled', '2025-09-20 16:45:00');

-- Insert Prescriptions for completed appointments
INSERT INTO prescriptions (appointment_id, created_at) VALUES
(1, '2025-09-20 10:30:00'),
(2, '2025-09-21 15:00:00'),
(3, '2025-09-22 09:30:00'),
(4, '2025-09-23 11:30:00');

-- Insert Prescription Items for your patients
INSERT INTO prescription_items (prescription_id, medicine, dosage, duration, instructions) VALUES
-- Prescription 1 (Bhanuteja - General health checkup)
(1, 'Multivitamin', '1 tablet', '30 days', 'Take once daily with breakfast'),
(1, 'Vitamin D3', '1000 IU', '30 days', 'Take once daily with food'),
(1, 'Omega-3', '1000mg', '30 days', 'Take once daily with meals'),

-- Prescription 2 (Rahul Verma - Cardiology consultation)
(2, 'Atorvastatin', '20mg', '30 days', 'Take once daily in the evening'),
(2, 'Aspirin', '81mg', '30 days', 'Take once daily with food'),
(2, 'Lisinopril', '5mg', '30 days', 'Take once daily in the morning'),

-- Prescription 3 (Bhanuteja - Cardiology follow-up)
(3, 'CoQ10', '100mg', '30 days', 'Take once daily with food'),
(3, 'Magnesium', '400mg', '30 days', 'Take once daily in the evening'),

-- Prescription 4 (Rahul Verma - Internal medicine consultation)
(4, 'Albuterol Inhaler', '90mcg', '30 days', 'Use as needed for breathing difficulties'),
(4, 'Fluticasone Inhaler', '250mcg', '30 days', 'Use twice daily morning and evening');

-- Insert Treatments for your appointments
INSERT INTO treatments (appointment_id, treatment_name, date, notes, cost, created_at) VALUES
(1, 'General Health Consultation', '2025-09-20', 'Complete health assessment, vital signs check, lifestyle counseling', 200.00, '2025-09-20 10:30:00'),
(1, 'Blood Work', '2025-09-20', 'Complete blood count, lipid panel, kidney function tests', 85.00, '2025-09-20 10:45:00'),

(2, 'Cardiology Consultation', '2025-09-21', 'EKG, heart health assessment, cardiovascular screening', 300.00, '2025-09-21 15:00:00'),
(2, 'Echocardiogram', '2025-09-21', 'Complete cardiac ultrasound examination', 250.00, '2025-09-21 15:15:00'),

(3, 'Cardiology Follow-up', '2025-09-22', 'Heart health monitoring, medication review', 180.00, '2025-09-22 09:30:00'),
(3, 'Stress Test', '2025-09-22', 'Exercise stress test for heart function', 150.00, '2025-09-22 10:00:00'),

(4, 'Internal Medicine Consultation', '2025-09-23', 'General health evaluation, asthma management', 180.00, '2025-09-23 11:30:00'),
(4, 'Spirometry Test', '2025-09-23', 'Lung function assessment for asthma', 120.00, '2025-09-23 11:45:00');

-- Insert Bills for your appointments
INSERT INTO bills (patient_id, doctor_id, appointment_id, total_amount, status, paid_at, created_at) VALUES
(6, 4, 1, 285.00, 'paid', '2025-09-20 11:00:00', '2025-09-20 10:30:00'),
(7, 5, 2, 550.00, 'paid', '2025-09-21 15:30:00', '2025-09-21 15:00:00'),
(6, 5, 3, 330.00, 'paid', '2025-09-22 10:30:00', '2025-09-22 09:30:00'),
(7, 4, 4, 300.00, 'unpaid', NULL, '2025-09-23 11:30:00');

-- Insert Bill Items for your bills
INSERT INTO bill_items (bill_id, description, amount) VALUES
-- Bill 1 (Bhanuteja - General Health)
(1, 'General Health Consultation', 200.00),
(1, 'Blood Work - Complete Blood Count, Lipid Panel', 85.00),

-- Bill 2 (Rahul Verma - Cardiology)
(2, 'Cardiology Consultation', 300.00),
(2, 'Echocardiogram', 250.00),

-- Bill 3 (Bhanuteja - Cardiology Follow-up)
(3, 'Cardiology Follow-up Consultation', 180.00),
(3, 'Stress Test', 150.00),

-- Bill 4 (Rahul Verma - Internal Medicine)
(4, 'Internal Medicine Consultation', 180.00),
(4, 'Spirometry Test', 120.00);

-- Insert Payments for your bills
INSERT INTO payments (bill_id, amount, method, transaction_id, created_at) VALUES
(1, 285.00, 'card', 'TXN_001_20250920', '2025-09-20 11:00:00'),
(2, 550.00, 'upi', 'TXN_002_20250921', '2025-09-21 15:30:00'),
(3, 330.00, 'card', 'TXN_003_20250922', '2025-09-22 10:30:00');

-- Insert Notifications for your users
INSERT INTO notifications (user_id, message, link, is_read, created_at) VALUES
-- Patient notifications
(6, 'Your appointment with Dr. Harith Kumar has been completed. Please check your prescriptions and bills.', '/patient/prescriptions.php', 1, '2025-09-20 10:30:00'),
(6, 'Your bill of ₹285.00 has been paid successfully. Receipt available for download.', '/patient/bills.php', 1, '2025-09-20 11:00:00'),
(6, 'Your upcoming appointment with Dr. Sowmiya R is scheduled for October 10th at 9:30 AM.', '/patient/appointments.php', 0, '2025-09-23 12:00:00'),

(7, 'Your appointment with Dr. Sowmiya R has been completed. Please check your prescriptions.', '/patient/prescriptions.php', 1, '2025-09-21 15:00:00'),
(7, 'Your bill of ₹550.00 has been paid successfully.', '/patient/bills.php', 1, '2025-09-21 15:30:00'),
(7, 'Your upcoming appointment with Dr. Harith Kumar is scheduled for October 11th at 4:00 PM.', '/patient/appointments.php', 0, '2025-09-24 14:20:00'),

(6, 'Your appointment with Dr. Sowmiya R has been completed. Please check your prescriptions and bills.', '/patient/prescriptions.php', 1, '2025-09-22 09:30:00'),
(6, 'Your bill of ₹330.00 has been paid successfully.', '/patient/bills.php', 1, '2025-09-22 10:30:00'),
(6, 'Your upcoming appointment with Dr. Harith Kumar is scheduled for October 12th at 1:00 PM.', '/patient/appointments.php', 0, '2025-09-25 09:30:00'),

(7, 'Your appointment with Dr. Harith Kumar has been completed. Please check your prescriptions and bills.', '/patient/prescriptions.php', 1, '2025-09-23 11:30:00'),
(7, 'You have an unpaid bill of ₹300.00. Please make payment at your earliest convenience.', '/patient/bills.php', 0, '2025-09-23 11:30:00'),

-- Doctor notifications
(4, 'You have 3 pending appointments that require your approval.', '/doctor/pending_appointments.php', 0, '2025-09-23 12:00:00'),
(5, 'You have 1 pending appointment that requires your approval.', '/doctor/pending_appointments.php', 0, '2025-09-23 12:00:00'),

-- Admin notifications
(2, 'System has processed 4 completed appointments today.', '/admin/dashboard.php', 0, '2025-09-23 18:00:00'),
(2, 'Total revenue for today: ₹1,165.00', '/admin/reports.php', 0, '2025-09-23 18:00:00');

-- Insert Audit Logs for your users
INSERT INTO audit_logs (user_id, action, meta, created_at) VALUES
(2, 'system_initialized', '{"version": "1.0", "timestamp": "2025-09-28 19:47:58"}', '2025-09-28 19:47:58'),
(4, 'doctor_registered', '{"specialty": "Internal Medicine", "email": "harith@healsync.com"}', '2025-09-28 19:47:58'),
(5, 'doctor_registered', '{"specialty": "Cardiology", "email": "sowmiya@healsync.com"}', '2025-09-28 19:47:58'),
(3, 'receptionist_registered', '{"email": "sabari@healsync.com"}', '2025-09-28 19:47:58'),
(6, 'patient_registered', '{"email": "bhanuteja@healsync.com"}', '2025-09-28 19:47:58'),
(7, 'patient_registered', '{"email": "rahul@healsync.com"}', '2025-09-28 19:47:58'),
(6, 'appointment_booked', '{"appointment_id": 1, "doctor_id": 4, "date": "2025-09-20 10:00:00"}', '2025-09-15 14:30:00'),
(7, 'appointment_booked', '{"appointment_id": 2, "doctor_id": 5, "date": "2025-09-21 14:30:00"}', '2025-09-16 09:15:00'),
(6, 'appointment_booked', '{"appointment_id": 3, "doctor_id": 5, "date": "2025-09-22 09:00:00"}', '2025-09-17 11:45:00'),
(7, 'appointment_booked', '{"appointment_id": 4, "doctor_id": 4, "date": "2025-09-23 11:00:00"}', '2025-09-18 16:20:00'),
(4, 'appointment_approved', '{"appointment_id": 1, "patient_id": 6}', '2025-09-16 08:00:00'),
(5, 'appointment_approved', '{"appointment_id": 2, "patient_id": 7}', '2025-09-17 08:00:00'),
(5, 'appointment_approved', '{"appointment_id": 3, "patient_id": 6}', '2025-09-18 08:00:00'),
(4, 'appointment_approved', '{"appointment_id": 4, "patient_id": 7}', '2025-09-19 08:00:00'),
(4, 'prescription_created', '{"prescription_id": 1, "patient_id": 6, "medicines": 3}', '2025-09-20 10:30:00'),
(5, 'prescription_created', '{"prescription_id": 2, "patient_id": 7, "medicines": 3}', '2025-09-21 15:00:00'),
(5, 'prescription_created', '{"prescription_id": 3, "patient_id": 6, "medicines": 2}', '2025-09-22 09:30:00'),
(4, 'prescription_created', '{"prescription_id": 4, "patient_id": 7, "medicines": 2}', '2025-09-23 11:30:00'),
(6, 'payment_made', '{"bill_id": 1, "amount": 285.00, "method": "card"}', '2025-09-20 11:00:00'),
(7, 'payment_made', '{"bill_id": 2, "amount": 550.00, "method": "upi"}', '2025-09-21 15:30:00'),
(6, 'payment_made', '{"bill_id": 3, "amount": 330.00, "method": "card"}', '2025-09-22 10:30:00');

-- Reset AUTO_INCREMENT values for your data
ALTER TABLE users AUTO_INCREMENT = 8;
ALTER TABLE appointments AUTO_INCREMENT = 12;
ALTER TABLE prescriptions AUTO_INCREMENT = 5;
ALTER TABLE prescription_items AUTO_INCREMENT = 9;
ALTER TABLE treatments AUTO_INCREMENT = 9;
ALTER TABLE bills AUTO_INCREMENT = 5;
ALTER TABLE bill_items AUTO_INCREMENT = 9;
ALTER TABLE payments AUTO_INCREMENT = 4;
ALTER TABLE notifications AUTO_INCREMENT = 11;
ALTER TABLE audit_logs AUTO_INCREMENT = 22;
