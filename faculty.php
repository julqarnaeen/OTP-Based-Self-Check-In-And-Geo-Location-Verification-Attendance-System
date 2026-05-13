<?php
session_start();
include("connection.php");

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "faculty") {
	header("Location: login.php");
	exit();
}

$faculty_id = $_SESSION["faculty_id"];
$message = "";
$current_otp_data = null;

if (isset($_POST["add_course_submit"])) {
	$course_name = trim($_POST["course_name"]);
	$course_code = trim($_POST["course_code"]);

	if ($course_name == "" || $course_code == "") {
		$message = "Please fill course name and course code";
	} else {
		$created_at = date("Y-m-d H:i:s");
		$insert_course = "INSERT INTO courses (faculty_id, course_name, course_code, created_at) VALUES ('$faculty_id', '$course_name', '$course_code', '$created_at')";
		$result_course = mysqli_query($conn, $insert_course);

		if ($result_course) {
			$message = "Course added successfully";
		} else {
			if (strpos(mysqli_error($conn), "course_name") !== false) {
				$message = "Course name already exists";
			} else if (strpos(mysqli_error($conn), "course_code") !== false) {
				$message = "Course code already exists";
			} else {
				$message = "Failed to add course";
			}
		}
	}
}

if (isset($_POST["generate_otp_submit"])) {
	$course_id = $_POST["course_id"];
	$faculty_lat = $_POST["faculty_lat"];
	$faculty_long = $_POST["faculty_long"];

	if ($course_id == "") {
		$message = "Please select a course";
	} else {
		$check_course = mysqli_query($conn, "SELECT * FROM courses WHERE id = '$course_id' AND faculty_id = '$faculty_id'");
		if (mysqli_num_rows($check_course) == 0) {
			$message = "Invalid course selected";
		} else if ($faculty_lat == "" || $faculty_long == "") {
			$message = "Location not found. Please allow location and try again";
		} else {
			mysqli_query($conn, "UPDATE otp_table SET is_active = 0 WHERE course_id = '$course_id' AND is_active = 1");

			$otp_code = rand(100000, 999999);
			$create_time = date("Y-m-d H:i:s");
			$expire_time = date("Y-m-d H:i:s", strtotime("+2 minutes"));

			$insert_otp = "INSERT INTO otp_table (otp_code, created_by, course_id, create_time, expire_time, is_active, faculty_lat, faculty_long) VALUES ('$otp_code', '$faculty_id', '$course_id', '$create_time', '$expire_time', 1, '$faculty_lat', '$faculty_long')";
			$otp_result = mysqli_query($conn, $insert_otp);

			if ($otp_result) {
				$message = "OTP generated successfully";
				$new_otp_id = mysqli_insert_id($conn);
				$otp_query = mysqli_query($conn, "SELECT o.*, c.course_name, c.course_code FROM otp_table o JOIN courses c ON o.course_id = c.id WHERE o.id = '$new_otp_id'");
				$current_otp_data = mysqli_fetch_assoc($otp_query);
			} else {
				$message = "Failed to generate OTP";
			}
		}
	}
}

$my_courses = mysqli_query($conn, "SELECT * FROM courses WHERE faculty_id = '$faculty_id' ORDER BY id DESC");

if ($current_otp_data == null) {
	$latest_active_otp = mysqli_query($conn, "SELECT o.*, c.course_name, c.course_code FROM otp_table o JOIN courses c ON o.course_id = c.id WHERE o.created_by = '$faculty_id' AND o.is_active = 1 ORDER BY o.id DESC LIMIT 1");
	if (mysqli_num_rows($latest_active_otp) > 0) {
		$current_otp_data = mysqli_fetch_assoc($latest_active_otp);
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Faculty Panel</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
	<div class="container wide">
		<h1>Faculty Panel</h1>
		<p>Welcome <?php echo $_SESSION["faculty_name"]; ?></p>
		<p><a href="faculty_report.php">Attendance Report</a> | <a href="logout.php">Logout</a></p>

		<?php if ($message != "") { ?>
			<p class="message"><?php echo $message; ?></p>
		<?php } ?>

		<h2>Add Course</h2>
		<form method="POST">
			<label>Course Name</label>
			<input type="text" name="course_name" placeholder="Example: Web Programming" required>

			<label>Course Code</label>
			<input type="text" name="course_code" placeholder="Example: CSE 301" required>

			<button type="submit" name="add_course_submit" class="green-btn">Add Course</button>
		</form>

		<h2>Generate Course OTP</h2>
		<form method="POST">
			<label>Select Course</label>
			<select name="course_id" required>
				<option value="">Choose a course</option>
				<?php
				$course_dropdown = mysqli_query($conn, "SELECT * FROM courses WHERE faculty_id = '$faculty_id' ORDER BY id DESC");
				while ($course = mysqli_fetch_assoc($course_dropdown)) {
				?>
					<option value="<?php echo $course["id"]; ?>"><?php echo $course["course_code"]; ?> - <?php echo $course["course_name"]; ?></option>
				<?php } ?>
			</select>

			<input type="hidden" name="faculty_lat" id="faculty_lat">
			<input type="hidden" name="faculty_long" id="faculty_long">

			<button type="submit" name="generate_otp_submit" class="green-btn">Generate OTP</button>
		</form>

		<?php if ($current_otp_data != null) { ?>
			<h2>Current OTP</h2>
			<p>Course: <?php echo $current_otp_data["course_code"]; ?> - <?php echo $current_otp_data["course_name"]; ?></p>
			<p>OTP Code: <strong><?php echo $current_otp_data["otp_code"]; ?></strong></p>
			<p>Create Time: <?php echo $current_otp_data["create_time"]; ?></p>
			<p>Expire Time: <?php echo $current_otp_data["expire_time"]; ?></p>
		<?php } ?>

		<h2>My Courses</h2>
		<table>
			<tr>
				<th>Course Code</th>
				<th>Course Name</th>
				<th>Created At</th>
			</tr>
			<?php while ($row = mysqli_fetch_assoc($my_courses)) { ?>
				<tr>
					<td><?php echo $row["course_code"]; ?></td>
					<td><?php echo $row["course_name"]; ?></td>
					<td><?php echo $row["created_at"]; ?></td>
				</tr>
			<?php } ?>
		</table>
	</div>

	<script>
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function (position) {
				document.getElementById("faculty_lat").value = position.coords.latitude;
				document.getElementById("faculty_long").value = position.coords.longitude;
			});
		}
	</script>
</body>
</html>