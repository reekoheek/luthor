<?php

use \Norm\Schema\String;
use \Norm\Schema\Password;
use \Norm\Schema\Integer;
use \Norm\Schema\Boolean;
use \Norm\Schema\Text;
use \Norm\Schema\Reference;
use \Norm\Schema\Object;

return array(
    // 'app.salt' => 'f67f7b84d57d1ee681c3bc6ab490ef327f4c433aeecab6e13e231fbfb98d2062',
    'bono.providers' => array(
        '\\Bono\\Provider\\LanguageProvider',
        '\\Norm\\Provider\\NormProvider',
    ),
    'bono.middlewares' => array(
        '\\Bono\\Middleware\\ControllerMiddleware',
        '\\Bono\\Middleware\\ContentNegotiatorMiddleware',
        '\\ROH\\BonoAuth\\Middleware\\AuthMiddleware',
        '\\Bono\\Middleware\\SessionMiddleware',
    ),
    'bono.controllers' => array(
        'default' => '\\Norm\\Controller\\NormController',
        'mapping' => array(
            '/anu' => NULL,
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
        'mapping' => array(
            'Container' => array(
                'model' => '\\App\\Model\\Container',
                'schema' => array(
                    'name' => String::getInstance('name')->filter('trim|required'),
                    'network' => Reference::getInstance('network')->to('Network', 'name')->filter('trim|required'),
                    'ip_address' => String::getInstance('ip_address', 'IP Address'),
                    'memlimit' => String::getInstance('memlimit', 'Memory Limit'),
                    'memswlimit' => String::getInstance('memswlimit', 'Mem+Swap Limit'),
                    'cpus' => String::getInstance('cpus', 'CPUS'),
                    'cpu_shares' => String::getInstance('cpu_shares', 'CPU Shares'),
                    'state' => Integer::getInstance('state')->set('readonly', true),
                    'pid' => Integer::getInstance('pid', 'PID')->set('readonly', true),
                    'actual_ip_address' => String::getInstance('actual_ip_address', 'Actual IP Address')->set('readonly', true),
                    'config' => Object::getInstance('config'),
                ),
            ),
            'Anu' => array(
                // 'observers' =>
                'schema' => array(
                    'name' => String::getInstance('name')->filter('trim|required'),
                    'description' => Text::getInstance('description'),
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
                    'password' => Password::getInstance('password')->filter('trim|confirmed'),
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
    ),
    'component.form' => array(
        'mapping' => array(
            'Container' => array(
                'name' => NULL,
                'network' => NULL,
                'ip_address' => NULL,
                'memlimit' => NULL,
                'memswlimit' => NULL,
                'cpus' => NULL,
                'cpu_shares' => NULL,
            ),
            'Template' => array(
                'name' => NULL,
                'content' => NULL,
            ),
            'Network' => array(
                // 'state' => NULL,
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
                    'actual_ip_address' => NULL,
                    'mem_usage' => function($value) {
                        return sprintf('%.1fM', $value / 1000000);
                    },
                ),
                'actions' => array(
                    'onoff' => function($key, $value, $context) {
                        $app = \Bono\App::getInstance();
                        $label = ($context['state'] != 0) ? 'Stop' : 'Start';
                        return "<a href=\"".\Bono\Helper\URL::site($app->controller->getBaseUri().'/'.$context['$id'].'/onoff')."\">$label</a>\n";
                    },
                    'chpasswd' => function($key, $value, $context) {
                        $app = \Bono\App::getInstance();
                        return "<a href=\"".\Bono\Helper\URL::site($app->controller->getBaseUri().'/'.$context['$id'].'/chpasswd')."\">Password</a>\n";
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
        'luthor.https' => false,
        'luthor.ip' => '192.168.122.1',
        'luthor.path' => '/app/luthor/www/index.php',
        'luthor.allowed' => '192.168.122.0/24',
    ),
    'auth' => array(
        // array(
        //     '/login' => NULL,
        //     '/logout' => NULL,
        // )
        'allow' => function($request) {

            if ($request->getSegments(1) == 'login' || $request->getSegments(1) == 'logout') {
                return true;
            } elseif ($request->getSegments(1) == 'container' && $request->getSegments(3) == 'poke') {
                return true;
            }
        },
    ),
);