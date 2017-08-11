<?php

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
            if (substr($tres[1], 0, 7) == '192.168') {
                if (!array_key_exists($tres[1], $result)) {
                    $result[$tres[1]] = array();
                }
                $result[$tres[1]]['download'] = $tres[3];
            } else if (substr($tres[0], 0, 7) == '192.168') {
                if (!array_key_exists($tres[0], $result)) {
                    $result[$tres[0]] = array();
                }
                $result[$tres[0]]['upload'] = $tres[3];
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
