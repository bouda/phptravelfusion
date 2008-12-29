<?
/*****************************
 * phptravelfusion
 * http://code.google.com/p/phptravelfusion/
 * 
 * Authors: Rob Thompson (rob @ wayne (dot) edu)
 * 
 * All classes for this API
 *****************************/

class phpTravelFusion {
  // Grab the config file with the login info, etc.
  require_once("config.php");

  function submitXML($xmldata) {
    $socket = socket_create (AF_INET, SOCK_STREAM, 0);
    if ($socket < 0){
      print "Could not create socket";
      die();
      }
    if (!@socket_connect ($socket, $address, $service_port)) {}
      $length = strlen($xmldata);
      $in = "POST /$url HTTP/1.0\r\nContent-Type: text/xml\r\n" . "HOST:$address\r\nContent-Length: $length\r\n\r\n$xmldata";
      $out = '';
      socket_write($socket, $in, strlen ($in));
  
    while ($out = socket_read ($socket, 32768)) {
      $temp = $temp."$out";
      }   
    socket_close($socket);
    return $temp;      
    }
}

?>         