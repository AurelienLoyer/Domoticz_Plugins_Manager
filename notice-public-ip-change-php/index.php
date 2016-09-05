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
$old_ip = file_get_contents($file_ip);
$current_ip = file_get_contents("http://ipecho.net/plain");

if(!$config){
	create_default_config($file_config);
}
$domoticz_url = $config->notice_domoticz->domoticz_url;

//file_put_contents($file_ip, $current_ip);

if($current_ip != $old_ip){
	//Les ip sont différentes
	if($config->active){
		echo "Not same ip notice user in progress !<br>";
		//Free
		if($config->notice_free->active){
			echo "- On previent par sms via Free Api<br>";
			$message = str_replace('%IP%',$current_ip,$config->notice_free->msg);
			$user = $config->notice_free->user;
			$pass = $config->notice_free->pass;
			$url = $free_url."?user=".$user."&pass=".$pass."&msg=".$message;
			echo $url."</br>";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_exec($ch);
			curl_close($ch);
		}
		//Mail
		if($config->notice_mail->active){
			echo "- On previent par mail<br>";
			$message = str_replace('%IP%',$current_ip,$config->notice_mail->msg);
			$headers   = array();
			$headers[] = "MIME-Version: 1.0";
			$headers[] = "Content-type: text/plain; charset=utf";
			$headers[] = "From: Domoticz <".$config->notice_mail->from.">";
			mail($config->notice_mail->mail, $config->notice_mail->subject, $message, implode("\r\n", $headers));
		}
		//Domoticz
		if($config->notice_domoticz->active){
			echo "- On previent domoticz<br>";
			send_to_domoticz($config->notice_domoticz->widget_id,$current_ip,NULL,0,NULL);
		}
		//...
	}
}else{
	echo 'Same IP do nothing';
}

function create_default_config($file_config){
	$config = '{
		"active": true,
		"notice_free":{
			"active": true,
			"user": "********",
			"pass": "********",
			"msg": "[ALERTE DOMOTIQUE] IP Publique change to -> %IP%"
		},
		"notice_mail":{
			"active": true,
			"from": "domoticz@no-reply.fr",
			"subject" : "[ALERTE DOMOTIQUE]",
			"mail_to": "mail.test@gmail.com",
			"msg": "[ALERTE DOMOTIQUE] IP Publique change to -> %IP%"
		},
		"notice_domoticz":{
			"active": true,
			"domoticz_url" : "ip.de.mon.domoticz:port",
			"widget_id" : 123
		}
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
