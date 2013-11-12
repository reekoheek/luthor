<?php

namespace App\Helper;

class SCRUD {
    public static function get($name, $value, $context = NULL) {
        if (is_callable($value)) {
            return $value($context);
        }
        return $value ?: \Reekoheek\Util\Inflector::classify($name);
    }

    public static function actionButton($name, $options, $context) {
        if (empty($options)) {
            $options = array();
        }
        $app = \Slim\Slim::getInstance();
        if (is_null(@$options['url'])) {
            $options['url'] = $app->request->getResourceUri().'/%s/'.$name;
        }
        $url = \Bono\Helper\URL::site(sprintf($options['url'], $context['$id']));
        $label = static::get($name, @$options['label'], $context);
        return '<a href="'.$url.'">'.$label.'</a>';
    }
}