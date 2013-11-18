<?php

namespace App\Controller;

use App\LXC\Template;
use Norm\Controller\NormController;

class TemplateController extends NormController {

    protected $template;

    public function __construct($app, $name) {
        parent::__construct($app, $name);

        $this->map('/null/populate', 'populate')->via('GET');

        $this->template = new Template($app->config('lxc'));

        $schema = $this->app->config('norm.schemas');
    }

    // public function search() {
    //     $entries = $this->template->find();

    //     $this->data['entries'] = $entries;
    // }

    public function populate() {
        $entries = $this->template->find();
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

        $model->set($entry);
        $model->save();
    }

    public function create() {
        if ($this->request->isPost()) {
            $this->template->save(NULL, $this->data['entry']);

            $this->populateOne($this->data['entry']);

            $this->flash('info', 'Template created.');
            $this->redirect($this->getBaseUri());
        }
    }

    public function update($id) {
        if ($this->request->isPost() || $this->request->isPut()) {
            $model = $this->collection->findOne($id);
            $entry = $this->template->save($this->data['entry']['name'], $this->data['entry']);
            $this->populateOne($entry, $model);

            $this->flash('info', 'Template updated.');
            $this->redirect($this->getBaseUri());
        }

        parent::update($id);
    }

    public function delete($id) {

        if (!$this->request->isGet()) {
            $model = $this->collection->findOne($id);
            $this->template->delete($model['name']);
        }

        return parent::delete($id);
    }
}