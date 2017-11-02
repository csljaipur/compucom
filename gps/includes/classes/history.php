<?php

class history {

	static public function condition() {
		
		if (!isset($_GET['date'])) {
			throw new Exception("Date not specified");
		}

		$dateparts = explode('-', $_GET['date']);

		if (count($dateparts) != 3) {
			throw new Exception('Date not corectly formatted');
		}

		if (!checkdate($dateparts[1], $dateparts[2], $dateparts[0])) {
			throw new Exception("Spcified date \"{$_GET['date']}\" not valid");
		}

		$start_utc = date('Y-m-d H:i:s', strtotime("{$_GET['date']}"));
		$end_utc = date('Y-m-d H:i:s', strtotime("{$_GET['date']} +1 day"));

		$cond = array("datetime >= '{$start_utc}' AND datetime < '{$end_utc}'");

		return $cond;

	}

	static public function calendar_json($unit_id, $year, $month) {
		global $db, $tbl, $authinfo;

		$year = intval($year);
		$month = intval($month);

		$unit_id = intval($unit_id);

		$calendar_json_name_js = "{$year}-{$month}";

		if (!checkdate($month, 1, $year)) {
			throw new Exception("Date year: \"{$year}\", month: \"{$month}\" not valid");
		}

		$monthstart_utc = date('Y-m-d H:i:s', strtotime("{$year}-{$month}-01"));
		$monthend_utc = date('Y-m-d H:i:s', strtotime("{$year}-{$month}-01 +1 month"));

		//Auth check
		$unit_result = $db->table_query($db->tbl($tbl['unit']), '1', $db->cond(array("user_id = {$authinfo['id']}", "id = {$unit_id}"), 'AND'), '', 0, 1);
		if ($db->record_count($unit_result) == 0) {
			throw new Exception("Unit id \"{$editid}\" not found / user not authorised");
		}

		//$date = new DateTime();
		//$offset_sec = date_offset_get($date);
		$offset_sec = date('Z');

		$query = <<<EOSQL
SELECT
		DISTINCT DATE_FORMAT(
			DATE_ADD(datetime, INTERVAL {$offset_sec} SECOND)
		,'%e') AS day
FROM
		{$tbl['position']}
WHERE
			unit_id = {$unit_id}
		AND
			(fixtype = 2 OR fixtype = 3)
		AND
			datetime >= '{$monthstart_utc}'
		AND
			datetime < '{$monthend_utc}'
ORDER BY
		day ASC
EOSQL;

		$dayswithhistory = array();
		$position_result = $db->query($query);
		while ($position_record = $db->record_fetch($position_result)) {
			$dayswithhistory[] = $position_record['day'];
			//lib::prh($position_record);
		}

		/*
		$position_result = $db->table_query($db->tbl($tbl['position']), "", $db->cond(array("unit_id = {$unit_id}", "(fixtype = 2 OR fixtype = 3)"), 'AND'), $db->order(array(array('datetime', 'DESC'))), 0, 1);
		while ($position_record = $db->record_fetch($position_result)) {

			list($date) = explode(' ', $position_record['datetime']);
			$currdate = date('Y-m-d', strtotime($position_record['datetime'] . ' UTC'));

		}
		*/

		$dayswithhistory_js = '[' . implode(',', $dayswithhistory) . ']';

		return $dayswithhistory_js;

	}

}

?>