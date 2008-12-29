<?php
error_reporting(E_ERROR);
require_once("phptravelfusion.php");
require_once("config.php");

// Set the search criteria
$from_airport_code = "DTW";
$to_airport_code = "SCL";
$leave_date = "03/04/09-10:00";
$return_date = "04/04/09-10:00";

// Instantiate a new phpTravelFusion object
$tfapi = new phpTravelFusion();

// Get a routing ID for the search criteria
$routeid = $tfapi->doStartRouting($from_airport_code,$to_airport_code,$leave_date,$return_date,'Plane',$tfconfig);
echo "Received RoutingID: " . $routeid . "\n";

// Submit the search and wait until all routes are complete
do  {
    $status = $tfapi->doCheckRouteStatus($routeid,$tfconfig);
    $status_array = explode(":",$status);
    echo "Received " . $status_array[0] . " out of " . $status_array[1] . " total routes..\n";
    flush();
    sleep(2);
    } while ($status_array[0] < $status_array[1]);

// Get the finished route resulsts w/ pricing
$routes = $tfapi->getRoutes($routeid,$tfconfig);
echo sizeof($routes);
?>