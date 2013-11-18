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
        $templates = \Norm\Norm::factory('Template')->find(array('luthor_version!ne' => ''));
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

    // FIXME reekoheek: update should be redo
    // public function update($id) {
    //     if ($this->request->isPost()) {
    //         $this->flash('info', 'Container updated.');
    //         $this->redirect($this->getBaseUri());
    //     } else {
    //         $this->data['entry'] = $this->lxc->getInfo($id);

    //         if (is_null($this->data['entry'])) {
    //             $this->app->notFound();
    //         }
    //     }
    // }

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
        $this->flash('info', 'Template populated.');
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
        $model->save();
    }
}