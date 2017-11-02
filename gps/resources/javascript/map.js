/* ----- General ----- */

var pospopup_markerinfo = [];
var icons = {};

//Load Map
function loadmap() {

	var maptype = "google";
	//var maptype = "multimap";
	//var maptype = "openstreetmap";
	//var maptype = "openlayers";

	if (maptype == "openlayers") {

		//LatLonPoint.prototype.toOpenLayers = function() {
		   //return new OpenLayers.LonLat(this.lon, this.lat).transform(new OpenLayers.Projection("EPSG:4326"), basemap.getProjectionObject());
		//   return new OpenLayers.LonLat(0.062398, 51.421762).transform(new OpenLayers.Projection("EPSG:4326"), new OpenLayers.Projection("EPSG:900913"));
		//};

		OpenLayers.Util.OSM = {};
		OpenLayers.Util.OSM.MISSING_TILE_URL = "resources/openlayers/404.png";
		OpenLayers.Util.OSM.originalOnImageLoadError = OpenLayers.Util.onImageLoadError;
		OpenLayers.Util.onImageLoadError = function() {
			this.src = OpenLayers.Util.OSM.MISSING_TILE_URL;
		};

		Mapstraction.prototype.addAPI = function(element,api) {
		  this.loaded[api] = false;
		  this.onload[api] = [];
		  var me = this;

			  this.maps[api] = new OpenLayers.Map(
				element.id, 
				{
				  maxExtent: new OpenLayers.Bounds(-20037508.34,-20037508.34,20037508.34,20037508.34),
				  maxResolution:156543, numZoomLevels:18, units:'meters', projection: "EPSG:900913",
				  eventListeners: { "zoomend": openlayerszoomchange }
				}
			  );

			  this.layers['osm'] = new OpenLayers.Layer.TMS(
				'OSM',
				[
					"http://a.tile.openstreetmap.org/"
//					"http://a.tah.openstreetmap.org/Tiles/tile/",
//					"http://b.tah.openstreetmap.org/Tiles/tile/",
//					"http://c.tah.openstreetmap.org/Tiles/tile/"
					//"http://server/code/openlayerstest/Tiles/"
					//"http://server/code/openlayerstest/Kosmos/Console/Tiles/"
				],
				{
				  type:'png', 
				  getURL: function (bounds) {
					var res = this.map.getResolution();
					var x = Math.round ((bounds.left - this.maxExtent.left) / (res * this.tileSize.w));
					var y = Math.round ((this.maxExtent.top - bounds.top) / (res * this.tileSize.h));
					var z = this.map.getZoom();
					var limit = Math.pow(2, z);    
					if (y < 0 || y >= limit) {
					  return null;
					} else {
					  x = ((x % limit) + limit) % limit;
					  var path = z + "/" + x + "/" + y + "." + this.type;
					  var url = this.url;
					  if (url instanceof Array) {
						url = this.selectUrl(path, url);
					  }
					  return url + path;
					}
				   }, 
				   displayOutsideMaxExtent: true
				 }
			   );

			  this.maps[api].addLayer(this.layers['osm']);
			  this.loaded[api] = true;

		};

	}

	mapstraction = new Mapstraction("map", maptype);
	//mapstraction = new Mapstraction("map", "google");
	//mapstraction = new Mapstraction("map", "multimap");
	//mapstraction = new Mapstraction("map", "openstreetmap");
	//mapstraction = new Mapstraction("map", "openlayers");

	if (mapstraction.api == "google") {
		window.onunload = GUnload;
	}

	basemap = mapstraction.getMap();

	mapstraction.setCenterAndZoom(new LatLonPoint(map_default_lat, map_default_lon), map_default_zoom);

	var maptype = (mapstraction.api == "openlayers") ? false : true;

	mapstraction.addControls({
		pan: true,
		zoom: "large",
		scale: true,
		map_type: maptype
	});

	if (mapstraction.api == "google") {
		basemap.addMapType(G_PHYSICAL_MAP);
		basemap.addMapType(G_SATELLITE_3D_MAP);
		basemap.enableScrollWheelZoom();
		new GKeyboardHandler(basemap);
	} else if (mapstraction.api == "multimap") {
		basemap.setOption("keyboard", "pan");
		basemap.setOption("mousewheel:wheelup" , "zoomin");
		basemap.setOption("mousewheel:wheeldown", "zoomout");
	}

}

