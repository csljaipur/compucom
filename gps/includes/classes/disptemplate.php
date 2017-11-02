<?php

class disptemplate {

	static public function mapsheaderaddin() {
		global $cfg;

		return <<<EOHTML

	<style type="text/css">
		v\:* {
			behavior:url(#default#VML);
		}
	</style>

	<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key={$cfg['googlemap_api_key']}" type="text/javascript"></script>

	<!--<script type="text/javascript" src="http://developer.multimap.com/API/maps/1.2/licensekey"></script>-->

	<!--<script src="resources/openlayers/OpenLayers.js" type="text/javascript"></script>-->

	<script src="resources/javascript/mapstraction.js" language="javascript" type="text/javascript"></script>

	<script src="resources/javascript/map.js" language="javascript" type="text/javascript"></script>

EOHTML;

	}

	static public function uniticonpaths_js() {
		global $cfg;

		$icons = array();
		$icons_js = '';
		foreach ($cfg['icon'] as $iconid => $icondata) {
			$icons_js .= <<<EOJS
{$iconid}: "{$icondata['img']['map']}", 
EOJS;
		}

		$icons_js = rtrim($icons_js, ", ");

		return '{' . $icons_js . '}';

	}

}

?>