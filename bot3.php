<?php

require_once 'vendor/autoload.php';

$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient('QDd6Ba0wLQlflnU/kxWlc3rGTLBX/ioj+2T/SfAp8oxT/1p2sEZoOLhXJAL21PxO2hw+TCQ+Xlqhr/aGozTEsZRf7yDIwV+j/hKYlIxpBDUmnOE3IQIXzbY8BY12i66iaKVRzuJ6Z3WYnxIA27xSdwdB04t89/1O/w1cDnyilFU=');
$bot = new \LINE\LINEBot($httpClient, '8c8824d765fe14b40b05dc83294764a5');
$signature = $_SERVER['HTTP_' . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];
try {
  $events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);
} catch(\LINE\LINEBot\Exception\InvalidSignatureException $e) {
  error_log('parseEventRequest failed. InvalidSignatureException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownEventTypeException $e) {
  error_log('parseEventRequest failed. UnknownEventTypeException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownMessageTypeException $e) {
  error_log('parseEventRequest failed. UnknownMessageTypeException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\InvalidEventRequestException $e) {
  error_log('parseEventRequest failed. InvalidEventRequestException => '.var_export($e, true));
}
foreach ($events as $event) {
  // Postback Event
  if (($event instanceof \LINE\LINEBot\Event\PostbackEvent)) {
    //$logger->info('Postback message has come');
    continue;
  }
  // Location Event
  if  ($event instanceof LINE\LINEBot\Event\MessageEvent\LocationMessage) {
    //$logger->info("location -> ".$event->getLatitude().",".$event->getLongitude());
    continue;
  }
  
  // Message Event = TextMessage
  if (($event instanceof \LINE\LINEBot\Event\MessageEvent\TextMessage)) {
    // get message text
    $messageText=strtolower(trim($event->getText()));
    
  }
}

$outputText = new \LINE\LINEBot\MessageBuilder\LocationMessageBuilder("Eiffel Tower", "Champ de Mars, 5 Avenue Anatole France, 75007 Paris, France", 48.858328, 2.294750);
$response = $bot->replyMessage($event->getReplyToken(), $outputText);