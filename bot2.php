<?php
include ('line-bot.php');
$channelSecret = '8c8824d765fe14b40b05dc83294764a5';
$access_token  = 'QDd6Ba0wLQlflnU/kxWlc3rGTLBX/ioj+2T/SfAp8oxT/1p2sEZoOLhXJAL21PxO2hw+TCQ+Xlqhr/aGozTEsZRf7yDIwV+j/hKYlIxpBDUmnOE3IQIXzbY8BY12i66iaKVRzuJ6Z3WYnxIA27xSdwdB04t89/1O/w1cDnyilFU=';
$bot = new BOT_API($channelSecret, $access_token);
	
if (!empty($bot->isEvents)) {
		
    $bot->replyMessageNew($bot->replyToken, json_encode($bot->message));
    if ($bot->isSuccess()) {
        echo 'Succeeded!';
        exit();
    }
    // Failed
    echo $bot->response->getHTTPStatus . ' ' . $bot->response->getRawBody(); 
    exit();
}