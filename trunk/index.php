<?
/*****************************
 * phptravelfusion
 * http://code.google.com/p/phptravelfusion/
 *
 * Authors: Rob Thompson (rob @ wayne (dot) edu)
 *
 * Example basic flight search engine
 *****************************/

require_once("phptravelfusion.php");
require_once("config.php");

$tfapi = new phpTravelFusion();
$tfapi->submitXML("<test></test>",$tfconfig);

?>
