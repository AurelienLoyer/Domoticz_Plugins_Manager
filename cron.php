<?php

$error = false;
$result = array();

if(isset($_GET['save'])){

    $request_body = file_get_contents('php://input');

    if($request_body){
        $data = json_decode($request_body);
        $plugin = $data->plugin;
        $config = $data->data;
    }else{
        $error = true;
    }

    if($plugin && $config){

        $file_config = "cron.json";
        //file_put_contents($file_config, json_encode($config));
        $result = array(
            "error" => false,
            "message" => "Save plugin cron done !"
        );
    }else{
        $error = true;
    }

    if($error){
        $result = array(
            "error" => true,
            "message" => "Missing data for saving cron file"
        );
    }

    echo json_encode($result);

}
