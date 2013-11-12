<?php

namespace App\Test\LXC;

use App\LXC\LXC;

class LXCTest extends \PHPUnit_Framework_TestCase {

    public function setUp() {
        $this->lxc = new LXC(array(
            'directory' => '/var/lib/lxc'
        ));
    }

    public function testList() {
        $containers = $this->lxc->find();

        foreach ($containers as $key => $container) {
            $this->assertNotEmpty($container['state']);
        }
    }
}