<?php

$hostname     = "db27.webme.it"; // enter your hostname
$username     = "sitidi_711";  // enter your table username
$password     = "u28civFr";   // enter your password
$databasename = "sitidi_711";  // enter your database
// Create connection 
$conn = new mysqli($hostname, $username, $password,$databasename);
 // Check connection 
if ($conn->connect_error) { 
die("Unable to Connect database: " . $conn->connect_error);
 }
?>