//Init icons
function init_icons() {

	//Unit icons
	icons["unit"] = new Array();

	for (i in uniticonpaths) {

		icons["unit"][i] = {
			"iconsize": [32, 32],
			"iconanchor": [16, 16],
			//"infowindowanchor": [16, 1],
			"image": uniticonpaths[i]
		};

	}

	//Journey inbetween arrow
	if (line_color_id != undefined) {

		icons["arrow"] = new Array();
		icons["arrow"][line_color_id] = new Array();
		
		for (var i=0; i<360; i+=45) {

			icons["arrow"][line_color_id][i] = {
				"iconsize": [16, 16],
				"iconanchor": [8, 8],
				//"infowindowanchor": [8, 1],
				"image": "resources/images/map/arrow/" + line_color_id + "/" + i + ".png"
			};

		}

	}

	/*
	//Start journey icon
	icons["start"] = {
		"iconsize": [20, 34],
		"iconanchor": [10, 34],
		//"infowindowanchor": [, ],
		"image": "http://www.google.com/mapfiles/dd-start.png"
	};

	//End journey icon
	icons["end"] = {
		"iconsize": [20, 34],
		"iconanchor": [10, 34],
		//"infowindowanchor": [10, ],
		"image": "http://www.google.com/mapfiles/dd-end.png"
	};
	*/

	//Start journey icon
	icons["start"] = {
		"iconsize": [21, 25],
		"iconanchor": [10, 34],
		//"infowindowanchor": [, ],
		"image": "resources/openlayers/img/marker-green.png"
	};

	//End journey icon
	icons["end"] = {
		"iconsize": [21, 25],
		"iconanchor": [10, 25],
		//"infowindowanchor": [10, ],
		"image": "resources/openlayers/img/marker.png"
	};

}

//Init general
function initgeneral() {

	//Multimap marker click handling
	if (mapstraction.api == "multimap") {

		/*
		basemap.addEventHandler("click", function(type, target) {
			if (target instanceof MMMarkerOverlay) {
				alert(target.mid);
			}
		});
		*/

		basemap.addEventHandler("click", multimapmarkerclicked);

	}

}

//Load marker html info
function googlemarkerclicked() {

	var currmarker = this;

	var markerid = this.mid;

	//Handle marker click
	markerclicked(markerid, currmarker);

}

//Multimap marker clicked
function multimapmarkerclicked(type, target) {

	if (target instanceof MMMarkerOverlay) {

		//Handle marker click
		markerclicked(target.mid, target);

	}

}

//Openlayers marker clicked
function openlayersmarkerclicked(event) {

	var currmarker = this;

	var markerid = this.mid;

	//Handle marker click
	markerclicked(markerid, currmarker);

}

//Load marker html info
function markerclicked(markerid, markerele) {

	if (pospopup_markerinfo[markerid] == undefined) {

		try {
			
			nocache_urladdon = "&nocache=" + new Date().getTime();
			geturl = baseurl + "&t=positionpopup&marker=" + markerid + nocache_urladdon;
			
			var loader = new net.ContentLoader(geturl, function() {
				eval("var jsondata = " + this.req.responseText);

				pospopup_markerinfo[markerid] = jsondata;
				
				//Open marker info window
				openmarkerinfowindow(markerele, markerid);

			});

		} catch (e) {
			alert(e.message + "\nWas trying to add marker info for specified marker Rka" + markerid + ", try clicking refresh");
		}

	} else {

		//Open marker info window
		openmarkerinfowindow(markerele, markerid);

	}

}

