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
            ),
        ),
    ),
    'bono.middlewares' => array(
        '\\Bono\\Middleware\\ControllerMiddleware' => array(
            'default' => '\\Norm\\Controller\\NormController',
            'mapping' => array(
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
        '\\Bono\\Middleware\\NotificationMiddleware' => null,
        '\\Bono\\Middleware\\SessionMiddleware' => null,
    ),
    'lxc' => array(
        'directory' => '/var/lib/lxc',
        'templatesDirectory' => '/usr/share/lxc/templates',
        'luthor.https' => false,
        'luthor.ip' => '192.168.122.1',
        'luthor.path' => '/app/luthor/www/index.php',
        'luthor.allowed' => '192.168.122.0/24',
    ),
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
