<?php

class appgeneral {

	static public function distance_latlon($lat1, $lon1, $lat2, $lon2) {

		/*
		$lat1='54.33273';
		$lon1='1.893523';
		$lat2='52.33123';
		$lon2='1.133743';
		*/

		$theta = $lon1 - $lon2;
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
		$dist = ("{$dist}" === "1") ? 1 : $dist; //Fix odd bug?
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$miles = $dist * 60 * 1.1515;

		return $miles;

	}

	static public function utcdatetime_tolocal($datetime) {
		return date('Y-m-d H:i:s', strtotime($datetime . ' UTC'));
		
	}

	static public function trim_length($string, $trimlen) {
		if (strlen($string) > $trimlen) {
			 return substr($string, 0, $trimlen).'...';
		} else {
			return $string;
		}
	}

}

?>