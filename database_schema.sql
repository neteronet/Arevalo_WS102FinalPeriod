-- SAC Cyberian Repository - Users Table Schema
-- This file contains the SQL statements to create the users table with role management

-- Drop existing table if it exists (be careful with this in production!)
-- DROP TABLE IF EXISTS users;

-- Create users table with role field
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'supervisor') NOT NULL DEFAULT 'student',
    status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    student_id VARCHAR(50) UNIQUE,
    supervisor_id VARCHAR(50) UNIQUE,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    department VARCHAR(100),
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_role (role),
    INDEX idx_status (status),
    INDEX idx_email (email),
    INDEX idx_username (username)
);

-- Create user sessions table for tracking active sessions
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_session_token (session_token)
);

-- Create user roles log table for audit trail
CREATE TABLE IF NOT EXISTS user_roles_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    old_role VARCHAR(50),
    new_role VARCHAR(50),
    changed_by INT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reason VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_changed_at (changed_at)
);

-- Sample queries to identify user roles:

-- 1. Get user role by user ID
-- SELECT id, username, email, role FROM users WHERE id = 1;

-- 2. Get user role by username
-- SELECT id, username, email, role FROM users WHERE username = 'student_username';

-- 3. Get user role by email
-- SELECT id, username, email, role FROM users WHERE email = 'user@example.com';

-- 4. Count students
-- SELECT COUNT(*) as student_count FROM users WHERE role = 'student' AND status = 'active';

-- 5. Count supervisors
-- SELECT COUNT(*) as supervisor_count FROM users WHERE role = 'supervisor' AND status = 'active';

-- 6. Get all students
-- SELECT id, username, email, student_id, first_name, last_name FROM users WHERE role = 'student' AND status = 'active';

-- 7. Get all supervisors
-- SELECT id, username, email, supervisor_id, first_name, last_name FROM users WHERE role = 'supervisor' AND status = 'active';

-- 8. Check if user is student
-- SELECT role FROM users WHERE id = 1 AND role = 'student' LIMIT 1;

-- 9. Check if user is supervisor
-- SELECT role FROM users WHERE id = 1 AND role = 'supervisor' LIMIT 1;

-- 10. Get user role and last login
-- SELECT id, username, role, last_login FROM users WHERE email = 'user@example.com';

-- 11. Get user role history
-- SELECT user_id, old_role, new_role, changed_at, reason FROM user_roles_log WHERE user_id = 1 ORDER BY changed_at DESC;

-- 12. Get active user sessions by role
-- SELECT u.id, u.username, u.role, us.session_token, us.created_at, us.expires_at 
-- FROM users u 
-- JOIN user_sessions us ON u.id = us.user_id 
-- WHERE u.status = 'active' ORDER BY u.role;
