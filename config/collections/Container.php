<?php

use Norm\Schema\String;
use Norm\Schema\Reference;
use Norm\Schema\Integer;
use Norm\Schema\Object;

return array(
    'model' => '\\App\\Model\\Container',
    'schema' => array(
        'name' => String::create('name')->filter('trim|required'),
        'network' => Reference::create('network')->to('Network', 'name')->filter('trim|required'),
        'ip_address' => String::create('ip_address', 'IP Address'),
        'memlimit' => String::create('memlimit', 'Memory Limit'),
        'memswlimit' => String::create('memswlimit', 'Mem+Swap Limit'),
        'cpus' => String::create('cpus', 'CPUS'),
        'cpu_shares' => String::create('cpu_shares', 'CPU Shares'),
        'state' => Integer::create('state')->set('readonly', true),
        'pid' => Integer::create('pid', 'PID')->set('readonly', true),
        'actual_ip_address' => String::create('actual_ip_address', 'Actual IP Address')->set('readonly', true),
        'config' => Object::create('config'),
    ),
);
