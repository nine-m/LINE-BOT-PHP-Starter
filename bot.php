<?php
include('LineAPI.php');

$proxy = 'velodrome.usefixie.com:80';
$proxyauth = 'fixie:NnwPBqm5tR6Jtx5';

$access_token = 'QDd6Ba0wLQlflnU/kxWlc3rGTLBX/ioj+2T/SfAp8oxT/1p2sEZoOLhXJAL21PxO2hw+TCQ+Xlqhr/aGozTEsZRf7yDIwV+j/hKYlIxpBDUmnOE3IQIXzbY8BY12i66iaKVRzuJ6Z3WYnxIA27xSdwdB04t89/1O/w1cDnyilFU=';

//mlab config
$mlab_api_key = 'wGv_MG_7RHOetlGwfsSENc5p-A2J9LcC';
$mlab_url = 'https://api.mlab.com/api/1/databases/nine-m/collections/linebot?apiKey='.$mlab_api_key.'';


// Get POST body content
$content = file_get_contents('php://input');
// Parse JSON
$events = json_decode($content, true);
// Validate parsed JSON data
if (!is_null($events['events'])) {
	// Loop through each event
	foreach ($events['events'] as $event) {
		// Reply only when message sent is in 'text' format
		if ($event['type'] == 'message' && $event['message']['type'] == 'text') {
			// Get text sent
			$text = $event['message']['text'];
			// Get replyToken
			$replyToken = $event['replyToken'];

			//หารูปแบบการตอบกลับ (Text,Location) โดยดูจาก keyword
			if (strpos($text, 'ค้นหารถ') !== false) {
				$pieces = explode(" ",$text);
				$car_no = $pieces[1];

				$mlab_json = file_get_contents('https://api.mlab.com/api/1/databases/nine-m/collections/car_location?apiKey='.$mlab_api_key.'&q={"car_no":"'.$car_no.'"}');
				$mlab_data = json_decode($mlab_json);
				$isData=sizeof($mlab_data);

				if($isData > 0){
					foreach($mlab_data as $rec){
						$answer = 'รถหมายเลข '.$rec->car_no.' วิ่งอยู่ที่ '.$rec->car_location.' ด้วยความเร็ว '.$rec->car_speed.' กม/ชม';
					}
				} else {
					$answer = "ไม่พบรถหมายเลข ".$rec->car_no.' ในระบบ';
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

			// Build message to reply back
			$messages = [
				'type' => 'text',
				'text' => $answer
			];

			$lineApi = new LINE_API;

			$lineApi->replyMessage($replyToken,$messages);



			// // Make a POST Request to Messaging API to reply to sender
			// $url = 'https://api.line.me/v2/bot/message/reply';
			// $data = [
			// 	'replyToken' => $replyToken,
			// 	'messages' => [$messages]
			// ];
			// $post = json_encode($data);
			// $headers = array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token);

			// $ch = curl_init($url);
			// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			// curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			// curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			// curl_setopt($ch, CURLOPT_PROXY, $proxy);  //proxy
			// curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth); //proxy
			// $result = curl_exec($ch);
			// curl_close($ch);

			// echo $result . "\r\n";
		}
	}
}
echo "OK";