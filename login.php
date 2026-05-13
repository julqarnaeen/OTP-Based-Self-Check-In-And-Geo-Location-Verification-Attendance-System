<?php
session_start();
include("connection.php");

$message = "";

if (isset($_POST["login_submit"])) {
	$role = $_POST["role"];
	$email = trim($_POST["email"]);
	$password = trim($_POST["password"]);

	if ($role == "student") {
		$sql = "SELECT * FROM students WHERE email = '$email' AND password = '$password'";
		$result = mysqli_query($conn, $sql);
		if (mysqli_num_rows($result) == 1) {
			$row = mysqli_fetch_assoc($result);
			$_SESSION["role"] = "student";
			$_SESSION["student_id"] = $row["student_id"];
			$_SESSION["student_name"] = $row["name"];
			header("Location: student.php");
			exit();
		} else {
			$message = "Invalid student login";
		}
	} else if ($role == "faculty") {
		$sql = "SELECT * FROM faculty WHERE email = '$email' AND password = '$password'";
		$result = mysqli_query($conn, $sql);
		if (mysqli_num_rows($result) == 1) {
			$row = mysqli_fetch_assoc($result);
			$_SESSION["role"] = "faculty";
			$_SESSION["faculty_id"] = $row["faculty_id"];
			$_SESSION["faculty_name"] = $row["name"];
			header("Location: faculty.php");
			exit();
		} else {
			$message = "Invalid faculty login";
		}
	}
}

if (isset($_POST["register_submit"])) {
	$student_id = trim($_POST["student_id"]);
	$name = trim($_POST["name"]);
	$reg_email = trim($_POST["reg_email"]);
	$reg_password = trim($_POST["reg_password"]);

	if ($student_id == "" || $name == "" || $reg_email == "" || $reg_password == "") {
		$message = "Please fill all student registration fields";
	} else {
		$insert_sql = "INSERT INTO students (student_id, name, email, password) VALUES ('$student_id', '$name', '$reg_email', '$reg_password')";
		$insert_result = mysqli_query($conn, $insert_sql);

		if ($insert_result) {
			$message = "Student registration successful. Now login.";
		} else {
			if (strpos(mysqli_error($conn), "student_id") !== false) {
				$message = "Student ID already exists";
			} else if (strpos(mysqli_error($conn), "email") !== false) {
				$message = "Email already exists";
			} else {
				$message = "Registration failed";
			}
		}
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Attendance Login</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
	<div class="container">
		<h1>Attendance Login</h1>

		<?php if ($message != "") { ?>
			<p class="message"><?php echo $message; ?></p>
		<?php } ?>

		<h3>Login As</h3>
		<form method="POST">
			<select name="role" required>
				<option value="student">Student</option>
				<option value="faculty">Faculty</option>
			</select>

			<label>Email</label>
			<input type="email" name="email" required>

			<label>Password</label>
			<input type="password" name="password" required>

			<button type="submit" name="login_submit" class="blue-btn">Login</button>
		</form>

		<h2>New Student Registration</h2>
		<form method="POST">
			<label>Student ID</label>
			<input type="text" name="student_id" required>

			<label>Full Name</label>
			<input type="text" name="name" required>

			<label>Email</label>
			<input type="email" name="reg_email" required>

			<label>Password</label>
			<input type="password" name="reg_password" required>

			<button type="submit" name="register_submit" class="green-btn">Register Student</button>
		</form>

		<p class="note">Default faculty (seeded): faculty@gub.com / 123456</p>
	</div>
</body>
</html>