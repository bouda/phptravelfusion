<?php
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

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache'); 

// This piece of code actually does the search, if we have some search data passed.
if (strlen(trim($_GET[from])) > 0 && strlen(trim($_GET[to])) > 0 && strlen(trim($_GET[tleave])) > 0 && strlen(trim($_GET[treturn])) > 0)
    {    
    $tfapi = new phpTravelFusion();
    $routeid = $tfapi->doStartRouting($_GET[from],$_GET[to],$_GET[tleave],$_GET[treturn],'Plane',$tfconfig);
    echo $routeid;        
    exit();    
    }

if (strlen(trim($_GET[checkroute])) > 0)
    {    
    $tfapi = new phpTravelFusion();
    $status = $tfapi->doCheckRouteStatus($_GET[checkroute],$tfconfig);
    echo $status;        
    exit();    
    }

if (strlen(trim($_GET[getroutes])) > 0)
    {    
    $tfapi = new phpTravelFusion();
    $routes = $tfapi->getRoutes($_GET[getroutes],$tfconfig);
    //print_r($routes);
    $simplepricing = $tfapi->getSimplePricing($routes);
    
    echo "<table border=1>";
    for ($i=0;$i<sizeof($simplepricing);$i++)
        {
        echo "<tr>";
        echo "<td>";
        echo "<a href='". $simplepricing[$i][url] ."'>" . $simplepricing[$i][vendor] . "</a>";        
        echo "</td>";
        echo "<td>";
        for ($j=0;$j<sizeof($simplepricing[$i][route]);$j++)
            {
            echo "<table border=1>";
            echo "<tr>";
            echo "<td>";
            echo $simplepricing[$i][route][$j][routeid];
            echo "</td>";
            echo "<td>";
            echo $simplepricing[$i][route][$j][price] . " " . $simplepricing[$i][route][$j][currency];
            echo "</td>";
            echo "</tr>";
            echo "</table>";
            }
        echo "</td>";
        echo "</tr>";
        }
    echo "</table>";                
    exit();    
    }
echo "<head>";
echo "<title>PHPTravelFusion API Example #1</title>";
?>

<script type='text/javascript'>

// Define a bare-bones XMLHTTP request
var xmlhttp
if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
 try {
  xmlhttp = new XMLHttpRequest();
 } catch (e) {
  xmlhttp=false
 }
}

var xmlhttp2
if (!xmlhttp2 && typeof XMLHttpRequest!='undefined') {
 try {
  xmlhttp2 = new XMLHttpRequest();
 } catch (e) {
  xmlhttp2=false
 }
}

var xmlhttp3
if (!xmlhttp3 && typeof XMLHttpRequest!='undefined') {
 try {
  xmlhttp3 = new XMLHttpRequest();
 } catch (e) {
  xmlhttp3=false
 }
}

// What happens when the user clicks 'Search'
function doSearch()
    {
    // Clear out any existing status or search results
    document.getElementById('status').innerHTML="";
    document.getElementById('results').innerHTML="";
        
    var from = document.getElementById('from').value;
    var to = document.getElementById('to').value;
    var tleave = document.getElementById('tleave').value;
    var treturn = document.getElementById('treturn').value;    

    // Do some basic input validation
    if (from.length <1 || to.length <1 || tleave.length <1 || treturn.length <1)
        {
        alert('All fields are required')
        }
    else
        {
        document.getElementById('status').innerHTML = "Submitting Request..<br>";
        url="?from=" + escape(from) + "&to=" + escape(to) + "&tleave=" + escape(tleave) + "&treturn=" + escape(treturn);
        xmlhttp.open("GET",url,true);
        xmlhttp.onreadystatechange=function()
            {
            if (xmlhttp.readyState==4)
                {
                document.getElementById('status').innerHTML += "Routing ID: " + trim(xmlhttp.responseText) + "<br>";
                getStatus(trim(xmlhttp.responseText));                
                }
            };
        xmlhttp.setRequestHeader('Accept','message/x-jl-formresult');
        xmlhttp.send(null);
        return false;
        }
    }

function pausecomp(millis)
{
var date = new Date();
var curDate = null;

do { curDate = new Date(); }
while(curDate-date < millis);
} 
// This function gets the status of the route query from the class, to see if we are done yet.
function getStatus(route)
    {
    document.getElementById('status').innerHTML += "Checking Status of Route Query...";
    url="?checkroute=" + escape(route);
    xmlhttp2.open("GET",url,true);
    xmlhttp2.onreadystatechange=function()
        {
        if (xmlhttp2.readyState==4)
            {
            document.getElementById('status').innerHTML += xmlhttp2.responseText + "<br>";
            var curstat = trim(xmlhttp2.responseText).split(":");
            document.getElementById('done').value = trim(curstat[0]); 
            document.getElementById('todo').value = trim(curstat[1]);
            var done = curstat[0] / 1;
            var todo = curstat[1] / 1;            
            if (done < todo)
                {
                setTimeout("getStatus(trim(xmlhttp.responseText))",2250);
                }
            else
                {
                getRoutes(route);
                }                             
            //return trim(xmlhttp2.responseText);            
            }
        };
    xmlhttp2.setRequestHeader('Accept','message/x-jl-formresult');
    xmlhttp2.send(null);
    return trim(xmlhttp2.responseText);
    }

function getRoutes(route)
    {
    document.getElementById('status').innerHTML += "Requesting Result Routes...";
    url="?getroutes=" + escape(route);
    xmlhttp3.open("GET",url,true);
    xmlhttp3.onreadystatechange=function()
        {
        if (xmlhttp3.readyState==4)
            {
            document.getElementById('results').innerHTML = "<pre>" + xmlhttp3.responseText + "</pre>";
            document.getElementById('status').innerHTML += "Done.";            
            }
        };
    xmlhttp3.setRequestHeader('Accept','message/x-jl-formresult');
    xmlhttp3.send(null);
    return false;
    }


// A few text cleanup functions
function trim(str, chars) {
    return ltrim(rtrim(str, chars), chars);
}

function ltrim(str, chars) {
    chars = chars || "\\s";
    return str.replace(new RegExp("^[" + chars + "]+", "g"), "");
}

function rtrim(str, chars) {
    chars = chars || "\\s";
    return str.replace(new RegExp("[" + chars + "]+$", "g"), "");
}

</script>

<?php
echo "</head>";
echo "<body>";
echo "<h2>PHPTravelFusion Example #1</h2>";
echo "From Airport Code<br>";
echo "<input type='text' id='from' value='DTW'><br>"; 
echo "Destination Airport Code<br>";
echo "<input type='text' id='to' value='SCL'><br>";
echo "Leave Date (DD/MM/YY)<br>";
echo "<input type='text' id='tleave' value='". date("d/m/y",time() + 604800) ."'><br>";
echo "Return Date (DD/MM/YY)<br>";
echo "<input type='text' id='treturn' value='". date("d/m/y",time() + 1604800) ."'><br><br>";
echo "<button onClick='javascript:doSearch()'>Search Fares</button>";
echo "<input type='hidden' id='done'>"; 
echo "<input type='hidden' id='todo'>";
echo "<hr><b>Status</b><br>";
echo "<div id='status'></div>";
echo "<hr><b>Results</b><br>";
echo "<div id='results'></div>";
echo "</body>";

?>
