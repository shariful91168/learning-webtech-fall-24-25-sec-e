CREATE DATABASE job_marketplace;

USE job_marketplace;

CREATE TABLE users (
 
    id INT AUTO_INCREMENT PRIMARY KEY,
 
    name VARCHAR(100) NOT NULL,
 
    email VARCHAR(100) NOT NULL UNIQUE,
 
    password VARCHAR(255) NOT NULL,
 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
 
);
 
SELECT * FROM job_marketplace;

CREATE DATABASE IF NOT EXISTS job_marketplace;

USE job_marketplace;

DROP TABLE IF EXISTS users;

CREATE TABLE users (
 
    id INT AUTO_INCREMENT PRIMARY KEY,
 
    first_name VARCHAR(50) NOT NULL,
 
    last_name VARCHAR(50) NOT NULL,
 
    email VARCHAR(100) NOT NULL UNIQUE,
 
    password VARCHAR(255) NOT NULL,
 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
 
);

select* from users;


ALTER TABLE users DROP COLUMN role;
 
ALTER TABLE users ADD COLUMN role ENUM('Job Seeker', 'Employer') NOT NULL;
 
 
CREATE TABLE jobs (

    id INT AUTO_INCREMENT PRIMARY KEY,

    job_title VARCHAR(255) NOT NULL,

    company_name VARCHAR(255) NOT NULL,

    location VARCHAR(255) NOT NULL,

    salary VARCHAR(255) NOT NULL,

    job_type VARCHAR(100) NOT NULL,

    experience_level VARCHAR(100) NOT NULL,

    description TEXT NOT NULL,

    posted_at DATETIME DEFAULT CURRENT_TIMESTAMP

);
 
 
ALTER TABLE jobs ADD employer_id INT;
 
select* from jobs;
 
 
CREATE TABLE shortlisted_candidates (

    id INT AUTO_INCREMENT PRIMARY KEY,

    job_id INT NOT NULL,

    candidate_id INT NOT NULL,

    status VARCHAR(50) DEFAULT 'Shortlisted',

    notes TEXT,

    FOREIGN KEY (job_id) REFERENCES jobs(id),

    FOREIGN KEY (candidate_id) REFERENCES users(id)

);
 
select* from shortlisted_candidates;
 
 
CREATE TABLE shortlisted_candidates (

    id INT AUTO_INCREMENT PRIMARY KEY,

    job_id INT NOT NULL,

    candidate_id INT NOT NULL,

    status VARCHAR(50) DEFAULT 'Shortlisted',

    notes TEXT,

    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,

    FOREIGN KEY (candidate_id) REFERENCES users(id) ON DELETE CASCADE

);
 
DESCRIBE shortlisted_candidates;
 
UPDATE jobs SET employer_id = 1 WHERE employer_id IS NULL;
 
INSERT INTO users (first_name, last_name, email, password) 

VALUES ('Default', 'Employer', 'default_employer@example.com', 'dummy_password');
 
SELECT id FROM users WHERE email = 'default_employer@example.com';
 
UPDATE jobs SET employer_id = 1 WHERE employer_id IS NULL AND id > 0;
 
SELECT * FROM jobs;

CREATE TABLE jobs (

    id INT AUTO_INCREMENT PRIMARY KEY,

    job_title VARCHAR(255) NOT NULL,

    company_name VARCHAR(255) NOT NULL,

    location VARCHAR(255) NOT NULL,

    salary VARCHAR(255) NOT NULL,

    job_type VARCHAR(100) NOT NULL,

    experience_level VARCHAR(100) NOT NULL,

    description TEXT NOT NULL,

    posted_at DATETIME DEFAULT CURRENT_TIMESTAMP

);

select* from jobs;

DELETE FROM jobs;

DELETE FROM jobs WHERE id = 1;


CREATE TABLE job_applications (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    job_id INT NOT NULL,

    applied_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE

);

select* from job_applications;


ALTER TABLE jobs ADD user_id INT NOT NULL;
 
ALTER TABLE users 

ADD phone_number VARCHAR(15),

ADD address TEXT,

ADD profile_picture VARCHAR(255),

ADD cv VARCHAR(255);
 
 
 CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employer_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE
);
select* from notifications;

DESCRIBE users;
drop table notifications;

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employer_id INT NOT NULL,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES users(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_seeker_id INT NOT NULL,
    employer_id INT NOT NULL,
    feedback_text TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_seeker_id) REFERENCES users(id),
    FOREIGN KEY (employer_id) REFERENCES users(id)
);
select* from feedback;

CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    job_id INT NOT NULL,
    applied_at DATETIME NOT NULL,
    shortlisted BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (job_id) REFERENCES jobs(id)
);

select* from applications;

ALTER TABLE users ADD COLUMN interests TEXT;
select* from users;


CREATE TABLE applied_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    job_id INT NOT NULL,
    applied_at DATETIME NOT NULL,
    UNIQUE(user_id, job_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
);

select* from applications;



CREATE TABLE IF NOT EXISTS resumes (
            resume_id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            skills TEXT NOT NULL,
            experience TEXT NOT NULL,
            education TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        
CREATE TABLE IF NOT EXISTS interview_schedule (
            schedule_id INT AUTO_INCREMENT PRIMARY KEY,
            applicant_id INT NOT NULL,
            interview_date DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        drop table interview_schedule;
        
        CREATE TABLE interview_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    applicant_id INT NOT NULL,
    interview_date DATE NOT NULL,
    FOREIGN KEY (applicant_id) REFERENCES users(id) ON DELETE CASCADE
);


ALTER TABLE applications
ADD COLUMN status ENUM('Applied', 'Shortlisted', 'Interview Scheduled', 'Hired', 'Rejected') DEFAULT 'Applied',
ADD COLUMN notes TEXT;


CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
);

select* from messages;

INSERT INTO messages (sender_id, receiver_id, message)
VALUES (1, 2, 'Test message from user 1 to user 2');


ALTER TABLE messages ADD COLUMN is_read TINYINT(1) DEFAULT 0;
