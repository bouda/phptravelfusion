Overview
--------
PHPTravelFusion is an API that allows easy access to the TravelFusion API.  The 
TravelFusion API allows XML-based queries for flight, hotel and car rental 
information and prices. 

The base class for this API is designed to work both from the PHP commandline 
and from web pages (please see examples provided).

Requirements
------------
- PHPv5 with the default XML-related extensions and the 'socket' extension 

Releases
--------
v0.1.02 - Not yet released
v0.1.01 - Major bug fixes (see Change Log)
v0.1    - Initial release, minimal but functional for only flight searches.

Change Log
----------
v0.1.02 * Added total round-trip pricing to getSimplePricing() if route is 
          priced in segments.
        * Added convertCurrency() method, which uses rates from the xavier.com 
          conversion API
        * Disabled search button if config file does not have any values.
        * Added getCheapestRoute() method, which returns a single cheapest
          route.
        * Added caching to the convertCurrency() method, so it is not called
          every time a price is needed, but rather only once per object
          instantiation.
                                                             
v0.1.01 * Fixed date translation (mm/dd vs dd/mm) issue
        * New method to return a simplified list of fares 
        * Fixed problem where search was returning one-way flights only
               
Files
-----
index.php - Example implementation of a web-based flight fare search engine 
            using the base class.

example-cli.php - Example implementation of a CLI (command line) client.

config.php - Where you put your login and other setup paramenters.  This should 
             be the only file you need to edit.

phptravelfusion.php - All methods required to implement the API

Web Installation
----------------
1) Unarchive the package and place the directory under any web server that 
   supports PHP (with sockets extensions).
2) Edit config.php and place TravelFusion usernames, IDs and passwords into that
   file.
3) Done!

Resources
---------
For more information on the TravelFusion API, visit:
    http://www.travelfusion.com/xmlspec/v2/ 

You can sign up for a free TravelFusion.com XML API account by visiting:
    http://www.travelfusion.com/info/products.html 