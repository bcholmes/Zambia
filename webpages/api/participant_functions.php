<?php

define('__ROOT__', dirname(dirname(__FILE__))); 
require_once(__ROOT__.'/name.php');

function get_name($dbobject) {
    $name = PersonName::from($dbobject);
    return $name->getBadgeName();
}

function get_participant_assignments($db, $sessionId, $showAnonymousNames = true) {
    $query = <<<EOD
    SELECT
        POS.badgeid,
        COALESCE(POS.moderator, 0) AS moderator,
        P.pubsname,
        CD.badgename,
        CD.firstname,
        CD.lastname,
        P.anonymous
    FROM
                  ParticipantOnSession POS
             JOIN Participants P ON P.badgeid = POS.badgeid
             JOIN CongoDump CD ON CD.badgeid = POS.badgeid
    WHERE
        POS.sessionid=?;
EOD;

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $sessionId);
    $assignments = [];
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_object($result)) {
            $name = PersonName::from($row);
            $anonymous = $row->anonymous === 'Y' ? true : false;
            $assignments[] = [ 
                "badgeid" => $row->badgeid,
                "moderator" => $row->moderator ? true : false,
                "name" => ((!$anonymous || $showAnonymousNames) ? $name->getPubsName() : "Anonymous")
            ];
        }
        mysqli_stmt_close($stmt);
        return $assignments;
    } else {
        throw new DatabaseSqlException("Query could not be executed: $query");
    }
}

?>