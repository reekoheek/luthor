<?php

namespace App\Controller;

use Norm\Controller\NormController;
use \App\LXC\Net;

class NetworkController extends NormController {
    protected $net;

    public function __construct($app, $name) {
        parent::__construct($app, $name);

        $this->map('/null/populate', 'populate')->via('GET');

        $this->net = new Net($app->config('lxc'));

        $schema = $this->app->config('norm.schemas');
        $this->data['_schema'] = $schema['Network'];
    }

    // function search() {
    //     $entries = $this->net->find();

    //     $this->data['entries'] = $entries;
    // }

    // function create() {
    //     throw new \Exception(__METHOD__.' unimplemented yet!');
    // }

    // function read($id) {
    //     throw new \Exception(__METHOD__.' unimplemented yet!');

    // }

    // function update($id) {
    //     throw new \Exception(__METHOD__.' unimplemented yet!');

    // }

    function delete($id) {
        $model = $this->collection->findOne($id);
        if (is_null($model)) {
            $this->app->notFound();
        }

        if ($this->request->isPost()) {
            try {
                if ($model['state'] != 0) {
                    throw new \Exception('Network is running, stop the network first to delete.');
                }

                if (!is_null($this->net->findOne($model['name']))) {
                    $this->net->destroy($model['name']);
                }

                $model->remove();
            } catch(\Exception $e) {
                return $this->flashNow('error', $e->getMessage());
            }

            $this->flash('info', 'Network deleted.');
            $this->redirect($this->getBaseUri());
        }

    }

    public function populate() {
        $entries = $this->net->find();

        foreach ($entries as $key => $entry) {
            $model = $this->collection->findOne(array('name' => $key));
            $this->populateOne($entry, $model);
        }
        $this->flash('info', 'Network populated.');
        $this->redirect($this->getBaseUri());
    }

    protected function populateOne($entry, $model = NULL) {
        if (is_null($model)) {
            $model = $this->collection->newInstance();
        } elseif (!is_object($model)) {
            $model = $this->collection->findOne($model);
        }

        $model->set($entry);
        $model->save();
    }
}