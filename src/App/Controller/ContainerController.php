<?php

namespace App\Controller;

use Norm\Norm;
use App\LXC\LXC;
use App\LXC\Template;
use Bono\Helper\URL;
use Norm\Controller\NormController;

class ContainerController extends NormController {
    public function __construct($app, $name) {
        parent::__construct($app, $name);

        $this->map('/null/populate', 'populate')->via('GET');
        $this->map('/:id/onoff', 'onoff')->via('GET');
        $this->map('/:id/poke', 'poke')->via('GET');

        $this->lxc = LXC::getInstance();
        $this->lxcTemplate = new Template($this->app->config('lxc'));
    }

    public function create() {
        $templates = \Norm\Norm::factory('Template')->find(array('luthor_version' => 'v1'));
        $this->data['_templates'] = array();
        foreach ($templates as $key => $template) {
            $this->data['_templates'][$template['name']] = $template['name'];
        }

        if ($this->request->isPost()) {
            try {
                $entry = $this->lxc->create($this->data['entry']);
                $this->populateOne($entry);
            } catch(\Exception $e) {
                return $this->flashNow('error', $e->getMessage());
            }

            $this->flash('info', 'Container created.');
            $this->redirect($this->getBaseUri());
        }
    }

    public function update($id) {
        if ($this->request->isPost() || $this->request->isPut()) {
            $this->lxc->setMemLimit($this->data['entry']['name'], $this->data['entry']['memlimit']);
            $this->lxc->setMemSwLimit($this->data['entry']['name'], $this->data['entry']['memswlimit']);
            $this->lxc->setCPUS($this->data['entry']['name'], $this->data['entry']['cpus']);
            $this->lxc->setCPUShares($this->data['entry']['name'], $this->data['entry']['cpu_shares']);
        }
        return parent::update($id);
    }

    public function delete($id) {
        $model = $this->collection->findOne($id);
        if (is_null($model)) {
            $this->app->notFound();
        }

        if ($this->request->isPost()) {
            try {
                if ($model['state'] != 0) {
                    throw new \Exception('Container is running, stop the container first to delete.');
                }

                if (!is_null($this->lxc->findOne($model['name']))) {
                    $this->lxc->destroy($model['name']);
                }

                $model->remove();
            } catch(\Exception $e) {
                return $this->flashNow('error', $e->getMessage());
            }

            $this->flash('info', 'Container deleted.');
            $this->redirect($this->getBaseUri());
        }
    }

    public function onoff($id) {
        $model = $this->collection->findOne($id);

        if (is_null($model)) {
            $this->app->notFound();
            return;
        }

        try {
            if ($model['state'] == 0) {
                $info = $this->lxc->start($model['name']);
                $this->populateOne($info, $model);
                $this->flash('info', 'Container started.');
            } else {
                $info = $this->lxc->stop($model['name']);
                $this->populateOne($info, $model);
                $this->flash('info', 'Container stopped.');
            }
        } catch(\Exception $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->getBaseUri());
    }

    public function populate() {
        $entries = $this->lxc->find();
        foreach ($entries as $key => $entry) {
            $model = $this->collection->findOne(array('name' => $key));
            $this->populateOne($entry, $model);
        }
        $this->flash('info', 'Container populated.');
        $this->redirect($this->getBaseUri());
    }

    protected function populateOne($entry, $model = NULL) {
        if (is_null($model)) {
            $model = $this->collection->newInstance();
        } elseif (!is_object($model)) {
            $model = $this->collection->findOne($model);
        }

        $model->set('name', $entry['name']);
        $model->set('state', $entry['state'] ?: 0);
        $model->set('pid', $entry['pid'] ?: 0);
        $model->set('ip_address', isset($entry['ip_address']) ? $entry['ip_address'] : '');
        $model->set('memlimit', $entry['memlimit']);
        $model->set('memswlimit', $entry['memswlimit']);
        $model->set('cpus', $entry['cpus']);
        $model->set('cpu_shares', $entry['cpu_shares']);
        $model->save();
    }
}