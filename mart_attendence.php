<?php
session_start();
include("connection.php");

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "student") {
	header("Location: login.php");
	exit();
}

if (!isset($_POST["mark_submit"])) {
	header("Location: student.php?msg=Invalid+request");
	exit();
}

$student_id = $_SESSION["student_id"];
$course_id = trim($_POST["course_id"]);
$otp_code = trim($_POST["otp_code"]);
$student_lat = trim($_POST["student_lat"]);
$student_long = trim($_POST["student_long"]);

$device_identifier = $_SERVER["REMOTE_ADDR"];

if ($course_id == "" || $otp_code == "") {
	header("Location: student.php?msg=Please+select+course+and+enter+OTP");
	exit();
}

$otp_query = mysqli_query(
	$conn,
	"SELECT * FROM otp_table WHERE otp_code = '$otp_code' AND course_id = '$course_id' AND is_active = 1 ORDER BY id DESC LIMIT 1"
);

if (mysqli_num_rows($otp_query) == 0) {
	header("Location: student.php?msg=Invalid+OTP+or+inactive+OTP");
	exit();
}

$otp_row = mysqli_fetch_assoc($otp_query);
$otp_id = $otp_row["id"];
$faculty_lat = $otp_row["faculty_lat"];
$faculty_long = $otp_row["faculty_long"];
$expire_time = strtotime($otp_row["expire_time"]);
$current_time = time();

if ($current_time > $expire_time) {
	mysqli_query($conn, "UPDATE otp_table SET is_active = 0 WHERE id = '$otp_id'");
	header("Location: student.php?msg=OTP+expired");
	exit();
}

if ($student_lat == "" || $student_long == "" || $faculty_lat == "" || $faculty_long == "") {
	header("Location: student.php?msg=Location+not+found");
	exit();
}

$lat_diff = abs($student_lat - $faculty_lat);
$long_diff = abs($student_long - $faculty_long);
if ($lat_diff > 0.001 || $long_diff > 0.001) {
	header("Location: student.php?msg=You+are+outside+the+classroom+boundary");
	exit();
}

$check_duplicate = mysqli_query($conn, "SELECT * FROM attendance WHERE student_id = '$student_id' AND otp_id = '$otp_id'");
if (mysqli_num_rows($check_duplicate) > 0) {
	header("Location: student.php?msg=You+already+marked+attendance+for+this+OTP");
	exit();
}

$check_device = mysqli_query(
	$conn,
	"SELECT * FROM attendance WHERE otp_id = '$otp_id' AND device_identifier = '$device_identifier' AND student_id != '$student_id'"
);
if (mysqli_num_rows($check_device) > 0) {
	header("Location: student.php?msg=This+device+already+marked+attendance+for+another+student");
	exit();
}

$attendance_time = date("Y-m-d H:i:s");
$insert_attendance = mysqli_query(
	$conn,
	"INSERT INTO attendance (student_id, otp_id, attendance_time, student_lat, student_long, device_identifier) VALUES ('$student_id', '$otp_id', '$attendance_time', '$student_lat', '$student_long', '$device_identifier')"
);

if ($insert_attendance) {
	header("Location: student.php?msg=Attendance+marked+successfully");
} else {
	header("Location: student.php?msg=Failed+to+mark+attendance");
}
exit();
?>