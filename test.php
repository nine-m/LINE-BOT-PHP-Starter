<?php
$mlab_api_key = 'wGv_MG_7RHOetlGwfsSENc5p-A2J9LcC';
$car_no = "L-001";
$mlab_json = file_get_contents('https://api.mlab.com/api/1/databases/nine-m/collections/car_location?apiKey='.$mlab_api_key.'&q={"car_no":"'.$car_no.'"}');
$mlab_data = json_decode($mlab_json);
$isData = sizeof($mlab_data);

$map_api_url = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=true&region=th&language=th&latlng=';
$map_dist_url = 'https://maps.googleapis.com/maps/api/distancematrix/json?language=th';

if($isData > 0){
    foreach($mlab_data as $rec){
        $map_addr_json = file_get_contents($map_api_url.$rec->lat.','.$rec->long);
        $map_addr_data = json_decode($map_addr_json);
        
        $answer = 'รถหมายเลข '.$rec->car_no.' วิ่งอยู่ที่ '.$map_addr_data->results[0]->formatted_address.' ด้วยความเร็ว '.$rec->car_speed.' กม/ชม';


        $car_dest_json = file_get_contents('https://api.mlab.com/api/1/databases/nine-m/collections/car_dest?apiKey='.$mlab_api_key.'&q={"car_no":"'.$car_no.'"}');
        $car_dest_data = json_decode($car_dest_json);
        if (sizeof($car_dest_data) > 0){
             $answer2 = "";
             foreach ($car_dest_data[0]->dest as $rec2){
                 //var_dump($rec2);
                 $map_dist_json = file_get_contents($map_dist_url.'&origins='.$rec->lat.','.$rec->long.'&destinations='.$rec2->latlng);
                 $map_dist_data = json_decode( $map_dist_json);
                 $answer2 = $answer2." รถคันนี้จะถึงปลายทางที่ ".$rec2->id.": ".$map_dist_data->destination_addresses[0].' ในอีก '.$map_dist_data->rows[0]->elements[0]->duration->text; 
             };
        };

        //$map_dist_json = file_get_contents($map_dist_url.'&origins='.$rec->lat.','.$rec->long.'&destinations=19.0000,100.10000');
       // $map_dist_data = json_decode( $map_dist_json);
        
      //  $answer2 = "รถคันนี้จะถึงปลายทางที่ 1: ".$map_dist_data->destination_addresses[0].' ในอีก '.$map_dist_data->rows[0]->elements[0]->duration->text;

        //$bot->replyMessageNew($bot->replyToken, $answer);
        //$bot->replyLocation($rec->car_no,$map_addr_data->results[0]->formatted_address,$rec->lat,$rec->long);
    }
} else {
    $answer = "ไม่พบรถหมายเลข ".$car_no.' ในระบบ';
    //$bot->replyMessageNew($bot->replyToken, $answer);
}  

echo $answer;
echo $answer2;


//$map_api_url = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=true&region=th&language=th&latlng=';
//$map_addr_json = file_get_contents($map_api_url.'13.7213727,100.523901');
//$map_addr = json_decode($map_addr_json);

//var_dump(json_decode($map_addr_json));

//echo $map_addr->results[0]->formatted_address;

//$addr = $map_addr['result']['formatted_address'];

//echo $addr;

?>