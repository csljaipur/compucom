<?php

error_reporting(E_ALL | E_STRICT);

$includes_path = '../../includes/';
$publichtml_path = '';

include $includes_path . 'config.php';
include $includes_path . 'general/init.php';

//Connect to database
$db = new dbmysql($cfg['db_server'], $cfg['db_username'], $cfg['db_password'], $cfg['db_database']);

$runbycron = new runbycron();
$runbycron->run();
$runbycron->debughandler();


class runbycron {

	const DEBUGTYPE_ERROR = 1;
	const DEBUGTYPE_STATUS = 2;

	public $fatalshandledinternally = true;
	public $showdebugging = true;

	//Run
	function run() {
		global $db, $tbl, $cfg;

		$this->debuginfo(self::DEBUGTYPE_STATUS, 0, 'Running @ ' . date('Y-m-d H:i:s') . "\n");

		try {

			//Go through all units
			$unit_result = $db->table_query($db->tbl($tbl['unit']), $db->col(array('id')));
			while ($unit_record = $db->record_fetch($unit_result)) {

				$this->debuginfo(self::DEBUGTYPE_STATUS, 0, "Unit: {$unit_record['id']}");

				//Get count of total positions
				$position_result = $db->table_query($db->tbl($tbl['position']), 'COUNT(id) AS count', $db->cond(array("unit_id = {$unit_record['id']}"), 'AND'));
				while ($position_record = $db->record_fetch($position_result)) {
					$this->debuginfo(self::DEBUGTYPE_STATUS, 0, "Total: {$position_record['count']}");

					//If user is over their limit
					if ($position_record['count'] > $cfg['position_report_limit']) {

						$this->debuginfo(self::DEBUGTYPE_STATUS, 0, "Over limit");

						//Get id where the limit is met
						$position_result = $db->table_query($db->tbl($tbl['position']), $db->col(array('id')), $db->cond(array("unit_id = {$unit_record['id']}"), 'AND'), $db->order(array(array('id', 'DESC'))), $cfg['position_report_limit'], 1);
						if ($position_record = $db->record_fetch($position_result)) {
							$this->debuginfo(self::DEBUGTYPE_STATUS, 0, "Over limit id: {$position_record['id']}");

							$db->record_delete($tbl['position'], $db->cond(array("unit_id = {$unit_record['id']}", "id <= {$position_record['id']}"), 'AND'));

						} else {
							throw new Exception('Unable to find over limit id');
						}

					} else {

						$this->debuginfo(self::DEBUGTYPE_STATUS, 0, "Not over limit");

					}

				}

				$this->debuginfo(self::DEBUGTYPE_STATUS, 0, '');

			}

		} catch (Exception $e) {
			$this->debuginfo(self::DEBUGTYPE_ERROR, 0, 'Exception: ' . $e->getMessage());
		}

	}

	//Save debug message
	function debuginfo($debug_type, $debug_level, $debug_message) {

		//Save arguments to array
		$this->debuginfomsgs[] = func_get_args();

		$spaceaddin = str_repeat(' ', $debug_level);

		if ($this->showdebugging == true) {
			$spaceaddin = str_repeat(' ', $debug_level);
			echo "{$spaceaddin}{$debug_message}\n";
		}

	}

	//Handle debug messages
	function debughandler() {

		$debuginfotext = '';
		$fatalerror = false;
		foreach ($this->debuginfomsgs as $debugitem) {
			$spaceaddin = str_repeat(' ', $debugitem[1]);

			if ($debugitem[0] == self::DEBUGTYPE_ERROR) {
				$fatalerror = true;
				$fatalerroraddin = '#';
			} else {
				$fatalerroraddin= '';
			}

			$debuginfotext .= "{$spaceaddin}{$fatalerroraddin}{$debugitem[2]}\n";
		}

		if ($debuginfotext) {

			//If fatals should be handled internally / reported
			if ($this->fatalshandledinternally == true) {

				//Echo out debugging information
				//echo $debuginfotext;

				//If there was a fatal error reported
				if ($fatalerror == true) {

					//Fatal error...
					//...

				}

			} else {

				//Otherwise fatals should be rethrown, and handled by the application higher up the chain

				//If there was a fatal error reported
				if ($fatalerror == true) {

					//ReThrow (but not properly) exception (error)
					throw new Exception($debuginfotext);

				}

			}

		}

	}


}

?>