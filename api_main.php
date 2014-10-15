<?php
/**
 * Created by PhpStorm.
 * User: kag
 * Date: 10/15/14
 * Time: 9:13 AM
 */
class API
{
    var $remoute_addr;
    var $hash;
    var $config;
    var $system_api=False;
    var $context;
    private $auth_url='';

    function log_it($msg)
    {
        $msg = date('Y-m-d h:i:s ') . $msg . "\n";
        $f = fopen('/var/log/api.log', 'a+');
        fwrite($f, $msg);
        fclose($f);
    }

    public function __construct($context='webcash',$system_api=True){
        $this->system_api=$system_api;
        if (!$this->context) {
            $this->context = $context;
        }
        $this->API();
        $this->auth_url='http://'.$this->remoute_addr.'/admin/';
        if(!$this->system_api){
            $this->login();
        }

    }

    function get_config()
    {
        $filedata = file_get_contents('/cfg/config.json');
        if ($filedata) {
            $this->config = json_decode($filedata, true);
        }
        else{
            $this->config=null;
        }
    }

    private function login(){
        $login=$this->config['api']['login'][0];
        $pass=$this->config['api']['password'][0];
        $params_ar=array(
            "username"=>$login,
            "password"=>$pass,
            "api" => "Y",
            "koko" => "koko",
            "format"=>"json"
        );
        $params_ar=array_map('urlencode', $params_ar);
        $params='?';
        $params .= http_build_query($params_ar);
        $response=\Httpful\Request::get($this->auth_url.$params)->send();
        $this->hash=$response->body;


    }

    function API()
    {
        $this->get_config();
        if ($this->config and $this->config['api'] and $this->config['api']['remoute_addr']){
            $this->remoute_addr = $this->config['api']['remoute_addr'][0];
        }
        else{
            $this->remoute_addr='169.1.80.82';
        }
    }

    function call_api($params){
        return $this->__call_api($params);
    }


    /**
     * @deprecated
     * Старый вызов api. Начинаем уходить от него.
     */
    function __call_api($params)
    {
        $params['format'] = 'json';
        $params['hash_key'] = $this->hash;
        $pattern='http://%s/rest_api/?%s';
        if($this->system_api){
            $pattern='http://%s/system_api/?%s';
            if ($this->config and array_key_exists('network',$this->config) and array_key_exists('api.' . $this->context . '.psw',$this->config['network'])) {
                $params['psw'] = $this->config['network']['api.' . $this->context . '.psw'][0];
            }
            $params['context']=$this->context;
        }
        $encoded_params = http_build_query($params);
        $api_url = vsprintf($pattern, array($this->remoute_addr, $encoded_params,));
        // создание нового cURL ресурса
        $this->log_it($api_url);
        $ch = curl_init();
        // установка URL и других необходимых параметров
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //сохранять полученные COOKIE в файл
        curl_setopt($ch, CURLOPT_COOKIEJAR, $_SERVER['DOCUMENT_ROOT'] . '/cookie');
        //отсылаем серверу COOKIE полученные от него при авторизации
        curl_setopt($ch, CURLOPT_COOKIEFILE, $_SERVER['DOCUMENT_ROOT'] . '/cookie');
        // загрузка страницы и выдача её браузеру
        $get_res = curl_exec($ch);
        $content = curl_multi_getcontent($ch);
        // завершение сеанса и освобождение ресурсов
        curl_close($ch);
        $result = json_decode($content, true);

        return $result;
    }



    function get_field($res, $field)
    {
        try {
            if ($field == 'pk')
                return $res['result']['result'][0][$field];
            return $res['result']['result'][0]['fields'][$field];
        } catch (Exception $e) {
            return false;
        }
    }

    function arr_to_args($arr){
        return json_encode($arr);
    }

    function call_func($func_name,$params,$model_name='Abonents'){
        $client = new API(True);
        $api_params = array(
            'model' => $model_name,
            'arg1' => $this->arr_to_args($params),
            'method1' => $func_name,
        );
        $res_arr = $client->call_api($api_params);
        $this->log_it($func_name.': API_REQUEST: ' . print_r($api_params, true));
        $this->log_it($func_name.': API_RESULT: ' . print_r($res_arr, true));
        return $res_arr;
    }


}

if (basename($argv[0]) == basename(__FILE__)) {
    $params = array(
        'model' => 'Abonents',
        'method1' => 'objects.filter',
        'arg1' => '{"is_folder": 1}',
    );
    $client = new API();
    $res_arr = $client->call_api($params);
}