<?php

/**
 * Xee Data Script 4 Domoticz
 * @author Aurélien Loyer
 * @site aurelien-loyer.fr
 * @github github.com/T3kstiil3
 */

header("Access-Control-Allow-Origin: *");

if(isset($argv) && isset($argv[1])){
	parse_str($argv[1], $params);
	$_GET = $params;
}

// Dev mode ON :)
$mod_debug = true;
if($mod_debug){
	error_reporting(E_ALL);  // On affiche les erreurs php
	ini_set("display_errors", 1); // On affiche les erreurs php
}

$display_result = true;

// Réglages xee api
$actual_link = "http://$_SERVER[HTTP_HOST]".parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$file_token = 'xee_token.txt'; //fichier pour le token xee
$file_conf = 'xee_conf.json'; //fichier pour la conf xee
// On recupere les fichier token & conf
//$file_token = str_replace('xee.php','',$actual_link).$file_token;
//$file_conf = str_replace('xee.php','',$actual_link).$file_conf;

$token = json_decode(file_get_contents($file_token,FILE_USE_INCLUDE_PATH),true);
$conf = json_decode(file_get_contents($file_conf,FILE_USE_INCLUDE_PATH),true);

$client_id = $conf['Client_Id']; // client id de l'app xee
$client_secret = $conf['Client_secret']; // client secret de l'app xee
$domoticz_url = $conf['domoticz_url']; // url de votre domoticz
$garage_lat = $conf['garage_lat']; // latitude
$garage_lng = $conf['garage_lng']; // longitude
$garage_radis_size = $conf['garage_radis_size']; // taille de la zone autour du garage en kilomètre

// Urls du script
$current_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; //  url du script pour la redirection /!\ url défini dans l'appli xee.
$auth_url = 'https://cloud.xee.com/v3/auth/auth?client_id='.$client_id.'&redirect_uri='.$current_url; // url xee d'auth
$access_token_url = 'https://cloud.xee.com/v3/auth/access_token'; // url xee recuperation des tokens

if($token && array_key_exists('access_token',$token)){
	// On a un token dans notre fichier on vérifie qu'il est toujours valide sinon on en demande un nouveau
	if(verif_token($token)){
		get_xee_data($token);
	}else{
		// On demande de nouveau un token avec le refresh token et on le sauvegarde
		$credentials = $client_id.":".$client_secret;
		$params = array(
		    'grant_type' => 'refresh_token',
		    'refresh_token' => $token['refresh_token']
		);
		$headers = array(
			'Accept: */*',
			'Content-Type: application/x-www-form-urlencoded',
		);
		$json = httpPost($access_token_url,$params,$headers,$credentials);
		$curl_object = json_decode($json,true);
		if(array_key_exists('error',$curl_object)){
			echo "Error : ".$json['error'];
		}else{
			file_put_contents($file_token, $json);
			get_xee_data(json_decode($json,true));
		}
	}

}else if(!isset($_GET['code'])){ // Si on n'a pas de code et de token enregistré on redirige le user sur le login xee.
	header('Location:'.$auth_url);
	exit();
}else{

	$credentials = $client_id.":".$client_secret;
	$params = array(
	    'code' => $_GET['code'],
	    'grant_type' => 'authorization_code'
	);
	$headers = array(
		'Accept: */*',
		'Content-Type: application/x-www-form-urlencoded',
	);

	$json = httpPost($access_token_url,$params,$headers,$credentials);
	echo $json;
	$curl_object = json_decode($json,true);

	if(array_key_exists('error',$curl_object)){
		echo "Error : ".$json['error'];
	}else{
		file_put_contents($file_token, $json);
	}
}

function verif_token($token){
	if($token['expires_at'] > strtotime("now"))
		return true;
	else
		return false;
}

function httpPost($url,$params,$headers,$credentials){
	$postData = '';

	foreach($params as $k => $v)
	{
		$postData .= $k . '='.$v.'&';
	}
	$postData = rtrim($postData, '&');
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_USERPWD, $credentials);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_POST, count($postData));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$output = curl_exec($ch);
	curl_close($ch);
	return $output;
}

