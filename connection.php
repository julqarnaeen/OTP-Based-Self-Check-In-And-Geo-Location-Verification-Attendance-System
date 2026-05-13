<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance_management_system";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if ($conn) {
	// connected
} else {
	die("Connection failed");
}
?>
