<?php
include ('line-bot.php');
date_default_timezone_set("Asia/Bangkok");



$channelSecret = getenv('channelSecret', true) ?: getenv('channelSecret');
$access_token  = getenv('access_token', true) ?: getenv('access_token');
$proxy = getenv('proxy', true) ?: getenv('proxy');
$proxyauth = getenv('proxyauth', true) ?: getenv('proxyauth');


$bot = new BOT_API($channelSecret, $access_token);

//mlab config
$mlab_api_key = 'wGv_MG_7RHOetlGwfsSENc5p-A2J9LcC';
$mlab_url = 'https://api.mlab.com/api/1/databases/nine-m/collections/linebot?apiKey='.$mlab_api_key.'';

//google api config
$map_api_url = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=true&region=th&language=th&latlng=';
$map_dist_url = 'https://maps.googleapis.com/maps/api/distancematrix/json?language=th';

//eyefleet config
$eyefleetuser =  getenv('eyefleetuser', true) ?: getenv('eyefleetuser');
$eyefleetpass =  getenv('eyefleetpass', true) ?: getenv('eyefleetpass');
$eyefleet_url = 'http://www.eye-fleet.com/east/history.xml?user='.$eyefleetuser.'&pass='.$eyefleetpass.'$eyefleetpass';


$isError = false;
	
