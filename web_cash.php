<?php
/**
 * Created by PhpStorm.
 * User: kag
 * Date: 10/15/14
 * Time: 9:32 AM
 */
include_once('api_main.php');
class WebCashApi extends API
{
    var $context='webcash';
    function get_vpn_const(){
        return $this->call_func('webcash.get_vpn_const',array());
    }

}