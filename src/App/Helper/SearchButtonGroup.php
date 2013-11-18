<?php

namespace App\Helper;

class SearchButtonGroup {
    public function __construct() {
        $this->app = \Bono\App::getInstance();

        $config = $this->app->config('component.searchButtonGroup');
        if (isset($config['mapping'][$this->app->controller->clazz])) {
            $this->config = $config['mapping'][$this->app->controller->clazz];
        } else {
            $this->config = $config['default'];
        }

        $this->view = new \Slim\View();
        $this->view->setTemplatesDirectory($this->app->config('templates.path'));
        $this->view->set('self', $this);
        $this->view->set('controller', $this->app->controller);
        $this->view->set('config', $this->config);

    }

    public function show() {
        return $this->view->fetch('components/searchButtonGroup.php');
    }
}