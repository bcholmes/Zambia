<?php
// Copyright (c) 2021 BC Holmes. All rights reserved. See copyright document for more details.
// This function provides support for mobile apps such as WisSched and FogGuide

if (!include ('../config/db_name.php')) {
	include ('../config/db_name.php');
}

require_once('./db_support_functions.php');
require_once("jwt_functions.php");

function get_name($dbobject) {
    if (isset($dbobject->badgename) && $dbobject->badgename !== '') {
        return $dbobject->badgename;
    } else {
        return $dbobject->firstname." ".$dbobject->lastname;
    }
}

// the standard db_functions file makes certain assumptions about the end-client being
// HTML (to which it renders error pages), and those assumptions aren't good 
// in a REST/JSON world.
function resolve_login($db, $userid, $password) {
    $query = <<<EOD
 SELECT 
        P.password, P.data_retention, P.badgeid, C.firstname, C.lastname, C.badgename 
   FROM 
        Participants P 
   JOIN CongoDump C USING (badgeid)
  WHERE 
         P.badgeid = ?
      OR 
         C.email = ?;
 EOD;

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "ss", $userid, $userid);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) == 1) {
            $dbobject = mysqli_fetch_object($result);
            mysqli_stmt_close($stmt);
            if (password_verify($password, $dbobject->password)) {
                return jwt_create_token($dbobject->badgeid, get_name($dbobject));
            } else {
                return false;
            }
        } else {
            throw new DatabaseSqlException("Invalid number of userids");
        }
    } else {
        throw new DatabaseSqlException("Query could not be executed");
    }
}

function is_input_data_valid($json) {
    return array_key_exists("userid", $json) && array_key_exists("password", $json);
}
$db = connect_to_db();
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $json_string = file_get_contents('php://input');
        $json = json_decode($json_string, true);

        // we want to avoid letting this API be an easy way for
        // hackers to test userid/password combinations, so we
        // will reject all requests that don't have an app id.
        if (is_input_data_valid($json)) {
            $loginResult = resolve_login($db, $json['userid'], $json['password']);
            if ($loginResult) {
                header('Content-type: application/json');
                header("Authorization: Bearer ".$loginResult);
                $result = array( "success" => true, "message" => "I like you. I really like you." );
                echo json_encode($result);
                    
            } else {
                header('Content-type: application/json');
                $result = array( "success" => false, "message" => "You're not of the body!" );
                http_response_code(401);
                echo "\n\n";
                echo json_encode($result);
            }
        } else {
            http_response_code(400);
        }
    } else {
        http_response_code(404);
    }
} finally {
    $db->close();
}
?>