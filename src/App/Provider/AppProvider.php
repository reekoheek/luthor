<?php

namespace App\Provider;

use \App\Helper\Table;
use \App\Helper\Form;
use \App\Helper\SearchButtonGroup;

class AppProvider extends \Bono\Provider\Provider {
    public function initialize() {
        $app = $this->app;
        $this->app->hook('bono.controller.before', function($options) use ($app) {
            if ($options['method'] === 'search') {

                $searchButtonGroup = new SearchButtonGroup();
                $this->app->response->set('_searchButtonGroup', $searchButtonGroup);
            } else {
                $form = new Form($options['controller']->clazz);
                $this->app->response->set('_form', $form);
            }
        });
    }
}