//Open marker info window
function openmarkerinfowindow(markerele, markerid) {

	//Google marker click handling
	if (mapstraction.api == "google") {

		markerele.openInfoWindowHtml(genmarkerinfohtml(pospopup_markerinfo[markerid]));

	} else if (mapstraction.api == "multimap") {

		markerele.openInfoBox(genmarkerinfohtml(pospopup_markerinfo[markerid]));

	} else if (mapstraction.api == "openlayers") {

		if (markerele.popup == null) {



//??????????????????????????????????????????????????????????????

			var point = new OpenLayers.LonLat(90, 90);

			var popup = new OpenLayers.Popup.AnchoredBubble(
				null,
				point,
				new OpenLayers.Size(200, 120),
				genmarkerinfohtml(pospopup_markerinfo[markerid]),
				null,
				true
			);

			markerele.popup = popup;

			basemap.addPopup(popup);

		} else {
			markerele.popup.toggle();
		}

	}

}

//Generate marker info html
function genmarkerinfohtml(markerdata) {

	var baloonhtml = "";
	
	baloonhtml += "<div class=\"baloonhtml\">\n";
	baloonhtml += "<div class=\"baloontitle\">Date:</div>" + markerdata["datetime"] + "<br />\n";
	baloonhtml += "<div class=\"baloontitle\">Name:</div>" + markerdata["name"] + "<br />\n";
	baloonhtml += "<div class=\"baloontitle\">Location:</div>" + markerdata["raw_input"] + "<br />\n";
	baloonhtml += "<div class=\"baloontitle\">Mobile:</div>" + markerdata["imei"] + "<br />\n";
	baloonhtml += "<div class=\"baloontitle\">Lat:</div>" + markerdata["lat"] + "<br />\n";
	baloonhtml += "<div class=\"baloontitle\">Lon:</div>" + markerdata["lon"] + "<br />\n";
	baloonhtml += "<div class=\"baloontitle\">View on:</div>" + "<a target=\"_blank\" href=\"http://maps.google.co.uk/maps?f=q&hl=en&q=" + markerdata["lat"] + "," + markerdata["lon"] + "\">Google</a> | <a target=\"_blank\" href=\"http://www.multimap.com/maps/#t=l&map=" + markerdata["lat"] + "," + markerdata["lon"] + "|14|4&loc=GB:" + markerdata["lat"] + ":" + markerdata["lon"] + ":14|" + markerdata["lat"] + "," + markerdata["lon"] + "|Lat:%20" + markerdata["lat"] + ",%20Lon:%20" + markerdata["lon"] + "\">Multimap</a><br />\n";
	baloonhtml += "</div>";

	return baloonhtml;

}


/* ----- Live Map ----- */

//Vars
var updatetimeout_live;
var trackerunitchk;
//var trackerunitmarker = {};
//var trackerunitactive = [];
var trackerunitlive = [];
//var lastid;

//Check / uncheck all unit checkboxes
function unit_chkboxchange(status) {
	for (i in trackerunitchk) {
		$(trackerunitchk[i]).checked = status;
	}
}

//Change checkbox
function checkboxchange(chckboxid) {
	if ($(chckboxid).checked == true) {
		$(chckboxid).checked = false;
	} else {
		$(chckboxid).checked = true;
	}
	
}

//Change autoupdate status
function autoupdatechanged(autoupdate) {

	//Clear timer
	if (updatetimeout_live) {
		window.clearTimeout(updatetimeout_live);
	}

	//If checked
	if (radiogetselected("autoupdate") == "1") {

		//Update display / restart timer
		updatedisplay();

	}

}

//Update display
function updatedisplay() {

	try {

		nocache_urladdon = "&nocache=" + new Date().getTime();
		geturl = baseurl + "&t=liveupdate" + nocache_urladdon;
		var loader = new net.ContentLoader(geturl, function() {
			eval("var jsondata = " + this.req.responseText);

			//Update current position data
			currpositions = jsondata;

			//Update markers
			placeliveicons();

		});

		//If checked
		if (radiogetselected("autoupdate") == "1") {
			//Set display to autoupdate
			updatetimeout_live = window.setTimeout("updatedisplay();", dispupd_interval);
		}

	} catch (e) {
		alert(e.message + "\nWas trying to retrieve new data, try clicking refresh");
	}

}

//Init live icons
function init_liveicons() {

	//Go through units
	for (unit in unitinfo) {

		//Add on extra fields
		unitinfo[unit]["lat"] = undefined;
		unitinfo[unit]["lon"] = undefined;
		unitinfo[unit]["marker"] = undefined;

	}

}

