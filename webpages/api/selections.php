<?php
// Copyright (c) 2021 BC Holmes. All rights reserved. See copyright document for more details.
// This function provides support for mobile apps such as WisSched and FogGuide

if (!include ('../../db_name.php')) {
	include ('../../db_name.php');
}

require_once("jwt_functions.php");

function add_all_selections_to_database($json, $badgeid) {
    $deviceId = $json->deviceId;
    $selections = $json->selections;

    if ($deviceId != null && $selections != null) {

        $db = mysqli_connect(DBHOSTNAME, DBUSERID, DBPASSWORD, DBDB);
        if (!$db) {
            throw new Exception("This database, it has woes.");
        }
    
        $query = <<<EOD
 INSERT 
   INTO `Leaderboard` 
        (sessionid, deviceid, badgeid, scheduled, highlighted, liked)
 SELECT S.sessionid, ?, ?, ?, ?, ? 
   FROM 
        `Sessions` S
  WHERE 
        S.sessionid = ?
     ON DUPLICATE KEY
 UPDATE
        badgeid = ?, scheduled = ?, highlighted = ?, liked = ?;
 EOD;

        $stmt = mysqli_prepare($db, $query);
        if ($stmt !== false) {

            foreach ($selections as $s) {

                if (isset($s->sessionId)) {
                    $sessionid = $s->sessionId;
                    $highlighted = isset($s->highlighted) ? ($s->highlighted == 'true' ? 1 : 0)  : 0;
                    $liked = isset($s->liked) ? ($s->liked == 'true' ? 1 : 0) : 0;
                    $scheduled = isset($s->scheduled) ? ($s->scheduled == 'true' ? 1 : 0) : 0;

                    mysqli_stmt_bind_param($stmt, "ssiiiiiiii", $deviceId, $badgeid, $scheduled, $highlighted, $liked, $sessionid, $badgeid, $scheduled, $highlighted, $liked);
                    if (mysqli_stmt_execute($stmt)) {
                        $count = mysqli_affected_rows($db);
                    } else {
                        throw new Exception("Query problem: ".mysqli_error($db));
                    }
                } else {
                    throw new InvalidArgumentException("sessionid is missing");
                }
            }

            mysqli_stmt_close($stmt);
        } else {
            throw new Exception("Query problem: ".mysqli_error($db));
            
        }
        mysqli_close($db);
        return true;
    } else {
        throw new InvalidArgumentException("Invalid input.");
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	$jwt = jwt_from_header();

    // the mobile user might not have logged in, but record their 
    // selections anyway.
	if (jwt_validate_token($jwt)) {

        try {
            $json_string = file_get_contents('php://input');
            if ($json_string === false) {
                http_response_code(403);
            } else {
                $json = json_decode($json_string);
                if ($json === false) {
                    throw new InvalidArgumentException("Invalid JSON");
                } else {
                    add_all_selections_to_database($json, jwt_extract_badgeid($jwt));
                    http_response_code(204);
                }
            }
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
        } catch (Exception $e) {
            http_response_code(500);
        }
	} else {
		http_response_code(401);
	}
} else {
	http_response_code(404);
}

?>