<?php

use Norm\Schema\String;
use Norm\Schema\Text;

return array(
    'observers' => array(
        '\\App\\Observer\\TemplateObserver'
    ),
    'schema' => array(
        'name' => String::create('name'),
        'filename' => String::create('filename')->set('readonly', true),
        'content' => Text::create('content'),
    ),
);
