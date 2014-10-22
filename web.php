<?php
/**
 * Created by PhpStorm.
 * User: kag
 * Date: 10/15/14
 * Time: 9:32 AM
 */
include_once('api_main.php');
class WebCabinetApi extends API
{
    var $context='web';
    function WebCabinetApi($suid){
        $this->suid=$suid;
    }

    function get_vpn_const(){
        return $this->call_func('web_cabinet.get_vpn_const',array(),'Users');
    }

    function call_api($params){
        $params['suid']=$this->suid;
        parent::call_api($params);
    }

}