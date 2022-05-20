<?php

if (!include ('../../config/db_name.php')) {
    include ('../../config/db_name.php');
}

require_once('../http_session_functions.php');
require_once('../db_support_functions.php');
require_once('../../data_functions.php');
require_once("../jwt_functions.php");


function insert_mobile_device($db, $badgeid, $deviceId, $clientId, $os, $deviceModel) {
    $query = <<<EOD
    SELECT m.id
      FROM mobile_device m
     WHERE m.badgeid = ?
       AND m.client_id = ?
EOD;

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "ss", $badgeid, $clientId);
    $found = false;
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_object($result)) {
            $found = true;
        }
        mysqli_stmt_close($stmt);
    } else {
        throw new DatabaseSqlException("Query could not be executed: $query");
    }

    if ($found) {
        $query = <<<EOD
        UPDATE mobile_device m
        SET m.device_token = ?,
            m.device_model = ?
        WHERE m.badgeid = ?
          AND m.client_id = ?
EOD;
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $deviceId, $deviceModel, $badgeid, $clientId);
        if ($stmt->execute()) {
            mysqli_stmt_close($stmt);
        } else {
            throw new DatabaseSqlException("The Update could not be processed: $query --> " . mysqli_error($db));
        }
    } else {
        $query = <<<EOD
        INSERT INTO mobile_device (device_token, device_model, badgeid, client_id, os) 
          VALUES (?, ?, ?, ?, ?);
EOD;
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "sssss", $deviceId, $deviceModel, $badgeid, $clientId, $os);
        if ($stmt->execute()) {
            mysqli_stmt_close($stmt);
        } else {
            throw new DatabaseSqlException("The Update could not be processed: $query --> " . mysqli_error($db));
        }
    }
}


function is_input_data_valid($json) {
    return array_key_exists("deviceId", $json) && array_key_exists("clientId", $json) &&
        array_key_exists("os", $json) && array_key_exists("device", $json);
}


$db = connect_to_db();
try {
    $jwt = jwt_from_header();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && jwt_validate_token($jwt)) {

        $json_string = file_get_contents('php://input');
        $json = json_decode($json_string, true);

        $badgeid = jwt_extract_badgeid($jwt);
        if (is_input_data_valid($json)) {

            insert_mobile_device($db, $badgeid, $json["deviceId"], $json["clientId"], $json["os"], $json["device"]);
            http_response_code(201);
        } else {
            http_response_code(400);
        }
    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        http_response_code(401); // not authenticated
    } else {
        http_response_code(405); // method not allowed
    }
} finally {
    $db->close();
}
?>