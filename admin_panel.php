<?php
 session_start();

 // Check if user is logged in and is an admin (you'd have an 'admin' field in your users table)
 if (!isset($_SESSION["user_id"]) || !isset($_SESSION["username"])) {
     header("Location: login.php"); // Redirect to login if not logged in
     exit();
 }

 // Connect to the database
 $servername = "localhost";
 $username = "root";
 $password = "";
 $dbname = "edulib_db";

 $conn = new mysqli($servername, $username, $password, $dbname);

 if ($conn->connect_error) {
     die("Connection failed: " . $conn->connect_error);
 }

 // Create the books table if it doesn't exist
 $sql_create_books_table = "CREATE TABLE IF NOT EXISTS books (
     id INT AUTO_INCREMENT PRIMARY KEY,
     title VARCHAR(255) NOT NULL,
     author VARCHAR(255) NOT NULL,
     description TEXT,
     file_path VARCHAR(255) NOT NULL,
     upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
 )";

 if ($conn->query($sql_create_books_table) === FALSE) {
     echo "Error creating books table: " . $conn->error;
 }


 // Handle file upload
 if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["upload"])) {
     $title = $_POST["title"];
     $author = $_POST["author"];
     $description = $_POST["description"];

     $title = $conn->real_escape_string($title);
     $author = $conn->real_escape_string($author);
     $description = $conn->real_escape_string($description);

     $target_dir = "uploads/"; // Create a folder named "uploads" in your EDULIB directory
     if (!file_exists($target_dir)) {
         mkdir($target_dir, 0777, true); //create directory if not exists
     }
     $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
     $uploadOk = 1;
     $fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

     // Check if file already exists
     if (file_exists($target_file)) {
         echo "File already exists.";
         $uploadOk = 0;
     }

     // Check file size (limit to 5MB)
     if ($_FILES["fileToUpload"]["size"] > 5000000) {
         echo "File is too large.";
         $uploadOk = 0;
     }

     // Allow only certain file formats
     if($fileType != "pdf" && $fileType != "doc" && $fileType != "docx") {
         echo "Only PDF, DOC, and DOCX files are allowed.";
         $uploadOk = 0;
     }

     if ($uploadOk == 0) {
         echo "Sorry, your file was not uploaded.";
     } else {
         if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
             //  Store the file path in the database
             $sql_insert_book = "INSERT INTO books (title, author, description, file_path) VALUES ('$title', '$author', '$description', '$target_file')";
             if ($conn->query($sql_insert_book) === TRUE) {
                 echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded and details saved to database.";
             } else {
                 echo "Error saving file details: " . $conn->error;
             }

         } else {
             echo "Sorry, there was an error uploading your file.";
         }
     }
 }

 // Get all books from the database
 $sql_select_books = "SELECT * FROM books";
 $books_result = $conn->query($sql_select_books);

 $conn->close();
 ?>

 <!DOCTYPE html>
 <html lang="en">
 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Admin Panel - EDULIB</title>
     <link rel="stylesheet" href="style.css">
 </head>
 <body>
     <header>
         <h1>Admin Panel</h1>
         <nav>
             <ul>
                 <li><a href="index.html">Home</a></li>
                 <li><a href="logout.php">Logout</a></li>
             </ul>
         </nav>
     </header>

     <main>
         <section>
             <h2>Upload a Book</h2>
             <form method="post" action="admin_panel.php" enctype="multipart/form-data">
                 <label for="title">Book Title:</label>
                 <input type="text" name="title" required><br><br>
                 <label for="author">Author:</label>
                 <input type="text" name="author" required><br><br>
                 <label for="description">Description:</label><br>
                 <textarea name="description" rows="4" cols="50"></textarea><br><br>
                 <label for="fileToUpload">Select file:</label>
                 <input type="file" name="fileToUpload" required><br><br>
                 <input type="submit" value="Upload Book" name="upload">
             </form>
         </section>

         <section>
             <h2>Uploaded Books</h2>
             <?php
             if ($books_result->num_rows > 0) {
                 echo "<ul>";
                 while($row = $books_result->fetch_assoc()) {
                     echo "<li>";
                     echo "Title: " . $row["title"] . "<br>";
                     echo "Author: " . $row["author"] . "<br>";
                     echo "Description: " . $row["description"] . "<br>";
                     echo "<a href='" . $row["file_path"] . "' target='_blank'>Download</a>"; //  Make sure the path is correct and files are accessible
                     echo "</li>";
                 }
                 echo "</ul>";
             } else {
                 echo "No books uploaded yet.";
             }
             ?>
         </section>
     </main>
 </body>
 </html>


