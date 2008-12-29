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

  function submitXML($xmldata,$tfconfig) {
    $socket = socket_create (AF_INET, SOCK_STREAM, 0);
    if ($socket < 0){
      print "Could not create socket";
      die();
      }
    if (!socket_connect ($socket, $tfconfig[TFADDRESS], $tfconfig[TFSERVICEPORT])) {}
      $length = strlen($xmldata);
      $in = "POST /$tfconfig[TFURL] HTTP/1.0\r\nContent-Type: text/xml\r\n" . "HOST:$tfconfig[TFADDRESS]\r\nContent-Length: $length\r\n\r\n$xmldata";
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
