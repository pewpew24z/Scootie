<?php
     $db_server = "localhost";
     $db_user = "root";
     $db_pass = "";
     $db_name = "scootiedb";

    //create connection
    $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);           

    //check connection
    if(!$conn){
        die("Connection failed" . $conn->connect_error);
    }

$conn->close();
?>
