<?php

//Save position
class gpsdata {

	//Convert NMEA degres to decimal degrees
	static public function degminsec_degdec($value) {

		//Note: This does not work if south of the equator

		//http://www.base64.co.uk/google_maps_api_uk.html
		//http://www.maptools.com/faq.html#anchor5.7
		//http://www.mapwindow.org/phorum/read.php?11,5780
		//http://www.calculatorcat.com/latitude_longitude.phtml
		//http://jan.ucc.nau.edu/~cvm/latlongdist.html
		//http://mathforum.org/library/drmath/sets/select/dm_lat_long.html
		//http://www.google.co.uk/search?q=php+convert++nmea+to+decimal+degrees&hl=en&start=20&sa=N

		//<latitude> - 4542.82691N - format is ddmm.mmmm N/S
		//<longitude> - 01344.26820E - format is dddmm.mmmm E/W

		$matched = preg_match("/^(\d{2,3})(\d{2}\.\d{3,4})([NESW])$/", $value, $matches);

		if ($matched) {

			$decdeg = $matches[1] + ($matches[2] / 60);

			if ( ($matches[3] == 'S') || ($matches[3] == 'W') ) {
				$decdeg = -$decdeg;
			}

			$decdeg = round($decdeg, 11);

			return $decdeg;

		} else {
			//throw new Exception('Unable to recognise degrees for conversion');
			return 0;
		}

	}

	static public function utcdate_datetime($date, $utc) {

		//Match date / time parts
		$matched = preg_match("/^(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})\.(\d{3})$/", $date . $utc, $matches);

		if ($matched) {

			$datetime = array();
			list(
				$null,
				$datetime['dd'],
				$datetime['mm'],
				$datetime['yy'],
				$datetime['hh'],
				$datetime['ii'],
				$datetime['ss'],
				$datetime['sss'],

			) = $matches;

			$datetime['yy'] = '20' . $datetime['yy'];

			return "{$datetime['yy']}-{$datetime['mm']}-{$datetime['dd']} {$datetime['hh']}:{$datetime['ii']}:{$datetime['ss']}";

		} else {
			throw new Exception('Unable to recognise UTC date time');
		}

	}

	//Parse position input string
	static public function parseposition($string) {

		$pattern_gpsacp = <<<EOPATTERN
(\d{6}\.\d{3}|)		#UTC Time
,
(\d{4}\.\d{4}[NS]|)	#Latitude
,
(\d{5}\.\d{4}[EW]|)	#Longitude
,
(\d{1,2}\.\d{1}|)	#Horizontal Diluition of Precision
,
((?:\-|)\d+(?:\.\d+|)|)	#Altitude - mean-sea-level in meters
,
([023])				#Fix type (0 => Invalid, 2 => 2D Fix, 3 => 3D Fix)
,
(\d{1,3}\.\d{1,2}|)	#Cource over ground (direction in degrees)
,
(\d{1,4}\.\d{1,2}|)	#Speed over ground (Km per hr)
,
(\d{1,4}\.\d{1,2}|)	#Speed over ground (knots)
,
(\d{6}|)			#Date of fix
,
(\d{2}|)			#Total satellites in use
EOPATTERN;

		//Match pattern
		$match = preg_match('/^'.$pattern_gpsacp.'$/x', $string, $matches);

		//If match found
		if ($match) {

			//Create named array
			$gpsdata = array();
			list(
				$null,
				$gpsdata['utc'],
				$gpsdata['latitude'],
				$gpsdata['longitude'],
				$gpsdata['hdop'],
				$gpsdata['altitude'],
				$gpsdata['fix'],
				$gpsdata['cog'],
				$gpsdata['spkm'],
				$gpsdata['spkn'],
				$gpsdata['date'],
				$gpsdata['nsat'],
				) = $matches;

			return $gpsdata;

		} else {
			throw new Exception("Unable to parse position \"{$string}\"");
		}

	}

	//Parse input string
	static public function parseinput($dataline, $unit_id) {
		global $cfg, $db, $tbl;

		//Parse gps data
		$gpsdata = gpsdata::parseposition($dataline);

		$hash = md5(print_r(array($dataline, $unit_id), true));

		//Parse out datetime
		$datetime = gpsdata::utcdate_datetime($gpsdata['date'], $gpsdata['utc']);
		
		//See if position has already been saved
		$position_result = $db->table_query($db->tbl($tbl['position']), '1', $db->cond(array("hash = '{$hash}'"), 'AND'), '', 0, 1);
		if ( (!($chk_position_record = $db->record_fetch($position_result))) || (strtotime($datetime) < strtotime('2007-01-01')) ) {
		//if (!($chk_position_record = $db->record_fetch($position_result))) {

			$position_record = array(
				'unit_id' => $unit_id,
				'datetime' => $datetime,
				'datetime_received' => $db->datetimenow(),
				'deg' => round($gpsdata['cog'], 1),
				'lat' => gpsdata::degminsec_degdec($gpsdata['latitude']),
				'lon' => gpsdata::degminsec_degdec($gpsdata['longitude']),
				'alt' => $gpsdata['altitude'],
				'fixtype' => $gpsdata['fix'],
				//'' => $gpsdata['hdop'],
				//'speed_km' => round($gpsdata['spkm'], 1),
				'speed_km' => round($gpsdata['spkn']*1.85, 1),
				'speed_kn' => round($gpsdata['spkn'], 1),
				'sattotal' => $gpsdata['nsat'],
				'raw_input' => $dataline,
				'hash' => $hash,
			);

			$db->record_insert($tbl['position'], $db->rec($position_record));

		}

	}

}

?>