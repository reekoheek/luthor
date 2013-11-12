<?php

namespace App\Controller;

use \Bono\Controller\RestController;
use \App\LXC\Net;

class NetworkController extends RestController {
    protected $net;

    public function __construct($app, $name) {
        parent::__construct($app, $name);

        $this->net = new Net($app->config('lxc'));

        $schema = $this->app->config('norm.schemas');
        $this->data['_schema'] = $schema['Network'];
    }

    function search() {
        $this->data['_actions'] = array(
            // 'start' => array('label' => $toggleOnOff, 'url' => $this->getBaseUri().'/%s/onoff'),
            'update' => NULL,
            'delete' => NULL,
        );

        $entries = $this->net->find();

        $this->data['entries'] = $entries;
    }

    function create() {
        throw new \Exception(__METHOD__.' unimplemented yet!');
    }

    function read($id) {
        throw new \Exception(__METHOD__.' unimplemented yet!');

    }

    function update($id) {
        throw new \Exception(__METHOD__.' unimplemented yet!');

    }

    function delete($id) {
        throw new \Exception(__METHOD__.' unimplemented yet!');

    }

}