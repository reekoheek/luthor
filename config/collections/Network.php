<?php

use Norm\Schema\String;
use Norm\Schema\Boolean;
use Norm\Schema\Text;

return array(
    'observers' => array(
        '\\App\\Observer\\NetworkObserver'
    ),
    'schema' => array(
        'name' => String::create('name')->filter('unique:Network,name'),
        'uuid' => String::create('uuid', 'UUID')->set('readonly', true),
        'state' => Boolean::create('state')->set('readonly', true),
        'autostart' => Boolean::create('autostart', 'Auto Start'),
        // 'persistent' => Boolean::create('persistent'),
        'bridge' => String::create('bridge'),
        'ip_address' => String::create('ip_address', 'IP Address')->set('transient', true),
        'netmask' => String::create('netmask')->set('transient', true),
        'dhcp_start' => String::create('dhcp_start', 'DHCP Start')->set('transient', true),
        'dhcp_end' => String::create('dhcp_end', 'DHCP End')->set('transient', true),
        // 'xml' => Text::create('xml'),
        // 'ip_address' => String::create('ip_address', 'IP Address'),
        // 'netmask' => String::create('netmask'),
        // 'dhcp_start' => String::create('dhcp_start', 'DHCP Start'),
        // 'dhcp_end' => String::create('dhcp_end', 'DHCP End'),
    ),
);
