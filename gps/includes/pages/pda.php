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



if (isset($_GET['unit'])) {
	$currtrackerunit = intval($_GET['unit']);
} else {
	$currtrackerunit = 0;
}

/*
//Retrieve unit info
$unit_result = $db->table_query($db->tbl($tbl['unit']), $db->col(array('name', 'linecol')), $db->cond(array("user_id = {$authinfo['id']}", "id = {$unit_id}"), 'AND'));
if (!($unit_record = $db->record_fetch($unit_result))) {
	throw new Exception("Unit \"{$unit_id}\" not found / not authorised to view");
}
*/


//Tracker units
$trackerunit = array();
$currtrackerunit_default = 0;
$unit_result = $db->table_query($db->tbl($tbl['unit']), $db->col(array('id', 'name')), $db->cond(array("user_id = {$authinfo['id']}"), 'AND'), $db->order(array(array('name', 'ASC'))));
while ($unit_record = $db->record_fetch($unit_result)) {
	$trackerunit[$unit_record['id']] = $unit_record['name'];
	$currtrackerunit_default = $unit_record['id'];
}

if ( (!isset($_GET['go'])) && (count($trackerunit) == 1) ) {
	$currtrackerunit = $currtrackerunit_default;
}

//If current tracker unit, must be in list
if ($currtrackerunit) {
	if (!in_array($currtrackerunit, array_keys($trackerunit))) {
		throw new Exception("Tracker unit id \"{$currtrackerunit}\" not valid (not authorised)");
	}
}









//note: implement credits and use up a credit if the cache is not hit









//If tracker unit(s) available
if (count($trackerunit) > 0) {

	if (isset($_GET['go'])) {
		$style = intval($_GET['style']);
		$zoom = intval($_GET['zoom']);
	} else {
		$style = 0;
		$zoom = 3;
	}


	$trackerunit_options_html = lib::create_options($trackerunit, $currtrackerunit, lib::CREATEOPT_PLEASESELECT);

	$zoom_options = array();
	for ($i=1; $i<=10; $i++) {

		$zoom_options[$i] = $i;

		if ($i == 1) {
			$zoom_options[$i] .= ' (in)';
		} else if ($i == 10) {
			$zoom_options[$i] .= ' (out)';
		}

	}

	$style_options = array();
	foreach (postcodeanywhere::$availstyle as $style_id => $style_item) {
		$style_options[$style_id] = $style_item['name'];

		if ($authinfo['usergroup'] == auth::LOGIN_GROUP_USER_DEMO) {
			break;
		}

	}

	$style_options_html = lib::create_options($style_options, $style);

	$zoom_options_html = lib::create_options($zoom_options, $zoom);

	// onchange="this.form.submit()"
	$top_html = <<<EOHTML
<p>Style: <select size="1" name="style">{$style_options_html}</select> &nbsp;Zoom: <select size="1" name="zoom">{$zoom_options_html}</select></p>
<p>Tracker Unit: <select size="1" name="unit">{$trackerunit_options_html}</select></p>
<p><input type="submit" value=" View Last Position " /></p>
EOHTML;


	//If tracker unit selected
	if ($currtrackerunit) {

		//Retrieve last known position
		$position_result = $db->table_query($db->tbl($tbl['position']), $db->col(array('id', 'datetime', 'lat', 'lon', 'alt', 'deg', 'speed_km')), $db->cond(array("unit_id = {$currtrackerunit}", "(fixtype = 2 OR fixtype = 3)"), 'AND'), $db->order(array(array('id', 'DESC'))));

		//If have last position
		if ($position_record = $db->record_fetch($position_result)) {

			$position = $position_record;

			$position = $position_record;

			$position['speed_mph'] = round(($position['speed_km'] * 0.621371192), 1);
			$position['datetime'] = appgeneral::utcdatetime_tolocal($position['datetime']);

			$position_h = lib::htmlentities_array($position);


			//Check if will read from cache
			$pca = new postcodeanywhere();
			$pca->setlatlon($position_record['lat'], $position_record['lon']);
			$pca->setwidthheight($cfg['pda_map_size']['width'], $cfg['pda_map_size']['height']);
			$pca->setstyle($style);
			$pca->setzoom($zoom);
			$pca->generatecachename();
			$pca->checkcache();

			$cached = $pca->retrievecachestatus();

			//Retrieve credit balance
			if ( ($cached) || ($authinfo['pcacredit'] > 0) ) {

				$link_map_h = htmlentities($self . navpd::args_querystring(array('p' => 'pda_map', 'style' => $style, 'zoom' => $zoom, 'id' => $position_record['id'])));

				$lastposmap_html = <<<EOHTML
<p><img src="{$link_map_h}" width="{$cfg['pda_map_size']['width']}" height="{$cfg['pda_map_size']['height']}" alt="Last Position Map" /></p>
EOHTML;

			} else {
				$link_site = htmlentities($cfg['site_url']);
				$lastposmap_html = <<<EOHTML
<div class="crediterror">
	<p>Error, no credit available - to use this feature please login and purchase credits at <a href="{$link_site}">{$link_site}</a></p>
</div>
EOHTML;
			}

			$map_html = <<<EOHTML

<h2>Last Position Fix</h2>

{$lastposmap_html}

<p>
	<strong>Date:</strong> {$position_h['datetime']}<br />
	<strong>Speed:</strong> {$position_h['speed_km']} kph ({$position_h['speed_mph']} mph)<br />
	<strong>Heading:</strong> {$position_h['deg']} (deg)<br />
	<strong>Altitude:</strong> {$position_h['alt']}<br />
	<strong>Lat:</strong> {$position_h['lat']}<br />
	<strong>Lon:</strong> {$position_h['lon']}
</p>

<p>Note: Not all map styles support the full range of zoom levels for all areas.</p>

EOHTML;

		} else {

			$map_html = <<<EOHTML
<p>Last position not found.</p>
EOHTML;

		}

	} else {
		$map_html = '';
	}

} else {

	$top_html = <<<EOHTML
<p>No tracker units available.</p>
EOHTML;

	$map_html = '';

}

$link_h = htmlentities($self);

$self_form_html = navpd::self_form(array('logout' => null, 'unit' => null));

$page_html = <<<EOHTML
<form method="get" action="{$link_h}">
	<div>
	<input type="hidden" name="go" value="1" />
{$self_form_html}
	</div>

{$top_html}

</form>

{$map_html}
EOHTML;




$body_html = <<<EOHTML

{$page_html}

EOHTML;


$template = new template_pda();
$template->settitle('Track Live');
//$template->setheaderaddinhtml($headeraddin_html);
$template->setbodyhtml($body_html);
$template->display();

?>