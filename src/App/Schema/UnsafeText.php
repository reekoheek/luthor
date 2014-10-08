<?php

namespace App\Schema;

class UnsafeText extends \Norm\Schema\Text
{
    public function prepare($value)
    {
        return utf8_encode($value);
    }
}
