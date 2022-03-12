<?php
// Copyright (c) 2022 BC Holmes. All rights reserved. See copyright document for more details.

global $title;
$title = "Time Slots";

require_once('StaffCommonCode.php'); // Checks for staff permission among other things
require_once('time_slot_functions.php');

class TimeSlot {
    public $day;
    public $roomId;
    public $startTime;
    public $endTime;
    public $divisionName;
    public $room;

    function getColumnWidth() {
        return $this->room ? $this->room->getColumnWidth() : 1;
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
    SELECT r.roomname, r.roomid, r.is_online, r.area, r.display_order, r.parent_room
      FROM Rooms r
    WHERE r.is_scheduled = 1
      AND r.roomid in (select roomid from room_to_availability)
    ORDER BY display_order;
    EOD;
    if (!$result = mysqli_query_exit_on_error($query)) {
        exit;
    } else {
        $temp = array();
        while ($row = mysqli_fetch_array($result)) {
            $room = new Room();
            $room->roomName = $row["roomname"];
            $room->roomId = $row["roomid"];
            $room->area = $row["area"];
            $room->isOnline = $row["is_online"] == 'Y' ? true : false;
            $room->displayOrder = $row["display_order"];
            $room->parentRoomId = $row["parent_room"];
            $room->children = array();
            $temp[$room->roomId] = $room;
        }

        return $temp;
    }
}

function select_time_slots($allRooms) {

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
        $slots = array();
        while ($row = mysqli_fetch_array($result)) {
            $slot = new TimeSlot();
            $slot->roomId = $row["roomid"];
            $slot->room = $allRooms[$row["roomid"]];
            $slot->startTime = $row["start_time"];
            $slot->endTime = $row["end_time"];
            $slot->day = $row["day"];
            $slot->divisionName = $row["divisionname"];
            $slots[] = $slot;
        }
        return $slots;
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

function find_slot_for_index_and_room($index, $column, $slots) {
    $result = null;

    foreach ($slots as $slot) {
        if ($slot->room == null) {
            // it's probably a room that has no panels
        } else if ($slot->room->columnNumber <= $column && ($slot->room->columnNumber + $slot->room->getColumnWidth()) > $column
                && $slot->getStartIndex() <= $index && $slot->getEndIndex() > $index) {
            $result = $slot;
            break;
        }
    }

    return $result;
}

function render_table_header_rows(&$headerRows, $rooms, $rowNumber) {
    $header = $headerRows[$rowNumber];
    if ($rowNumber == 0) {
        $header .= "<th rowSpan=\"" . count($headerRows) . "\">Time</th>";
    }
    foreach ($rooms as $value) {
        $width = $value->getColumnWidth() > 1 ? "colspan=\"{$value->getColumnWidth()}\"" : "";
        $height = count($headerRows) - $rowNumber - $value->getRowHeight() + 1;
        $rowHeight = $height == 1 ? "" : "rowspan=\"$height\"";
        $header .= "<th $rowHeight $width>" . $value->roomName 
            . ($value->isOnline ? " <span class=\"small\"><br />(Online)</span>" : "") 
            . ($value->area ? ("<span class=\"small\"><br />" . number_format($value->area) . " sq ft</span>") : "") 
            . "</th>";

        if ($value->children && count($value->children) > 0) {
            render_table_header_rows($headerRows, $value->children, $rowNumber + 1);
        }
    }
    $headerRows[$rowNumber] = $header;
}

function render_table_header($rooms) {
    $maxRows = 1;
    foreach ($rooms as $r) {
        $maxRows = max($r->getRowHeight(), $maxRows);
    }
    $headerRows = array();
    for ($i = 0; $i < $maxRows; $i++) {
        $headerRows[] = "";
    }
    render_table_header_rows($headerRows, $rooms, 0);
    foreach ($headerRows as $header) {
        echo "<tr>" . $header . "</tr>";
    }
}

function render_table($rooms, $slots) {
    echo <<<EOD
    <table class="table table-sm table-bordered">
    <thead>
EOD;

    $lastRoom = $rooms[count($rooms)-1];
    $maxColumns = $lastRoom->columnNumber + $lastRoom->getColumnWidth() - 1;
    render_table_header($rooms);

echo <<<EOD
    </thead>
    <tbody>
EOD;
    $startDate = determine_con_start_date();
    for ($day = 0; $day < CON_NUM_DAYS; $day++) {
        echo "<tr><th colspan=\"" . ($maxColumns + 2) . "\">" . $startDate->format('D, d M') . "</th></tr>";
        
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

                for ($column = 0; $column <= $maxColumns; $column++) {
                    $slot = find_slot_for_index_and_room($row, $column, $timeSlotsForDay);
                    if ($slot) {
                        if ($slot->getStartIndex() == $row && $slot->room->columnNumber == $column) {
                            echo "<td class=\"small\" rowspan=\"" . $slot->getRowHeight() . "\" colspan=\"" . $slot->getColumnWidth() . "\">" . $slot->divisionName . "</td>";
                        }
                    } else {
                        echo "<td class=\"bg-light small\">&nbsp;</td>";
                    }
                }
                echo "</tr>";
            }
        } else {
            echo "<tr><td class=\"bg-light\" colspan=\"" . ($maxColumns + 2) . "\">None</td></tr>";
        }
        $startDate->add(new DateInterval('P1D'));
    }
echo <<<EOD
    </tbody>
</table>
EOD;

}


$rooms = select_rooms();
$collatedRooms = Room::collateParentsAndAssignColumns($rooms);
$slots = select_time_slots($rooms);


staff_header($title, true);
?>

<div class="card">
    <div class="card-header">
        <h4>Time Slots</h4>
    </div>
    <div class="card-body">
        <p>The auto-scheduler uses the following time slots to help allocated panels:</p>

<?php

    echo render_table($collatedRooms, $slots);

?>
    </div>
</div>

<?php
    staff_footer();
?>