<?php

include $includes_path . 'config.php';
include $includes_path . 'general/init.php';
//date_default_timezone_set('Asia/Calcutta');
//Set exception handler
exceptions::sethandler_js();

//Connect to database
$db = new dbmysql($cfg['db_server'], $cfg['db_username'], $cfg['db_password'], $cfg['db_database']);

//Authentication
$auth = new auth();
$auth->handle();
$authinfo = $auth->getauthinfo();
$authinfo_admin = $auth->getauthinfo_admin();
$auth->login_required();

$type = $_GET['t'];

if ($type == 'liveupdate') {

	$jsondata = new jsondata();
	$livepos_json = $jsondata->getlivepos_json();

	$jsondata = <<<EOJSON
{$livepos_json}
EOJSON;

} else if ($type == 'positionpopup') {

	$marker_id = intval($_GET['marker']);

	//Retrieve map data
	$position_result = $db->table_query($db->tbl(array($tbl['position'],$tbl['unit'])), $db->col(array('unit_id', 'datetime', 'lat', 'lon', 'alt', 'deg', 'speed_km', 'sattotal', 'fixtype','raw_input','name','imei')), $db->cond(array("pt_position.id = {$marker_id}","pt_position.unit_id=pt_unit.id", "(fixtype = 2 OR fixtype = 3)"), 'AND'));
	if (!($position_record = $db->record_fetch($position_result))) {
		throw new Exception("Marker id \"{$marker_id}\" not found.");
	}

	//Check unit id is allocated to user
	$unit_result = $db->table_query($db->tbl($tbl['unit']), '1', $db->cond(array("user_id = {$authinfo['id']}", "id = {$position_record['unit_id']}"), 'AND'), '', 0, 1);
	if ($db->record_count($unit_result) == 0) {
		throw new Exception("Unit id \"{$position_record['unit_id']}\" not allocated to requesting user");
	}

	$position = $position_record;

	$position['speed_mph'] = round(($position['speed_km'] * 0.621371192), 1);

	$pospopupjson = '';
	

	foreach ($position as $name => $value) {

		/* if ($name == 'datetime') {
			$value = appgeneral::utcdatetime_tolocal($value);
			
		} */

		$value_s = addslashes($value);
		$value_s = str_replace("\r", '', $value_s);
		$value_s = str_replace("\n", '\n', $value_s);
		$value_s = str_replace("\t", '\t', $value_s);

		$pospopupjson .= <<<EOJSON
"{$name}": "{$value_s}",\n
EOJSON;

	}
	
	$pospopupjson = rtrim($pospopupjson, ",\n");

	$pospopupjson = '{ ' . rtrim($pospopupjson, ",\n") . ' }';

	$jsondata = $pospopupjson;

} else if ($type == 'histcal') {

	$unit_id = intval($_GET['unit']);

	$year = intval($_GET['year']);
	$month = intval($_GET['month']);

	$calendar_json_name_js = "{$year}-{$month}";
	$calendar_json_js = history::calendar_json($unit_id, $year, $month);

	$jsondata = <<<EOJSON
	{
		"name": "{$calendar_json_name_js}",
		"data": {$calendar_json_js}
	}
EOJSON;

} else if ($type == 'markerdata') {

	$unit_id = intval($_GET['unit']);
	$zoomlevel = intval($_GET['zoomlevel']);

	//Check unit id is allocated to user
	$unit_result = $db->table_query($db->tbl($tbl['unit']), '1', $db->cond(array("user_id = {$authinfo['id']}", "id = {$unit_id}"), 'AND'), '', 0, 1);
	if ($db->record_count($unit_result) == 0) {
		throw new Exception("Unit id \"{$unit_id}\" not allocated to requesting user");
	}

	if (isset($_GET['date'])) {

		if (preg_match("/^(\d{4})\-(\d{1,2})\-(\d{1,2})$/", $_GET['date'], $dateparts)) {

			if (checkdate($dateparts[2], $dateparts[3], $dateparts[1])) {
				$currdate = $_GET['date'];
			} else {
				throw new Exception("Specified date \"{$_GET['date']}\" not valid");
			}

		} else {
			throw new Exception("Specified date \"{$_GET['date']}\" not formatted correctly");
		}

	} else {
		throw new Exception("Date not specified");
	}

	//Retrieve unit data
	$jsondata = new jsondata();

	$jsondata->retrievedata($unit_id, $currdate);

	$marker_json = $jsondata->getmarker_json($zoomlevel);

	$jsondata = <<<EOHTML
{
	"data": {$marker_json}
}
EOHTML;

} else {
	throw new Exception("Unknown request type \"{$type}\"");
}

//header("Content-Type: application/json; charset=ISO-8859-1");
echo $jsondata;

?>