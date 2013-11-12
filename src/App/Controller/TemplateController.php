<?php

namespace App\Controller;

use App\LXC\LXC;
use Bono\Controller\RestController;

class TemplateController extends RestController {

    public function __construct($app, $name) {
        parent::__construct($app, $name);

        $this->lxc = new LXC($app->config('lxc'));

        $schema = $this->app->config('norm.schemas');
        $this->data['_schema'] = $schema['Template'];

        $this->data['_templates'] = $this->lxc->getTemplates();
    }

    public function search() {

        $this->data['_actions'] = array(
            'update' => NULL,
            'delete' => NULL,
        );

        $entries = $this->lxc->findTemplates();

        $this->data['entries'] = $entries;
    }

    public function create() {
        if (!$this->request->isGet()) {
            $this->lxc->saveTemplate(NULL, $this->data['entry']);

            $this->flash('info', 'Template is inserted.');
            $this->redirect($this->getBaseUri());
        }
    }

    public function read($id) {
        throw new \Exception(__METHOD__.' not implemented yet!');
    }

    public function update($id) {
        if ($this->request->isGet()) {
            $template = $this->lxc->getTemplate($id);
            if (empty($template)) {
                $this->app->notFound();
            }
            $this->data['entry'] = $template;
        } else {
            $this->lxc->saveTemplate($id, $this->data['entry']);

            $this->flash('info', 'Template is updated.');
            $this->redirect($this->getBaseUri());
        }
    }

    public function delete($id) {
        if (!$this->request->isGet()) {
            $this->lxc->deleteTemplate($id);

            $this->flash('info', 'Template is deleted.');
            $this->redirect($this->getBaseUri());
        }
    }
}