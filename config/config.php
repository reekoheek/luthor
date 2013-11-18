<?php

use \Norm\Schema\String;
use \Norm\Schema\Password;
use \Norm\Schema\Integer;
use \Norm\Schema\Boolean;
use \Norm\Schema\Text;

return array(
    'app.salt' => 'f67f7b84d57d1ee681c3bc6ab490ef327f4c433aeecab6e13e231fbfb98d2062',
    'bono.providers' => array(
        '\\Bono\\Provider\\LanguageProvider',
        '\\Norm\\Provider\\NormProvider',
        '\\App\\Provider\\AppProvider',
    ),
    'bono.middlewares' => array(
        '\\Bono\\Middleware\\ControllerMiddleware',
        '\\Bono\\Middleware\\ContentNegotiatorMiddleware',
        '\\App\\Middleware\\AuthMiddleware',
        '\\Bono\\Middleware\\SessionMiddleware',
    ),
    'bono.controllers' => array(
        'default' => '\\Norm\\Controller\\NormController',
        'mapping' => array(
            '/user' => NULL,
            '/network' => '\\App\\Controller\\NetworkController',
            '/container' => '\\App\\Controller\\ContainerController',
            '/template' => '\\App\\Controller\\TemplateController',
        ),
    ),
    'bono.contentNegotiator' => array(
        'extensions' => array(
            'json' => 'application/json',
        ),
        'views' => array(
            'application/json' => '\\Bono\\View\\JsonView',
        ),
    ),
    'norm.databases' => array(
        'mongo' => array(
            'driver' => '\\Norm\\Connection\\MongoConnection',
            'database' => 'luthor',
        ),
    ),
    'norm.collections' => array(
        'Container' => array(
            'model' => '\\App\\Model\\Container',
            'schema' => array(
                'name' => String::getInstance('name')->set('required', true),
                'ip_address' => String::getInstance('ip_address', 'IP Address'),
                'mem_usage' => Integer::getInstance('mem_usage', 'Mem Usage')->set('readonly', true)
                    ->set('cellFormat', function($value) {
                        return sprintf('%.1fM', $value / 1000000);
                    }),
                'state' => Integer::getInstance('state')->set('readonly', true),
                'pid' => Integer::getInstance('pid', 'PID')->set('readonly', true),
            ),
        ),
        'Network' => array(
            'schema' => array(
                'name' => String::getInstance('name'),
                'uuid' => String::getInstance('uuid', 'UUID')->set('readonly', true),
                'state' => Boolean::getInstance('state')->set('readonly', true),
                'autostart' => Boolean::getInstance('autostart', 'Auto Start'),
                'persistent' => Boolean::getInstance('persistent'),
                'bridge' => String::getInstance('bridge'),
                'ip_address' => String::getInstance('ip_address', 'IP Address'),
                'netmask' => String::getInstance('netmask'),
                'dhcp_start' => String::getInstance('dhcp_start', 'DHCP Start'),
                'dhcp_end' => String::getInstance('dhcp_end', 'DHCP End'),
            ),
        ),
        'User' => array(
            'schema' => array(
                'username' => String::getInstance('username')->filter('trim|required|unique:User,username'),
                'password' => Password::getInstance('password')->filter('trim|confirmed|salt'),
            ),
        ),
        'Template' => array(
            'schema' => array(
                'name' => String::getInstance('name'),
                'filename' => String::getInstance('filename')->set('readonly', true),
                'luthor_version' => String::getInstance('luthor_version', 'Version')->set('readonly', true),
                'content' => Text::getInstance('content'),
            ),
        ),
    ),
    'component.form' => array(
        'mapping' => array(
            'Template' => array(
                'name' => NULL,
                'content' => NULL,
            ),
            'Network' => array(
                'state' => NULL,
                'name' => NULL,
                'autostart' => NULL,
                'bridge' => NULL,
                'ip_address' => NULL,
                'netmask' => NULL,
                'dhcp_start' => NULL,
                'dhcp_end' => NULL,
            ),
        ),
    ),
    'component.table' => array(
        'default' => array(
            'actions' => array('update' => NULL, 'delete' => NULL),
        ),
        'mapping' => array(
            'Container' => array(
                'columns' => array(
                    'name' => NULL,
                    // 'state' => NULL,
                    'pid' => NULL,
                    'ip_address' => NULL,
                    'mem_usage' => NULL,
                ),
                'actions' => array(
                    'onoff' => function($key, $value, $context) {
                        $app = \Bono\App::getInstance();
                        $label = ($context['state'] != 0) ? 'Stop' : 'Start';
                        return "<a href=\"".\Bono\Helper\URL::site($app->controller->getBaseUri().'/'.$context['$id'].'/onoff')."\">$label</a>\n";
                    },
                    'update' => NULL,
                    'delete' => NULL,
                ),
            ),
            'Template' => array(
                'columns' => array(
                    'name' => NULL,
                    'luthor_version' => NULL,
                ),
            ),
            'Network' => array(
                'columns' => array(
                    'name' => NULL,
                    'state' => NULL,
                    'bridge' => NULL,
                    'ip_address' => NULL,
                    'netmask' => NULL,
                    'autostart' => NULL,
                ),
            ),
        ),
    ),
    'component.searchButtonGroup' => array(
        'default' => array('create' => NULL),
        'mapping' => array(
            'Container' => array('create' => NULL, 'populate' => NULL),
            'Template' => array('create' => NULL, 'populate' => NULL),
            'Network' => array('create' => NULL, 'populate' => NULL),
        ),
    ),
    'lxc' => array(
        'directory' => '/var/lib/lxc',
        'templatesDirectory' => '/usr/share/lxc/templates',
        'luthor.ip' => '192.168.122.1',
        'luthor.path' => '/luthor/www/index.php',
        'luthor.allowed' => '192.168.122.0/24',
    ),
    'auth' => array(
        'allow' => array(
            '/login' => NULL,
            '/logout' => NULL,
        ),
    ),
);