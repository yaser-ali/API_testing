<?php
//Global variables for DB config.
//function DB_Config() {

GLOBAL $servername, $db, $user, $pass, $conn;


$servername = "localhost\SQLEXPRESS";
$db = "API";
$user = "user";
$pass = "Bedmaker$";

$conn = odbc_connect("Driver={SQL Server};Server=$servername;Database=$db;", $user, $pass)
or die ("Connection failed: " . $conn);


//}