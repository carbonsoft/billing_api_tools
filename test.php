<?php

include_once('api_main.php');
class Billing extends API{
    var $login='root';
    var $pass='servicemode';
    var $remoute_addr='10.90.170.155:8082';
}
if (basename($argv[0]) == basename(__FILE__)) {
    $params = array(
        'model' => 'Abonents',
        'method1' => 'objects.filter',
        'arg1' => '{"is_folder": 1}',
    );
    $client = new Billing('',False);
    $res_arr = $client->call_api($params);
    var_dump($res_arr);
}