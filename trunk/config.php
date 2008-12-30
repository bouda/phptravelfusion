<?php
/*****************************
 * phptravelfusion
 * http://code.google.com/p/phptravelfusion/
 * 
 * Authors: Rob Thompson (rob @ wayne (dot) edu)
 * 
 * Configuration File
 *****************************/

// Initialize a new array
$tfconfig = array();

// Travelfusion XML API address, port and location
$tfconfig[TFADDRESS] = "www.travelfusion.com";
$tfconfig[TFURL] = 'Xml';
$tfconfig[TFSERVICEPORT] = "80";

// User credentials
$tfconfig[TFUSER] = ""; //This is the ID used to login to the travelfusion.com web-based admin screen 
$tfconfig[TFPASS] = ""; //The english-ish password used to login to the web-based admin screen

// Note: Travelfusion does not provide this key to you.  Rather it is returned as a result of a
//       login request to their server using the above credentials.  However, it is the only key
//       that you need to use the XML services, so you only need to get this once, and place it below.
$tfconfig[TFXMLLOGIN] = "";  // 16 character XML key
?>