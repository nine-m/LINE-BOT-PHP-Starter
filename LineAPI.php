<?php
class LINE_API {
    private $access_token = 'QDd6Ba0wLQlflnU/kxWlc3rGTLBX/ioj+2T/SfAp8oxT/1p2sEZoOLhXJAL21PxO2hw+TCQ+Xlqhr/aGozTEsZRf7yDIwV+j/hKYlIxpBDUmnOE3IQIXzbY8BY12i66iaKVRzuJ6Z3WYnxIA27xSdwdB04t89/1O/w1cDnyilFU=';
    private $proxy = 'velodrome.usefixie.com:80';
    private $proxyauth = 'fixie:NnwPBqm5tR6Jtx5';


    public function replyMessage($replyToken = null, $messages = null){
        	// Make a POST Request to Messaging API to reply to sender
			$url = 'https://api.line.me/v2/bot/message/reply';
			$data = [
				'replyToken' => $replyToken,
				'messages' => [$messages]
			];
			$post = json_encode($data);
			$headers = array('Content-Type: application/json', 'Authorization: Bearer ' . $this->access_token);

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_PROXY, $this->proxy);  //proxy
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxyauth); //proxy
			$result = curl_exec($ch);
			curl_close($ch);

			echo $result . "\r\n";
    }

    public function replyLocation($replyToken = null){
        // Make a POST Request to Messaging API to reply to sender
        $url = 'https://api.line.me/v2/bot/message/reply';
        $data = [
            'replyToken' => $replyToken,
            'messages' => [
                {
                    'type':'location',
                    'title':'My Location',
                    'address':'ทดสอบ',
                    'latitude':35.65910807942215,
                    'longtitude':139.70372892916203
                }
            ]
        ];
        $post = json_encode($data);
        $headers = array('Content-Type: application/json', 'Authorization: Bearer ' . $this->access_token);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_PROXY, $this->proxy);  //proxy
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxyauth); //proxy
        $result = curl_exec($ch);
        curl_close($ch);

        echo $result . "\r\n";
}
}