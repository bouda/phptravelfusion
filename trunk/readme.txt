Overview
--------
PHPTravelFusion is an API that allows easy access to the TravelFusion API.  The TravelFusion API allows XML-based queries for flight, hotel and car rental information and prices. 

The base class for this API is designed to work both from the PHP commandline and from web pages (please see examples provided).

Requirements
------------
- PHPv5 with the default XML-related extensions and the 'socket' extension 

Releases
--------
v0.1 - Initial release, minimal but functional for only flight searches.

Files
-----
index.php - Example implementation of a web-based flight fare search engine using the base class.
example-cli.php - Example implementation of a CLI (command line) client.
config.php - Where you put your login and other setup paramenters.  This should be the only file you need to edit.
phptravelfusion.php - All methods required to implement the API