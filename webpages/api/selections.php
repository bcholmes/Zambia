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

function get_all_selections() {
    $db = mysqli_connect(DBHOSTNAME, DBUSERID, DBPASSWORD, DBDB);
    if (!$db) {
        throw new Exception("This database, it has woes.");
    }

    $query = <<<EOD
 SELECT sessionid, count(sessionid) as total, count(if(badgeid is not null, 1, 0)) as loggedIn, SUM(scheduled) as scheduled, SUM(highlighted) as highlighted, SUM(liked) as liked
   FROM `Leaderboard` 
  GROUP 
     BY sessionid;
 EOD;

    $stmt = mysqli_prepare($db, $query);
    if ($stmt !== false) {

        $selections = array();
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
			while ($dbobject = mysqli_fetch_object($result)) {
                $record = array();
                $stats = array();
                // skew the results so that rankings from actual, signed-in users matter more than folks in Russia who 
                // are trying out the app.
                $ratio = $dbobject->total == 0 ? 0 : $dbobject->loggedIn / $dbobject->total;
                $stats['rank'] = round(4 * $dbobject->liked * $ratio + ($dbobject->liked * (1-$ratio)) 
                    + 2 * $dbobject->highlighted * $ratio + ($dbobject->highlighted * (1 - $ratio)) 
                    + 4 * $dbobject->scheduled * $ratio + ($dbobject->scheduled * (1 - $ratio)));
                $stats['liked'] = $dbobject->liked * 1;
                $record['sessionId'] = "" . $dbobject->sessionid;
                $record['stats'] = $stats;

                unset($stats);
                $selections[] = $record;
                unset($record);
			}
        } else {
            throw new Exception("Query problem: ".mysqli_error($db));
        }

        mysqli_stmt_close($stmt);
        mysqli_close($db);
        return $selections;
    } else {
        throw new Exception("Query problem: ".mysqli_error($db));
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
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $json = get_all_selections();
        header('Content-type: application/json');
        echo json_encode($json);
    } catch (Exception $e) {
        http_response_code(500);
    }

} else {
	http_response_code(404);
}

?>