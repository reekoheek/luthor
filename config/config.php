<?php

return array(
    'bono.providers' => array(
        '\\Bono\\Provider\\LanguageProvider',
        '\\Norm\\Provider\\NormProvider',
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
    'norm.schemas' => array(
        'Container' => array(
            'name' => new \Norm\Schema\String('name', 'Name'),
            'state' => (new \Norm\Schema\String('state', 'State'))->set('readOnly', true),
            'pid' => (new \Norm\Schema\Integer('pid', 'PID'))->set('readOnly', true),
            'ip_address' => new \Norm\Schema\String('ip_address', 'IP Address'),
            'mem_usage' => (new \Norm\Schema\String('mem_usage', 'Mem Usage'))
                ->set('cellFormat', function($value) {
                    return sprintf('%.1fM', $value / 1000000);
                }),
        ),
        'Network' => array(
            'name' => new \Norm\Schema\String('name', 'Name'),
            // 'uuid' => (new \Norm\Schema\String('uuid', 'UUID'))->set('readOnly', true),
            'state' => (new \Norm\Schema\Boolean('state', 'State'))->set('readOnly', true),
            'autostart' => new \Norm\Schema\Boolean('autostart', 'Auto Start'),
            'bridge' => new \Norm\Schema\String('bridge', 'Bridge'),
            'ip_address' => new \Norm\Schema\String('ip_address', 'IP Address'),
            'netmask' => new \Norm\Schema\String('netmask', 'Netmask'),
            'dhcp_start' => new \Norm\Schema\String('dhcp_start', 'DHCP Start'),
            'dhcp_end' => new \Norm\Schema\String('dhcp_end', 'DHCP End'),
        ),
        'User' => array(
            'username' => new \Norm\Schema\String('username', 'Username'),
            'password' => new \Norm\Schema\Password('password', 'Password'),
        ),
        'Template' => array(
            'name' => new \Norm\Schema\String('name', 'Name'),
            'filename' => (new \Norm\Schema\String('filename', 'Filename'))->set('readOnly', true),
            'content' => new \Norm\Schema\Text('content', 'Content'),
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