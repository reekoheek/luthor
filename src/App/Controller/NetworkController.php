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
        $this->map('/:id/stop', 'stop')->via('GET');
        $this->map('/:id/start', 'start')->via('GET');
        $this->map('/:id/autostart', 'autostart')->via('GET');

        $this->networks = NetworkList::getInstance();
    }

    public function populate()
    {
        try {
            $this->collection->remove();
            $entries = $this->networks->find();

            foreach ($entries as $key => $entry) {
                if ($entry['persistent']) {
                    $model = $this->collection->findOne(array('name' => $entry['name']));
                    if (is_null($model)) {
                        $model = $this->collection->newInstance();
                    }
                    $model->set($entry->toArray());
                    $model->save();
                }
            }

            h('notification.info', 'Existing networks populated.');
        } catch (\Exception $e) {
            h('notification.error', $e);
        }

        $this->redirect($this->getBaseUri());
    }

    public function stop($id)
    {
        $model = $this->collection->findOne($id);
        if (is_null($model)) {
            $this->app->notFound();
            return;
        }

        try {
            $network = $this->networks->findOne($model['name']);
            $network->stop();


            $network = $this->networks->findOne($model['name']);
            $model->set($network->toArray());
            $model->save();

            h('notification.info', 'Container stopped.');

        } catch (\Exception $e) {
            h('notification.error', $e);
        }

        $this->redirect($this->getBaseUri());
    }

    public function start($id)
    {
        $model = $this->collection->findOne($id);
        if (is_null($model)) {
            $this->app->notFound();
            return;
        }

        try {
            $network = $this->networks->findOne($model['name']);
            $network->start();


            $network = $this->networks->findOne($model['name']);
            $model->set($network->toArray());
            $model->save();

            h('notification.info', 'Container started.');

        } catch (\Exception $e) {
            h('notification.error', $e);
        }

        $this->redirect($this->getBaseUri());
    }

    public function autostart($id)
    {
        $model = $this->collection->findOne($id);
        if (is_null($model)) {
            $this->app->notFound();
            return;
        }

        try {
            $network = $this->networks->findOne($model['name']);
            $model->set($network->toArray());
            $model['autostart'] = !$model['autostart'];
            $model->save();

            h('notification.info', 'Container set '.($model['autostart'] ? 'enable' : 'disable').' autostart.');

        } catch (\Exception $e) {
            h('notification.error', $e);
        }

        $this->redirect($this->getBaseUri());
    }
}
