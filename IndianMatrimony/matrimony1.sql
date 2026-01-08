-- ========================================
-- Indian Matrimony Database
-- DBMS Mini-Project
-- Developed by: Varad Kalanke and Vaishnavi Ghadge
-- ========================================

-- Create database
CREATE DATABASE IF NOT EXISTS indian_matrimony;
USE indian_matrimony;

-- Drop existing table if exists
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    age INT NOT NULL,
    religion VARCHAR(50) NOT NULL,
    mother_tongue VARCHAR(50) NOT NULL,
    city VARCHAR(100) NOT NULL,
    education VARCHAR(100) NOT NULL,
    occupation VARCHAR(100) NOT NULL,
    bio TEXT,
    photo VARCHAR(255) DEFAULT 'default-avatar.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_gender (gender),
    INDEX idx_religion (religion),
    INDEX idx_city (city),
    INDEX idx_education (education)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data (passwords are hashed using PHP password_hash)
-- Default password for all sample users: "password123"
INSERT INTO users (name, email, password, gender, age, religion, mother_tongue, city, education, occupation, bio, photo) VALUES
('Rahul Sharma', 'rahul.sharma@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Male', 28, 'Hindu', 'Hindi', 'Mumbai', 'B.Tech in Computer Science', 'Software Engineer', 'Looking for a life partner who shares similar values and interests. Love traveling and cooking.', 'male_profile_1.jpg'),
('Priya Patel', 'priya.patel@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Female', 26, 'Hindu', 'Gujarati', 'Ahmedabad', 'MBA in Finance', 'Financial Analyst', 'Family-oriented person who values traditions. Enjoys reading and music.', 'female_profile_1.jpg'),
('Arjun Reddy', 'arjun.reddy@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Male', 30, 'Hindu', 'Telugu', 'Hyderabad', 'MS in Data Science', 'Data Scientist', 'Tech enthusiast looking for an understanding and supportive partner.', 'male_profile_2.jpg'),
('Anjali Singh', 'anjali.singh@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Female', 27, 'Hindu', 'Hindi', 'Delhi', 'MBBS', 'Doctor', 'Passionate about healthcare and helping others. Looking for a caring partner.', 'female_profile_2.jpg'),
('Mohammed Khan', 'mohammed.khan@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Male', 29, 'Muslim', 'Urdu', 'Bangalore', 'B.E. in Mechanical Engineering', 'Mechanical Engineer', 'Simple person with strong family values. Love sports and traveling.', 'male_profile_3.jpg'),
('Fatima Begum', 'fatima.begum@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Female', 25, 'Muslim', 'Urdu', 'Lucknow', 'B.A. in English Literature', 'Teacher', 'Love teaching and making a difference in children lives. Seeking a respectful partner.', 'female_profile_3.jpg'),
('David Kumar', 'david.kumar@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Male', 31, 'Christian', 'English', 'Chennai', 'B.Com', 'Accountant', 'God-fearing person looking for someone who shares the same faith and values.', 'male_profile_4.jpg'),
('Sarah Thomas', 'sarah.thomas@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Female', 24, 'Christian', 'English', 'Kochi', 'BCA', 'Software Developer', 'Creative and ambitious person looking for a supportive life partner.', 'female_profile_4.jpg'),
('Vikram Malhotra', 'vikram.malhotra@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Male', 32, 'Hindu', 'Punjabi', 'Chandigarh', 'CA', 'Chartered Accountant', 'Well-settled professional looking for an educated and understanding partner.', 'male_profile_5.jpg'),
('Sneha Desai', 'sneha.desai@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Female', 28, 'Hindu', 'Marathi', 'Pune', 'B.Sc in Biotechnology', 'Research Scientist', 'Dedicated researcher with a passion for science. Looking for an intelligent partner.', 'female_profile_5.jpg');

-- ========================================
-- Additional Tables for Enhanced Features
-- ========================================

