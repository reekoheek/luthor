<?php
require '../vendor/autoload.php';

use \App\LXC\Host;

$app = new \Bono\App(array(
    'autorun' => false
));

$app->get('/', function() use ($app) {
    $response = $app->response;

    $app->redirect('home');
});

$app->get('/home', function() use ($app) {
    $response = $app->response;

    $response->template('home');

    $host = new Host();

    $response->set('info', $host->getInfo());
});

$app->run();
