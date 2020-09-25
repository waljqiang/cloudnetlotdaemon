<?php
require_once __DIR__ . "/../vendor/autoload.php";
use Waljqiang\Encode\Encode;
try{
	$encode = new Encode();
    $encode->init("waljqiang",Encode::AES,[
        "key" => "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFG"
    ]);
	$data = '{
"fun": "1",
"service_type":"1",
"bind_account":"01aab9d2131ad77f2556ff29f102542a",
"type": "0",
"hal_info": {
"productname": "FR350",
"producttype": "FR350",
"chip": "RTL8197F",
"chip_product":" RTL8197F-FR350",
"flash": "128M",
"ram": "512M",
"cpu": "1GHZ",
"mac": "XXXXXXXXXXXX"
    },	
"wifi_info": {
"vap_max_count": "4",
"wlan_countrycode": "1",
"vap": [
            {
"vap_id": "0",
"vap_enable": "1",
"vap_bssid": "78D38DB98EE0",
"vap_ssid": "ssid2222222222222",
"vap_hide_ssid": "0",
"vap_vlan_id": "0",
"vap_auth": "NONE",
"vap_authmode": "0",
"vap_wep_key": "",
"vap_psk_key": "66666666"
            },
            {
"vap_id": "1",
"vap_enable": "1",
"vap_bssid": "78D38DB98EE0",
"vap_ssid": "WLAN1wirelessvap",
"vap_hide_ssid": "0",
"vap_vlan_id": "0",
"vap_auth": "NONE",
"vap_authmode": "0",
"vap_wep_key": "",
"vap_psk_key": ""
            },
            {
"vap_id": "2",
"vap_enable": "1",
"vap_bssid": "78D38DB98EE0",
"vap_ssid": "WLAN2wirelessvap",
"vap_hide_ssid": "0",
"vap_vlan_id": "0",
"vap_auth": "NONE",
"vap_authmode": "0",
"vap_wep_key": "",
"vap_psk_key": ""
            },
            {
"vap_id": "3",
"vap_enable": "1",
"vap_bssid": "78D38DB98EE0",
"vap_ssid": "WLAN3wirelessvap",
"vap_hide_ssid": "0",
"vap_vlan_id": "0",
"vap_auth": "NONE",
"vap_authmode": "0",
"vap_wep_key": "",
"vap_psk_key": ""
            }
        ],
"wlan_has80m": "0",
"wlan_channel": "1",
"wlan_channel_width": "0",
"wlan_coveragethreshold": "-90",
"wlan_txpower": "20",
"wlan_sta_num": "0"
    },
"user_info": {
"count": "10",
"userlist": [
            {
"name": "XX的手机",
"user_type": "0",
"is_kids": "0",
"is_tv": "0",
"ip": "192.168.0.11",
"mac": "78D38DB98EE0",
"up_bytes": "435",
"up_pkts": "23",
"down_bytes": "3445345",
"down_pkts": "2344",
"conn_count": "345"
            }
        ]
    },
"management_dev": {
"count": "1",
"Info": [
            {
"dev_type": "XD9508S",
"dev_ip": "192.168.188.2",
"dev_mac": "78D38DB98EE0",
"dev_onlietime": "2:31:44",
"dev_status": "1",
"dev_swver": "XD9508S-SPI-AP-2T2R-V1.5-B20141222172047",
"vap_max_count": "4",
"vap": [
                    {
"vap_id": "0",
"vap_enable": "1",
"vap_bssid": "78D38DB98EE0",
"vap_ssid": "ssid2222222222222",
"vap_hide_ssid": "0",
"vap_vlan_id": "0",
"vap_auth": "NONE",
"vap_authmode": "0",
"vap_wep_key": "",
"vap_psk_key": "66666666"
                    },
                    {
"vap_id": "1",
"vap_enable": "1",
"vap_bssid": "78D38DB98EE0",
"vap_ssid": "WLAN1wirelessvap",
"vap_hide_ssid": "0",
"vap_vlan_id": "0",
"vap_auth": "NONE",
"vap_authmode": "0",
"vap_wep_key": "",
"vap_psk_key": ""
                    },
                    {
"vap_id": "2",
"vap_enable": "1",
"vap_bssid": "78D38DB98EE0",
"vap_ssid": "WLAN2wirelessvap",
"vap_hide_ssid": "0",
"vap_vlan_id": "0",
"vap_auth": "NONE",
"vap_authmode": "0",
"vap_wep_key": "",
"vap_psk_key": ""
                    },
                    {
"vap_id": "3",
"vap_enable": "1",
"vap_bssid": "78D38DB98EE0",
"vap_ssid": "WLAN3wirelessvap",
"vap_hide_ssid": "0",
"vap_vlan_id": "0",
"vap_auth": "NONE",
"vap_authmode": "0",
"vap_wep_key": "",
"vap_psk_key": ""
                    }
                ],
"wlan_countrycode": "1",
"wlan_has80m": "0",
"wlan_mode": "1",
"wlan_channel": "1",
"wlan_coveragethreshold": "-90",
"wlan_txpower": "20",
"wlan_user_num": "0"
            }
        ]
    },
"gw_info": {
"lan_ip": "192.168.10.1",
"lan_subnet": "255.255.255.0",
"lan_mac": "78D38DB98EE0",
"lan_gateway": "192.168.10.254",
"lan_dns": "114.114.114.114/114.114.115.115",
"lan_dhcp_server_status": "1",
"lan_dhcp_server_startip": "192.168.10.2",
"lan_dhcp_server_endip": "192.168.10.200",
"wan_conn_type": "2",
"wan_ip": "12.123.4.12",
"wan_subnet": "255.255.255.255",
"wan_gateway": "12.123.4.1",
"wan_dns": "114.114.114.114/114.114.115.115",
"wan_mac": "7A:D3:8D:BA:8E:E1",
"sw_name": "FR-350-GW-V3.0-B20170801144905",
"uptime": "1233423434",
" lib_ver":"1"
    },
"auth_info": {
"authenable": "0",
"auth_host": "",
"auth_port": "",
"auth_hostpath": "",
"local_authport": "",
"gw_id": "",
"trust_url": "",
"trust_mac": "",
"client_timeout": ""
    },
"timing_plan": {
"dev_timereboot_enable": "0",
"dev_timereboot_time": "3",
"wifi_time_down_enable": "0",
"wifi_time_down_starttime": "1",
"wifi_time_down_endtime": "8"
    },
"led_onoff": {
"led_off_enable": "0"
    },
"firewall_ip_filter": {
"ipfilter_enable": "0",
"ItemList": {
"data": [
                {
"id": "",
"item_devname": "",
"item_devip": "",
"item_devfromport": "",
"item_devtoport": "",
"item_status": "",
"item_timecontrol": "",
"item_weekday1": "",
"item_weekday2": "",
"item_weekday3": "",
"item_weekday4": "",
"item_weekday5": "",
"item_weekday6": "",
"item_weekday7": "",
"item_start_time": "",
"item_end_time": ""
                }
            ],
"count": "1"

        }
    },
"firewall_mac_filter": {
"macfilter_enable": "0",
"ItemList": {
"data": [
                {
"id": "",
"item_devname": "",
					"item_devmac": "",
"item_status": "",
"item_timecontrol": "",
"item_weekday1": "",
"item_weekday2": "",
"item_weekday3": "",
"item_weekday4": "",
"item_weekday5": "",
"item_weekday6": "",
"item_weekday7": "",
"item_start_time": "",
"item_end_time": ""
                }
            ],
"count": ""

        }
    },
"firewall_url_filter": {
"urlfilter_enable": "0",
"ItemList": {
"data": [
                {
"id": "",
"item_url": "",
"item_status": "",
"item_timecontrol": "",
"item_weekday1": "",
"item_weekday2": "",
"item_weekday3": "",
"item_weekday4": "",
"item_weekday5": "",
"item_weekday6": "",
"item_weekday7": "",
"item_start_time": "",
"item_end_time": ""
                }
            ],
"count": ""

        }
    },
"qos_info": {
"qos_mode": "0",
"fixedqos_itemList": {
"data": [
                {
"id": "",
"item_type": "",
"item_devmac": "",
"item_devstartip": "",
"item_devendip": "",
"item_bw_up": "",
"item_bw_down": "",
"item_bw_shared_mode": "",
"item_devname": "",
"item_status": "",
"item_timecontrol": "",
"item_weekday1": "",
"item_weekday2": "",
"item_weekday3": "",
"item_weekday4": "",
"item_weekday5": "",
"item_weekday6": "",
"item_weekday7": "",
"item_start_time": "",
"item_end_time": ""
                }
            ],
"count": "1"

        },
"smart_qos": {
"smartqos_enable": "",
"limit_down": "",
"limit_up": "",
"limit_up_rate": "",
"limit_down_rate": "",
"limit_time": "",
"limit_whitelist_enable": "",
"limit_whitelist": {
"data": [
                    {
"id": "",
"item_devmac": "",
"item_devname": ""
                    }
                ],
"count": "1"

            },
"log_enable": ""

        }
    },
"ipgroup_info": {
"ItemList": {
"data": [
                {
"id": "",
"item_name": "",
"item_startip": "",
"item_endip": "",
"item_mark": ""
                }
            ],
"count": "1"

        }
    },
"timegroup_info": {
"ItemList": {
"data": [
                {
"id": "",
"item_name": "",
"item_weekday1": "",
"item_weekday2": "",
"item_weekday3": "",
"item_weekday4": "",
"item_weekday5": "",
"item_weekday6": "",
"item_weekday7": "",
"item_start_time": "",
"item_end_time": "",
"item_mark": ""
                }
            ],
"count": "1"

        }
    }
}
';
	$res = $encode->encode($data);
	var_dump($res);
	$encode->setNonce($res["nonce"]);
	$encode->setTimeStamp($res["timestamp"]);
	$encode->setSignature($res["signature"]);
	$a = $encode->decode($res["encrypted"]);
	var_dump($a);
}catch(\Exception $e){
	var_dump($e);
}