<?php
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
      //echo $in;
      $out = '';
      socket_write($socket, $in, strlen ($in));
  
      $temp = "";
    while ($out = socket_read ($socket, 32768)) {
      $temp = $temp."$out";
      }   
    socket_close($socket);
    return $temp;      
    }

  function doStartRouting($from,$to,$tleave,$treturn,$mode='Plane',$tfconfig)
      {
      $xmldata="<CommandList>
                <StartRouting>
                    <XmlLoginId>$tfconfig[TFXMLLOGIN]</XmlLoginId>
                    <LoginId>$tfconfig[TFXMLLOGIN]</LoginId>
                    <Mode>$mode</Mode>
                    <Origin>
                        <Descriptor>$from</Descriptor>
                        <Type>airportcode</Type>
                        <Radius>10000</Radius>
                    </Origin>
                    <Destination>
                        <Descriptor>$to</Descriptor>
                        <Type>airportcode</Type>
                        <Radius>10000</Radius>
                    </Destination>
                    <OutwardDates>
                        <DateOfSearch>$tleave-10:00</DateOfSearch>
                    </OutwardDates>
                    <MaxChanges>1</MaxChanges>
                    <MaxHops>2</MaxHops>
                    <Timeout>40</Timeout>
                    <TravellerList>
                        <Traveller>
                            <Age>30</Age>
                        </Traveller>
                    </TravellerList>
                    <IncrementalResults>false</IncrementalResults>
                </StartRouting>
            </CommandList>";
      $xmlresults = $this->submitXML($xmldata,$tfconfig);      
      $results = substr($xmlresults,strpos($xmlresults,"<CommandList>"),10000000);           
      $p = xml_parser_create();
      xml_parse_into_struct($p, $results, $vals, $index);
      xml_parser_free($p);      
      $routeid = $index[ROUTINGID][0];
      $routeid = $vals[$routeid][value];
      return trim($routeid);
      }

  function doCheckRouteStatus($route,$tfconfig)
      {
      $vals = "";
      $results = "";
      $index = "";

      $xmldata="<CommandList>
                <CheckRouting>
                    <XmlLoginId>$tfconfig[TFXMLLOGIN]</XmlLoginId>
                    <LoginId>$tfconfig[TFXMLLOGIN]</LoginId>
                    <RoutingId>$route</RoutingId>
                </CheckRouting>
            </CommandList>";
      $xmlresults = $this->submitXML($xmldata,$tfconfig);      
      $results = substr($xmlresults,strpos($xmlresults,"<CommandList>"),10000000);           
           
      $p = xml_parser_create();
      xml_parse_into_struct($p, $results, $vals, $index);
      xml_parser_free($p);
            
      $total_index = $index[TOTALROUTERS][0];
      $total = $vals[$total_index][value];      
      $complete_index = $index[TOTALROUTERSCOMPLETE][0];
      $complete = $vals[$complete_index][value];
      
      $retval = $complete . ":" . $total;            
      return trim($retval);      
      }

  function getRoutes($route,$tfconfig)
      {
      $xmldata="<CommandList>
                <CheckRouting>
                    <XmlLoginId>$tfconfig[TFXMLLOGIN]</XmlLoginId>
                    <LoginId>$tfconfig[TFXMLLOGIN]</LoginId>
                    <RoutingId>$route</RoutingId>
                </CheckRouting>
            </CommandList>";
      
      $xmlresults = $this->submitXML($xmldata,$tfconfig);      
      $results = substr($xmlresults,strpos($xmlresults,"<CommandList>"),10000000);           
      
      return nl2br(htmlentities(trim(str_replace($tfconfig[TFXMLLOGIN],"XXXXXXX",$results))));      
      }                       
}

?>         
