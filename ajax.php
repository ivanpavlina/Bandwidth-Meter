<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

require('conf.php');
require('routeros_api.class.php');

$cmd  = filter_input(INPUT_POST, "cmd");
if (isset($cmd)) {
    switch($cmd){
    case 'get_bandwidth_data':
        $url = "http://".MHOST."/accounting/ip.cgi";
        $res = file_get_contents($url);
        $res = trim($res);
        $arr = explode(PHP_EOL, $res);

        $result = array();
        foreach($arr as $dtmp){
            $tres = explode(' ', $dtmp);
            $bandwidth = $tres[2];
            if (substr($tres[1], 0, 7) == '192.168') {
                $name = $tres[1];
                if (array_key_exists($name, $result)) {
                    $result[$name]['download'] += $bandwidth;
                } else {
                    $result[$name] = array('download'=>$bandwidth);
                }
            } else if (substr($tres[0], 0, 7) == '192.168') {
                $name = $tres[0];
                if (array_key_exists($name, $result)) {
                    $result[$name]['upload'] += $bandwidth;
                } else {
                    $result[$name] = array('upload'=>$bandwidth);
                }
            }
        }
        echo json_encode($result, JSON_NUMERIC_CHECK);
        break;
    case 'get_hosts':
        $api = new RouterosAPI();
        $api->timeout = 2;
        $api->attempts = 3;
        $api->debug = False;

        $api->connect(MHOST, MUSERNAME, MPASSWORD);
        $api->write('/ip/dhcp-server/lease/print');
        $api_res = $api->read(True);
        $api->disconnect();

        $result = array();
        foreach($api_res as $itm){
            $result[$itm['active-address']] = array($itm['comment'] ? $itm['comment'] : $itm['host-name']);
        }

        echo json_encode($result, JSON_NUMERIC_CHECK);
        break;
    }
}
