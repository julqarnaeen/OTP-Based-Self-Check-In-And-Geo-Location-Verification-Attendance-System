<?php
session_start();
include("connection.php");

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "faculty") {
	header("Location: login.php");
	exit();
}

$faculty_id = $_SESSION["faculty_id"];
$course_id = "";

if (isset($_POST["report_submit"])) {
	$course_id = $_POST["course_id"];
}

$courses = mysqli_query($conn, "SELECT * FROM courses WHERE faculty_id = '$faculty_id' ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
	<title>Attendance Report</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
	<div class="container wide">
		<h1>Attendance Report</h1>
		<p><a href="faculty.php">Back to Faculty Panel</a> | <a href="logout.php">Logout</a></p>

		<form method="POST">
			<label>Select Course</label>
			<select name="course_id" required>
				<option value="">Choose a course</option>
				<?php while ($row = mysqli_fetch_assoc($courses)) { ?>
					<option value="<?php echo $row["id"]; ?>" <?php if ($course_id == $row["id"]) { echo "selected"; } ?>>
						<?php echo $row["course_code"]; ?> - <?php echo $row["course_name"]; ?>
					</option>
				<?php } ?>
			</select>
			<button type="submit" name="report_submit" class="green-btn">Show Report</button>
		</form>

		<?php if ($course_id != "") { ?>
			<?php
			$otp_result = mysqli_query($conn, "SELECT * FROM otp_table WHERE course_id = '$course_id' ORDER BY create_time ASC");
			$otp_ids = array();
			$otp_dates = array();
			while ($o = mysqli_fetch_assoc($otp_result)) {
				$otp_ids[] = $o["id"];
				$otp_dates[] = $o["create_time"];
			}

			$students_result = mysqli_query(
				$conn,
				"SELECT DISTINCT s.student_id, s.name FROM attendance a JOIN otp_table o ON a.otp_id = o.id JOIN students s ON a.student_id = s.student_id WHERE o.course_id = '$course_id' ORDER BY s.student_id"
			);
			?>

			<button onclick="window.print()" class="blue-btn">Print</button>

			<table>
				<tr>
					<th>Student ID</th>
					<th>Name</th>
					<?php for ($i = 0; $i < count($otp_dates); $i++) { ?>
						<th><?php echo date("Y-m-d", strtotime($otp_dates[$i])); ?></th>
					<?php } ?>
					<th>Total Percentage</th>
				</tr>

				<?php while ($s = mysqli_fetch_assoc($students_result)) { ?>
					<?php
					$present_count = 0;
					$total_classes = count($otp_ids);
					?>
					<tr>
						<td><?php echo $s["student_id"]; ?></td>
						<td><?php echo $s["name"]; ?></td>
						<?php for ($j = 0; $j < count($otp_ids); $j++) { ?>
							<?php
							$oid = $otp_ids[$j];
							$check = mysqli_query($conn, "SELECT * FROM attendance WHERE student_id = '" . $s["student_id"] . "' AND otp_id = '$oid'");
							if (mysqli_num_rows($check) > 0) {
								echo "<td>P</td>";
								$present_count++;
							} else {
								echo "<td>A</td>";
							}
							?>
						<?php } ?>
						<?php
						if ($total_classes > 0) {
							$percent = ($present_count / $total_classes) * 100;
						} else {
							$percent = 0;
						}
						?>
						<td><?php echo round($percent, 2); ?>%</td>
					</tr>
				<?php } ?>
			</table>
		<?php } ?>
	</div>
</body>
</html>