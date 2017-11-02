<?php

include $includes_path . 'config.php';
include $includes_path . 'general/init.php';

//Set exception handler
exceptions::sethandler();

//Connect to database
$db = new dbmysql($cfg['db_server'], $cfg['db_username'], $cfg['db_password'], $cfg['db_database']);

//Authentication
$auth = new auth();
$auth->handle();
$authinfo = $auth->getauthinfo();
$authinfo_admin = $auth->getauthinfo_admin();
$auth->login_required();


$unit_id = isset($_GET['unit']) ? intval($_GET['unit']) : 0;

$cond = array();
$cond = array_merge($cond, array("unit_id = {$unit_id}", "(fixtype = 2 OR fixtype = 3)"));
$cond = array_merge($cond, history::condition($_GET));

//Retrieve unit info
$unit_result = $db->table_query($db->tbl($tbl['unit']), $db->col(array('name', 'linecol')), $db->cond(array("user_id = {$authinfo['id']}", "id = {$unit_id}"), 'AND'));
if (!($unit_record = $db->record_fetch($unit_result))) {
	throw new Exception("Unit \"{$unit_id}\" not found / not authorised to view");
}

//Retrieve line color
if (!isset($cfg['linecol'][$unit_record['linecol']]['kml'])) {
	throw new Exception("Specified line color \"{$unit_record['linecol']}\" not valid");
}

$kmlcolor = $cfg['linecol'][$unit_record['linecol']]['kml'];

//Check date valid
if (!preg_match("/^\d{4}\-\d{1,2}\-\d{1,2}$/", $_GET['date'])) {
	throw new Exception("Date \"{$_GET['date']}\" not valid");
}

$date = $_GET['date'];

$coordinates_xml = '';

$positiondata = array();

$total_distance = 0;

$last_position = false;

$result = $db->table_query($db->tbl($tbl['position']), $db->col(array('lat', 'lon', 'datetime')), $db->cond($cond, 'AND'), $db->order(array(array('id', 'ASC'))));
while ($record = $db->record_fetch($result)) {

	if ($last_position != false) {
		$total_distance += appgeneral::distance_latlon($last_position['lat'], $last_position['lon'], $record['lat'], $record['lon']);
	}

	if (!isset($positiondata['start'])) {
		$positiondata['start'] = $record;
	} else {
		$positiondata['end'] = $record;
	}

	$coordinates_xml .= <<<EOXML
{$record['lon']},{$record['lat']}\n
EOXML;

	$last_position = $record;

}

$types = array(
	'start' => array(
		'name' => 'Start',
	),
	'end' => array(
		'name' => 'End',
	),
);

$placemarks = '';
foreach ($positiondata as $type => $item) {

	$item['datetime'] = appgeneral::utcdatetime_tolocal($item['datetime']);

	$placemarks .= <<<EOHTML
    <Placemark>
      <name>{$types[$type]['name']}: {$item['datetime']}</name>
      <description>
        <![CDATA[
          <b>Latitude:</b> {$item['lat']}<br />
          <b>Longitude:</b> {$item['lon']}<br />
        ]]>
      </description>
      <Point>
        <coordinates>{$item['lon']}, {$item['lat']}</coordinates>
      </Point>
    </Placemark>\n
EOHTML;

}


$name = $unit_record['name'];
$name = preg_replace("%[^a-zA-Z0-9\ ]%", '', $name);

$filename = $name;
$filename = strtolower(str_replace(' ', '_', $filename)).'.kml';

//application/vnd.google-earth.kmz
//header('Content-type: application/xml');
//header('Content-Type: application/xhtml+xml');

header('Content-type: application/vnd.google-earth.kml+xml');
header("Content-Disposition: attachment; filename=\"{$filename}\"");

$distancem = round($total_distance, 2);

$q = '?';
$xmlcontent = <<<EOXML
<{$q}xml version="1.0" encoding="UTF-8"{$q}>
<kml xmlns="http://earth.google.com/kml/2.1">
  <Document>
    <name>{$date}: {$name}</name>
    <Style id="linestyle1">
      <LineStyle>
        <color>{$kmlcolor}</color>
        <width>6</width>
      </LineStyle>
    </Style>
    <Placemark>
      <name>{$name}</name>
      <description>Distance Travelled: ~{$distancem} miles</description>
      <LineString>
        <extrude>1</extrude>
        <tessellate>1</tessellate>
        <altitudeMode>absolute</altitudeMode>
        <coordinates>
{$coordinates_xml}
        </coordinates>
      </LineString>
      <styleUrl>#linestyle1</styleUrl>
    </Placemark>
{$placemarks}
  </Document>
</kml>

EOXML;

$xmlcontent = str_replace("\r", '', $xmlcontent);
$xmlcontent = str_replace("\n", "\r\n", $xmlcontent);

echo $xmlcontent;

?>