<?php
error_reporting(E_ERROR);
require_once("phptravelfusion.php");
require_once("config.php");

$tfapi = new phpTravelFusion();
$routeid = $tfapi->doStartRouting('DTW','SCL','03/04/09-10:00','04/04/09-10:00','Plane',$tfconfig);
echo "Received RoutingID: " . $routeid . "\n";

do
    {
    $status = $tfapi->doCheckRouteStatus($routeid,$tfconfig);
    $status_array = explode(":",$status);
    echo "Received " . $status_array[0] . " out of " . $status_array[1] . " total routes..\n";
    flush();
    sleep(2);
    } while ($status_array[0] < $status_array[1]);

$status = $tfapi->getRoutes($routeid,$tfconfig);
echo substr($status,0,100);
?>