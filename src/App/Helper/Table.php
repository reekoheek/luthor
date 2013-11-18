<?php

namespace App\Helper;

use Bono\Helper\URL;
use Reekoheek\Util\Inflector;

class Table {
    protected $app;
    protected $clazz;
    protected $config;
    protected $schema;

    protected $actions;

    public function __construct($clazz) {
        $this->app = \Bono\App::getInstance();
        $this->clazz = $clazz;

        $collections = $this->app->config('norm.collections');
        $this->schema = $collections[$clazz]['schema'];

        $this->config = $this->app->config('component.table');

        if (!empty($this->config['mapping'][$clazz]['actions'])) {
            $this->actions = $this->config['mapping'][$clazz]['actions'];
        } elseif (!empty($this->config['default']['actions'])) {
            $this->actions = $this->config['default']['actions'];
        }

        if (!empty($this->config['mapping'][$clazz]['columns'])) {
            $this->columns = $this->config['mapping'][$clazz]['columns'];
        } elseif (!empty($this->config['default']['columns'])) {
            $this->columns = $this->config['default']['columns'];
        }

        $this->view = new \Slim\View();
        $this->view->setTemplatesDirectory($this->app->config('templates.path'));
        $this->view->set('self', $this);
    }

    public function renderColumns($entry = NULL) {
        $html = '';

        $iterator = isset($this->columns) ? $this->columns : $this->schema;

        if (is_null($entry)) {
            foreach ($iterator as $key => $valueGetter) {
                if ($key[0] !== '$') {
                    $html .= '<th>'.$this->schema[$key]['label'].'</th>';
                }
            }
        } else {
            $first = true;
            foreach ($iterator as $key => $valueGetter) {
                if ($key[0] !== '$') {
                    $html .= '<td>';
                    if ($first) {
                        $url = URL::site($this->app->controller->getBaseUri().'/'.$entry['$id']);
                        $html .= '<a href="'.$url.'">';
                    }
                    if (isset($valueGetter) && $iterator !== $this->schema) {
                        if ($valueGetter) {
                            $html .= $valueGetter(@$entry[$key], $entry);
                        }
                    } else {
                        $html .= $this->schema[$key]->cell(@$entry[$key], $entry);
                    }
                    if ($first) {
                        $html .= '</a>';
                        $first = false;
                    }
                    $html .= '</td>';
                }
            }
        }
        return $html;
    }

    public function renderAction($entry = NULL) {
        if (isset($this->actions)) {

            if (is_null($entry)) {
                return '<th style="width:1px">&nbsp;</th>';
            } else {
                $html = '<td>';
                foreach ($this->actions as $key => $value) {
                    $html .= $this->renderActionButton($key, $value, $entry);
                }
                $html .= '</td>';
                return $html;
            }
        }
    }

    public function renderActionButton($name, $value, $context) {
        if (empty($value)) {
            $url = URL::site($this->app->controller->getBaseUri().'/'.$context['$id'].'/'.$name);
            return '<a href="'.$url.'">'.$this->humanize($name)."</a>\n";
        } else {
            return $value($name, $value, $context);
        }
    }

    public function show($entries) {
        $this->view->set('entries', $entries);
        return $this->view->fetch('components/table.php');
    }

    protected function humanize($name) {
        return Inflector::classify($name);
    }
}