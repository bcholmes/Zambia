<?php

if (!include ('../config/db_name.php')) {
	include ('../config/db_name.php');
}
require_once('../api/http_session_functions.php');
require_once('../api/db_support_functions.php');
require_once('../api/format_functions.php');
require_once('../api/http_session_functions.php');
require_once('../data_functions.php');
require_once('../name.php');
require_once("../api/participant_functions.php");

class ParticipationFormat {

    public $participantId;
    public $type;

    function asJson() {
        return array(
            "id" => $this->participantId,
            "type" => $this->type,
        );
    }

    static function asJsonArray($events) {
        $result = array();
        foreach ($events as $event) {
            $result[] = $event->asJson();
        }
        return $result;
    }
}

class WisSchedEventFormat {
    public $externalId;
    public $description;
    public $startTime;
    public $location;
    public $endTime;
    public $title;
    public $databaseId;
    public $track;
    public $type;
    public $timestamp;
    public $hashTag;
    public $participants;
    public $meetingLink;


    function asJson() {
        $result = array(
            "externalId" => $this->externalId,
            "description" => $this->description,
            "location" => $this->location,
            "title" => $this->title,
            "databaseId" => $this->databaseId,
            "track" => $this->track,
            "type" => $this->type, // really, division
            "timestamp" => $this->timestamp,
            "hashTag" => $this->hashTag,
            "startTime" => date_format($this->startTime, "c"),
            "endTime" => date_format($this->endTime, "c"),
            "participants" => ParticipationFormat::asJsonArray($this->participants),
        );
        if ($this->meetingLink) {
            $link = array("type" => "STREAMING", "href" => $this->meetingLink, "name" => "Streaming");
            $result['links'] = array($link);
        }
        return $result;
    }

    static function asJsonArray($events) {
        $result = array();
        foreach ($events as $event) {
            $result[] = $event->asJson();
        }
        return $result;
    }

    static function allScheduledSessions($db) {
        $CON_START_DATIM = CON_START_DATIM;

        $query = <<<EOD
        SELECT sch.roomid, sess.title, sch.starttime, t.trackname, sess.duration, sess.sessionid, sess.pubsno, sess.meetinglink,
            sess.progguiddesc, r.roomname, D.divisionname, sess.hashtag,
            DATE_FORMAT(ADDTIME('$CON_START_DATIM', sch.starttime),'%Y-%m-%d %H:%i:%S') AS formattedstarttime,
            DATE_FORMAT(ADDTIME('$CON_START_DATIM', ADDTIME(sch.starttime, sess.duration)),'%Y-%m-%d %H:%i:%S') AS formattedendtime
          FROM Sessions sess
          JOIN Schedule sch USING (sessionid)
          JOIN Tracks t USING (trackid)
          JOIN Rooms r ON (sch.roomid = r.roomid)
          JOIN Divisions D ON (D.divisionid = sess.divisionid)
         WHERE sess.pubstatusid = 2
         ORDER BY sch.starttime, r.display_order
EOD;

        $stmt = mysqli_prepare($db, $query);
        $result = array();
        $eventById = array();
        if (mysqli_stmt_execute($stmt)) {
            $resultSet = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_object($resultSet)) {
                $event = new WisSchedEventFormat();
                $event->databaseId = $row->sessionid;
                $event->title = $row->title;
                $event->description = $row->progguiddesc;
                $event->location = $row->roomname;
                $event->externalId = $row->pubsno;
                $event->track = $row->trackname;
                if ($row->formattedstarttime != null) {
                    $event->startTime = convert_database_date_to_date($row->formattedstarttime);
                }
                if ($row->formattedendtime != null) {
                    $event->endTime = convert_database_date_to_date($row->formattedendtime);
                }
                $event->type = $row->divisionname;
                $event->hashTag = $row->hashtag;
                $event->participants = array();
                $event->meetingLink = $row->meetinglink;
                $result[] = $event;
                $eventById[$event->databaseId] = $event;
            }
            mysqli_stmt_close($stmt);

            WisSchedEventFormat::allParticipations($db, $eventById);

            return $result;
        } else {
            throw new Exception("Query could not be executed: $query");
        }
    }


    private static function allParticipations($db, $eventById) {
        $query = <<<EOD
        SELECT
            POS.sessionid,
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
            POS.sessionid IN (SELECT sess.sessionid
                FROM Sessions sess
                JOIN Schedule sch USING (sessionid)
                WHERE sess.pubstatusid = 2)
EOD;

        $stmt = mysqli_prepare($db, $query);
        if (mysqli_stmt_execute($stmt)) {
            $resultSet = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_object($resultSet)) {
                $sessionId = $row->sessionid;
                $event = array_key_exists($sessionId, $eventById) ? $eventById[$sessionId] : null;
                if ($event) {
                    $participation = new ParticipationFormat();
                    $participation->participantId = $row->badgeid;
                    $participation->type = $row->moderator ? 'moderator' : 'participant';
                    $event->participants[] = $participation;
                }
            }
        } else {
            throw new Exception("Query could not be executed: $query");
        }
    }
}

