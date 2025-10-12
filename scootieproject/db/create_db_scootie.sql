-- ===================================================================
-- ==          Scootie (120 TOTAL CUSTOMERS)    ==
-- ===================================================================

-- create database
DROP DATABASE IF EXISTS scootiedb;
CREATE DATABASE scootiedb;

-- select database
USE scootiedb;

-- create tables
CREATE TABLE Branch (
    Branch_ID INT AUTO_INCREMENT PRIMARY KEY,
    Location VARCHAR(255) NOT NULL UNIQUE,
    Phone_Number VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE Account (
    Account_ID INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(100) NOT NULL UNIQUE,
    Password_Hash VARCHAR(255) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE Customer (
    Customer_ID INT AUTO_INCREMENT PRIMARY KEY,
    Account_ID INT UNIQUE,
    Name VARCHAR(100) NOT NULL,
    Phone_Number VARCHAR(20) NOT NULL UNIQUE,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Driver_License_Number VARCHAR(50) UNIQUE,
    Membership_Level ENUM('Regular', 'VIP') DEFAULT 'Regular',
    CONSTRAINT fk_customer_account FOREIGN KEY (Account_ID) REFERENCES Account(Account_ID) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Employee (
    Employee_ID INT AUTO_INCREMENT PRIMARY KEY,
    Account_ID INT UNIQUE,
    Branch_ID INT NOT NULL,
    Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Phone_Number VARCHAR(20) NOT NULL UNIQUE,
    Position VARCHAR(50) NOT NULL,
    Salary DECIMAL(10, 2),
    CONSTRAINT fk_employee_branch FOREIGN KEY (Branch_ID) REFERENCES Branch(Branch_ID) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_employee_account FOREIGN KEY (Account_ID) REFERENCES Account(Account_ID) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Scooter (
    License_Plate VARCHAR(20) PRIMARY KEY,
    Model_Name VARCHAR(100) NOT NULL,
    Current_Branch_ID INT NOT NULL,
    Status ENUM('Available', 'Rented', 'In Maintenance') DEFAULT 'Available',
    Daily_Rate DECIMAL(10, 2) NOT NULL,
    CONSTRAINT fk_scooter_branch FOREIGN KEY (Current_Branch_ID) REFERENCES Branch(Branch_ID) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Promotion (
    Promotion_ID INT AUTO_INCREMENT PRIMARY KEY,
    Promotion_Name VARCHAR(100) NOT NULL,
    Description TEXT,
    Discount_Percentage DECIMAL(5, 2),
    Start_Date DATE NOT NULL,
    End_Date DATE NOT NULL,
    Membership_Level_Required ENUM('Regular', 'VIP', 'None') DEFAULT 'None',
    Manager_ID INT,
    CONSTRAINT fk_promotion_manager FOREIGN KEY (Manager_ID) REFERENCES Employee(Employee_ID) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_discount_percentage CHECK (Discount_Percentage >= 0 AND Discount_Percentage <= 1)
) ENGINE=InnoDB;

CREATE TABLE Rental (
    Rental_ID INT AUTO_INCREMENT PRIMARY KEY,
    Customer_ID INT NOT NULL,
    License_Plate VARCHAR(20) NOT NULL,
    Pickup_Branch_ID INT NOT NULL,
    Return_Branch_ID INT,
    Pickup_Date_Time DATETIME NOT NULL,
    Return_Date_Time DATETIME,
    Actual_Return_Date_Time DATETIME,
    Initial_Total_Cost DECIMAL(10, 2),
    Discount_Amount DECIMAL(10, 2) DEFAULT 0.00,
    Final_Total_Cost DECIMAL(10, 2),
    Payment_Status ENUM('Pending', 'Paid', 'Refunded') DEFAULT 'Pending',
    Promotion_ID INT,
    CONSTRAINT fk_rental_customer FOREIGN KEY (Customer_ID) REFERENCES Customer(Customer_ID) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_rental_scooter FOREIGN KEY (License_Plate) REFERENCES Scooter(License_Plate) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_rental_pickup_branch FOREIGN KEY (Pickup_Branch_ID) REFERENCES Branch(Branch_ID) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_rental_return_branch FOREIGN KEY (Return_Branch_ID) REFERENCES Branch(Branch_ID) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_rental_promotion FOREIGN KEY (Promotion_ID) REFERENCES Promotion(Promotion_ID) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT chk_return_date CHECK (Return_Date_Time IS NULL OR Return_Date_Time >= Pickup_Date_Time)
) ENGINE=InnoDB;

CREATE TABLE Maintenance (
    Maintenance_ID INT AUTO_INCREMENT PRIMARY KEY,
    License_Plate VARCHAR(20) NOT NULL,
    `Date` DATE NOT NULL,
    Description TEXT,
    Cost DECIMAL(10, 2),
    Performed_By_Employee_ID INT,
    CONSTRAINT fk_maintenance_scooter FOREIGN KEY (License_Plate) REFERENCES Scooter(License_Plate) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_maintenance_employee FOREIGN KEY (Performed_By_Employee_ID) REFERENCES Employee(Employee_ID) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ===================================================================

-- Branch table
INSERT INTO Branch (Location, Phone_Number) VALUES 
('Pantip Plaza Area', '053-111-2222'), ('Tha Phae Gate', '053-222-3333');

-- Account table (6 Employees + 114 Customers = 120 total)
INSERT INTO Account (Username, Password_Hash, Email) VALUES
('somchai_m', 'hashed_pw', 'somchai.m@scootie.com'), ('suda_r', 'hashed_pw', 'suda.r@scootie.com'),
('prawit_t', 'hashed_pw', 'prawit.t@scootie.com'), ('nida_m', 'hashed_pw', 'nida.m@scootie.com'),
('chatchai_r', 'hashed_pw', 'chatchai.r@scootie.com'), ('mana_t', 'hashed_pw', 'mana.t@scootie.com'),
('john_d', 'hashed_pw', 'john.doe@email.com'), ('jane_s', 'hashed_pw', 'jane.smith@email.com'),
('alex_w', 'hashed_pw', 'alex.w@email.com'), ('maria_g', 'hashed_pw', 'maria.g@email.com'),
('chris_p', 'hashed_pw', 'chris.p@email.com'), ('user_12', 'hashed_pw', 'user12@example.com'),
('user_13', 'hashed_pw', 'user13@example.com'), ('user_14', 'hashed_pw', 'user14@example.com'),
('user_15', 'hashed_pw', 'user15@example.com'), ('user_16', 'hashed_pw', 'user16@example.com'),
('user_17', 'hashed_pw', 'user17@example.com'), ('user_18', 'hashed_pw', 'user18@example.com'),
('user_19', 'hashed_pw', 'user19@example.com'), ('user_20', 'hashed_pw', 'user20@example.com'),
('user_21', 'hashed_pw', 'user21@example.com'), ('user_22', 'hashed_pw', 'user22@example.com'),
('user_23', 'hashed_pw', 'user23@example.com'), ('user_24', 'hashed_pw', 'user24@example.com'),
('user_25', 'hashed_pw', 'user25@example.com'), ('user_26', 'hashed_pw', 'user26@example.com'),
('user_27', 'hashed_pw', 'user27@example.com'), ('user_28', 'hashed_pw', 'user28@example.com'),
('user_29', 'hashed_pw', 'user29@example.com'), ('user_30', 'hashed_pw', 'user30@example.com'),
('user_31', 'hashed_pw', 'user31@example.com'), ('user_32', 'hashed_pw', 'user32@example.com'),
('user_33', 'hashed_pw', 'user33@example.com'), ('user_34', 'hashed_pw', 'user34@example.com'),
('user_35', 'hashed_pw', 'user35@example.com'), ('user_36', 'hashed_pw', 'user36@example.com'),
('user_37', 'hashed_pw', 'user37@example.com'), ('user_38', 'hashed_pw', 'user38@example.com'),
('user_39', 'hashed_pw', 'user39@example.com'), ('user_40', 'hashed_pw', 'user40@example.com'),
('user_41', 'hashed_pw', 'user41@example.com'), ('user_42', 'hashed_pw', 'user42@example.com'),
('user_43', 'hashed_pw', 'user43@example.com'), ('user_44', 'hashed_pw', 'user44@example.com'),
('user_45', 'hashed_pw', 'user45@example.com'), ('user_46', 'hashed_pw', 'user46@example.com'),
('user_47', 'hashed_pw', 'user47@example.com'), ('user_48', 'hashed_pw', 'user48@example.com'),
('user_49', 'hashed_pw', 'user49@example.com'), ('user_50', 'hashed_pw', 'user50@example.com'),
('user_51', 'hashed_pw', 'user51@example.com'), ('user_52', 'hashed_pw', 'user52@example.com'),
('user_53', 'hashed_pw', 'user53@example.com'), ('user_54', 'hashed_pw', 'user54@example.com'),
('user_55', 'hashed_pw', 'user55@example.com'), ('user_56', 'hashed_pw', 'user56@example.com'),
('user_57', 'hashed_pw', 'user57@example.com'), ('user_58', 'hashed_pw', 'user58@example.com'),
('user_59', 'hashed_pw', 'user59@example.com'), ('user_60', 'hashed_pw', 'user60@example.com'),
('user_61', 'hashed_pw', 'user61@example.com'), ('user_62', 'hashed_pw', 'user62@example.com'),
('user_63', 'hashed_pw', 'user63@example.com'), ('user_64', 'hashed_pw', 'user64@example.com'),
('user_65', 'hashed_pw', 'user65@example.com'), ('user_66', 'hashed_pw', 'user66@example.com'),
('user_67', 'hashed_pw', 'user67@example.com'), ('user_68', 'hashed_pw', 'user68@example.com'),
('user_69', 'hashed_pw', 'user69@example.com'), ('user_70', 'hashed_pw', 'user70@example.com'),
('user_71', 'hashed_pw', 'user71@example.com'), ('user_72', 'hashed_pw', 'user72@example.com'),
('user_73', 'hashed_pw', 'user73@example.com'), ('user_74', 'hashed_pw', 'user74@example.com'),
('user_75', 'hashed_pw', 'user75@example.com'), ('user_76', 'hashed_pw', 'user76@example.com'),
('user_77', 'hashed_pw', 'user77@example.com'), ('user_78', 'hashed_pw', 'user78@example.com'),
('user_79', 'hashed_pw', 'user79@example.com'), ('user_80', 'hashed_pw', 'user80@example.com'),
('user_81', 'hashed_pw', 'user81@example.com'), ('user_82', 'hashed_pw', 'user82@example.com'),
('user_83', 'hashed_pw', 'user83@example.com'), ('user_84', 'hashed_pw', 'user84@example.com'),
('user_85', 'hashed_pw', 'user85@example.com'), ('user_86', 'hashed_pw', 'user86@example.com'),
('user_87', 'hashed_pw', 'user87@example.com'), ('user_88', 'hashed_pw', 'user88@example.com'),
('user_89', 'hashed_pw', 'user89@example.com'), ('user_90', 'hashed_pw', 'user90@example.com'),
('user_91', 'hashed_pw', 'user91@example.com'), ('user_92', 'hashed_pw', 'user92@example.com'),
('user_93', 'hashed_pw', 'user93@example.com'), ('user_94', 'hashed_pw', 'user94@example.com'),
('user_95', 'hashed_pw', 'user95@example.com'), ('user_96', 'hashed_pw', 'user96@example.com'),
('user_97', 'hashed_pw', 'user97@example.com'), ('user_98', 'hashed_pw', 'user98@example.com'),
('user_99', 'hashed_pw', 'user99@example.com'), ('user_100', 'hashed_pw', 'user100@example.com'),
('user_101', 'hashed_pw', 'user101@example.com'), ('user_102', 'hashed_pw', 'user102@example.com'),
('user_103', 'hashed_pw', 'user103@example.com'), ('user_104', 'hashed_pw', 'user104@example.com'),
('user_105', 'hashed_pw', 'user105@example.com'), ('user_106', 'hashed_pw', 'user106@example.com'),
('user_107', 'hashed_pw', 'user107@example.com'), ('user_108', 'hashed_pw', 'user108@example.com'),
('user_109', 'hashed_pw', 'user109@example.com'), ('user_110', 'hashed_pw', 'user110@example.com'),
('user_111', 'hashed_pw', 'user111@example.com'), ('user_112', 'hashed_pw', 'user112@example.com'),
('user_113', 'hashed_pw', 'user113@example.com'), ('user_114', 'hashed_pw', 'user114@example.com'),
('user_115', 'hashed_pw', 'user115@example.com'), ('user_116', 'hashed_pw', 'user116@example.com'),
('user_117', 'hashed_pw', 'user117@example.com'), ('user_118', 'hashed_pw', 'user118@example.com'),
('user_119', 'hashed_pw', 'user119@example.com'), ('user_120', 'hashed_pw', 'user120@example.com');

-- Populating the Employee table
INSERT INTO Employee (Account_ID, Branch_ID, Name, Email, Phone_Number, Position, Salary) VALUES
(1, 1, 'Somchai Maneewan', 'somchai.m@scootie.com', '081-123-4567', 'Manager', 45000.00),
(2, 1, 'Suda Rakdee', 'suda.r@scootie.com', '082-234-5678', 'Receptionist', 22000.00),
(3, 1, 'Prawit Thongkam', 'prawit.t@scootie.com', '083-345-6789', 'Technician', 25000.00),
(4, 2, 'Nida Maak', 'nida.m@scootie.com', '084-456-7890', 'Manager', 46000.00),
(5, 2, 'Chatchai Ritthirong', 'chatchai.r@scootie.com', '085-567-8901', 'Receptionist', 23000.00),
(6, 2, 'Mana Tongsuk', 'mana.t@scootie.com', '086-678-9012', 'Technician', 26000.00);

-- Populating the Customer table (120 total)
INSERT INTO Customer (Account_ID, Name, Phone_Number, Email, Driver_License_Number, Membership_Level) VALUES
(7, 'John Doe', '091-111-1111', 'john.doe@email.com', 'DL123456', 'Regular'),
(8, 'Jane Smith', '092-222-2222', 'jane.smith@email.com', 'DL654321', 'VIP'),
(9, 'Alex Williams', '093-333-3333', 'alex.w@email.com', 'DL789012', 'Regular'),
(10, 'Maria Garcia', '094-444-4444', 'maria.g@email.com', 'DL210987', 'VIP'),
(11, 'Chris Pine', '095-555-5555', 'chris.p@email.com', 'DL345678', 'Regular'),
(12, 'Panadda Ankulanon', '081-000-0012', 'user12@example.com', 'DL100012', 'VIP'),
(13, 'Jirayu Suksom', '081-000-0013', 'user13@example.com', 'DL100013', 'Regular'),
(14, 'Nattapong Jaturaporn', '081-000-0014', 'user14@example.com', 'DL100014', 'VIP'),
(15, 'Siriratana Laohateeranonda', '081-000-0015', 'user15@example.com', 'DL100015', 'Regular'),
(16, 'Pornthip Saetang', '081-000-0016', 'user16@example.com', 'DL100016', 'Regular'),
(17, 'David Johnson', '081-000-0017', 'user17@example.com', 'DL100017', 'VIP'),
(18, 'Michael Brown', '081-000-0018', 'user18@example.com', 'DL100018', 'Regular'),
(19, 'Waraporn Wong', '081-000-0019', 'user19@example.com', 'DL100019', 'VIP'),
(20, 'Atchara Saelim', '081-000-0020', 'user20@example.com', 'DL100020', 'Regular'),
(21, 'Emily White', '081-000-0021', 'user21@example.com', 'DL100021', 'VIP'),
(22, 'Preecha Charoensuk', '081-000-0022', 'user22@example.com', 'DL100022', 'Regular'),
(23, 'Supachai Panyarachun', '081-000-0023', 'user23@example.com', 'DL100023', 'VIP'),
(24, 'Kamonwan Chirathivat', '081-000-0024', 'user24@example.com', 'DL100024', 'VIP'),
(25, 'Olivia Harris', '081-000-0025', 'user25@example.com', 'DL100025', 'Regular'),
(26, 'Somsak Leeswadtrakul', '081-000-0026', 'user26@example.com', 'DL100026', 'VIP'),
(27, 'Thaksin Shinawatra', '081-000-0027', 'user27@example.com', 'DL100027', 'VIP'),
(28, 'William Clark', '081-000-0028', 'user28@example.com', 'DL100028', 'Regular'),
(29, 'Pimala Sirichai', '081-000-0029', 'user29@example.com', 'DL100029', 'Regular'),
(30, 'Anutin Charnvirakul', '081-000-0030', 'user30@example.com', 'DL100030', 'Regular'),
(31, 'Sophia Lewis', '081-000-0031', 'user31@example.com', 'DL100031', 'VIP'),
(32, 'Boonchai Bencharongkul', '081-000-0032', 'user32@example.com', 'DL100032', 'VIP'),
(33, 'James Walker', '081-000-0033', 'user33@example.com', 'DL100033', 'Regular'),
(34, 'Uthai Thammavet', '081-000-0034', 'user34@example.com', 'DL100034', 'VIP'),
(35, 'Ava Hall', '081-000-0035', 'user35@example.com', 'DL100035', 'Regular'),
(36, 'Prayuth Chan-o-cha', '081-000-0036', 'user36@example.com', 'DL100036', 'Regular'),
(37, 'Isabella Allen', '081-000-0037', 'user37@example.com', 'DL100037', 'VIP'),
(38, 'Chaleo Yoovidhya', '081-000-0038', 'user38@example.com', 'DL100038', 'VIP'),
(39, 'Mia Young', '081-000-0039', 'user39@example.com', 'DL100039', 'Regular'),
(40, 'Dhanin Chearavanont', '081-000-0040', 'user40@example.com', 'DL100040', 'VIP'),
(41, 'Charlotte King', '081-000-0041', 'user41@example.com', 'DL100041', 'VIP'),
(42, 'Vichai Srivaddhanaprabha', '081-000-0042', 'user42@example.com', 'DL100042', 'VIP'),
(43, 'Amelia Wright', '081-000-0043', 'user43@example.com', 'DL100043', 'Regular'),
(44, 'Thongma Vijitpongpun', '081-000-0044', 'user44@example.com', 'DL100044', 'Regular'),
(45, 'Evelyn Lopez', '081-000-0045', 'user45@example.com', 'DL100045', 'VIP'),
(46, 'Vanich Chaiyawan', '081-000-0046', 'user46@example.com', 'DL100046', 'Regular'),
(47, 'Abigail Hill', '081-000-0047', 'user47@example.com', 'DL100047', 'VIP'),
(48, 'Prasert Prasarttong-Osoth', '081-000-0048', 'user48@example.com', 'DL100048', 'VIP'),
(49, 'Harper Scott', '081-000-0049', 'user49@example.com', 'DL100049', 'Regular'),
(50, 'Krit Ratanarak', '081-000-0050', 'user50@example.com', 'DL100050', 'Regular'),
(51, 'Luna Green', '081-000-0051', 'user51@example.com', 'DL100051', 'VIP'),
(52, 'Sathien Setthasit', '081-000-0052', 'user52@example.com', 'DL100052', 'Regular'),
(53, 'Sofia Adams', '081-000-0053', 'user53@example.com', 'DL100053', 'VIP'),
(54, 'Chuchat Petaumpai', '081-000-0054', 'user54@example.com', 'DL100054', 'Regular'),
(55, 'Aria Baker', '081-000-0055', 'user55@example.com', 'DL100055', 'VIP'),
(56, 'Niti Osathanugrah', '081-000-0056', 'user56@example.com', 'DL100056', 'Regular'),
(57, 'Grace Nelson', '081-000-0057', 'user57@example.com', 'DL100057', 'VIP'),
(58, 'Manas Tayechayapong', '081-000-0058', 'user58@example.com', 'DL100058', 'Regular'),
(59, 'Chloe Carter', '081-000-0059', 'user59@example.com', 'DL100059', 'Regular'),
(60, 'Taksaorn Paksukcharern', '081-000-0060', 'user60@example.com', 'DL100060', 'VIP'),
(61, 'Zoe Mitchell', '081-000-0061', 'user61@example.com', 'DL100061', 'Regular'),
(62, 'Patchrapa Chaichua', '081-000-0062', 'user62@example.com', 'DL100062', 'VIP'),
(63, 'Penelope Perez', '081-000-0063', 'user63@example.com', 'DL100063', 'Regular'),
(64, 'Urassaya Sperbund', '081-000-0064', 'user64@example.com', 'DL100064', 'VIP'),
(65, 'Riley Roberts', '081-000-0065', 'user65@example.com', 'DL100065', 'Regular'),
(66, 'Araya A. Hargate', '081-000-0066', 'user66@example.com', 'DL100066', 'VIP'),
(67, 'Nora Turner', '081-000-0067', 'user67@example.com', 'DL100067', 'Regular'),
(68, 'Davika Hoorne', '081-000-0068', 'user68@example.com', 'DL100068', 'VIP'),
(69, 'Lily Phillips', '081-000-0069', 'user69@example.com', 'DL100069', 'Regular'),
(70, 'Ranee Campen', '081-000-0070', 'user70@example.com', 'DL100070', 'VIP'),
(71, 'Ellie Campbell', '081-000-0071', 'user71@example.com', 'DL100071', 'VIP'),
(72, 'Pimchanok Luevisadpaibul', '081-000-0072', 'user72@example.com', 'DL100072', 'Regular'),
(73, 'Hannah Parker', '081-000-0073', 'user73@example.com', 'DL100073', 'VIP'),
(74, 'Kimberley Anne Woltemas', '081-000-0074', 'user74@example.com', 'DL100074', 'Regular'),
(75, 'Lillian Evans', '081-000-0075', 'user75@example.com', 'DL100075', 'VIP'),
(76, 'Nittha Jirayungyurn', '081-000-0076', 'user76@example.com', 'DL100076', 'Regular'),
(77, 'Addison Edwards', '081-000-0077', 'user77@example.com', 'DL100077', 'Regular'),
(78, 'Mario Maurer', '081-000-0078', 'user78@example.com', 'DL100078', 'VIP'),
(79, 'Aubrey Collins', '081-000-0079', 'user79@example.com', 'DL100079', 'Regular'),
(80, 'Nadech Kugimiya', '081-000-0080', 'user80@example.com', 'DL100080', 'VIP'),
(81, 'Brooklyn Stewart', '081-000-0081', 'user81@example.com', 'DL100081', 'Regular'),
(82, 'James Jirayu Tangsrisuk', '081-000-0082', 'user82@example.com', 'DL100082', 'VIP'),
(83, 'Skylar Morris', '081-000-0083', 'user83@example.com', 'DL100083', 'VIP'),
(84, 'Prin Suparat', '081-000-0084', 'user84@example.com', 'DL100084', 'Regular'),
(85, 'Claire Rogers', '081-000-0085', 'user85@example.com', 'DL100085', 'VIP'),
(86, 'Pakorn Chatborirak', '081-000-0086', 'user86@example.com', 'DL100086', 'Regular'),
(87, 'Violet Reed', '081-000-0087', 'user87@example.com', 'DL100087', 'Regular'),
(88, 'Sukollawat Kanarot', '081-000-0088', 'user88@example.com', 'DL100088', 'VIP'),
(89, 'Savannah Cook', '081-000-0089', 'user89@example.com', 'DL100089', 'Regular'),
(90, 'Theeradej Wongpuapan', '081-000-0090', 'user90@example.com', 'DL100090', 'VIP'),
(91, 'Paisley Morgan', '081-000-0091', 'user91@example.com', 'DL100091', 'Regular'),
(92, 'Thanapob Leeratanakajorn', '081-000-0092', 'user92@example.com', 'DL100092', 'VIP'),
(93, 'Genesis Bell', '081-000-0093', 'user93@example.com', 'DL100093', 'Regular'),
(94, 'Sunny Suwanmethanont', '081-000-0094', 'user94@example.com', 'DL100094', 'VIP'),
(95, 'Naomi Murphy', '081-000-0095', 'user95@example.com', 'DL100095', 'VIP'),
(96, 'Ananda Everingham', '081-000-0096', 'user96@example.com', 'DL100096', 'Regular'),
(97, 'Madelyn Bailey', '081-000-0097', 'user97@example.com', 'DL100097', 'VIP'),
(98, 'Chantavit Dhanasevi', '081-000-0098', 'user98@example.com', 'DL100098', 'Regular'),
(99, 'Serenity Rivera', '081-000-0099', 'user99@example.com', 'DL100099', 'VIP'),
(100, 'Kanes Thepsuwan', '081-000-0100', 'user100@example.com', 'DL100100', 'Regular'),
(101, 'Julia Cooper', '081-000-0101', 'user101@example.com', 'DL100101', 'VIP'),
(102, 'Bordin Jaidee', '081-000-0102', 'user102@example.com', 'DL100102', 'Regular'),
(103, 'Caroline Richardson', '081-000-0103', 'user103@example.com', 'DL100103', 'VIP'),
(104, 'Nicha Lertpitaksinchai', '081-000-0104', 'user104@example.com', 'DL100104', 'Regular'),
(105, 'Ariana Cox', '081-000-0105', 'user105@example.com', 'DL100105', 'Regular'),
(106, 'Wiradech Kothny', '081-000-0106', 'user106@example.com', 'DL100106', 'VIP'),
(107, 'Allison Howard', '081-000-0107', 'user107@example.com', 'DL100107', 'Regular'),
(108, 'Panipak Wongpattanakit', '081-000-0108', 'user108@example.com', 'DL100108', 'VIP'),
(109, 'Gabriella Ward', '081-000-0109', 'user109@example.com', 'DL100109', 'Regular'),
(110, 'Ariya Jutanugarn', '081-000-0110', 'user110@example.com', 'DL100110', 'VIP'),
(111, 'Kayla Brooks', '081-000-0111', 'user111@example.com', 'DL100111', 'Regular'),
(112, 'Paradorn Srichaphan', '081-000-0112', 'user112@example.com', 'DL100112', 'VIP'),
(113, 'Samantha Peterson', '081-000-0113', 'user113@example.com', 'DL100113', 'VIP'),
(114, 'Ratchanok Intanon', '081-000-0114', 'user114@example.com', 'DL100114', 'Regular'),
(115, 'Katherine Gray', '081-000-0115', 'user115@example.com', 'DL100115', 'VIP'),
(116, 'Sapsiree Taerattanachai', '081-000-0116', 'user116@example.com', 'DL100116', 'Regular'),
(117, 'Brianna James', '081-000-0117', 'user117@example.com', 'DL100117', 'Regular'),
(118, 'Dechawat Poomjaeng', '081-000-0118', 'user118@example.com', 'DL100118', 'VIP'),
(119, 'Sarah Watson', '081-000-0119', 'user119@example.com', 'DL100119', 'Regular'),
(120, 'Thongchai Jaidee', '081-000-0120', 'user120@example.com', 'DL100120', 'VIP');

-- Populating the Scooter table
INSERT INTO Scooter (License_Plate, Model_Name, Current_Branch_ID, Status, Daily_Rate) VALUES
('1กข 1234', 'Honda Click 125i', 1, 'Available', 250.00), ('1กข 5678', 'Honda Click 125i', 1, 'Available', 250.00),
('2กค 1111', 'Honda PCX 150', 1, 'In Maintenance', 400.00), ('2กค 2222', 'Yamaha NMAX', 2, 'Rented', 450.00),
('3กง 3333', 'Honda Click 150i', 2, 'Available', 300.00), ('3กง 4444', 'Yamaha Aerox', 2, 'Available', 350.00);

-- Populating the Promotion table
INSERT INTO Promotion (Promotion_Name, Description, Discount_Percentage, Start_Date, End_Date, Membership_Level_Required, Manager_ID) VALUES
('Rainy Season Special', '15% off for all rentals during the rainy season.', 0.15, '2025-09-01', '2025-10-31', 'None', 1),
('VIP Long-Term Rental', '25% off for VIP members renting for 7 days or more.', 0.25, '2025-01-01', '2025-12-31', 'VIP', 4);

-- Populating the Maintenance table
INSERT INTO Maintenance (License_Plate, `Date`, Description, Cost, Performed_By_Employee_ID) VALUES
('2กค 1111', '2025-09-28', 'Regular oil change and brake check.', 500.00, 3),
('1กข 1234', '2025-08-15', 'Tire replacement.', 1200.00, 6),
('2กค 2222', '2025-09-05', 'Engine tune-up.', 800.00, 3);

-- Populating the Rental table
INSERT INTO Rental (Customer_ID, License_Plate, Pickup_Branch_ID, Return_Branch_ID, Pickup_Date_Time, Return_Date_Time, Actual_Return_Date_Time, Initial_Total_Cost, Discount_Amount, Final_Total_Cost, Payment_Status, Promotion_ID) VALUES
(1, '1กข 1234', 1, 1, '2025-09-20 10:00:00', '2025-09-22 10:00:00', '2025-09-22 09:45:00', 500.00, 0.00, 500.00, 'Paid', NULL),
(2, '3กง 3333', 2, 2, '2025-09-25 14:00:00', '2025-09-28 14:00:00', '2025-09-28 13:30:00', 900.00, 135.00, 765.00, 'Paid', 1),
(3, '3กง 4444', 2, 1, '2025-09-26 11:00:00', '2025-09-27 11:00:00', '2025-09-27 15:00:00', 350.00, 0.00, 450.00, 'Paid', NULL),
(4, '2กค 2222', 2, NULL, '2025-10-02 09:00:00', '2025-10-05 09:00:00', NULL, 1350.00, NULL, NULL, 'Pending', NULL);