if (!empty($bot->isEvents)) {

    if($bot->isText){
        //หารูปแบบการตอบกลับ (Text,Location) โดยดูจาก keyword
        if (strpos($bot->text, 'ค้นหารถ') !== false) {
            $pieces = explode(" ",$bot->text);
            $car_no = $pieces[1];

            // $mlab_json = file_get_contents('https://api.mlab.com/api/1/databases/nine-m/collections/car_location?apiKey='.$mlab_api_key.'&q={"car_no":"'.$car_no.'"}');
            // $mlab_data = json_decode($mlab_json);
            // $isData = sizeof($mlab_data);

            $mlab_trucks = file_get_contents('https://api.mlab.com/api/1/databases/nine-m/collections/TRUCKS?apiKey='.$mlab_api_key.'&q={"TRUCK_NO":"'.$car_no.'"}');
            $trucks = json_decode($mlab_trucks);
            $isData = sizeof($trucks);

            if($isData > 0){
                foreach($trucks  as $truck){
                    //ค้นหาตำแหน่งรถจากระบบ eye-fleet
                    $current_date_text = date("Y-m-d");
                    $current_time = strtotime('-1 minutes');
                    $current_time_text = date("H:i:s",$current_time);
            
                    $eyefleet_xml = file_get_contents($eyefleet_url.'&mid='.$truck->MID.'&date='.$current_date_text.'%20'.$current_time_text);
                    
                    //retry to get location 
                    if (strlen($eyefleet_xml) < 60) {
                        //echo 'loop 2';
                        $current_time = strtotime('-4 minutes');
                        $current_time_text = date("H:i:s",$current_time);
                        $eyefleet_xml = file_get_contents($eyefleet_url.'&mid='.$truck->MID.'&date='.$current_date_text.'%20'.$current_time_text);
                    }
            
                    if (strlen($eyefleet_xml) < 60) {
                        $isError = true;
                    } else {
                        $xml=simplexml_load_string($eyefleet_xml) or die("Error: Cannot create object");
                    }
            
            
                    // echo ' lat:'.$xml->data[0]->lat;
                    // echo ' lng:'.$xml->data[0]->lng;
                    // echo ' spd:'.$xml->data[0]->spd;

                    if (!$isError){
                        
                        $map_addr_json = file_get_contents($map_api_url.$xml->data[0]->lat.','.$xml->data[0]->lng);
                        $map_addr_data = json_decode($map_addr_json);
            
                        if( intval($xml->data[0]->spd) == 0) {
                            $answer = 'รถหมายเลข '.$truck->TRUCK_NO.' ทะเบียน '.$truck->TRUCK_REGIST.' จอดอยู่ที่ '.$map_addr_data->results[0]->formatted_address;
                        } else {
                            $answer = 'รถหมายเลข '.$truck->TRUCK_NO.' ทะเบียน '.$truck->TRUCK_REGIST.' วิ่งอยู่ที่ '.$map_addr_data->results[0]->formatted_address.' ด้วยความเร็ว '.$xml->data[0]->spd.' กม/ชม';
                        }
                    
                    } else {
                        $answer = 'การสื่อสารระบบ GPS ขัดข้องหรือไม่มีข้อมูลพิกัดในระบบ กรุณาลองใหม่อีกครั้ง!!!!';
                    }

                    // $map_addr_json = file_get_contents($map_api_url.$rec->lat.','.$rec->long);
                    // $map_addr_data = json_decode($map_addr_json);
                    
                    // $answer = 'รถหมายเลข '.$rec->car_no.' วิ่งอยู่ที่ '.$map_addr_data->results[0]->formatted_address.' ด้วยความเร็ว '.$rec->car_speed.' กม/ชม';

                    $bot->replyMessageNew($bot->replyToken, $answer);

                    if (!$isError) {
                        $bot->replyLocation($truck->TRUCK_NO,$map_addr_data->results[0]->formatted_address,$xml->data[0]->lat,$xml->data[0]->lng);
                    }
                    ///////////////// ข้อมูลงานวิ่ง ///////////////////////////
                    $senduser = getenv('senduser', true) ?: getenv('senduser');
                    $sendpass = getenv('sendpass', true) ?: getenv('sendpass');
                    $send_api_url = "https://send.sahaviriyalogistics.com/linev2/?username=".$senduser."&password=".$sendpass;
                    $answer_dn = "";
                    $send_data_json = file_get_contents($send_api_url.'&work_date='.date("d/m/Y").'&truck='.urlencode($truck->TRUCK_REGIST));
                    $send_data = json_decode($send_data_json);
            
                    if (sizeof($send_data) > 0){
                        foreach ($send_data as $send) {
                            $answer_dn = "\n งานวันนี้ : \n===================\n" ;
                            $answer_dn = $answer_dn."DN : ".$send->DN_NO."\n ";
                            $answer_dn = $answer_dn."JOB : ".$send->JOB_NO."\n ";
                            $answer_dn = $answer_dn."DRIVER : ".$send->DRIVER."\n ";
                            $answer_dn = $answer_dn."TEL : ".$send->TEL."\n ";
                            $answer_dn = $answer_dn."CUSTOMER : ".$send->CUSTOMER_NO."\n ";
                            $answer_dn = $answer_dn."RECEIVER : ".$send->RECEIVER."\n ";
                            $answer_dn = $answer_dn."PRODUCT : ".$send->PRODUCT."\n ";
                            $answer_dn = $answer_dn."WEIGHT : ".$send->WEIGHT."\n ";
                            $answer_dn = $answer_dn."====================\n";
                        }
                        $bot->sendMessageNew($bot->userid,$answer_dn);
                    } 



                    
                    ///////////////// หาจุดหมายรถ ///////////////////////////
                    $answer2 = "รายงานจุดหมายรถ\n=======================\n";
                    $car_dest_json = file_get_contents('https://api.mlab.com/api/1/databases/nine-m/collections/car_dest?apiKey='.$mlab_api_key.'&q={"car_no":"'.$car_no.'"}');
                    $car_dest_data = json_decode($car_dest_json);
                    if (sizeof($car_dest_data) > 0){
                         foreach ($car_dest_data[0]->dest as $rec2){
                             //var_dump($rec2);
                             $map_dist_json = file_get_contents($map_dist_url.'&origins='.$xml->data[0]->lat.','.$xml->data[0]->lng.'&destinations='.$rec2->latlng);
                             $map_dist_data = json_decode( $map_dist_json);
                             $answer2 = $answer2."\nรถคันนี้จะถึงปลายทางที่ ".$rec2->id.": \n".$map_dist_data->destination_addresses[0]."\nในอีก ".$map_dist_data->rows[0]->elements[0]->duration->text."\n"; 
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
            $mlab_json = file_get_contents('https://api.mlab.com/api/1/databases/nine-m/collections/linebot?apiKey='.$mlab_api_key.'&q={"question":"'.urlencode($bot->text).'"}');
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