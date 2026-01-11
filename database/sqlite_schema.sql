-- Table structure for table 'users'
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    gender VARCHAR(50) NOT NULL,
    mobile VARCHAR(50) NOT NULL,
    designation VARCHAR(255) NOT NULL,
    image VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    status INTEGER NOT NULL DEFAULT 1
);

-- Table structure for table 'notification'
CREATE TABLE notification (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    notiuser VARCHAR(255) NOT NULL,
    notireceiver VARCHAR(255) NOT NULL,
    notitype VARCHAR(255) NOT NULL,
    time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table structure for table 'feedback'
CREATE TABLE feedback (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sender VARCHAR(255) NOT NULL,
    receiver VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    feedbackdata TEXT NOT NULL,
    attachment VARCHAR(255),
    is_read INTEGER DEFAULT 0
);

-- Table structure for table 'deleteduser'
CREATE TABLE deleteduser (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email VARCHAR(255) NOT NULL,
    deltime TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default Admin User (password: admin)
INSERT INTO users (name, email, password, gender, mobile, designation, image, status, role) VALUES ('admin', 'admin@admin.com', '$2y$10$L4kM9ZMFq0pgtgZbFe0Bcu0FabVSNTrP1L6FVvL0mk7.9BuHJSR8G', 'Male', '0000000000', 'Administrator', '', 1, 'admin');