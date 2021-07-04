<?php
// Copyright (c) 2021 BC Holmes. All rights reserved. See copyright document for more details.
// This function provides support for mobile apps such as WisSched and FogGuide

require_once("jwt_functions.php");

if (!include ('../../db_name.php')) {
	include ('../../db_name.php');
}


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
function resolve_login($userid, $password) {
    $db = mysqli_connect(DBHOSTNAME, DBUSERID, DBPASSWORD, DBDB);
    if (!$db) {
        return false;
    } else {
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
                mysqli_close($db);
                if (password_verify($password, $dbobject->password)) {
                    return create_jwt_token($dbobject->badgeid, get_name($dbobject));
                } else {
                    return false;
                }
            } else {
                mysqli_close($db);
                return false;
            }
        } else {
            mysqli_close($db);
            return false;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $userid = '';
    $password = '';
    $app_id = '';
    if (isset($_POST['userid'])) {
        $userid = $_POST['userid'];
    }
    if (isset($_POST['password'])) {
        $password = $_POST['password'];
    }
    if (isset($_POST['app-id'])) {
        $app_id = $_POST['app-id'];
    }

    if ($app_id !== MOBILE_APP_ID) {
        http_response_code(401);
    } else {

        $loginResult = resolve_login($userid, $password);

        if ($loginResult) {
            header('Content-type: application/json');
            header("Authorization: Bearer ".$loginResult);
            $result = array( "success" => true, "message" => "I like you. I really like you." );
            echo json_encode($result);
                
        } else {
            $result = array( "success" => false, "message" => "You're not of the body!" );
            http_response_code(401);
            echo "\n\n";
            echo json_encode($result);
        }
    }
} else {
    http_response_code(404);
}

?>