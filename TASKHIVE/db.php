<?php
if (basename($_SERVER['SCRIPT_FILENAME']) === 'db_connector.php') {
    die("Direct access to data infrastructure handshake socket denied.");
}

$conn = mysqli_connect("localhost", "root", "", "taskhive_db");

if (!$conn) {
    die("Database Connection Failure: " . mysqli_connect_error());
}
?>