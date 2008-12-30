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
  // variables available to this class
  
  // Store cached versions of the xavier.com API results, so we don't have to query
  // them every time. 
  var $currency_cache = "";
  var $currency_cache_date = 0;

  // Submits the passed $xmldata to the TravelFusion API and returns the response
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
  // Generates the XML required for the TravelFusion API to return a routing ID
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
                        <DateOfSearch>$tleave-00:01</DateOfSearch>
                    </OutwardDates>
                    <ReturnDates>
                        <DateOfSearch>$treturn-00:01</DateOfSearch>
                    </ReturnDates>
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

  // Checks the status of a routing request.
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

  // Returns an array of the routes when passed a routeid
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
      
      $return_results = $this->xml2array(str_replace($tfconfig[TFXMLLOGIN],"XXXXXXX",$results));      
      return $return_results;      
      }

  // Returns a simple array of prices/routeids from different sites given the results of a getRoutes query
  function getSimplePricing($routesxml)
      {
      $simplepricing = array();

      $sites = $routesxml[checkrouting][0][routerlist][0][router];
      for ($i=0;$i<sizeof($sites);$i++)
          {
          $simplepricing[$i][vendor] = $sites[$i][vendor][0][name];
          $simplepricing[$i][url] = $sites[$i][vendor][0][url];          
          $flightgroups = $sites[$i][grouplist][0][group];
          for ($j=0;$j<sizeof($flightgroups);$j++)
              {
              if (sizeof($flightgroups[$j]) > 0)
                  {
                  // One-way travel
                  //$simplepricing[$i][route][$j][routeid] = $flightgroups[$j][id]; 
                  //$outwardlist = $flightgroups[$j][outwardlist];
                  //$simplepricing[$i][route][$j][price] = $outwardlist[0][outward][0][price][0][amount];
                  //$simplepricing[$i][route][$j][currency] = $outwardlist[0][outward][0][price][0][currency];
                  
                  // Round Trip
                  $simplepricing[$i][route][$j][routeid] = $flightgroups[$j][id]; 
                  $outwardlist = $flightgroups[$j];
                  $simplepricing[$i][route][$j][price] = $flightgroups[$j][price][0][amount];
                  $simplepricing[$i][route][$j][currency] = $flightgroups[$j][price][0][currency];
                  
                  if (trim($simplepricing[$i][route][$j][price]) == "")
                      {
                      // This trip was possibly priced in multiple segments, let's add them up
                      $outbound_price = $outwardlist[outwardlist][0][outward][0][price][0][amount];
                      $return_price = $outwardlist[returnlist][0]["return"][0][price][0][amount];
                      $currency = $outwardlist[outwardlist][0][outward][0][price][0][currency];                      
                      $simplepricing[$i][route][$j][price] = $outbound_price + $return_price; 
                      $simplepricing[$i][route][$j][currency] = $currency;
                      }                                          
                                                            
                  }
              }
          }

      // Rewrite array to only include vendors that have pricing
      $simplepricing2 = array();
      $counter = 0;
      for ($i=0;$i<sizeof($simplepricing);$i++)
          {
          if (sizeof($simplepricing[$i][route]) > 0)
              {
              $simplepricing2[$counter] = $simplepricing[$i];
              $counter++;
              }          
          }
                
      return $simplepricing2;          
      }

  function getCheapestRoute($routesxml)
      {
      $cheapest = 999999999;
      //print_r($routesxml);
      $simplepricing = $this->getSimplePricing($routesxml);
      //print_r($simplepricing);      
      for ($i=0;$i<sizeof($simplepricing);$i++)
          {                  
          for ($j=0;$j<sizeof($simplepricing[$i][route]);$j++)
              {              
              if ($simplepricing[$i][route][$j][currency] != 'USD')
                  {                    
                  $simplepricing[$i][route][$j][price] = $this->convertCurrency($simplepricing[$i][route][$j][price],$simplepricing[$i][route][$j][currency],'USD');
                  $simplepricing[$i][route][$j][currency] = 'USD';
                  }
              //echo "current price: " . intval($simplepricing[$i][route][$j][price]) . "\n";
              //echo "current cheapest: " . intval($cheapest) . "\n";                                                    
              if ((intval($simplepricing[$i][route][$j][price]) < intval($cheapest)) && intval($simplepricing[$i][route][$j][price]) > 0)
                  {                  
                  //$cheapest_array = array();
                  $cheapest = $simplepricing[$i][route][$j][price];
                  $cheapestarr[0][url] = $simplepricing[$i][url]; 
                  $cheapestarr[0][vendor] = $simplepricing[$i][vendor];
                  $cheapestarr[0][route][0][routeid] = $simplepricing[$i][route][$j][routeid];
                  $cheapestarr[0][route][0][currency] = $simplepricing[$i][route][$j][currency];
                  $cheapestarr[0][route][0][price] = $simplepricing[$i][route][$j][price];
                  }                    
              }
          }            
      return $cheapestarr;      
      }

  // Get currency exchange rates from Xavier exchange rate API:
  // http://api.finance.xaviermedia.com/api/latest.xml
  function convertCurrency($amount,$fromcurrency,$tocurrency)
      {
      global $currency_cache, $currency_cache_date;
      // Only get new rates if we have not yet called the xavier API yet 
      // or if it has been a *very* long time since this object was 
      // instantiated.
      if ($currency_cache_date < (time() - 86400))
          {
          $rates = file_get_contents("http://api.finance.xaviermedia.com/api/latest.xml");
          $rates = substr($rates,strpos($rates,"<xavierresponse"),10000000);
          $currency_cache = $rates;
          $currency_cache_date = time();
          }
      else
          {
          $rates = $currency_cache;          
          }          
                 
      $raw_exchange_rates = $this->xml2array($rates);
      
      $rate_array = $raw_exchange_rates[exchange_rates][0][fx];
      for ($i=0;$i<sizeof($rate_array);$i++)
            {
            if ($fromcurrency == $rate_array[$i][currency_code])
                {
                $from_1_eur_rate = $rate_array[$i][rate];
                }
            if ($tocurrency == $rate_array[$i][currency_code])
                {
                $to_1_eur_rate = $rate_array[$i][rate];
                }
            }
      //echo "1 euro = " . $from_1_eur_rate . " $fromcurrency\n";
      //echo "1 euro = " . $to_1_eur_rate . " $tocurrency\n";
      $amount_in_euros = ((100/$from_1_eur_rate) * $amount) / 100;                                        
      //echo $amount . " " . $fromcurrency . " = " . $amount_in_euros  . " euro\n";
      $amount_in_to = $amount_in_euros * $to_1_eur_rate;          
      //echo $amount_in_euros . " euros = " . $amount_in_to  . " $tocurrency\n";
            
      return round($amount_in_to,2);
      }

  function calcCoordinateDistance($from_airport,$to_airport)
      {
      $airports = file("airports.txt");
      for ($i=0;$i<sizeof($airports);$i++)
              {
              $fields = explode(":",trim($airports[$i]));
              if ($fields[1] == $from_airport)
                  {
                  $from_airport_data = $fields;
                  }
              if ($fields[1] == $to_airport)
                  {
                  $to_airport_data = $fields;
                  }
              }

      //print_r($from_airport_data);

      $from_name = $from_airport_data[2];
      $to_name = $to_airport_data[2];
      $from_country = $from_airport_data[4];
      $to_country = $to_airport_data[4];
      
      echo $from_name . " -> " . $to_name . "\n";
      echo $from_country . " -> " . $to_country . "\n";
      
      $from_lat = "";
      if (trim($from_airport_data[8]) == "S")
              {
              $from_lat = "-";
              }
      $seconds = ($from_airport_data[7] / 60) * 0.01666;
      $minutes = ($from_airport_data[6] / 60);
      $decimal = round($minutes + $seconds,5);
      $from_lat .= $from_airport_data[5] + $decimal;

      $from_lon = "";
      if (trim($from_airport_data[12]) == "U")
              {
              $from_lon = "-";
              }
      $seconds = ($from_airport_data[11] / 60) * 0.01666;
      $minutes = ($from_airport_data[10] / 60);
      $decimal = round($minutes + $seconds,5);
      $from_lon .= $from_airport_data[9] + $decimal;

      //echo $from_lat . ", ". $from_lon . "\n";

      $to_lat = "";
      if (trim($to_airport_data[8]) == "S")
              {
              $to_lat = "-";
              }
      $seconds = ($to_airport_data[7] / 60) * 0.01666;
      $minutes = ($to_airport_data[6] / 60);
      $decimal = round($minutes + $seconds,5);
      $to_lat .= $to_airport_data[5] + $decimal;

      $to_lon = "";
      if (trim($to_airport_data[12]) == "U")
              {
              $to_lon = "-";
              }
      $seconds = ($to_airport_data[11] / 60) * 0.01666;
      $minutes = ($to_airport_data[10] / 60);
      $decimal = round($minutes + $seconds,5);
      $to_lon .= $to_airport_data[9] + $decimal;

      //echo $to_lat . ", ". $to_lon . "\n";
            
      $calc = new calcMiles($from_lat,$from_lon,$to_lat,$to_lon);
      $calc->calcMiles($from_lat, $from_lon, $to_lat, $to_lon, $units = "miles");

      //echo "  Distance: " . $calc->lastResultFormatted . "\n";
      return round($calc->lastResult,2);
            
      }

  // Converts XML into a PHP array      
  private function xml2array($data)
      {
      $p = xml_parser_create();
      xml_parser_set_option($p, XML_OPTION_SKIP_WHITE, 1);
      xml_parse_into_struct($p, $data, &$vals, &$index);
      xml_parser_free($p);
      $tree = array();
      $i = 0;
      $tree = $this->GetChildren($vals, $i);
      return $tree;
      }
      
   // A recursive function called by xml2array
   private function GetChildren($vals, &$i)
      {
      $children = array();
      if ($vals[$i]['value']) array_push($children, $vals[$i]['value']);      
      $prevtag = "";
      while (++$i < count($vals))
          {
          switch ($vals[$i]['type'])
              {
              case 'cdata':
                array_push($children, $vals[$i]['value']);
                break;
              case 'complete':
                $children[ strtolower($vals[$i]['tag']) ] = $vals[$i]['value'];
                break;              
              case 'open':
                $j++;
                if ($prevtag <> $vals[$i]['tag']) 
                    {
                    $j = 0;
                    $prevtag = $vals[$i]['tag'];
                    }
                $children[ strtolower($vals[$i]['tag']) ][$j] = $this->GetChildren($vals,$i);
                break;              
              case 'close':
                return $children;
              }
          }
      }                             
}

