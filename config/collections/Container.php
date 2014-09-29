<?php

use Norm\Schema\String;
use Norm\Schema\Reference;
use Norm\Schema\Integer;
use Norm\Schema\Object;
use Norm\Schema\NormArray;

use App\Schema\ContainerField;

return array(
    'observers' => array(
        '\\App\\Observer\\ContainerObserver'
    ),
    'schema' => array(
        'name' => String::create('name')->filter('trim|required'),

        'origin' => Reference::create('origin')->to('Container', 'name', 'name')->set('transient', true),
        'template' => Reference::create('template')->to('Template', 'name', 'name')->by(null, array('name' => 1))
            ->set('transient', true),

        'memlimit' => String::create('memlimit', 'Memory Limit'),
        'memswlimit' => String::create('memswlimit', 'Mem+Swap Limit'),
        'cpus' => String::create('cpus', 'CPUS'),
        'cpu_shares' => String::create('cpu_shares', 'CPU Shares'),

        'networks' => NormArray::create('networks')->set('hidden', true),

        'state' => ContainerField::create('state'),
        'pid' => ContainerField::create('pid', 'PID'),
        'ip' => ContainerField::create('ip', 'IP Address'),
        'cpu_use' => ContainerField::create('cpu_use'),
        'blkio_use' => ContainerField::create('blkio_use'),
        'memory_use' => ContainerField::create('memory_use'),
        'link' => ContainerField::create('link'),
        'tx_bytes' => ContainerField::create('tx_bytes'),
        'rx_bytes' => ContainerField::create('rx_bytes'),
        'total_bytes' => ContainerField::create('total_bytes'),


        // 'actual_ip_address' => String::create('actual_ip_address', 'Actual IP Address')->set('readonly', true),
        // 'config' => Object::create('config'),
    ),
);
