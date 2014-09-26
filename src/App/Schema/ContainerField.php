<?php

namespace App\Schema;

use \Norm\Schema\String;
use App\LXC\ContainerList;

class ContainerField extends String
{
    protected $containers;

    public function __construct($name, $label = null)
    {
        parent::__construct($name, $label);

        $this['readonly'] = true;
        $this['transient'] = true;

        $this->containers = ContainerList::getInstance();
    }

    public function formatPlain($value, $entry = null)
    {
        if (empty($entry['name'])) {
            return;
        }
        $container = $this->containers->findOne($entry['name']);

        switch ($this['name']) {
            case 'state':
            case 'pid':
            case 'ip':
            case 'link':
                return $container[$this['name']];
            case 'cpu_use':
                return sprintf('%.2fs', $container[$this['name']] / 1000000000);
            case 'blkio_use':
            case 'memory_use':
                return sprintf('%.2f MB', $container[$this['name']] / 1000000);
            case 'tx_bytes':
            case 'rx_bytes':
            case 'total_bytes':
                return sprintf('%.2f KB', $container[$this['name']] / 1000);
            default:
                throw new \Exception('Unhandled format for field: "'.$this['name'].'"');
        }
    }
}