//Place live icons
function placeliveicons() {

	var recenter_required = false;
	


	var track_count = 0;
	
for (unit in unitinfo) {

	if ($("trackerunit_" + unit).checked) {
		track_count = track_count + 1;
		
	}
	
}

	//Go through units
	for (unit in unitinfo) {

		//If unit should be shown
		if ($("trackerunit_" + unit).checked) {

			//If have data for the unit
			if (currpositions["livepos"][unit] != undefined) {

				var point = new LatLonPoint(currpositions["livepos"][unit]["lat"], currpositions["livepos"][unit]["lon"]);
				var unitnotmoved = point.equals(new LatLonPoint(unitinfo[unit]["lat"], unitinfo[unit]["lon"]));
				
				//if (track_count == 1) {
							
				//addpolylinecenterzoomall_live(point,line_color);
				//addmmangerforzoom(mapstraction.getZoom());
				//}
			
				//If unit marker is currently hidden or is shown but has has moved
				if ( (unitinfo[unit]["marker"] == undefined) || ((unitinfo[unit]["marker"] != undefined) && (unitnotmoved == false)) ) {

					//If marker currently shown, remove it
					if (unitinfo[unit]["marker"] != undefined) {
						mapstraction.removeMarker(unitinfo[unit]["marker"]);
						unitinfo[unit]["marker"] = undefined;
					}

					//Create point
					var point = new LatLonPoint(currpositions["livepos"][unit]["lat"], currpositions["livepos"][unit]["lon"]);

					//Icon id
					var iconid = unitinfo[unit]["icon"];

					//Create marker
					var marker = new Marker(point);

					//Set marker icon
					if (mapstraction.api == "openlayers") {
						//Mapstraction seems to have a bug, openlayers required negative anchor position
						var iconanchor = [
							-icons["unit"][iconid]["iconanchor"][0],
							-icons["unit"][iconid]["iconanchor"][1]
						];
						marker.setIcon(icons["unit"][iconid]["image"], icons["unit"][iconid]["iconsize"], iconanchor);
					} else {
						marker.setIcon(icons["unit"][iconid]["image"], icons["unit"][iconid]["iconsize"], icons["unit"][iconid]["iconanchor"]);
					}
					marker.setShadowIcon(icons["unit"][iconid]["image"], icons["unit"][iconid]["iconsize"]);

					//Add marker to map
					mapstraction.addMarker(marker);

					if (mapstraction.api == "google") {

						marker.proprietary_marker.mid = currpositions["livepos"][unit]["id"];

						//GEvent.addListener(marker.proprietary_marker, "mousedown", function() {alert(99)});
						GEvent.addListener(marker.proprietary_marker, "mousedown", googlemarkerclicked);

					} else if (mapstraction.api == "multimap") {
						marker.proprietary_marker.mid = currpositions["livepos"][unit]["id"];

						marker.proprietary_marker.text = "aaaaaaaaaaaaaaaa";

					} else if (mapstraction.api == "openlayers") {
						marker.proprietary_marker.mid = currpositions["livepos"][unit]["id"];
						marker.proprietary_marker.events.register("mousedown", marker.proprietary_marker, openlayersmarkerclicked);
					}

					//Save marker back to unit
					unitinfo[unit]["marker"] = marker;

					//Save new lat/lon back to unit
					unitinfo[unit]["lat"] = currpositions["livepos"][unit]["lat"];
					unitinfo[unit]["lon"] = currpositions["livepos"][unit]["lon"];

					//Recenter required
					recenter_required = true;

					/*
					var lonLat = new OpenLayers.LonLat(currpositions["livepos"][unit]["lon"], currpositions["livepos"][unit]["lat"]).transform(new OpenLayers.Projection("EPSG:4326"), basemap.getProjectionObject());

					var markers = new OpenLayers.Layer.Markers( "Markers" );
					basemap.addLayer(markers);

					var size = new OpenLayers.Size(10,17);
					var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);
					var icon = new OpenLayers.Icon('http://boston.openguides.org/markers/AQUA.png',size,offset);
					markers.addMarker(new OpenLayers.Marker(lonLat,icon));
					*/

				}
				if (track_count == 1) {
					
					addpolylinecenterzoomall_live(point,line_color);			
				} 
				else {
					points = [];
					mapstraction.removeAllPolylines();
					
				}
				
			}

		} else {
			//Unit should not be shown

			//If unit marker is currently shown
			if (unitinfo[unit]["marker"] != undefined) {

				//Remove marker
				mapstraction.removeMarker(unitinfo[unit]["marker"]);
				unitinfo[unit]["marker"] = undefined;

				//Recenter required
				recenter_required = true;
				points = [];
				mapstraction.removeAllPolylines();
				
			}

		}

	}

	//If icons were moved / newly shown
	if (recenter_required == true) {

		//Recenter map on icons
		centerlivemap();

	}

}