-- Table: interests (to track when users express interest in each other)
CREATE TABLE IF NOT EXISTS interests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    interested_in_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (interested_in_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_interest (user_id, interested_in_id),
    INDEX idx_user_id (user_id),
    INDEX idx_interested_in_id (interested_in_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: messages (for communication between users)
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_sender_id (sender_id),
    INDEX idx_receiver_id (receiver_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: match_preferences (user preferences for ideal partner)
CREATE TABLE IF NOT EXISTS match_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    preferred_gender ENUM('Male', 'Female', 'Other', 'Any') DEFAULT 'Any',
    min_age INT DEFAULT 18,
    max_age INT DEFAULT 100,
    preferred_religion VARCHAR(50),
    preferred_city VARCHAR(100),
    preferred_education VARCHAR(100),
    preferred_occupation VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_preferred_gender (preferred_gender),
    INDEX idx_preferred_religion (preferred_religion),
    INDEX idx_preferred_city (preferred_city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: activity_logs (to track user activities for analytics)
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type ENUM('login', 'profile_view', 'search', 'interest_sent', 'message_sent') NOT NULL,
    activity_details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_activity_type (activity_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: admin_users (for admin panel access)
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (username: admin, password: admin123)
INSERT INTO admin_users (username, password, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@matrimony.com');

-- Insert sample interests data
INSERT INTO interests (user_id, interested_in_id, status) VALUES
(1, 2, 'pending'),
(1, 4, 'accepted'),
(2, 1, 'pending'),
(3, 5, 'accepted'),
(4, 1, 'accepted');

-- Insert sample messages data
INSERT INTO messages (sender_id, receiver_id, message, is_read) VALUES
(1, 2, 'Hello! I found your profile interesting. Would you like to know more about me?', 0),
(2, 1, 'Hi there! Yes, I would love to know more about you.', 1),
(1, 4, 'Hello Anjali, I think we could be a good match. What do you think?', 0),
(4, 1, 'Hi Rahul, yes I think so too! Let\'s connect.', 1);

-- Insert sample match preferences
INSERT INTO match_preferences (user_id, preferred_gender, min_age, max_age, preferred_religion, preferred_city) VALUES
(1, 'Female', 24, 30, 'Hindu', 'Mumbai'),
(2, 'Male', 26, 35, 'Hindu', 'Ahmedabad'),
(3, 'Female', 25, 32, 'Hindu', 'Hyderabad');

-- ========================================
-- Views for Complex Queries
-- ========================================

-- View: user_statistics (aggregate statistics per user)
CREATE OR REPLACE VIEW user_statistics AS
SELECT 
    u.id,
    u.name,
    u.email,
    COUNT(DISTINCT i1.id) AS interests_sent,
    COUNT(DISTINCT i2.id) AS interests_received,
    COUNT(DISTINCT m1.id) AS messages_sent,
    COUNT(DISTINCT m2.id) AS messages_received,
    COUNT(DISTINCT CASE WHEN i1.status = 'accepted' THEN i1.id END) AS accepted_interests
FROM users u
LEFT JOIN interests i1 ON u.id = i1.user_id
LEFT JOIN interests i2 ON u.id = i2.interested_in_id
LEFT JOIN messages m1 ON u.id = m1.sender_id
LEFT JOIN messages m2 ON u.id = m2.receiver_id
GROUP BY u.id, u.name, u.email;

-- View: mutual_interests (users who have mutual interest)
CREATE OR REPLACE VIEW mutual_interests AS
SELECT 
    i1.user_id AS user1_id,
    i1.interested_in_id AS user2_id,
    i1.created_at AS interest_date
FROM interests i1
INNER JOIN interests i2 
    ON i1.user_id = i2.interested_in_id 
    AND i1.interested_in_id = i2.user_id
WHERE i1.status = 'accepted' AND i2.status = 'accepted';

-- View: unread_messages_count (count of unread messages per user)
CREATE OR REPLACE VIEW unread_messages_count AS
SELECT 
    receiver_id AS user_id,
    COUNT(*) AS unread_count
FROM messages
WHERE is_read = 0
GROUP BY receiver_id;

-- ========================================
-- Stored Procedures (Advanced DBMS Feature)
-- ========================================

DELIMITER //

-- Procedure: Get matches based on user preferences
CREATE PROCEDURE IF NOT EXISTS GetMatches(IN p_user_id INT)
BEGIN
    SELECT DISTINCT u.*
    FROM users u
    LEFT JOIN match_preferences mp ON u.id = mp.user_id
    WHERE u.id != p_user_id
      AND (mp.preferred_gender = 'Any' OR u.gender = mp.preferred_gender)
      AND (mp.min_age IS NULL OR u.age >= mp.min_age)
      AND (mp.max_age IS NULL OR u.age <= mp.max_age)
      AND (mp.preferred_religion IS NULL OR u.religion = mp.preferred_religion)
      AND (mp.preferred_city IS NULL OR u.city = mp.preferred_city);
END //

-- Procedure: Get user activity summary
CREATE PROCEDURE IF NOT EXISTS GetUserActivity(IN p_user_id INT, IN p_days INT)
BEGIN
    SELECT 
        activity_type,
        COUNT(*) AS activity_count,
        DATE(created_at) AS activity_date
    FROM activity_logs
    WHERE user_id = p_user_id
      AND created_at >= DATE_SUB(NOW(), INTERVAL p_days DAY)
    GROUP BY activity_type, DATE(created_at)
    ORDER BY activity_date DESC;
END //

DELIMITER ;

-- ========================================
-- Triggers (Advanced DBMS Feature)
-- ========================================

DELIMITER //

-- Drop triggers if they exist (for re-running the script)
DROP TRIGGER IF EXISTS after_user_insert;
DROP TRIGGER IF EXISTS after_user_update;
DROP TRIGGER IF EXISTS after_user_insert_preferences;
DROP TRIGGER IF EXISTS before_interest_update;

-- Trigger: Log user registration activity automatically
CREATE TRIGGER after_user_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO activity_logs (user_id, activity_type, activity_details)
    VALUES (NEW.id, 'login', CONCAT('User registered: ', NEW.name));
END //

-- Trigger: Log profile updates
CREATE TRIGGER after_user_update
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    IF OLD.name != NEW.name OR OLD.bio != NEW.bio OR OLD.photo != NEW.photo THEN
        INSERT INTO activity_logs (user_id, activity_type, activity_details)
        VALUES (NEW.id, 'profile_view', CONCAT('Profile updated at ', NOW()));
    END IF;
END //

-- Trigger: Auto-create match preferences when user registers
CREATE TRIGGER after_user_insert_preferences
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    -- Set default preferences based on user's gender
    INSERT INTO match_preferences (user_id, preferred_gender, min_age, max_age)
    VALUES (
        NEW.id,
        CASE WHEN NEW.gender = 'Male' THEN 'Female' 
             WHEN NEW.gender = 'Female' THEN 'Male' 
             ELSE 'Any' END,
        GREATEST(18, NEW.age - 5),
        LEAST(100, NEW.age + 5)
    );
END //

-- Trigger: Update timestamp on interest status change
CREATE TRIGGER before_interest_update
BEFORE UPDATE ON interests
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status AND NEW.status = 'accepted' THEN
        SET NEW.created_at = NOW();
    END IF;
END //

DELIMITER ;

-- ========================================
-- End of SQL file
-- ========================================
