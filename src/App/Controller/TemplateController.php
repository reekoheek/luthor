<?php

namespace App\Controller;

use App\LXC\TemplateList;
use Norm\Controller\NormController;

class TemplateController extends NormController
{

    protected $templates;

    public function __construct($app, $name)
    {
        parent::__construct($app, $name);

        $this->map('/null/populate', 'populate')->via('GET');

        $this->templates = new TemplateList($app->config('lxc'));
    }

    public function populate()
    {
        $entries = $this->templates->find();

        foreach ($entries as $key => $entry) {
            $model = $this->collection->findOne(array('name' => $entry['name']));
            if (is_null($model)) {
                $model = $this->collection->newInstance();
            }
            $model->set($entry->toArray());
            $model->save();
        }
        h('notification.info', 'Existing templates populated.');
        $this->redirect($this->getBaseUri());
    }

    // public function create() {
    //     if ($this->request->isPost()) {
    //         $post = $this->request->post();
    //         $this->templates->save(NULL, $post);

    //         $this->_populate($post);

    //         $this->flash('info', 'Template created.');
    //         $this->redirect($this->getBaseUri());
    //     }
    // }

    // public function update($id) {
    //     if ($this->request->isPost() || $this->request->isPut()) {
    //         $post = $this->request->post();

    //         $entry = $this->templates->save($post['name'], $post);

    //         $this->_populate($entry);

    //         $this->flash('info', 'Template updated.');
    //         $this->redirect($this->getBaseUri());
    //     }

    //     parent::update($id);
    // }

    // public function delete($id) {

    //     if (!$this->request->isGet()) {
    //         $model = $this->collection->findOne($id);
    //         $this->templates->delete($model['name']);
    //     }

    //     return parent::delete($id);
    // }
}
