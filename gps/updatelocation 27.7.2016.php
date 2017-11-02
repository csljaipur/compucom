<?php
    include 'dbconnect.php';
 

date_default_timezone_set('Asia/Calcutta');

	function getaddress($lat,$lng)
	{
     $url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($lat).','.trim($lng).'&sensor=false';
     $json = @file_get_contents($url);
     $data=json_decode($json);
     $status = $data->status;
     if($status=="OK")
     {
       return $data->results[0]->formatted_address;
     }
     else
     {
       return false;
     }
	}

 
 
 
 
 
    $latitude       = isset($_GET['latitude']) ? $_GET['latitude'] : '0';
    $latitude       = (float)str_replace(",", ".", $latitude); // to handle European locale decimals
    $longitude      = isset($_GET['longitude']) ? $_GET['longitude'] : '0';
    $longitude      = (float)str_replace(",", ".", $longitude);    
    $speed          = isset($_GET['speed']) ? $_GET['speed'] : 0;
   // $direction      = isset($_GET['direction']) ? $_GET['direction'] : 0;
   // $distance       = isset($_GET['distance']) ? $_GET['distance'] : '0';
  //  $distance       = (float)str_replace(",", ".", $distance);
    $date           = isset($_GET['date']) ? $_GET['date'] : '0000-00-00 00:00:00';
    $date           = urldecode($date);
   // $locationmethod = isset($_GET['locationmethod']) ? $_GET['locationmethod'] : '';
 //   $locationmethod = urldecode($locationmethod);
    $username       = isset($_GET['username']) ? $_GET['username'] : 0;
   // $phonenumber    = isset($_GET['phonenumber']) ? $_GET['phonenumber'] : '';
  //  $sessionid      = isset($_GET['sessionid']) ? $_GET['sessionid'] : 0;
  //  $accuracy       = isset($_GET['accuracy']) ? $_GET['accuracy'] : 0;
    $extrainfo      = isset($_GET['extrainfo']) ? $_GET['extrainfo'] : '';
   // $eventtype      = isset($_GET['eventtype']) ? $_GET['eventtype'] : '';
    //$latitude       = 47.6273270;
	//$longitude      = -122.3256910;
	
    // doing some validation here
	
	
	$alt=0;
  $deg=0;
  $speed_kn=0;
  $sattotal=0;
  $fixtype=3;
  $hash="0";
  
	
    if ($latitude == 0 && $longitude == 0) {
        exit('-1');
    }

	 $address = getaddress($latitude,$longitude);
	if($address)
	{
		$extrainfo =  $address;
	}
	else
	{
		$extrainfo =  "UNKNOWN";
	}
	//echo $extrainfo ;

	
    $params = array(':lat'        => $latitude,
                    ':lon'       => $longitude,
                    ':speed_km'           => $speed,
                    ':datetime'            => $date,
					':datetime_received'   => $date,
                    ':unit_id'        => $username,
                    ':raw_input'       => $extrainfo,
					':alt'       => $alt,
					':deg'       => $deg,
					':speed_kn'       => $speed_kn,
					':sattotal'       => $sattotal,
					':fixtype'       => $fixtype,
					':hash'       => $hash
                   
                );

 
            $stmt = $pdo->prepare( $sqlFunctionCallMethod.'prcSavePTPosition(
                          :lat, 
                          :lon, 
                          :speed_km, 
                          :datetime, 
                          :datetime_received, 
                          :unit_id, 
                          :raw_input,
						  :alt,
						  :deg,
						  :speed_kn,
						  :sattotal,
						  :fixtype,
						  :hash);'
             );
    $stmt->execute($params);
    $timestamp = $stmt->fetchColumn();
	
    echo $timestamp;    
?>
