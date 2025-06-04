<?php
// Database configuration
$Db_Server = "localhost";
$Db_User = "root";
$Db_Password = '@rem$Adrian123';
$Db_Name = "darasa";

$conn = new mysqli($Db_Server, $Db_User, $Db_Password, $Db_Name);

if ($conn) {
    echo "Connection to the database was successful.";
} else {
    echo "Connection to the database failed: " . $conn->connect_error;
}
?>