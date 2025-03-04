<?php

function start_apicall() {

    global $vue_url;
    require_once ("settings.php");

    header("Access-Control-Allow-Origin: ".$vue_url);
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    // Connect to database
    global $db_host, $db_username, $db_password, $db_name, $db_port;
    return new mysqli($db_host, $db_username, $db_password, $db_name, $db_port);
}

function end_apicall($conn) {
    $conn->close();
}

function clean_inputs($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
