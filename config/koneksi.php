<?php
$host = "localhost";
$username = "root";
$password = "";
$namaDB = "pojokbaca";
$mysqli = new mysqli($host, $username, $password, $namaDB);
// Check connection
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
} else {
    // echo "berhasil konek";
}
?>