<?php
// Start a session (this is important for login!)
session_start();

// Connect to the database
$servername = "localhost";
$username = "root"; //  Default username for XAMPP
$password = "";     //  Default password for XAMPP is usually blank
$dbname = "edulib_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    //  Prevent SQL Injection (VERY IMPORTANT!)
    $username = $conn->real_escape_string($username);
    $password = $conn->real_escape_string($password);

    $sql = "SELECT id, username, password FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Verify the password.  In a real application, you would use password_hash() and password_verify()
        if ($password == $row["password"]) { //  THIS IS NOT SECURE.  JUST FOR DEMO.
            //  Set session variables (like a "logged in" flag)
            $_SESSION["user_id"] = $row["id"];
            $_SESSION["username"] = $row["username"];
            $_SESSION["login_time"] = time();
            header("Location: index.html"); // Go to the home page
            exit();
        } else {
            $error_message = "Invalid username or password";
        }
    } else {
        $error_message = "Invalid username or password";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login to EDULIB</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main>
        <section>
            <h2>Login</h2>
            <?php if (isset($error_message)) { ?>
                <p style="color:red;"><?php echo $error_message; ?></p>
            <?php } ?>
            <form method="post" action="login.php">
                <label for="username">Username:</label>
                <input type="text" name="username" required><br><br>
                <label for="password">Password:</label>
                <input type="password" name="password" required><br><br>
                <input type="submit" value="Login">
            </form>
            <p>Don't have an account? <a href="register.php">Register</a></p>
        </section>
    </main>
</body>
</html>


