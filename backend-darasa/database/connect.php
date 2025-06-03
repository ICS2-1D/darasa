<?php
define("DB_SERVER","localhost");
define("DB_USER","root");
define("DB_PASSWORD","@rem$Adrian123");
define("DB_NAME","darasa");
function connect() {
    $connection = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
    if (!$connection === false || $connection -> connect_error) {
        die("Connection failed: " . $connection -> connect_error);
    }
    return $connection;
    if(!$connection-> set_charset("utf8mb4")) {
        
    }
}

?>
