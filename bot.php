<?php
//php bot.php > bot-$(date +%Y-%m-%d).log
// https://api.telegram.org/bot(token)/getFile?file_id=(fileId)
// https://api.telegram.org/file/bot(token)/(file_path)
// https://habrahabr.ru/post/311578/
require('youtube-dl.class.php');
require('config.php');
$config = new Config();
$token = $config->getTelegramToken();
define('addr', "https://api.telegram.org/bot$token/");
$updateAction = "getUpdates?timeout=60";
$lastEventId = '';

while(true){
    echo "\n\r===".date('H:i:s')."-lastId=$lastEventId-----------------------------\n\r";
    $update = addr.$updateAction;
    $url = $lastEventId?"$update&offset=$lastEventId":$update;
    $resp = sendRequest($url);
    $resp = json_decode($resp, true);

    print_r($resp);

    if($resp && isset($resp['ok']) && $resp['ok']){
        foreach ($resp['result'] as $result) {
            $lastEventId = isset($result['update_id'])?++$result['update_id']:$lastEventId;
            eventHandler($result);
        }
    }
}

function eventHandler($params){
    print_r($params);

    if(isset($params['message']) && isset($params['message']['text'])){
        try {
            echo $params['message']['text'];
            /** @var yt_downloader $object */
            $object = new yt_downloader(trim($params['message']['text']), TRUE, "audio");
            print_r($object);
            sendFile($params['message']['chat']['id'], $object->downloadsDir.$object->audio);
        }
        catch (Exception $e) {
            echo 'Error'.$e->getMessage();
            sendMessage($params['message']['chat']['id'], $e->getMessage());
        }
    }
}

function sendFile($id, $path){
    echo 'send file';
    $resp = sendRequest(addr."sendAudio?chat_id=$id", $path);
    print_r($resp);
}

function sendMessage($id, $message){
    $resp = sendRequest(addr."sendMessage?chat_id=$id&text=$message");
    print_r($resp);
}

function sendRequest($addr, $file = false){
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $addr
    ));

    if($file){
        $fields = [
            'audio' => new \CurlFile($file, 'audio/mp3', $file)
        ];
        curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
    }

    $resp = curl_exec($curl);
    $int = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    echo "code:$int\n\r";
    curl_close($curl);

    return $resp;
}
