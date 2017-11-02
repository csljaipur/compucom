<?php

//http://www.postcodeanywhere.co.uk/developers/documentation/maps/component.aspx?component=DrawMapByCoordinates

//Postcode anywhere
class postcodeanywhere {

	private $latitude;
	private $longitude;

	private $width;
	private $height;

	private $zoom;

	private $cachemap_name;
	private $cachestatus;

	static $cachemap_ext = '.gif';

	static $availstyle = array(
		0 => array(
			'name' => 'Classic',
			'codeid' => 'CLASSIC',
		),
		1 => array(
			'name' => 'Contemporary',
			'codeid' => 'CONTEMPORARY',
		),
		2 => array(
			'name' => 'Objective',
			'codeid' => 'OBJECTIVE2',
		),
	);

	//Retrieve cache status
	function retrievecachestatus() {
		return $this->cachestatus;
	}

	//Set lat/lon
	function setlatlon($latitude, $longitude) {
		$this->latitude = round($latitude, 4);
		$this->longitude = round($longitude, 4);
	}

	//Set width/height
	function setwidthheight($width, $height) {

		$width = intval($width);
		$height = intval($height);

		if (!( ($width >= 10) && ($width <= 300) )) {
			throw new Exception("Width of \"{$width}\" not valid");
		}

		if (!( ($height >= 10) && ($height <= 300) )) {
			throw new Exception("Width of \"{$width}\" not valid");
		}

		$this->width = $width;
		$this->height = $height;

	}

	//Set style
	function setstyle($style) {

		//Check style valid
		if (!in_array($style, array_keys(self::$availstyle))) {
			throw new Exception("Style of \"{$style}\" not valid");
		}

		$this->style = $style;

	}

	//Set zoom
	function setzoom($zoom) {

		$zoom = intval($zoom);

		//Check zoom valid
		if (!( ($zoom >= 1) && ($zoom <= 10) )) {
			throw new Exception("Zoom of \"{$zoom}\" is not 1-10");
		}

		$this->zoom = $zoom;

	}

	//Generate cache path
	function generatecachename() {
		$this->cachemap_name = md5($this->latitude . '-' . $this->longitude . '-' . $this->width . '-' . $this->height . '-' . $this->style . '-' . $this->zoom);
	}

	//Check cache for map
	function checkcache() {
		global $cfg;

		$cachedmap_path = $cfg['pca_map_cache_path'] . $this->cachemap_name . self::$cachemap_ext;
		if (file_exists($cachedmap_path)) {
			$status = true;
		} else {
			$status = false;
		}

		$this->cachestatus = $status;

	}

	//Save map to cache
	function savemapcache() {
		global $cfg;

		$cachedmap_path = $cfg['pca_map_cache_path'] . $this->cachemap_name . self::$cachemap_ext;

		$mapdata = $this->retrieve_map();

		$file = fopen($cachedmap_path, 'wb');
		flock($file, LOCK_EX);
		fwrite($file, base64_decode($mapdata[0]['image']));
		fclose($file);

	}

	//Retrieve map
	function retrieve_map() {
		global $cfg;

		//Build the url
		$url = 'http://services.postcodeanywhere.co.uk/xml.aspx?';
		$url .= '&action=map';
		$url .= '&longitude=' . urlencode($this->longitude);
		$url .= '&latitude=' . urlencode($this->latitude);
		$url .= '&datum=' . urlencode('WGS84');
		$url .= '&width=' . urlencode($this->width);
		$url .= '&height=' . urlencode($this->height);
		$url .= '&scale=' . intval($this->zoom);
		//$url .= '&pixel_size=' . urlencode('');
		$url .= '&style=' . urlencode(self::$availstyle[$this->style]['codeid']); //POSTCODE | CLASSIC | CONTEMPORARY | OBJECTIVE2
		$url .= '&image_format=' . urlencode('GIF'); //GIF | JPEG | PNG | BMP
		$url .= '&account_code=' . urlencode($cfg['postcodeanywhere']['account_code']);
		$url .= '&license_code=' . urlencode($cfg['postcodeanywhere']['license_code']);

		//Request map
		$httprequest = new httprequest();
		$httprequest->seturl($url);
		//$httprequest->setuseragent($cfg['site_name']);
		$httprequest->send();
		if (!($httprequest->requestsuccess())) {
			$errormsg = $httprequest->geterrormsg();
			throw new Exception('Curl reported error requesting map: ' . $errormsg);
		}

		//Make the request
		$data = simplexml_load_string($httprequest->gethttpdata());

		//Check for an error
		if ($data->Schema['Items'] == 2) {
			throw new exception('Postcode Anywhere: ' . $data->Data->Item['message']);
		}

		//Create the response
		foreach ($data->Data->children() as $row) {
			$rowitems = '';
			foreach($row->attributes() as $key => $value) {
				$rowitems[$key] = strval($value);
			}
			$output[] = $rowitems;
		}

		return $output;

	}

	//Display image
	function display() {
		global $cfg;

		$cachedmap_path = $cfg['pca_map_cache_path'] . $this->cachemap_name . self::$cachemap_ext;

		header('Content-Type: image/gif');
		header('Content-Length: ' . filesize($cachedmap_path));

		readfile($cachedmap_path);

	}


}

?>