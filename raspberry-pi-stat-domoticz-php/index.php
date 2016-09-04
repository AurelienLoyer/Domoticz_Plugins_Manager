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
