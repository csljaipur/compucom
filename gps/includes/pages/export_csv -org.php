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
$cond = array_merge($cond, array("unit_id = {$unit_id}")); //, "(fixtype = 2 OR fixtype = 3)"
$cond = array_merge($cond, history::condition($_GET));

//Retrieve unit info
$unit_result = $db->table_query($db->tbl($tbl['unit']), $db->col(array('name')), $db->cond(array("user_id = {$authinfo['id']}", "id = {$unit_id}"), 'AND'));
if (!($unit_record = $db->record_fetch($unit_result))) {
	throw new Exception("Unit \"{$unit_id}\" not found / not authorised to view");
}



$csvarray = array();

$result_position = $db->table_query($db->tbl($tbl['position']), $db->col(array('datetime', 'datetime_received', 'lat', 'lon', 'alt', 'deg', 'speed_km', 'sattotal', 'fixtype')), $db->cond($cond, 'AND'), $db->order(array(array('id', 'ASC'))));
while ($record_position = $db->record_fetch($result_position)) {
	$csvarray[] = $record_position;
	//lib::prh($record_position);
}


$name = $unit_record['name'];
$name = preg_replace("%[^a-zA-Z0-9\ ]%", '', $name);

$filename = $name;
$filename = strtolower(str_replace(' ', '_', $filename)).'.csv';

header('Content-type: text/csv');
header("Content-Disposition: attachment; filename=\"{$filename}\"");

if (count($csvarray) > 0) {
	echo arraytocsv($csvarray);
}

//Array to csv format file
function arraytocsv($csvarray) {

	$csvdata = '';

	//Get title row
	if (count($csvarray > 0)) {
		foreach ($csvarray[0] as $title => $null) {
			$title = str_replace('"', '""', $title);
			$csvdata .= "\"{$title}\",";
		}

		$csvdata = rtrim($csvdata, ',');
		$csvdata .= "\n";
	}

	//Go through all the row data, adding it to csv file
	foreach ($csvarray as $rowdata) {

		foreach ($rowdata as $rowitem) {
			$rowitem = str_replace('"', '""', $rowitem);
			$csvdata .= "\"{$rowitem}\",";
		}

		$csvdata = rtrim($csvdata, ',');
		$csvdata .= "\n";

	}

	return $csvdata;

}

?>