<?php
return array(
    'norm.databases' => array(
        'sqlite' => array(
            'driver' => '\\Norm\\Connection\\PDOConnection',
            'dialect' => '\\Norm\\Dialect\\SqliteDialect',
            'prefix' => 'sqlite',
            'database' => '../db/luthor-production.db',
        ),
    ),
);