//Center live map on shown icons
function centerlivemap() {

	var havemarkers = false;

	//Go through units
	for (unit in unitinfo) {

		//If unit marker is currently hidden or is shown but has has moved
		if (unitinfo[unit]["marker"] != undefined) {
			havemarkers = true;
		}

	}

	//If there are markers
	if (havemarkers) {
		//Auto center and zoom on markers
		mapstraction.autoCenterAndZoom();
		
	}

}

//Show / hide live icon
function showhideliveicon(unit, statushow) {

	//ignore args

	//Go through all icons hide/show as needed depending on checkbox status
	placeliveicons();

}

//Show / hide all live icon
function showallliveicon(statushow) {

	//ignore args

	//Go through all icons hide/show as needed depending on checkbox status
	placeliveicons();

}


/* ----- History Map ----- */

var trackhist_avail = {};
var markermng_zoomlevel = new Array();

//Select history tracker
function selecthisttracker(unit_id) {
	document.location.href = currpageunit_starturl + "&unit=" + unit_id;
}

//Add a polyline, then center and zoom to it
function addpolylinecenterzoomall(polyline_points, color) {

	//Go through lat/lon cords
 
	var points = [];
	for (var line in polyline_points) {

		//Place point on map
		var point = new LatLonPoint(polyline_points[line][0], polyline_points[line][1]);
		
		points.push(point);

	}

	//If there are points
	if (points.length > 0) {

		//Init polyline
		var polyline = new Polyline(points);

		//Set properties
		polyline.setColor(color);
		polyline.setOpacity(0.5);
        polyline.setWidth(2);
		if (mapstraction.api == "openlayers") {
			polyline.setWidth(5);
		}

		//Add polyline
		mapstraction.addPolyline(polyline);

		//Center and zoom on points (polyline)
		//done here, because it only needs doing once, and the line is only drawn once
		mapstraction.centerAndZoomOnPoints(points);

		if (mapstraction.api == "google") {
			basemap.savePosition();
		}

	}


}

////////////////////
function addpolylinecenterzoomall_live(point, color) {

	//Go through lat/lon cords

	// points = [];
	

		//Place point on map
		
		
		points.push(point);

	//If there are points
	if (points.length > 0) {

		//Init polyline
		var polyline = new Polyline(points);

		//Set properties
		polyline.setColor(color);
		polyline.setOpacity(0.5);
        polyline.setWidth(2);
		if (mapstraction.api == "openlayers") {
			polyline.setWidth(5);
		}

		//Add polyline
		mapstraction.addPolyline(polyline);

		//Center and zoom on points (polyline)
		//done here, because it only needs doing once, and the line is only drawn once
		//mapstraction.centerAndZoomOnPoints(points);

	//	if (mapstraction.api == "google") {
		//	basemap.savePosition();
		//}

	}

}

/////////////////////

//Zoom end event fired
function evtzoomend(oldzoom, newzoom) {

	//Handle zoom change
	handlezoomchange(newzoom);

}

//Zoom end event fired
function multimapzoomchange(type, target, oldzoom, newzoom) {

	//Handle zoom change
	handlezoomchange(newzoom);

}

//Open layers end zoom eent fired
function openlayerszoomchange(event) {

	if ( (currtrackerunit > 0) && (currdate != undefined) ) {

		//If on history page
		if (handlezoomchangeonmap == true) {

			//Handle zoom change
			handlezoomchange(mapstraction.getZoom());

		}

	}

}

