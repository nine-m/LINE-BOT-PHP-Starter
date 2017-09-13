<?php
include ('line-bot.php');
$channelSecret = '8c8824d765fe14b40b05dc83294764a5';
$access_token  = 'QDd6Ba0wLQlflnU/kxWlc3rGTLBX/ioj+2T/SfAp8oxT/1p2sEZoOLhXJAL21PxO2hw+TCQ+Xlqhr/aGozTEsZRf7yDIwV+j/hKYlIxpBDUmnOE3IQIXzbY8BY12i66iaKVRzuJ6Z3WYnxIA27xSdwdB04t89/1O/w1cDnyilFU=';
$bot = new BOT_API($channelSecret, $access_token);

//mlab config
$mlab_api_key = 'wGv_MG_7RHOetlGwfsSENc5p-A2J9LcC';
$mlab_url = 'https://api.mlab.com/api/1/databases/nine-m/collections/linebot?apiKey='.$mlab_api_key.'';
	
if (!empty($bot->isEvents)) {

    if($bot->isText){
        $bot->replyMessageNew($bot->replyToken, $bot->text);
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