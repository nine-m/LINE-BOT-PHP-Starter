<?php
$map_api_url = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=true&region=th&language=th&latlng=';
$map_addr_json = file_get_contents($map_api_url.'13.7213727,100.523901');
$map_addr = json_decode($map_addr_json);

//var_dump(json_decode($map_addr_json));

echo $map_addr->results[0]->formatted_address;

//$addr = $map_addr['result']['formatted_address'];

//echo $addr;

?>