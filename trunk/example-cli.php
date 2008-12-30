<?php
error_reporting(E_ERROR);
require_once("phptravelfusion.php");
require_once("config.php");

// Set the search criteria
$from_airport_code = "DTW";
$to_airport_code = "DEL";
$leave_date = "08/02/09";
$return_date = "21/02/09";

// Instantiate a new phpTravelFusion object
$tfapi = new phpTravelFusion();

echo "Route Airports: " . $from_airport_code . " -> " . $to_airport_code . "\n";
echo "Route Schedule: " . $leave_date . " -> " . $return_date . "\n";

// Get a routing ID for the search criteria
$routeid = $tfapi->doStartRouting($from_airport_code,$to_airport_code,$leave_date,$return_date,'Plane',$tfconfig);
echo "Received RoutingID: " . $routeid . "\n";

// Submit the search and wait until all routes are complete
do  {
    $status = $tfapi->doCheckRouteStatus($routeid,$tfconfig);
    $status_array = explode(":",$status);
    echo "Received " . $status_array[0] . " out of " . $status_array[1] . " total routes\n";
    flush();
    sleep(10);
    } while ($status_array[0] < $status_array[1]);

// Get the finished route resulsts w/ pricing
$routes = $tfapi->getRoutes($routeid,$tfconfig);
$simplepricing = $tfapi->getCheapestRoute($routes);

echo "Best Price: " . $simplepricing[0][vendor] . " $" . $simplepricing[0][route][0][price]  . " " . $simplepricing[0][route][0][currency] . "\n";
$totalmiles = $tfapi->calcCoordinateDistance($from_airport_code,$to_airport_code);
echo "Total miles: " . $totalmiles . "\n";
echo "Price/mile: $" . round($simplepricing[0][route][0][price] / $totalmiles,3);
?>
