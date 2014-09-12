<?php

use Norm\Schema\String;
use Norm\Schema\Text;

return array(
    'schema' => array(
        'name' => String::create('name'),
        'filename' => String::create('filename')->set('readonly', true),
        'luthor_version' => String::create('luthor_version', 'Version')->set('readonly', true),
        'content' => Text::create('content'),
    ),
);