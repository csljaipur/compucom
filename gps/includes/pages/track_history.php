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
	$currdate = '';
}


$unitrecordcurr = array();

//Tracker units
$trackerunit = array();
$unit_result = $db->table_query($db->tbl($tbl['unit']), $db->col(array('id', 'name', 'linecol')), $db->cond(array("user_id = {$authinfo['id']}"), 'AND'), $db->order(array(array('name', 'ASC'))));
while ($unit_record = $db->record_fetch($unit_result)) {
	$trackerunit[$unit_record['id']] = $unit_record['name'];
	$currtrackerunit = ($currtrackerunit) ? $currtrackerunit : $unit_record['id'];

	if ($currtrackerunit == $unit_record['id']) {
		$unitrecordcurr = $unit_record;
	}

}

//If current tracker unit, must be in list
if ($currtrackerunit) {
	if (!in_array($currtrackerunit, array_keys($trackerunit))) {
		throw new Exception("Tracker unit id \"{$currtrackerunit}\" not valid");
	}
}



if (count($trackerunit) > 0) {

	$trackerunit_options_html = lib::create_options($trackerunit, $currtrackerunit);

	$trackerunit_options_html = <<<EOHTML
<select size="1" name="unit" class="selectunit" onchange="selecthisttracker(this.value)">{$trackerunit_options_html}</select>
EOHTML;

} else {

	$link_config = htmlentities($self . '?p=track_config');

	$trackerunit_options_html = <<<EOHTML
<div class="addunits">None available, add units via <a href="{$link_config}">configuration page</a>.</div>
EOHTML;

}

//If unit set, but no current date set
if ( ($currtrackerunit) && (!$currdate) ) {

	//Try to default to last day with data

	$position_result = $db->table_query($db->tbl($tbl['position']), $db->col(array('id', 'unit_id', 'datetime')), $db->cond(array("unit_id = {$currtrackerunit}", "(fixtype = 2 OR fixtype = 3)"), 'AND'), $db->order(array(array('datetime', 'DESC'))), 0, 1);
	if ($position_record = $db->record_fetch($position_result)) {

		list($date) = explode(' ', $position_record['datetime']);
		$currdate = date('Y-m-d', strtotime($position_record['datetime']));

	}

}

if ($currtrackerunit) {

	if ($currdate) {

		$dateparts = explode('-', $currdate);
		$calendarselect_js = "calendar1.setdate({$dateparts[0]}, {$dateparts[1]}, {$dateparts[2]});";

		$calendar_year = intval($dateparts[0]);
		$calendar_month = intval($dateparts[1]);

		$calendar_json_js = history::calendar_json($currtrackerunit, $calendar_year, $calendar_month);

		$calendar_json_name_js = "{$calendar_year}-{$calendar_month}";

		$calendaravail_js = <<<EOJS
					trackhist_avail = {
						"{$calendar_json_name_js}": {$calendar_json_js}
					};
EOJS;

		$calendaraddin_html = <<<EOHTML
				<div id="calendar1_disp"></div>

				<script type="text/javascript">
				  <!--

{$calendaravail_js}

					var calendar1 = new calendar("calendar1", "calendar1_disp");
					{$calendarselect_js}
					calendar1.generate();

				  //-->
				</script>
EOHTML;

	} else {
		$calendaraddin_html = <<<EOHTML
<div class="nohistoryavail">No history available<br />for specified unit</div>
EOHTML;
	}

	$sidepanel_extra_html = '';

	$sidepanel_extra_html .= <<<EOHTML
		<div class="sidepanelbox">

			<div class="sidepanelboxtitle">Select Date</div>
			<div class="sidepanelboxcontent">

{$calendaraddin_html}

			</div>
		
		</div>
EOHTML;

	if ($currdate) {

		$link_csv = htmlentities($self . '?p=export_csv&unit='.$currtrackerunit.'&date='.$currdate);
		$link_html = htmlentities($self . '?p=export_html&unit='.$currtrackerunit.'&date='.$currdate);
		$link_kml = htmlentities($self . '?p=export_kml&unit='.$currtrackerunit.'&date='.$currdate);

		$sidepanel_extra_html .= <<<EOHTML
		<div class="sidepanelbox">
			<div class="sidepanelboxtitle">Export Options</div>

			<div class="sidepanelboxcontent">
				<ul>
					<li><a href="{$link_csv}">CSV</a></li>
					<li><a href="{$link_html}">HTML</a></li>
				<!--	<li><a href="{$link_kml}">KML</a></li> -->
				</ul>
			</div>
		
		</div>
EOHTML;

	}

} else {
	$sidepanel_extra_html = '';
}



