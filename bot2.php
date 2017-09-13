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
	
if (!empty($bot->isEvents)) {

    if($bot->isText){
        //หารูปแบบการตอบกลับ (Text,Location) โดยดูจาก keyword
        if (strpos($bot->text, 'ค้นหารถ') !== false) {
            $pieces = explode(" ",$bot->text);
            $car_no = $pieces[1];

            $mlab_json = file_get_contents('https://api.mlab.com/api/1/databases/nine-m/collections/car_location?apiKey='.$mlab_api_key.'&q={"car_no":"'.$car_no.'"}');
            $mlab_data = json_decode($mlab_json);
            $isData=sizeof($mlab_data);

            if($isData > 0){
                foreach($mlab_data as $rec){
                    $answer = 'รถหมายเลข '.$rec->car_no.' วิ่งอยู่ที่ '.$rec->car_location.' ด้วยความเร็ว '.$rec->car_speed.' กม/ชม';
                    $bot->replyMessageNew($bot->replyToken, $answer);
                    $bot->replyLocation("ทดสอบ","ทดสอบ",100,100);
                }
            } else {
                $answer = "ไม่พบรถหมายเลข ".$rec->car_no.' ในระบบ';
                $bot->replyMessageNew($bot->replyToken, $answer);
            }        

        } else {
            //หาคำตอบจาก DB
            $mlab_json = file_get_contents('https://api.mlab.com/api/1/databases/nine-m/collections/linebot?apiKey='.$mlab_api_key.'&q={"question":"'.$text.'"}');
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