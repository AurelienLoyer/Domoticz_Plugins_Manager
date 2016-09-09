<?php

$list_widget = array();

function send_to_domoticz($idx,$svalue,$type=NULL){
    if($type){
        $type = "&vtype=".$type;
    }else{
        $type = "";
    }
    $ch = curl_init('http://127.0.0.1:8080/json.htm?type=command&param=udevice&idx='.$idx.$type.'&svalue='.$svalue);
    $result = curl_exec($ch);
    echo "- Send value ".$svalue." to device idx ".$idx;
    echo $result;
    echo "\r\n <br><br>";
}

// CONNECTION VITESSE INTERNET

exec('/usr/local/bin/speedtest --simple > /home/pi/speedtest.log');
$file = file_get_contents('/home/pi/speedtest.log');
$file = nl2br($file);
$file = explode("<br />", $file);
$ping = str_replace('.', '.', $file[0]);
$download = str_replace('.', '.', $file[1]);
$upload = str_replace('.', '.', $file[2]);
if (preg_match('/\d{1,3}(,\d{3})*(\.\d+)?/', $ping, $matches) > 0) {
    $ping = $matches[0];
    send_to_domoticz(218,$ping,1);
}
if (preg_match('/\d{1,3}(,\d{3})*(\.\d+)?/', $download, $matches) > 0) {
    $download = $matches[0];
    send_to_domoticz(216,$download,1);
}
if (preg_match('/\d{1,3}(,\d{3})*(\.\d+)?/', $upload, $matches) > 0) {
    $upload = $matches[0];
    send_to_domoticz(217,$upload,1);
}

?>
