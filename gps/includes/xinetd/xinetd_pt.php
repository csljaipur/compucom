#!/usr/bin/php
<?php

//http://www.yolinux.com/TUTORIALS/LinuxTutorialNetworking.html#INET

//telnet log.paralleltrack.co.uk 8742

//error_reporting(E_ALL);
error_reporting(0);
//Turn this off - do not want errors going out over the socket

set_time_limit(60*60*2);
stream_set_blocking(STDIN, 0);

$includes_path = '/home/paralleltrack/domains/www.paralleltrack.co.uk/includes/';
include $includes_path . 'config.php';
include $includes_path . 'general/init.php';

//Set exception handler
set_exception_handler('exception_handler');

//Connect to database
$db = new dbmysql($cfg['db_server'], $cfg['db_username'], $cfg['db_password'], $cfg['db_database']);

$noactivity_timeout = 60;

debug::msg('Started @ ' . date('r'));

$timeout = time() + $noactivity_timeout;
$authenticated = false;

//Untill stdin is closed (ie socket closed)
$buffer = '';
while (!feof(STDIN)) {

	$timenow = time();

	//debug::msg('Entered loop at ' . $timenow);
	//debug::msg('Timeout is ' . $timeout);

	//If timedout
	if ($timenow >= $timeout) {
		break;
	}

	//Wait here untill timeout or incomming data available
	$read = array(STDIN);
	$null = null;
	stream_select($read, $null, $null, $noactivity_timeout);

	//Read in data
	$buffer .= fgets(STDIN, 1024);

	//If no data, then exit out of loop
	if (!trim($buffer)) {
		debug::msg('No data, existing loop');
		break;
	}

	//If not a newline, have not reached end of line, so continue reading
	if (!preg_match("/\r\n$/", $buffer)) {
		continue;
	}

	$timeout = time() + $noactivity_timeout;

	debug::msg('Received: ' . $buffer);

	$parsedata = $buffer;
	$parsedata = str_replace("\n", '', $parsedata);
	$parsedata = str_replace("\r", '', $parsedata);

	//If no data, then exit out of loop
	//if ($parsedata == '+++') {
	//	debug::msg('Done transmitting, closing socket');
	//	break;
	//}

	//If not authenticated, then try to do so
	if ($authenticated == false) {

		debug::msg('Not authenticated');

		//Parse auth data
		parse_str($parsedata, $initdata);

		//Auth password specified
		if (isset($initdata['a'])) {

			debug::msg('Password specified: ' . $initdata['a']);

			//Version specified
			if (isset($initdata['v'])) {

				debug::msg('Version specified: ' . $initdata['v']);

				//IMEI specified
				if (isset($initdata['i'])) {

					debug::msg('Mobile specified: ' . $initdata['i']);

					//If IMEI 15 digits
					if (preg_match("/^\d{10}$/", $initdata['i'])) {

						debug::msg('Mobile valid 10 digits');

						//Lookup unit info from imei
						$unit_result = $db->table_query($db->tbl($tbl['unit']), $db->col(array('id', 'password')), $db->cond(array("imei = '".$db->es($initdata['i'])."'"), 'AND'));
						if (!($unit_record = $db->record_fetch($unit_result))) {
							throw new Exception('Unit Mobile not found');
						}

						//If password is valid
						if ($unit_record['password'] == md5($initdata['a'])) {

							//$unit_record has been set (is used below, after authentication)

							$authenticated = true;

							//Send ok authorised
							fwrite(STDOUT, "=OK=\r\n");

						} else {
							throw new Exception('Password not valid');
						}

					} else {
						throw new Exception("Mobile \"{$initdata['i']}\" not valid");
					}

				} else {
					throw new Exception('Mobile not specified');
				}

			} else {
				throw new Exception('Version not specified');
			}

		} else {
			throw new Exception('Auth password not specified');
		}

	} else {

		//Unit authorised

		debug::msg('Authenticated, parsing data');

		//Parse data
		gpsdata::parseinput($parsedata, $unit_record['id']);

		//Send ok recevied data
		fwrite(STDOUT, "=OK=\r\n");

		/*
		try {

			//Parse data
			gpsdata::parseinput($parsedata, $unit_record['id']);

		} catch (Exception $exception) {
			//Log exception
			exceptions::savelogentry($exception);

			//lib::prh($exception);

			//Do nothing else - unit may occationally send back bad data, if it does then ignore it
			//_
		}
		*/

	}

	//Clear buffer
	$buffer = '';

}

debug::msg('Finished');


//Debugging
class debug {

	static public function msg($msg) {
		global $includes_path;

		$msg = str_replace("\r", '\r', $msg);
		$msg = str_replace("\n", '\n', $msg);
		$msg = str_replace("\t", '\t', $msg);

		//$spaceaddin = str_repeat(' ', $level);
		$logmsg = "{$msg}\n";

		//echo $logmsg . "\r\n";

		//Log to file
		file_put_contents($includes_path . 'xinetd/log.txt', $logmsg, FILE_APPEND);

	}

}

//Exception handler
function exception_handler($exception) {
	exceptions::savelogentry($exception);
	debug::msg('Exception: '. $exception->getMessage());
	fwrite(STDOUT, "ERROR Exception occured\r\n");
}

?>