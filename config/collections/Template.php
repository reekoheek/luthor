<?php

use Norm\Schema\String;
use App\Schema\UnsafeText;

return array(
    'observers' => array(
        '\\App\\Observer\\TemplateObserver'
    ),
    'schema' => array(
        'name' => String::create('name'),
        'filename' => String::create('filename')->set('readonly', true),
        'content' => UnsafeText::create('content'),
    ),
);