class WisSchedParticipantFormat {

    public $badgeId;
    public $name;
    public $anonymous;
    public $bio;
    public $pronouns;
    public $photo;

    static function allScheduledParticipants($db) {
        $query = <<<EOD
        SELECT P.pubsname, CD.firstname, CD.lastname, CD.badgename, P.badgeid, P.anonymous, P.bio, P.pronouns, P.approvedphotofilename
          FROM Participants P
          JOIN CongoDump CD USING (badgeid)
         WHERE P.badgeid in (select distinct badgeid 
               from ParticipantOnSession P
               JOIN Sessions S USING (sessionid)
               JOIN Schedule SCH USING (sessionid)
               where S.pubstatusid = 2)
         ORDER BY P.badgeid;
EOD;

        $stmt = mysqli_prepare($db, $query);
        $result = array();
        if (mysqli_stmt_execute($stmt)) {
            $resultSet = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_object($resultSet)) {
                $participant = new WisSchedParticipantFormat();
                $participant->badgeId = $row->badgeid;
                $participant->name = PersonName::from($row);
                $participant->bio = $row->bio;
                $participant->pronouns = $row->pronouns;
                $participant->anonymous = $row->anonymous == 'Y' ? true : false;
                $participant->photo = $row->approvedphotofilename;
                $result[] = $participant;
            }
            mysqli_stmt_close($stmt);
            return $result;
        } else {
            throw new Exception("Query could not be executed: $query");
        }
    }

    function asJson() {
        $sortName = $this->name->lastName;
        if (strpos($this->name->getPubsName(), $sortName) === false) {
            $index = mb_strripos($this->name->getPubsName(), " ");
            if ($index === false) {
                $sortName = $this->name->getPubsName();
            } else {
                $sortName = substr($this->name->getPubsName(), $index+1);
            }
        }
        $result = array(
            "id" => $this->badgeId,
            "name" => array("display" => $this->name->getPubsName(), "sort" => $sortName),
            "anonymous" => $this->anonymous
        );
        if (!$this->anonymous) {
            if ($this->bio != null && $this->bio != "") {
                $result["bio"] = $this->bio;
            }
            if ($this->pronouns != null && $this->pronouns != "") {
                $result["pronouns"] = $this->pronouns;
            }
        }
        if ($this->photo) {
            $result["avatarId"] = PHOTO_PUBLIC_DIRECTORY . "/" . $this->photo;
        }
        return $result;
    }

    static function asJsonArray($events) {
        $result = array();
        foreach ($events as $event) {
            $result[] = $event->asJson();
        }
        return $result;
    }
}

start_session_if_necessary();
$db = connect_to_db(true);
date_default_timezone_set(DB_DEFAULT_TIMEZONE);
try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isProgrammingStaff())) {

        $participants = WisSchedParticipantFormat::asJsonArray(WisSchedParticipantFormat::allScheduledParticipants($db));
        $events = WisSchedEventFormat::asJsonArray(WisSchedEventFormat::allScheduledSessions($db));

        header('Content-type: application/json; charset=utf-8');
        $json_string = json_encode(array("event" => array("events" => $events, "title" => CON_NAME, "participants" => $participants)));
        echo $json_string;
    } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        http_response_code(401);
    } else {
        http_response_code(405);
    }
} finally {
    $db->close();
}

?>