class calcMiles {
    var $Lat1 = NULL;
    var $Lon1 = NULL;
    var $Lat2 = NULL;
    var $Lon2 = NULL;

    var $units = NULL;
    var $lastResult = NULL;
    var $lastResultFormatted = NULL;

    function calcMiles ($Lat1, $Lon1, $Lat2, $Lon2, $units = "miles"){
        $Difference = 3958.75 * acos(  sin($Lat1/57.2958) * sin($Lat2/57.2958) + cos($Lat1/57.2958) * cos($Lat2/57.2958) * cos($Lon2/57.2958 - $Lon1/57.2958));

        switch ($units){
            default:
            case "":
            case "miles":
                $this->units = "Miles";
                $Difference = $Difference * 1;
                break;
            case "yards":
                $this->units = "Yards";
                $Difference = $Difference * 1760;
                break;
            case "parsec":
                $this->units = "Parsecs";
                $Difference = $Difference * 0.0000000000000521553443;
                break;
            case "nauticalmiles":
                $this->units = "Nautical Miles";
                $Difference = $Difference * 0.868974087;
                break;
            case "nanometer":
                $this->units = "Nanometers";
                $Difference = $Difference * 1609344000000;
                break;
            case "millimeter":
                $this->units = "Millimeters";
                $Difference = $Difference * 1609344;
                break;
            case "mil":
                $this->units = "Mils";
                $Difference = $Difference * 63360000;
                break;
            case "micrometer":
                $this->units = "Micrometers";
                $Difference = $Difference * 1609344000;
                break;
            case "meter":
                $this->units = "Meters";
                $Difference = $Difference * 1609.344;
                break;
            case "lightyear":
                $this->units = "Light Years";
                $Difference = $Difference * 0.0000000000001701114356;
                break;
            case "kilometer":
                $this->units = "Kilometers";
                $Difference = $Difference * 1.609344;
                break;
            case "inches":
                $this->units = "Inches";
                $Difference = $Difference * 63360;
                break;
            case "hectometer":
                $this->units = "Hectometers";
                $Difference = $Difference * 16.09344;
                break;
            case "furlong":
                $this->units = "Furlongs";
                $Difference = $Difference * 8;
                break;
            case "feet":
                $this->units = "Feet";
                $Difference = $Difference * 5280;
                break;
            case "dekameter":
                $this->units = "Dekameters";
                $Difference = $Difference * 160.9344;
                break;
            case "centimeter":
                $this->units = "Centimeters";
                $Difference = $Difference * 160934.4;
                break;
        }

        $this->lastResult = $Difference;
        $this->lastResultFormatted = $this->lastResult . " " . $this->units;
        return $Difference;
    }
}

?>