$body_html = <<<EOHTML

<div class="yui-ge">

	<div class="yui-u first">

		<div id="map"></div>

	</div>

	<div class="yui-u">

		<div class="sidepanelbox">

			<div class="sidepanelboxtitle">Select Tracker Unit</div>
			<div class="sidepanelboxcontent">
				{$trackerunit_options_html}
			</div>
		
		</div>

{$sidepanel_extra_html}

	</div>

</div>

EOHTML;


if (count($unitrecordcurr)) {

	//Retrieve line color
	if (!isset($cfg['linecol'][$unitrecordcurr['linecol']]['html_map'])) {
		throw new Exception("Specified line color \"{$unit_record['linecol']}\" not valid");
	}

	$linecol = $cfg['linecol'][$unitrecordcurr['linecol']]['html_map'];

	$linecolid = $unitrecordcurr['linecol'];
} else {
	$linecol = '';
	$linecolid = 'undefined';
}





$jsondata = new jsondata();

if ( ($currtrackerunit) && ($currdate) ) {
	$jsondata->retrievedata($currtrackerunit, $currdate);
}


//$unitinfo_json = $jsondata->getunitinfo_json();
$polyline_json = $jsondata->getpolyline_json();


$uniticonpaths_js = disptemplate::uniticonpaths_js();

$mapsheaderaddin_html = disptemplate::mapsheaderaddin();

$baseurl_js = $self . '?p=ajax_handler';

$currpageunit_starturl_js = addslashes($self . '?p=' . $current_page);
$currpagedate_starturl_js = addslashes($self . '?p=' . $current_page . '&unit=' . $currtrackerunit);//navpd::self(array('unit' => $currtrackerunit));

if ($currdate) {
	$currdate_js = "'".$currdate."'";
} else {
	$currdate_js = 'undefined';
}

$headeraddin_html = <<<EOHTML

{$mapsheaderaddin_html}

<script src="resources/javascript/maphist_calendar.js" language="javascript" type="text/javascript"></script>

<script type="text/javascript">
  //<![CDATA[

	function load() {

		//Load Map
		loadmap();

		basemap = mapstraction.getMap();

		if ( (currtrackerunit > 0) && (currdate != undefined) ) {

			//Init general
			initgeneral();

			//Init icons (graphics)
			init_icons();

			//Put on polyline, center and zoom to points
			addpolylinecenterzoomall(polyline_points, line_color);

			//Add markers for specified current zoom level
			addmmangerforzoom(mapstraction.getZoom());

			if (mapstraction.api == "google") {
				//On zoomchange rehandle marker icons
				GEvent.addListener(basemap, "zoomend", evtzoomend);
			} else if (mapstraction.api == "multimap") {

				//Handle zoom changed
				basemap.addEventHandler("changeZoom", multimapzoomchange);

			}

		}

	}

	var mapstraction;
	var basemap;
	var baseurl = "{$baseurl_js}";

	var currpageunit_starturl = "{$currpageunit_starturl_js}";
	var currpagedate_starturl = "{$currpagedate_starturl_js}";

	var map_default_lat = {$cfg['map_default_lat']};
	var map_default_lon = {$cfg['map_default_lon']};
	var map_default_zoom = {$cfg['map_default_zoom']};

	//var unitinfo = {unitinfo_json};

	var handlezoomchangeonmap = true;

	var uniticonpaths = {$uniticonpaths_js};

	var currtrackerunit = {$currtrackerunit};

	//Line Points data
	var polyline_points = {$polyline_json};

	//Line color
	var line_color = "{$linecol}";

	//Line color id
	var line_color_id = {$linecolid};

	//Current selected date
	var currdate = {$currdate_js};

	window.onload = load;

  //]]>
</script>

EOHTML;

$template = new template();
$template->setmainnavsection('track_live');
$template->settitle('Track History');
$template->setheaderaddinhtml($headeraddin_html);
$template->setbodyhtml($body_html);
$template->display();

?>