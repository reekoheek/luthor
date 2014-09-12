<?php

use \Norm\Schema\String;
use \Norm\Schema\Password;
use \Norm\Schema\Reference;

return array(
    'schema' => array(
        'username' => String::create('username')
            ->filter('trim|required|unique:User,username'),
        'email' => String::create('email')->filter('trim|required|unique:User,email'),
        'password' => Password::create('password')->filter('trim|confirmed|salt'),
        'first_name' => String::create('first_name'),
        'last_name' => String::create('last_name'),
        'role' => Reference::create('role')->to('Role'),
    ),
);
