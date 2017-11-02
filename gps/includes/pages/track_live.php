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


//Tracker units
$trackerunit = array();
$trackerunit_chkbox_html = '';
$unit_result = $db->table_query($db->tbl($tbl['unit']), $db->col(array('id', 'name', 'icon')), $db->cond(array("user_id = {$authinfo['id']}"), 'AND'), $db->order(array(array('name', 'ASC'))));
while ($unit_record = $db->record_fetch($unit_result)) {
	//lib::prh($unit_record);

	$trackerid = 'trackerunit_'.$unit_record['id'];

	$unit_record_h = lib::htmlentities_array($unit_record);

	$iconpath = $cfg['icon'][$unit_record['icon']]['img']['map_select'];

	$trackerunit_chkbox_html .= <<<EOHTML
				<img src="{$iconpath}" class="iconimg" onclick="checkboxchange('{$trackerid}'); showhideliveicon({$unit_record['id']}, $('{$trackerid}').checked);" />
				<p><input type="checkbox" checked="checked" name="trackerunit[]" id="{$trackerid}" value="{$unit_record['id']}" onclick="showhideliveicon({$unit_record['id']}, this.checked);" /> <label for="{$trackerid}">{$unit_record_h['name']}</label></p>

EOHTML;

	$trackerunit[] = $trackerid;
}

$trackerunit_js = implode("','", $trackerunit);
$trackerunit_js = ($trackerunit_js) ? "'" . $trackerunit_js . "'" : '';

if (count($trackerunit) > 1) {

	$trackerunit_chkbox_html .= <<<EOHTML
				<div class="selectunselectall">[<a href="#" onclick="unit_chkboxchange(true); showallliveicon(true); return false">Select All</a> | <a href="#" onclick="unit_chkboxchange(false); showallliveicon(false); return false">Unselect All</a>]</div>
EOHTML;

} else if (!$trackerunit_chkbox_html) {

	$link_config = htmlentities($self . '?p=track_config');

	$trackerunit_chkbox_html = <<<EOHTML
<div class="addunits">None available, add units via <a href="{$link_config}">configuration page</a>.</div>
EOHTML;
}

$autoupdate_html = lib::create_radio('autoupdate', array(1 => 'Yes', 0 => 'No'), 1, null, '', 'onclick="autoupdatechanged()"');

$body_html = <<<EOHTML

<div class="yui-ge">

	<div class="yui-u first">

		<div id="map"></div>

	</div>

	<div class="yui-u">

		<div class="sidepanelbox">

			<div class="sidepanelboxtitle">Tracker Units</div>
			<div class="sidepanelboxcontent">

{$trackerunit_chkbox_html}

			</div>
		
		</div>

		<div class="sidepanelbox">
			<div class="sidepanelboxtitle">Auto Update</div>

			<div class="sidepanelboxcontent">
				{$autoupdate_html}
			</div>
		
		</div>

	</div>

</div>

EOHTML;

//$cfg['dispupd_interv_sec']=5;




$jsondata = new jsondata();
$livepos_json = $jsondata->getlivepos_json();
$unitinfo_json = $jsondata->getunitinfo_json();

$mapsheaderaddin_html = disptemplate::mapsheaderaddin();

$baseurl_js = $self . '?p=ajax_handler';

$uniticonpaths_js = disptemplate::uniticonpaths_js();

$headeraddin_html = <<<EOHTML

{$mapsheaderaddin_html}

<script type="text/javascript">
  //<![CDATA[

	function load() {

		//Load Map
		loadmap();

		basemap = mapstraction.getMap();

		//List of all tracker unit check boxes
		trackerunitchk = new Array({$trackerunit_js});

		//If autoupdate checked by default, then set update timeout
		if (radiogetselected("autoupdate") == "1") {
			//Set display to autoupdate
			updatetimeout_live = window.setTimeout("updatedisplay();", dispupd_interval);
		}

		//Init general
		initgeneral();

		//Init icons (graphics)
		init_icons();

		//Init live icons (markers)
		init_liveicons();

		//Place icons
		placeliveicons(currpositions);

	}

	var mapstraction;
	var basemap;
	var baseurl = "{$baseurl_js}";

	var dispupd_interval = {$cfg['dispupd_interv_sec']} * 1000;

	var map_default_lat = {$cfg['map_default_lat']};
	var map_default_lon = {$cfg['map_default_lon']};
	var map_default_zoom = {$cfg['map_default_zoom']};

	var handlezoomchangeonmap = false;

	var uniticonpaths = {$uniticonpaths_js};

	var currpositions = {$livepos_json};

	var unitinfo = {$unitinfo_json};
    
	//Line color id
	//var line_color_id = undefined;
    var points = [];
	
	//Line color
	var line_color = "#ff8080";

	//Line color id
	var line_color_id = 1;
	
	
	
	window.onload = load;

  //]]>
</script>

EOHTML;

$template = new template();
$template->setmainnavsection('track_live');
$template->settitle('Track Live');
$template->setheaderaddinhtml($headeraddin_html);
$template->setbodyhtml($body_html);
$template->display();

?>