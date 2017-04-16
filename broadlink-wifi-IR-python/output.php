<?php

exec('python locatrm.py', $output, $ret_code);
echo json_encode($output);

//done :)
