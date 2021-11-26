<?php

if (!include ('../../../db_name.php')) {
	include ('../../../db_name.php');
}
require_once('../db_support_functions.php');


function find_select_dropdown_by_name($db, $table, $idcolumn, $keycolumnname, $key) {

    $query = <<<EOD
 SELECT $idcolumn as id FROM $table WHERE $keycolumnname = ?;
 EOD;

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "s", $key);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) == 1) {
            $dbobject = mysqli_fetch_object($result);
            mysqli_stmt_close($stmt);
            return $dbobject->id;
        } else {
            throw new DatabaseSqlException($query);
        }
    } else {
        throw new DatabaseSqlException($query);
    }
}

function write_session_to_database($db, $json) {

    $query = <<<EOD
 INSERT INTO Sessions 
        (title, progguiddesc, divisionid, statusid, kidscatid, trackid, typeid, pubstatusid, roomsetid, duration)
 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);
 EOD;

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "ssiiiiiiis", $json['title'], $json['progguiddesc'], $json['divisionid'],
        $json['statusid'], $json['kidscatid'], $json['trackid'], $json['typeid'], $json['pubstatusid'], $json['roomsetid'],
        $json['duration']);

    if ($stmt->execute()) {
        mysqli_stmt_close($stmt);
        return true;
    } else {
        throw new DatabaseSqlException($query);
    }     
}


function set_brainstorm_default_values($db, $json) {

    $json['statusid'] = find_select_dropdown_by_name($db, 'SessionStatuses', 'statusid', 'statusname', 'Brainstorm');
    $json['divisionid'] = find_select_dropdown_by_name($db, 'Divisions', 'divisionid', 'divisionname', 'Programming');
    $json['kidscatid'] = find_select_dropdown_by_name($db, 'KidsCategories', 'kidscatid', 'kidscatname', 'Welcome');
    $json['trackid'] = find_select_dropdown_by_name($db, 'Tracks', 'trackid', 'trackname', 'Unknown');
    $json['typeid'] = find_select_dropdown_by_name($db, 'Types', 'typeid', 'typename', 'I do not know');
    $json['pubstatusid'] = find_select_dropdown_by_name($db, 'PubStatuses', 'pubstatusid', 'pubstatusname', 'Public');
    $json['roomsetid'] = find_select_dropdown_by_name($db, 'RoomSets', 'roomsetid', 'roomsetname', 'Panel');
    $json['duration'] = DEFAULT_DURATION;
    return $json;
}



function send_confirmation_email($json) {

}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $body = file_get_contents('php://input');
    $json = json_decode($body, true);

    $db = connect_to_db();
    try {
        // validate input


        // write to database
        write_session_to_database($db, set_brainstorm_default_values($db, $json));

        // send email
        send_confirmation_email($json);

        http_response_code(201);
    } finally {
        $db->close();
    }
} else {
    http_response_code(405);
}

?>