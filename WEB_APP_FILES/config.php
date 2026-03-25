<?php

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'your_db_username'); 
define('DB_PASSWORD', 'your_db_password'); 
define('DB_NAME', 'crossword');

$conn = new mysqli('localhost', 'root', 'Mysql@1234', 'crossword');

// Check connection
if ($conn === false) {
    die("ERROR: Could not connect to the database. " . $conn->connect_error);
}
?>
