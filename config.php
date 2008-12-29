<?
/*****************************
 * phptravelfusion
 * http://code.google.com/p/phptravelfusion/
 * 
 * Authors: Rob Thompson (rob @ wayne (dot) edu)
 * 
 * Configuration File
 *****************************/

// Travelfusion XML API address, port and location
$tfaddress = "www.travelfusion.com";
$tfurl = 'Xml';
$tfservice_port = "80";

// User credentials
$tfuser = ""; //This is the ID used to login to the travelfusion.com web-based admin screen 
$tfpass = ""; //The english-ish password used to login to the web-based admin screen

// Note: Travelfusion does not provide this key to you.  Rather it is returned as a result of a
//       login request to their server using the above credentials.  However, it is the only key
//       that you need to use the XML services, so you only need to get this once, and place it below.
$tfxmllogin = "";  // 16 character XML key
?>         