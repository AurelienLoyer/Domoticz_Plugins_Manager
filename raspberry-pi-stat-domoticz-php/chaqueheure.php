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
    send_to_domoticz(109,$ping,1);
}
if (preg_match('/\d{1,3}(,\d{3})*(\.\d+)?/', $download, $matches) > 0) {
    $download = $matches[0];
    send_to_domoticz(111,$download,1);
}
if (preg_match('/\d{1,3}(,\d{3})*(\.\d+)?/', $upload, $matches) > 0) {
    $upload = $matches[0];
    send_to_domoticz(110,$upload,1);
}

/***
*** RPI
***/

// ESPACE UTILISE SUR LA RPI

$disk_total_space = disk_total_space ('/');
$disk_free_space = disk_free_space('/');
$pourcent_use = round( (($disk_total_space - $disk_free_space) * 100) / $disk_total_space, 2);
send_to_domoticz(72,$pourcent_use);

// Utilisation de la mémoire sur la RPI

function get_server_memory_usage(){
    $free = shell_exec('free');
    $free = (string)trim($free);
    $free_arr = explode("\n", $free);
    $mem = explode(" ", $free_arr[1]);
    $mem = array_filter($mem);
    $mem = array_merge($mem);
    $memory_usage = $mem[2]/$mem[1]*100;
    return round($memory_usage,2);
}
send_to_domoticz(73,get_server_memory_usage());


// Utilisation du CPU de la RPI

function get_server_cpu_usage(){
    $load = sys_getloadavg();
    return $load[0];
}
echo get_server_cpu_usage();
echo "\r\n";

// Temperature du RPI :)

$temperature_pi = shell_exec('vcgencmd measure_temp');
if (preg_match('/\d{1,3}(,\d{3})*(\.\d+)?/', $temperature_pi, $matches) > 0) {
    $temperature_pi = $matches[0];
    send_to_domoticz(71,$temperature_pi);
}


/*****
**** METEO API
******/

$openweatherapikey = "d3fc38b845cd365bd249dfab26338e96";
$url = "http://api.openweathermap.org/data/2.5/weather?q=Lille,fr&units=metric&APPID=".$openweatherapikey;
$json = json_decode(file_get_contents($url));
$temperature = $json->main->temp;
echo $temperature;

?>
