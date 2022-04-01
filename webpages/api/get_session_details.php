<?php

if (!include ('../../db_name.php')) {
	include ('../../db_name.php');
}
require_once('./http_session_functions.php');
require_once('./db_support_functions.php');
require_once('./format_functions.php');
require_once('./http_session_functions.php');
require_once('../data_functions.php');
require_once("./participant_functions.php");


function get_session_details($db, $sessionId) {
    $query = <<<EOD
    SELECT R.roomname, sess.title, sch.starttime, t.trackname, sess.duration, sess.progguiddesc, sess.participantlabel
    FROM Sessions sess
    JOIN Schedule sch USING (sessionid)
    JOIN Rooms R ON (R.roomid = sch.roomid)
    JOIN Tracks t USING (trackid)
   WHERE sess.pubstatusid = 2
     AND sess.sessionid = ?;
EOD;

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $sessionId);
    $result = array();
    if (mysqli_stmt_execute($stmt)) {
        $resultSet = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_object($resultSet)) {
            $result = [ 
                "description" => $row->progguiddesc,
                "title" => $row->title,
                "trackName" => $row->trackname,
                "participantLabel" => $row->participantlabel,
                "room" => [
                    "name" => $row->roomname
                ]
            ];
        }
        mysqli_stmt_close($stmt);
        return $result;
    } else {
        throw new DatabaseSqlException("Query could not be executed: $query");
    }
}


start_session_if_necessary();
$db = connect_to_db();
try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isLoggedIn()) {

        if (array_key_exists("sessionId", $_REQUEST)) {
            $sessionId = $_REQUEST["sessionId"];

            $session = get_session_details($db, $sessionId);
            if ($session != null) {
                $session['assignments'] = get_participant_assignments($db, $sessionId);

                header('Content-type: application/json; charset=utf-8');
                $json_string = json_encode($session);
                echo $json_string;
            } else {
                http_response_code(404);
            }
        } else {
            http_response_code(400);
        }
    } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        http_response_code(401);
    } else {
        http_response_code(405);
    }
} finally {
    $db->close();
}

?>