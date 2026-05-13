<?php
session_start();
include("connection.php");

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "student") {
	header("Location: login.php");
	exit();
}

$message = "";
if (isset($_GET["msg"])) {
	$message = $_GET["msg"];
}

$courses = mysqli_query($conn, "SELECT * FROM courses ORDER BY id DESC");
$student_id = $_SESSION["student_id"];

$history = mysqli_query(
	$conn,
	"SELECT a.attendance_time, c.course_code, c.course_name, o.otp_code
	 FROM attendance a
	 JOIN otp_table o ON a.otp_id = o.id
	 JOIN courses c ON o.course_id = c.id
	 WHERE a.student_id = '$student_id'
	 ORDER BY a.id DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Student Panel</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
	<div class="container wide">
		<h1>Student Panel</h1>
		<p>Welcome <?php echo $_SESSION["student_name"]; ?> (<?php echo $_SESSION["student_id"]; ?>)</p>
		<p><a href="logout.php">Logout</a></p>

		<?php if ($message != "") { ?>
			<p class="message"><?php echo $message; ?></p>
		<?php } ?>

		<h2>Mark Attendance</h2>
		<form method="POST" action="mark_attendance.php">
			<label>Select Course</label>
			<select name="course_id" required>
				<option value="">Choose a course</option>
				<?php while ($course = mysqli_fetch_assoc($courses)) { ?>
					<option value="<?php echo $course["id"]; ?>"><?php echo $course["course_code"]; ?> - <?php echo $course["course_name"]; ?></option>
				<?php } ?>
			</select>

			<label>Enter OTP</label>
			<input type="text" name="otp_code" maxlength="6" required>

			<input type="hidden" name="student_lat" id="student_lat">
			<input type="hidden" name="student_long" id="student_long">

			<button type="submit" name="mark_submit" class="blue-btn">Submit Attendance</button>
		</form>

		<h2>My Attendance History</h2>
		<table>
			<tr>
				<th>Course</th>
				<th>OTP</th>
				<th>Attendance Time</th>
			</tr>
			<?php while ($row = mysqli_fetch_assoc($history)) { ?>
				<tr>
					<td><?php echo $row["course_code"]; ?> - <?php echo $row["course_name"]; ?></td>
					<td><?php echo $row["otp_code"]; ?></td>
					<td><?php echo $row["attendance_time"]; ?></td>
				</tr>
			<?php } ?>
		</table>
	</div>

	<script>
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function (position) {
				document.getElementById("student_lat").value = position.coords.latitude;
				document.getElementById("student_long").value = position.coords.longitude;
			});
		}
	</script>
</body>
</html>