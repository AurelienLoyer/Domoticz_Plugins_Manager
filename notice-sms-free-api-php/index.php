<?php

/**
* Notice Public Ip Change
* @author Aurélien Loyer
* @site aurelien-loyer.fr
* @github github.com/T3kstiil3
* @twitter @T3kstiil3
*/

// Dev mode ON :)
$mod_debug = true;
if($mod_debug){
	error_reporting(E_ALL);  // On affiche les erreurs php
	ini_set("display_errors", 1); // On affiche les erreurs php
}

$display_result = true;

//Fichiers script & config
$file_ip = 'last_ip_save.txt'; //fichier pour l'ip
$file_config = 'config.json'; // fichier de config
$free_url = 'https://smsapi.free-mobile.fr/sendmsg'; // url free api

// On recupere le fichier de config et l'ip
$config = json_decode(file_get_contents($file_config));


if(!$config){
	create_default_config($file_config);
}
$domoticz_url = "";

//file_put_contents($file_ip, $current_ip);

if($config->active && isset($_GET['message'])){
	$message = $_GET['message'];
	echo "On previent par sms via Free Api<br>";
	echo $message."<br>";
	$message = str_replace('%MESSAGE%',$message,$config->msg);
	$user = $config->user;
	$pass = $config->pass;
	$url = $free_url."?user=".$user."&pass=".$pass."&msg=".$message;
	echo $url."</br>";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_exec($ch);
	curl_close($ch);
}else if(!$config->active){
	$error = array('error' => 'Notice not active !');
	echo json_encode($error);
	exit();
}else if(!isset($_GET['message'])){
	$error = array('error' => 'No message !');
	echo json_encode($error);
	exit();
}

function create_default_config($file_config){
	$config = '{
		"active": true,
		"user": "userapifree",
		"pass": "passapifree",
		"msg": "[ALERTE DOMOTIQUE] %MESSAGE%"
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
		echo "<br>";
		echo "- Set value ".$svalue." to device idx".$idx;
		echo "<br>";
		echo $url;
		echo "<br><br>";
		echo $result;
		echo "\r\n";
	}
}

?>
