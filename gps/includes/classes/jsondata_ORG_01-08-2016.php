<?php

class jsondata {

	private $position_data = array();

	public function getlivepos_json() {
		global $db, $tbl, $authinfo;
		$trackerunitpos = array();

		//Tracker units
		$trackerunit = array();
		$unit_result = $db->table_query($db->tbl($tbl['unit']), $db->col(array('id')), $db->cond(array("user_id = {$authinfo['id']}"), 'AND'), $db->order(array(array('name', 'ASC'))));
		while ($unit_record = $db->record_fetch($unit_result)) {
			$trackerunit[] = $unit_record['id'];
		}

		//For each tracker unit
		foreach ($trackerunit as $unit) {

			//Retrieve last known position
			$position_result = $db->table_query($db->tbl($tbl['position']), $db->col(array('id', 'lat', 'lon')), $db->cond(array("unit_id = {$unit}", "(fixtype = 2 OR fixtype = 3)"), 'AND'), $db->order(array(array('id', 'DESC'))));
			if ($position_record = $db->record_fetch($position_result)) {
				$trackerunitpos[$unit] = $position_record;
			}

		}

		//Create json data
		$trackerunitpos_js = '';
		foreach ($trackerunitpos as $unit => $unitdata) {

			$trackerunitpos_js .= <<<EOJSON
			{$unit}: {
				"id": {$unitdata['id']},
				"lat": {$unitdata['lat']},
				"lon": {$unitdata['lon']}             		
				
			},\n
EOJSON;

		}

		$trackerunitpos_js = rtrim($trackerunitpos_js, ",\n");
		$trackerunitpos_js = str_replace("\r", '', $trackerunitpos_js);

		$trackerunitpos_js = <<<EOJSON
{
		"livepos": {
{$trackerunitpos_js}
		}
	}
EOJSON;

		return $trackerunitpos_js;

	}

	public function getunitinfo_json() {
		global $db, $tbl, $authinfo;

		$unitinfo_js = '';
		$unit_result = $db->table_query($db->tbl($tbl['unit']), $db->col(array('id', 'icon', 'linecol')), $db->cond(array("user_id = {$authinfo['id']}"), 'AND'), $db->order(array(array('name', 'ASC'))));
		while ($unit_record = $db->record_fetch($unit_result)) {

			$unitinfo_js .= <<<EOJSON
{$unit_record['id']}: { "icon": {$unit_record['icon']}, "linecol": {$unit_record['linecol']} },
EOJSON;

		}

		$unitinfo_js = '{' . rtrim($unitinfo_js, ",") . '}';

		return $unitinfo_js;

	}

	public function getpolyline_json() {

		//Retrieve polyline points
		$polylinejson = '';
		foreach ($this->position_data as $dataitem) {
			$polylinejson .= "[{$dataitem['lat']},{$dataitem['lon']}],";
		}

		$polylinejson = '[ ' . rtrim($polylinejson, ",\n") . ' ]';

		return $polylinejson;

	}

	public function retrievedata($unit_id, $date) {
		global $db, $tbl;

		$unit_id = intval($unit_id);

		if ($unit_id == 0) {
			throw new Exception('Unit id is zero');
		}

		$start_utc = gmdate('Y-m-d H:i:s', strtotime("{$date}"));
		$end_utc = gmdate('Y-m-d H:i:s', strtotime("{$date} +1 day"));

		$cond = array("unit_id = {$unit_id}", "(fixtype = 2 OR fixtype = 3)", "datetime >= '{$start_utc}' AND datetime < '{$end_utc}'");

		//Retrieve map data
		$position_data = array();
		$position_result = $db->table_query($db->tbl($tbl['position']), $db->col(array('id', 'lat', 'lon', 'deg')), $db->cond($cond, 'AND'), $db->order(array(array('id', 'ASC'))));
		while ($position_record = $db->record_fetch($position_result)) {
			$position_data[] = $position_record;
		}

		$this->position_data = $position_data;

	}

	public function getmarker_json($zoomlevel) {
		global $cfg;

		if (!isset($cfg['zoomlevel_markdist'][$zoomlevel])) {
			throw new Exception("Zoom level \"{$zoomlevel}\" not recognised");
		}

		$position_data = $this->position_data;

		$last_dataitem = false;

		$distance_total = 0;

		$total_markers = count($position_data);

		//Retrieve marker icons
		$markerjson = '';
		$i = 0;
		foreach ($position_data as $dataitem) {

			if ($last_dataitem != false) {
				$distance_moved = appgeneral::distance_latlon($dataitem['lat'], $dataitem['lon'], $last_dataitem['lat'], $last_dataitem['lon']);
				$distance_total += $distance_moved;
			}

			if ( ($distance_total >= $cfg['zoomlevel_markdist'][$zoomlevel]) || ($i == 0) || ($total_markers-1 == $i) ) {

				$markerjson .= "[{$dataitem['id']},{$dataitem['lat']},{$dataitem['lon']},{$dataitem['deg']}],";
				$distance_total = 0;

			}

			$last_dataitem = $dataitem;

			$i++;
		}

		$markerjson = '[ ' . rtrim($markerjson, ",\n") . ' ]';

		return $markerjson;

	}

}

?>