<?php

use \Norm\Schema\String;
use \Norm\Schema\Password;
use \Norm\Schema\Integer;
use \Norm\Schema\Boolean;
use \Norm\Schema\Text;
use \Norm\Schema\Reference;
use \Norm\Schema\Object;

return array(
    'bono.salt' => 'f67f7b84d57d1ee681c3bc6ab490ef327f4c433aeecab6e13e231fbfb98d2062',
    'bono.providers' => array(
        '\\Bono\\Provider\\LanguageProvider' => null,
        '\\Norm\\Provider\\NormProvider' => array(
            'datasources' => array(
                'mongo' => array(
                    'driver' => '\\Norm\\Connection\\MongoConnection',
                    'database' => 'luthor',
                ),
            ),
            'collections' => array(
                'resolvers' => array(
                    '\\Norm\\Resolver\\CollectionResolver' => null,
                ),
                // 'mapping' => array(
                //     'Container' => array(
                //         'model' => '\\App\\Model\\Container',
                //         'schema' => array(
                //             'name' => String::getInstance('name')->filter('trim|required'),
                //             'network' => Reference::getInstance('network')->to('Network', 'name')->filter('trim|required'),
                //             'ip_address' => String::getInstance('ip_address', 'IP Address'),
                //             'memlimit' => String::getInstance('memlimit', 'Memory Limit'),
                //             'memswlimit' => String::getInstance('memswlimit', 'Mem+Swap Limit'),
                //             'cpus' => String::getInstance('cpus', 'CPUS'),
                //             'cpu_shares' => String::getInstance('cpu_shares', 'CPU Shares'),
                //             'state' => Integer::getInstance('state')->set('readonly', true),
                //             'pid' => Integer::getInstance('pid', 'PID')->set('readonly', true),
                //             'actual_ip_address' => String::getInstance('actual_ip_address', 'Actual IP Address')->set('readonly', true),
                //             'config' => Object::getInstance('config'),
                //         ),
                //     ),
                //     'Anu' => array(
                //         // 'observers' =>
                //         'schema' => array(
                //             'name' => String::getInstance('name')->filter('trim|required'),
                //             'description' => Text::getInstance('description'),
                //         ),
                //     ),
                //     'Network' => array(
                //         'schema' => array(
                //             'name' => String::getInstance('name'),
                //             'uuid' => String::getInstance('uuid', 'UUID')->set('readonly', true),
                //             'state' => Boolean::getInstance('state')->set('readonly', true),
                //             'autostart' => Boolean::getInstance('autostart', 'Auto Start'),
                //             'persistent' => Boolean::getInstance('persistent'),
                //             'bridge' => String::getInstance('bridge'),
                //             'ip_address' => String::getInstance('ip_address', 'IP Address'),
                //             'netmask' => String::getInstance('netmask'),
                //             'dhcp_start' => String::getInstance('dhcp_start', 'DHCP Start'),
                //             'dhcp_end' => String::getInstance('dhcp_end', 'DHCP End'),
                //         ),
                //     ),
                //     'User' => array(
                //         'schema' => array(
                //             'username' => String::getInstance('username')->filter('trim|required|unique:User,username'),
                //             'password' => Password::getInstance('password')->filter('trim|confirmed'),
                //         ),
                //     ),
                //     'Template' => array(
                //         'schema' => array(
                //             'name' => String::getInstance('name'),
                //             'filename' => String::getInstance('filename')->set('readonly', true),
                //             'luthor_version' => String::getInstance('luthor_version', 'Version')->set('readonly', true),
                //             'content' => Text::getInstance('content'),
                //         ),
                //     ),
                // ),
            ),
        ),
    ),
    'bono.middlewares' => array(
        '\\Bono\\Middleware\\ControllerMiddleware' => array(
            'default' => '\\Norm\\Controller\\NormController',
            'mapping' => array(
                '/anu' => null,
                '/user' => null,
                '/network' => '\\App\\Controller\\NetworkController',
                '/container' => '\\App\\Controller\\ContainerController',
                '/template' => '\\App\\Controller\\TemplateController',
            ),
        ),
        '\\Bono\\Middleware\\ContentNegotiatorMiddleware' => null,
        '\\ROH\\BonoAuth\\Middleware\\AuthMiddleware' => array(
            'driver' => '\\ROH\\BonoAuth\\Driver\\OAuth',
            'debug' => true,
            'baseUrl' => 'http://192.168.1.99/internal/account/www/index.php',
            'authUrl' => '/oauth/auth',
            'tokenUrl' => '/oauth/token',
            'revokeUrl' => '/oauth/revoke',
            // 'userUrl' => '/home/user/me',
            'clientId' => '54117aaeb75868440d8b4567.client.account.xinix.co.id',
            'clientSecret' => '2b9ed54c54776dbf86a7a15377e73165',
            'redirectUri' => \Bono\Helper\URL::site('/login'),
            'scope' => 'user',
        ),
        '\\Bono\\Middleware\\SessionMiddleware' => null,
    ),
    // 'bono.contentNegotiator' => array(
    //     'extensions' => array(
    //         'json' => 'application/json',
    //     ),
    //     'views' => array(
    //         'application/json' => '\\Bono\\View\\JsonView',
    //     ),
    // ),

    // 'component.form' => array(
    //     'mapping' => array(
    //         'Container' => array(
    //             'name' => null,
    //             'network' => null,
    //             'ip_address' => null,
    //             'memlimit' => null,
    //             'memswlimit' => null,
    //             'cpus' => null,
    //             'cpu_shares' => null,
    //         ),
    //         'Template' => array(
    //             'name' => null,
    //             'content' => null,
    //         ),
    //         'Network' => array(
    //             // 'state' => null,
    //             'name' => null,
    //             'autostart' => null,
    //             'bridge' => null,
    //             'ip_address' => null,
    //             'netmask' => null,
    //             'dhcp_start' => null,
    //             'dhcp_end' => null,
    //         ),
    //     ),
    // ),
    // 'component.table' => array(
    //     'default' => array(
    //         'actions' => array('update' => null, 'delete' => null),
    //     ),
    //     'mapping' => array(
    //         'Container' => array(
    //             'columns' => array(
    //                 'name' => null,
    //                 // 'state' => null,
    //                 'pid' => null,
    //                 'actual_ip_address' => null,
    //                 'mem_usage' => function ($value) {
    //                     return sprintf('%.1fM', $value / 1000000);
    //                 },
    //             ),
    //             'actions' => array(
    //                 'onoff' => function ($key, $value, $context) {
    //                     $app = \Bono\App::getInstance();
    //                     $label = ($context['state'] != 0) ? 'Stop' : 'Start';
    //                     return "<a href=\"".\Bono\Helper\URL::site($app->controller->getBaseUri().'/'.$context['$id'].'/onoff')."\">$label</a>\n";
    //                 },
    //                 'chpasswd' => function ($key, $value, $context) {
    //                     $app = \Bono\App::getInstance();
    //                     return "<a href=\"".\Bono\Helper\URL::site($app->controller->getBaseUri().'/'.$context['$id'].'/chpasswd')."\">Password</a>\n";
    //                 },
    //                 'update' => null,
    //                 'delete' => null,
    //             ),
    //         ),
    //         'Template' => array(
    //             'columns' => array(
    //                 'name' => null,
    //                 'luthor_version' => null,
    //             ),
    //         ),
    //         'Network' => array(
    //             'columns' => array(
    //                 'name' => null,
    //                 'state' => null,
    //                 'bridge' => null,
    //                 'ip_address' => null,
    //                 'netmask' => null,
    //                 'autostart' => null,
    //             ),
    //         ),
    //     ),
    // ),
    // 'component.searchButtonGroup' => array(
    //     'default' => array('create' => null),
    //     'mapping' => array(
    //         'Container' => array('create' => null, 'populate' => null),
    //         'Template' => array('create' => null, 'populate' => null),
    //         'Network' => array('create' => null, 'populate' => null),
    //     ),
    // ),
    'lxc' => array(
        'directory' => '/var/lib/lxc',
        'templatesDirectory' => '/usr/share/lxc/templates',
        'luthor.https' => false,
        'luthor.ip' => '192.168.122.1',
        'luthor.path' => '/app/luthor/www/index.php',
        'luthor.allowed' => '192.168.122.0/24',
    ),
    // 'auth' => array(
    //     // array(
    //     //     '/login' => NULL,
    //     //     '/logout' => NULL,
    //     // )
    //     'allow' => function ($request) {

    //         if ($request->getSegments(1) == 'login' || $request->getSegments(1) == 'logout') {
    //             return true;
    //         } elseif ($request->getSegments(1) == 'container' && $request->getSegments(3) == 'poke') {
    //             return true;
    //         }
    //     },
    // ),
    'bono.theme' => array(
        'class' => '\\ROH\\Theme\\BootstrapTheme',
        'overwrite' => true,
        'options' => array(
            'title' => 'Luthor',
            'menu' => array(
                array( 'label' => 'Home', 'url' => '/home' ),
                array( 'label' => 'Container', 'url' => '/container' ),
                array( 'label' => 'Network', 'url' => '/network' ),
                array( 'label' => 'Template', 'url' => '/template' ),
                array( 'label' => 'User', 'url' => '/user' ),
            ),
        ),
    ),
);
