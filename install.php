<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
global $db;

$sql = "INSERT INTO dpviz (`panzoom`,`horizontal`,`datetime`,`destination`,`scale`,`dynmembers`,`combineQueueRing`,`extOptional`) VALUES (1,0,1,1,1,0,0,0)";
$result = $db->query($sql);

if (DB::isError($result)) {
    die("Error inserting data: " . $result->getMessage());
}
