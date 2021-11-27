<?php
//	Created by BC Holmes
//  This file providees a consistent URL for requesting the Zambia-configurable header image

if (!include ('../db_name.php')) {
    include ('../db_name.php');
}

if (defined('CON_HEADER_IMG') && CON_HEADER_IMG !== "") {
    header('Location: ' . CON_HEADER_IMG);
} else {
    header('Location: images/Z_illuminated.jpg');
}

?>
