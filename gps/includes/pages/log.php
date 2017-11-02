<?php

include $includes_path . 'config.php';
include $includes_path . 'general/init.php';

//Set exception handler
exceptions::sethandler();
set_exception_handler('exception_handler');

//Connect to database
$db = new dbmysql($cfg['db_server'], $cfg['db_username'], $cfg['db_password'], $cfg['db_database']);


//Raw log of input
$logdata = print_r(array_merge($_POST, array('datetime' => date('r'))), true);
//$cfg['rawdatarec_log_path'] = 'log.txt';
file_put_contents($cfg['rawdatarec_log_path'], $logdata, FILE_APPEND);


//If imei is specified
if (isset($_POST['i'])) {

	//If imei 15 digits
	if (preg_match("/^\d{15}$/", $_POST['i'])) {

		//Lookup unit info from imei
		$unit_result = $db->table_query($db->tbl($tbl['unit']), $db->col(array('id', 'password')), $db->cond(array("imei = '".$db->es($_POST['i'])."'"), 'AND'));
		if (!($unit_record = $db->record_fetch($unit_result))) {
			throw new Exception('Unit IMEI not found');
		}

		//If password is specified
		if (isset($_POST['a'])) {

			//If password is valid
			if ($unit_record['password'] == md5($_POST['a'])) {

				//If data specified
				if ( (isset($_POST['d'])) && (is_array($_POST['d'])) ) {

					//Go through posted data
					foreach ($_POST['d'] as $dataline) {

						try {

							//Parse data
							gpsdata::parseinput($dataline, $unit_record['id']);

						} catch (Exception $exception) {
							//Log exception
							exceptions::savelogentry($exception);

							//lib::prh($exception);

							//Do nothing else - unit may occationally send back bad data, if it does then ignore it
							//_

						}

					}

					echo "=RECVOK=";

				} else {
					throw new Exception('No "d[]" data specified');
				}

			} else {
				throw new Exception('Password not valid');
			}

		} else {
			throw new Exception('Password not specified');
		}

	} else {
		throw new Exception('IMEI not valid');
	}

} else {
	throw new Exception('IMEI not specified');
}






function exception_handler($exception) {
	exceptions::savelogentry($exception);

	header('HTTP/1.0 500 Application error');
	//echo "EXCEPTION ERROR: " . $exception->getmessage();
	echo 'EXCEPTION ERROR';
}

?>