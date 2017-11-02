CSL GPS Tracker - A web application for displaying the position of one of more devices on a map.


Install:

1. Copy folder "gps" to your server.
2. Open "includes/config.php" and edit $cfg['db_server'], $cfg['db_username'], $cfg['db_password'], $cfg['db_database'] to be your database details, and $cfg['site_url'] to be the URL to the site with a trailing slash e.g. "http://www.example.com/" or "http://www.example.com/cslvts/" (if it has been uploaded to a sub folder named "cslvts".
3. Open "includes/config.php" and set the "$cfg['googlemap_api_key']" variable to be your Google Maps API key (to obtain a key go to http://code.google.com/apis/maps/signup.html )
4. Import gpstracker.sql into your database

Note: database name = gpstracker



