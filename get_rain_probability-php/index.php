<?php

/**
* Get rain probabillity
* @author Aurélien Loyer
* @site aurelien-loyer.fr
* @github github.com/T3kstiil3
* @twitter @T3kstiil3
*/

include('inc/functions.php');

// Dev mode ON :)
$mod_debug = true;
if($mod_debug){
	error_reporting(E_ALL);  // On affiche les erreurs php
	ini_set("display_errors", 1); // On affiche les erreurs php
}

$display_result = true;

//Fichiers script & config
$file_config = 'config.json'; // fichier de config

// On recupere le fichier de config et l'ip
$config = json_decode(file_get_contents($file_config,FILE_USE_INCLUDE_PATH),true);

if(!$config){
	create_default_config($file_config);
}

$api_url = "http://api.wunderground.com/api/".$config->apikey."/hourly/lang:".$config->lang."/q/".$config->country."/".$config->town.".json";
$domoticz_url = $config->domoticz_url;

$data = json_decode(file_get_contents($api_url));

if($data){
  if($display_result){
    echo "Proba Rain in 1 h : ".$data->hourly_forecast[0]->pop." % <br>";
    echo "Proba Rain in 5 h : ".$data->hourly_forecast[4]->pop." % <br>";
    echo "Proba Rain in 12 h : ".$data->hourly_forecast[11]->pop." % <br>";
    echo "Proba Rain in 24 h : ".$data->hourly_forecast[23]->pop." % <br><br>";
  }
  send_to_domoticz($config->IDX_1h,$data->hourly_forecast[0]->pop);
  send_to_domoticz($config->IDX_5h,$data->hourly_forecast[4]->pop);
  send_to_domoticz($config->IDX_12h,$data->hourly_forecast[11]->pop);
  send_to_domoticz($config->IDX_24h,$data->hourly_forecast[23]->pop);
}

function create_default_config($file_config){
	$config = '{
		"country":"FRANCE",
		"apikey" : "4567890567897890",
		"town": "LILLE",
		"lang" : "FR",
		"IDX_1h" : 200,
		"IDX_5h" : 201,
		"IDX_12h" : 202,
		"IDX_24h" : 203,
		"domoticz_url" : "127.0.0.1:8080"
  	}';
	file_put_contents($file_config, $config);
}

function send_to_domoticz($idx,$svalue,$type=NULL,$nvalue=NULL,$string=NULL){
	global $domoticz_url, $mod_debug,$display_result;

	$param = "&param=udevice";

	if(isset($string) && strpos($string, 'switchcmd') !== false)
	{
		$param = '&param=switchlight';
	}

	if(isset($type)){
		$type = "&vtype=".$type;
	}else{
		$type = "";
	}if(isset($nvalue)){
		$nvalue = "&nvalue=0";
	}else{
		$nvalue = "";
	}
	$url = 'http://'.$domoticz_url.'/json.htm?type=command&idx='.$idx.$param.$type.$nvalue.'&svalue='.$svalue.$string;
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$result = curl_exec($ch);

	if($display_result){
		echo "";
		echo "- Set value ".$svalue." to device idx ".$idx;
		echo "";
		echo $url;
		echo "";
		echo $result;
		echo "\r\n";
	}
}

?>
