<?php

/**
 * Notice Public Ip Change
 * @author Aurélien Loyer
 * @site aurelien-loyer.fr
 * @github github.com/T3kstiil3
 */

// Dev mode ON :)
$mod_debug = true;
if($mod_debug){
	error_reporting(E_ALL);  // On affiche les erreurs php
	ini_set("display_errors", 1); // On affiche les erreurs php
}
$display_result = true;

//Fichiers script
$file_ip = 'last_ip_save.txt'; //fichier pour l'ip
$file_config = 'config.json'; // fichier de config

// On recupere le fichier de config et l'ip
$config = json_decode(file_get_contents($file_config));
$old_ip = file_get_contents($file_ip);
$current_ip = file_get_contents("http://ipecho.net/plain");

if(0)
	file_put_contents($file_ip, $current_ip);

if($current_ip != $old_ip){
	//Les ip sont différentes
	if($config->active){
		echo "not same ip notice user in progress !";
		//Free
		if($config->notice_free->active){
			echo "on preveitn par sms via Free Api";
		}
		//Mail
		if($config->notice_mail->active){
			echo "on preveitn par mail";
		}
		//Domoticz
		if($config->notice_domoticz->active){
			echo "on preveitn domoticz";
		}
		//...
	}
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
	    echo "\r\n";
	}
}

?>
