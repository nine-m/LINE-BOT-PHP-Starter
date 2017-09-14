<?php
include ('line-bot.php');

$channelSecret = '8c8824d765fe14b40b05dc83294764a5';
$access_token  = 'QDd6Ba0wLQlflnU/kxWlc3rGTLBX/ioj+2T/SfAp8oxT/1p2sEZoOLhXJAL21PxO2hw+TCQ+Xlqhr/aGozTEsZRf7yDIwV+j/hKYlIxpBDUmnOE3IQIXzbY8BY12i66iaKVRzuJ6Z3WYnxIA27xSdwdB04t89/1O/w1cDnyilFU=';
$proxy = 'velodrome.usefixie.com:80';
$proxyauth = 'fixie:NnwPBqm5tR6Jtx5';


$bot = new BOT_API($channelSecret, $access_token);

//mlab config
$mlab_api_key = 'wGv_MG_7RHOetlGwfsSENc5p-A2J9LcC';
$mlab_url = 'https://api.mlab.com/api/1/databases/nine-m/collections/linebot?apiKey='.$mlab_api_key.'';

$map_api_url = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=true&region=th&language=th&latlng=';
$map_dist_url = 'https://maps.googleapis.com/maps/api/distancematrix/json?language=th';
	
if (!empty($bot->isEvents)) {

    if($bot->isText){
        //หารูปแบบการตอบกลับ (Text,Location) โดยดูจาก keyword
        if (strpos($bot->text, 'ค้นหารถ') !== false) {
            $pieces = explode(" ",$bot->text);
            $car_no = $pieces[1];

            $mlab_json = file_get_contents('https://api.mlab.com/api/1/databases/nine-m/collections/car_location?apiKey='.$mlab_api_key.'&q={"car_no":"'.$car_no.'"}');
            $mlab_data = json_decode($mlab_json);
            $isData = sizeof($mlab_data);

            if($isData > 0){
                foreach($mlab_data as $rec){
                    $map_addr_json = file_get_contents($map_api_url.$rec->lat.','.$rec->long);
                    $map_addr_data = json_decode($map_addr_json);
                    
                    $answer = 'รถหมายเลข '.$rec->car_no.' วิ่งอยู่ที่ '.$map_addr_data->results[0]->formatted_address.' ด้วยความเร็ว '.$rec->car_speed.' กม/ชม';
                    $bot->replyMessageNew($bot->replyToken, $answer);
                    $bot->replyLocation($rec->car_no,$map_addr_data->results[0]->formatted_address,$rec->lat,$rec->long);
                    
                    ///////////////// หาจุดหมายรถ ///////////////////////////
                    $answer2 = "";
                    $car_dest_json = file_get_contents('https://api.mlab.com/api/1/databases/nine-m/collections/car_dest?apiKey='.$mlab_api_key.'&q={"car_no":"'.$car_no.'"}');
                    $car_dest_data = json_decode($car_dest_json);
                    if (sizeof($car_dest_data) > 0){
                         foreach ($car_dest_data[0]->dest as $rec2){
                             //var_dump($rec2);
                             $map_dist_json = file_get_contents($map_dist_url.'&origins='.$rec->lat.','.$rec->long.'&destinations='.$rec2->latlng);
                             $map_dist_data = json_decode( $map_dist_json);
                             $answer2 = $answer2." รถคันนี้จะถึงปลายทางที่ ".$rec2->id.": ".$map_dist_data->destination_addresses[0].' ในอีก '.$map_dist_data->rows[0]->elements[0]->duration->text."\n"; 
                         };
                        $bot->sendMessageNew($bot->userid,$answer2);
                    };
                    ///////////////////////////////////////////////////////

                }
            } else {
                $answer = "ไม่พบรถหมายเลข ".$car_no.' ในระบบ';
                $bot->replyMessageNew($bot->replyToken, $answer);
            }        

        } else {
            //หาคำตอบจาก DB
            $mlab_json = file_get_contents('https://api.mlab.com/api/1/databases/nine-m/collections/linebot?apiKey='.$mlab_api_key.'&q={"question":"'.$bot->text.'"}');
            $mlab_data = json_decode($mlab_json);
            $isData=sizeof($mlab_data);

            //ถ้ามีคำตอบใน DB
            if($isData > 0){
                foreach($mlab_data as $rec){
                    $answer = $rec->answer;
                }
            } else {
                $answer = "พูดอะไรไม่รู้เรื่องเลย";
            }
            $bot->replyMessageNew($bot->replyToken, $answer);
        }
    } else {
        //$bot->replyMessageNew($bot->replyToken, $bot->message);
        $bot->replyLocation($bot->replyToken, $bot->message);
    }

	
    if ($bot->isSuccess()) {
        echo 'Succeeded!';
        exit();
    }
    // Failed
    echo $bot->response->getHTTPStatus . ' ' . $bot->response->getRawBody(); 
    exit();
}