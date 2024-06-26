<?php
$server_name = "localhost";
$username = "root";
$password = "";
$dbname = "php02";
$port = 3308;

$conn = new mysqli($server_name, $username, $password, $dbname, $port);

if($conn->connect_error){
    die("connection failed". $conn->connect_error);
}



?>