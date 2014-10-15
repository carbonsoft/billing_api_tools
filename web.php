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
    var $context='web';
    function get_vpn_const(){
        return $this->call_func('web_cabinet.get_vpn_const',array(),'Users');
    }

}