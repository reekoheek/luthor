<?php

namespace App\Model;

use App\LXC\LXC;

class Container extends \Norm\Model {
    protected $lxc;

    public function __construct(array $attributes = array(), $options = array()) {
        parent::__construct($attributes, $options);

        $this->lxc = LXC::getInstance();

    }

    // public function get_pid() {
    //     return $this->lxc->getMemUsage($this['name']);
    // }

    public function get_mem_usage() {
        return $this->lxc->getMemUsage($this['name']);
    }

    public function get_ip_address() {
        if ($this['state'] != 0)  {
            return $this->lxc->getIPAddress($this['name']);
        }
    }
}