//Handle zoom change
function handlezoomchange(zoomlevel) {

	//If marker manager for specified zoom level is not already created, create it
	if (inarray(markermng_zoomlevel, zoomlevel) == false) {

		//Add marker manager for specified zoom level
		addmmangerforzoom(zoomlevel);

	} else {

		//Show markers only for specifed zoom level
		markerszoomlevel(zoomlevel);

	}

}

//Show markers only for specifed zoom level
function markerszoomlevel(zoomlevel) {

	//Show markers only for specified filter level
	mapstraction.removeAllFilters();
	mapstraction.addFilter("zoomlevel", "le", zoomlevel);
	mapstraction.addFilter("zoomlevel", "ge", zoomlevel);
	mapstraction.doFilter();

}

//Add marker manager for specified zoom level
function addmmangerforzoom(zoomlevel) {

	try {

		nocache_urladdon = "&nocache=" + new Date().getTime();
		
		geturl = baseurl + "&t=markerdata&unit=" +  currtrackerunit + "&date=" + currdate + "&zoomlevel=" + zoomlevel + nocache_urladdon;
		var loader = new net.ContentLoader(geturl, function() {
			eval("var jsondata = " + this.req.responseText);

			//Go through lat/lon cords
			var gmarkers = [];
			var i = 0;
			var totalicon = jsondata["data"].length;
			for (var markerarr in jsondata["data"]) {

				//Choose icon
				var icon = chooseicon("archive", i, jsondata["data"][markerarr][3], totalicon);

				//Create point
				var point = new LatLonPoint(jsondata["data"][markerarr][1], jsondata["data"][markerarr][2]);

				
				//Create marker
				var marker = new Marker(point);

				//Set zoom level to show this marker
				marker.setAttribute("zoomlevel", zoomlevel);

				//Set marker icon

				if (mapstraction.api == "openlayers") {
					//Mapstraction seems to have a bug, openlayers required negative anchor position
					var iconanchor = [
						-icon["iconanchor"][0],
						-icon["iconanchor"][1]
					];
					marker.setIcon(icon["image"], icon["iconsize"], iconanchor);
				} else {
					marker.setIcon(icon["image"], icon["iconsize"], icon["iconanchor"]);
				}

				marker.setShadowIcon(icon["image"], icon["iconsize"]);

				//Add marker to map
				mapstraction.addMarker(marker);

				if (mapstraction.api == "google") {
					marker.proprietary_marker.mid = jsondata["data"][markerarr][0];
					GEvent.addListener(marker.proprietary_marker, "mousedown", googlemarkerclicked);
				} else if (mapstraction.api == "multimap") {
					marker.proprietary_marker.mid = jsondata["data"][markerarr][0];
				} else if (mapstraction.api == "openlayers") {
					marker.proprietary_marker.mid = jsondata["data"][markerarr][0];
					marker.proprietary_marker.Lon = jsondata["data"][markerarr][2];
					marker.proprietary_marker.Lat = jsondata["data"][markerarr][1];
					marker.proprietary_marker.events.register("mousedown", marker.proprietary_marker, openlayersmarkerclicked);
				}
/*
            //anchored bubble popup small contents autosize closebox
            ll = new OpenLayers.LonLat(-35,0);
            popupClass = AutoSizeAnchoredBubble;
            popupContentHTML = '<img src="small.jpg"></img>';
            addMarker(ll, popupClass, popupContentHTML, true, true);
*/








				i++;

			}

			//Add to list of marker managers allready created
			markermng_zoomlevel.push(zoomlevel);

			//Show markers only for specifed zoom level
			markerszoomlevel(zoomlevel);

		});

	} catch (e) {
		alert(e.message + "\nWas trying to add marker manager for specified zoom level " + zoomlevel + ", try clicking refresh");
	}

}

//Choose icon
function chooseicon(type, count, dir, totalicon) {

	//Choose icon
	var icon;

	if (count == 0) {
		icon = icons["start"];
	} else if (count+1 == totalicon) {
		icon = icons["end"];
	} else {

		//Round direction to be out of 10
		dir = Math.round(dir/45) * 45;
		dir = (dir == 360) ? 0 : dir;

		icon = icons["arrow"][line_color_id][dir];

	}

	return icon;

}