function get_xee_data($token){

	$context = stream_context_create(array(
    'http' => array(
        'header'  => "Authorization: Bearer " . $token['access_token']
    )
	));

	if(!isset($_GET['data'])){
		$error = array('error' => 'No data specify !');
		echo json_encode($error);
		exit();
	}else
		$data = $_GET['data'];

	global $domoticz_url, $garage_lng, $garage_lat, $garage_radis_size;

	//On recupere le user id;
	$user = json_decode(file_get_contents('https://cloud.xee.com/v1/user/me.json?access_token='.$token['access_token']),true);
	$user_id = $user['id'];

	if(!$user_id)
		return;
		
	//On recupere les voitures
	$cars = json_decode(file_get_contents('https://cloud.xee.com/v3/users/'.$user_id.'/cars', false, $context), true);
	//On pars du principe que l'utilisateur a une seule voiture...
	$car_id = $cars[0]['id'];

	$car = file_get_contents('https://cloud.xee.com/v3/cars/'.$car_id.'/status', false, $context);

	$car_data = [];
	foreach(json_decode($car,true)['signals'] as $signal){
		$car_data[$signal['name']] = $signal['value'];
	}

	$car_position = json_decode($car,true)['location'];
	$distance = getDistance( $garage_lat, $garage_lng, $car_position['latitude'],$car_position['longitude']);

	if($data == "car"){
		header('Content-Type: application/json');
		echo $car;
	}elseif($data == "trips"){
		$trips = file_get_contents('https://cloud.xee.com/v3/cars/'.$car_id.'/trips', false, $context);
		header('Content-Type: application/json');
		echo $trips;
	}elseif($data == "trip"){
		if(!isset($_GET['trip_id'])){
			$error = array('error' => 'No trip id specify !');
			header('Content-Type: application/json');
			echo json_encode($error);
			exit();
		}else{
			$trip_id = $_GET['trip_id'];
		}
		$trips = file_get_contents('https://cloud.xee.com/v3/trips/'.$trip_id.'/signals', false, $context);
		header('Content-Type: application/json');
		echo $trips;
	}elseif($data == "distance"){
		if($distance <= $garage_radis_size){
			$distance = array('distance' => 'La voiture est dans la zone autour du garage');
		}else{
			$distance = array('distance' => 'La voiture est en dehors de la zone autour du garage');
		}
		header('Content-Type: application/json');
		echo json_encode($distance);
		exit();
	}elseif($data == "domoticz"){

		echo "Send data to Domoticz :<br>";

		//Kilometrage total
		send_to_domoticz(212,$car_data['Odometer'],0,1);

		//Niveau Diesel
		send_to_domoticz(211,$car_data['FuelLevel'],0,1);

		//Ceinture conducteur n'est plus présent dans le retour api
		/*if($car_data['FrontLeftSeatBeltSts'] == 0){
			send_to_domoticz(198,1,null,null,'&switchcmd=Off');
		}else{
			send_to_domoticz(198,1,null,null,'&switchcmd=On');
		}*/

		//Voiture a moins de ... km du garage.
		if($distance <= $garage_radis_size){
			send_to_domoticz(193,1,null,null,'&switchcmd=On');
		}else{
			send_to_domoticz(193,1,null,null,'&switchcmd=Off');
		}

		//Niveau de la batterie
		//BatteryVoltage
		send_to_domoticz(213,$car_data['BatteryVoltage'],0,1);

		//Moteur tour/miniute
		//EngineSpeed
		send_to_domoticz(215,$car_data['EngineSpeed'],0,1);

		//Vitesse voiture
		//VehiculeSpeed
		send_to_domoticz(214,$car_data['VehiculeSpeed'],0,1);

	}else{
		/*var_dump($car_data['Odometer']);
		var_dump($car_data['FuelLevel']);
		var_dump($car_data['FrontLeftSeatBeltSts']);*/
	}
}

function getDistance( $latitude1, $longitude1, $latitude2, $longitude2 ) {
    $earth_radius = 6371;

    $dLat = deg2rad( $latitude2 - $latitude1 );
    $dLon = deg2rad( $longitude2 - $longitude1 );

    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * asin(sqrt($a));
    $d = $earth_radius * $c;

    return $d;
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
