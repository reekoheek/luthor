<?php

use Norm\Schema\String;
use Norm\Schema\Boolean;

return array(
    'schema' => array(
        'name' => String::create('name'),
        'uuid' => String::create('uuid', 'UUID')->set('readonly', true),
        'state' => Boolean::create('state')->set('readonly', true),
        'autostart' => Boolean::create('autostart', 'Auto Start'),
        'persistent' => Boolean::create('persistent'),
        'bridge' => String::create('bridge'),
        'ip_address' => String::create('ip_address', 'IP Address'),
        'netmask' => String::create('netmask'),
        'dhcp_start' => String::create('dhcp_start', 'DHCP Start'),
        'dhcp_end' => String::create('dhcp_end', 'DHCP End'),
    ),
);
