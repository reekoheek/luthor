<?php

namespace App\Model;

use App\LXC\LXC;

class Container extends \Norm\Model {

    public function get_mem_usage() {
        return LXC::getInstance()->fetchMemUsage($this['name']);
    }

}