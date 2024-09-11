<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection details
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "admission_form_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle student admission form submission
if (isset($_POST['submit_admission'])) {
    // Get form data
    $admissionNumber = $_POST['admission_number'];
    $program = $_POST['program'];
    $department = $_POST['department'];
    $yearOfStudy = $_POST['year_of_study'];
    $gender = $_POST['gender'];
    $otherDetails = $_POST['other_details'];

    // Insert into students table
    $sql = "INSERT INTO students (user_id, admission_number, program, department, year_of_study, gender, other_details) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("issssss", $_SESSION['user_id'], $admissionNumber, $program, $department, $yearOfStudy, $gender, $otherDetails);

    if ($stmt->execute()) {
        echo "Admission details submitted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Get all students associated with the logged-in user
$sql = "SELECT * FROM students WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: rgba(52, 34, 128, 0.861);
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;

        }

        .container {
            max-width: 800px;
            width: 100%;
            padding: 20px;
            background-color: white;
            color: rgba(52, 34, 128, 0.861);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            border-radius: 20px;
        }

        h2,
        h3 {
            color: rgba(52, 34, 128, 0.861);
        }

        button {
            background-color: rgba(52, 34, 128, 0.861);
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            margin: 10px;
        }

        button#logoutButton {
            float: right;
            border-radius: 10px;
            box-shadow: rgba(0, 0, 1, 0.9);
        }

        button:hover {
            background-color: rgba(34, 22, 83, 0.861);
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        form label {
            font-weight: bold;
        }

        form input[type="text"],
        form input[type="number"],
        form textarea,
        form select {
            padding: 10px;
            border: 1px solid rgba(52, 34, 128, 0.861);
            border-radius: 5px;
            width: 100%;
        }

        form input[type="submit"] {
            background-color: rgba(52, 34, 128, 0.861);
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 20px;
        }

        form input[type="submit"]:hover {
            background-color: rgba(34, 22, 83, 0.861);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th,
        table td {
            padding: 10px;
            border: 1px solid rgba(52, 34, 128, 0.861);
            text-align: left;
            color: black;
        }

        table th {
            background-color: rgba(52, 34, 128, 0.861);
            color: white;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table tr:hover {
            background-color: #ddd;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Welcome, <?php echo $_SESSION['user_email']; ?></h2>
        <button id="logoutButton">Logout</button>

        <h3>Add New Student</h3>
        <form method="post" action="admission_dashboard.php">
            <label for="admission_number">Admission Number:</label>
            <input type="text" name="admission_number" id="admission_number" required><br><br>

            <label for="program">Program:</label>
            <input type="text" name="program" id="program" required><br><br>

            <label for="department">Department:</label>
            <input type="text" name="department" id="department" required><br><br>

            <label for="year_of_study">Year of Study:</label>
            <input type="number" name="year_of_study" id="year_of_study" required><br><br>

            <label for="gender">Gender:</label>
            <select name="gender" id="gender" required>
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select><br><br>

            <label for="other_details">Other Details:</label>
            <textarea name="other_details" id="other_details"></textarea><br><br>

            <input type="submit" value="Submit Admission" name="submit_admission">
        </form>

        <h3>Your Students</h3>
        <table>
            <thead>
                <tr>
                    <th>Admission Number</th>
                    <th>Program</th>
                    <th>Department</th>
                    <th>Year of Study</th>
                    <th>Gender</th>
                    <th>Other Details</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['admission_number'] . "</td>";
                        echo "<td>" . $row['program'] . "</td>";
                        echo "<td>" . $row['department'] . "</td>";
                        echo "<td>" . $row['year_of_study'] . "</td>";
                        echo "<td>" . $row['gender'] . "</td>";
                        echo "<td>" . $row['other_details'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No students found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <script>
        document.getElementById('logoutButton').addEventListener('click', function () {
            window.location.href = 'logout.php';
        });
    </script>
</body>

</html>