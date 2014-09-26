<?php

namespace App\Controller;

use Norm\Controller\NormController;
use \App\LXC\NetworkList;

class NetworkController extends NormController
{
    protected $networks;

    public function __construct($app, $name)
    {
        parent::__construct($app, $name);

        $this->map('/null/populate', 'populate')->via('GET');

        $this->networks = NetworkList::getInstance();
    }

    // function search() {
    //     $entries = $this->networks->find();

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

    // public function delete($id)
    // {
    //     $model = $this->collection->findOne($id);
    //     if (is_null($model)) {
    //         $this->app->notFound();
    //     }

    //     if ($this->request->isPost()) {
    //         try {
    //             if ($model['state'] != 0) {
    //                 throw new \Exception('Network is running, stop the network first to delete.');
    //             }

    //             if (!is_null($this->networks->findOne($model['name']))) {
    //                 $this->networks->destroy($model['name']);
    //             }

    //             $model->remove();
    //         } catch (\Exception $e) {
    //             return $this->flashNow('error', $e->getMessage());
    //         }

    //         $this->flash('info', 'Network deleted.');
    //         $this->redirect($this->getBaseUri());
    //     }

    // }

    public function populate()
    {
        $entries = $this->networks->find();

        foreach ($entries as $key => $entry) {
            $model = $this->collection->findOne(array('name' => $entry['name']));
            if (is_null($model)) {
                $model = $this->collection->newInstance();
            }
            $model->set($entry->toArray());
            $model->save();
        }

        h('notification.info', 'Existing networks populated.');

        $this->redirect($this->getBaseUri());
    }
}
