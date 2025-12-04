<?php
//Database connection file
$servername = "localhost";
$username = "root";
$password = ""; 
$database = "sac_repository";

//create connection
$conn = new mysqli($servername, $username, $password, $database);

//check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}  
?>
    
