<?php
// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "edulib_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create the users table if it doesn't exist.  We do this here so the registration page will create the table.
$sql_create_table = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql_create_table) === FALSE) {
    echo "Error creating table: " . $conn->error; //  Don't use echo in real app.
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $email = $_POST["email"];

     //  Prevent SQL Injection (VERY IMPORTANT!)
    $username = $conn->real_escape_string($username);
    $password = $conn->real_escape_string($password);
    $email = $conn->real_escape_string($email);

    //  In a real application, you would use password_hash() to store passwords securely!
    //$hashed_password = password_hash($password, PASSWORD_DEFAULT);  <--  Use this!

    // Check if username already exists
    $check_sql = "SELECT username FROM users WHERE username = '$username'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        $error_message = "Username already exists. Please choose a different one.";
    } else {

        $sql = "INSERT INTO users (username, password, email) VALUES ('$username', '$password', '$email')"; //  THIS IS NOT SECURE

        if ($conn->query($sql) === TRUE) {
            $success_message = "Registration successful!  Please <a href='login.php'>login</a>.";
        } else {
            $error_message = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for EDULIB</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main>
        <section>
            <h2>Register</h2>
            <?php if (isset($error_message)) { ?>
                <p style="color:red;"><?php echo $error_message; ?></p>
            <?php } ?>
             <?php if (isset($success_message)) { ?>
                <p style="color:green;"><?php echo $success_message; ?></p>
            <?php } ?>
            <form method="post" action="register.php">
                <label for="username">Username:</label>
                <input type="text" name="username" required><br><br>
                <label for="password">Password:</label>
                <input type="password" name="password" required><br><br>
                <label for="email">Email:</label>
                <input type="email" name="email"><br><br>
                <input type="submit" value="Register">
            </form>
            <p>Already have an account? <a href="login.php">Login</a></p>
        </section>
    </main>
</body>
</html>
