<?php
$servername = "localhost";
$username = "root";
$password = "";

$conn = mysqli_connect($servername, $username, $password);

if (!$conn) {
	die("Connection failed");
}

$sql = "CREATE DATABASE IF NOT EXISTS attendance_management_system";
if ($conn->query($sql) === true) {
	echo "Database created successfully<br>";
} else {
	echo "Error creating database: " . $conn->error . "<br>";
}

$conn = mysqli_connect($servername, $username, $password, "attendance_management_system");

if (!$conn) {
	die("Database connect failed");
}

$sql1 = "CREATE TABLE IF NOT EXISTS students (
	student_id VARCHAR(50) PRIMARY KEY,
	name VARCHAR(100),
	email VARCHAR(100) UNIQUE,
	password VARCHAR(100)
)";
$conn->query($sql1);

$sql2 = "CREATE TABLE IF NOT EXISTS faculty (
	faculty_id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(100),
	email VARCHAR(100) UNIQUE,
	password VARCHAR(100)
)";
$conn->query($sql2);

$sql3 = "CREATE TABLE IF NOT EXISTS courses (
	id INT AUTO_INCREMENT PRIMARY KEY,
	faculty_id INT,
	course_name VARCHAR(150) UNIQUE,
	course_code VARCHAR(50) UNIQUE,
	created_at DATETIME
)";
$conn->query($sql3);

$sql4 = "CREATE TABLE IF NOT EXISTS otp_table (
	id INT AUTO_INCREMENT PRIMARY KEY,
	otp_code VARCHAR(10),
	created_by INT,
	course_id INT,
	create_time DATETIME,
	expire_time DATETIME,
	is_active TINYINT(1),
	faculty_lat VARCHAR(50),
	faculty_long VARCHAR(50)
)";
$conn->query($sql4);

mysqli_query($conn, "ALTER TABLE otp_table ADD COLUMN faculty_lat VARCHAR(50)");
mysqli_query($conn, "ALTER TABLE otp_table ADD COLUMN faculty_long VARCHAR(50)");

$sql5 = "CREATE TABLE IF NOT EXISTS attendance (
	id INT AUTO_INCREMENT PRIMARY KEY,
	student_id VARCHAR(50),
	otp_id INT,
	attendance_time DATETIME,
	student_lat VARCHAR(50),
	student_long VARCHAR(50),
	device_identifier VARCHAR(100),
	UNIQUE KEY unique_student_otp (student_id, otp_id)
)";
$conn->query($sql5);

mysqli_query($conn, "ALTER TABLE attendance ADD COLUMN student_lat VARCHAR(50)");
mysqli_query($conn, "ALTER TABLE attendance ADD COLUMN student_long VARCHAR(50)");
mysqli_query($conn, "ALTER TABLE attendance ADD COLUMN device_identifier VARCHAR(100)");

$check_faculty = mysqli_query($conn, "SELECT * FROM faculty WHERE email = 'faculty@gub.com'");
if (mysqli_num_rows($check_faculty) == 0) {
	mysqli_query(
		$conn,
		"INSERT INTO faculty (name, email, password) VALUES ('Faculty One', 'faculty@gub.com', '123456')"
	);
}

echo "Tables ready and default faculty inserted (if not exists).<br>";
echo "Now open login.php";
?>
