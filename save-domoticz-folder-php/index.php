<?php

/**
 * Save Domoticz configurator
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

$file_conf = 'config.json'; //fichier pour la conf

$token = json_decode(file_get_contents($file_token,FILE_USE_INCLUDE_PATH),true);
$conf = json_decode(file_get_contents($file_conf,FILE_USE_INCLUDE_PATH),true);

//get config
// /path/to/save/destination_folder.zip
// domoticz_save_2016-12-30.zip
// /path/to/folder -> usb device ?

$zip_name = 'domoticz_save_2016-12-30.zip';
$save_destination = '/path/to/save/';
$folder_2_save_path = '/path/to/domoticz';

exec('zip -r  '.$save_destination.$zip_name' '.$folder_2_save_path);
