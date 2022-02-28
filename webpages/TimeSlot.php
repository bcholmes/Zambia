<?php
// Copyright (c) 2022 BC Holmes. All rights reserved. See copyright document for more details.

global $title;
$title = "Time Slots";

require_once('StaffCommonCode.php'); // Checks for staff permission among other things

class Room {
    public $roomId;
    public $roomName;
    public $area;
    public $isOnline;
    public $displayOrder;
}

class TimeSlot {
    public $day;
    public $roomId;
    public $startTime;
    public $endTime;
    public $divisionName;

    function getColumnWidth() {
        return 1;
    }

    function getStartIndex() {
        return time_to_row_index($this->startTime);
    }

    function getEndIndex() {
        return time_to_row_index($this->endTime);
    }

    function getRowHeight() {
        return $this->getEndIndex() - $this->getStartIndex();
    }
}

function select_rooms() {
    $query = <<<EOD
    SELECT r.roomname, r.roomid, r.is_online, r.area
      FROM Rooms r
    WHERE r.is_scheduled = 1
      AND r.roomid in (select roomid from room_to_availability)
    ORDER BY display_order;
    EOD;
    if (!$result = mysqli_query_exit_on_error($query)) {
        exit;
    } else {
        $rooms = array();
        $column = 0;
        while ($row = mysqli_fetch_array($result)) {
            $rooms[] = array("name" => $row["roomname"], 
                "column" => $column, 
                "id" => $row["roomid"], 
                "is_online" => $row["is_online"] == 'Y' ? true : false,
                "area" => $row["area"]);
            $column++;
        }
        return $rooms;
    }
}

function select_time_slots() {
    $query = <<<EOD
    SELECT r.roomid, r2a.day, s.start_time, s.end_time, d.divisionid, d.divisionname
      FROM Rooms r,
           room_to_availability r2a,
           room_availability_schedule a,
           room_availability_slot s,
           Divisions d
    WHERE r.is_scheduled = 1
      AND r.roomid = r2a.roomid
      AND r2a.availability_id = a.id
      AND s.availability_schedule_id = a.id
      AND d.divisionid = s.divisionid
      ;
    EOD;
    if (!$result = mysqli_query_exit_on_error($query)) {
        exit;
    } else {
        $rooms = array();
        while ($row = mysqli_fetch_array($result)) {
            $slot = new TimeSlot();
            $slot->roomId = $row["roomid"];
            $slot->startTime = $row["start_time"];
            $slot->endTime = $row["end_time"];
            $slot->day = $row["day"];
            $slot->divisionName = $row["divisionname"];
            $rooms[] = $slot;
        }
        return $rooms;
    }
}

function filter_by_day($slots, $day) {
    $result = array();

    foreach ($slots as $slot) {
        if ($slot->day == $day) {
            $result[] = $slot;
        }
    }

    return $result;
}

function find_earliest_start_index($slots) {
    $result = 9999;
    foreach ($slots as $slot) {
        if ($slot->getStartIndex() < $result) {
            $result = $slot->getStartIndex();
        }
    }
    return $result;
}

function find_latest_end_index($slots) {
    $result = 0;
    foreach ($slots as $slot) {
        if ($slot->getEndIndex() > $result) {
            $result = $slot->getEndIndex();
        }
    }
    return $result;
}

function determine_con_start_date() {
    $timeZone = PHP_DEFAULT_TIMEZONE;
    $dateSrc = CON_START_DATIM;

    $dateTime = new DateTime($dateSrc, new DateTimeZone($timeZone));
    return $dateTime;
}

function find_slot_for_index_and_room($index, $room, $slots) {
    $result = null;

    foreach ($slots as $slot) {
        if ($slot->roomId == $room["id"] && $slot->getStartIndex() <= $index && $slot->getEndIndex() > $index) {
            $result = $slot;
            break;
        }
    }

    return $result;
}


function time_to_row_index($time, $rowSize = 15) {
    $hours = intval(substr($time, 0, 2));
    $minutes = intval(substr($time, 3, 2));
    return ($hours * 60 + $minutes) / $rowSize;
}

function render_table($rooms, $slots) {
    echo <<<EOD
    <table class="table table-sm table-bordered">
    <thead>
        <tr>
EOD;

    echo "<th>Time</th>";
    foreach ($rooms as $value) {
        echo "<th>" . $value['name'] 
            . ($value["is_online"] ? " <span class=\"small\"><br />(Online)</span>" : "") 
            . ($value["area"] ? ("<span class=\"small\"><br />" . number_format($value["area"]) . " sq ft</span>") : "") 
            . "</th>";
    }

echo <<<EOD
        </tr>
    </thead>
    <tbody>
EOD;

    $startDate = determine_con_start_date();
    for ($day = 0; $day < CON_NUM_DAYS; $day++) {
        echo "<tr><th colspan=\"" . (count($rooms) + 1) . "\">" . $startDate->format('D, d M') . "</th></tr>";

        $timeSlotsForDay = filter_by_day($slots, $day);
        if (count($timeSlotsForDay)) {
            $fromIndex = find_earliest_start_index($timeSlotsForDay);
            $toIndex = find_latest_end_index($timeSlotsForDay);

            for ($row = $fromIndex; $row <= $toIndex; $row++) {
                echo "<tr>";
                echo "<th class=\"bg-light small\">";
                if ($row % 4 == 0 || $row == $fromIndex) {
                    $hours = floor($row / 4);
                    if ($hours > 23) {
                        $hours -= 24;
                    }
                    $minutes = str_pad(($row % 4) * 15, 2, "0", STR_PAD_LEFT);

                    echo "$hours:$minutes";
                } else {
                    echo "&nbsp;";
                }
                echo "</th>";

                foreach ($rooms as $room) {
                    $slot = find_slot_for_index_and_room($row, $room, $timeSlotsForDay);
                    if ($slot) {
                        if ($slot->getStartIndex() == $row) {
                            echo "<td class=\"small\" rowspan=\"" . $slot->getRowHeight() . "\">" . $slot->divisionName . "</td>";
                        }
                    } else {
                        echo "<td class=\"bg-light small\">&nbsp;</td>";
                    }
                }
                echo "</tr>";
            }
        } else {
            echo "<tr><td class=\"bg-light\" colspan=\"" . (count($rooms) + 1) . "\">None</td></tr>";
        }

        $startDate->add(new DateInterval('P1D'));
    }

echo <<<EOD
    </tbody>
</table>
EOD;

}


$rooms = select_rooms();
$slots = select_time_slots();

staff_header($title, true);
?>

<div class="card">
    <div class="card-header">
        <h4>Time Slots</h4>
    </div>
    <div class="card-body">
        <p>The auto-scheduler uses the following time slots to help allocated panels:</p>

<?php

    echo render_table($rooms, $slots);

?>
    </div>
</div>

<?php
    staff_footer();
?>