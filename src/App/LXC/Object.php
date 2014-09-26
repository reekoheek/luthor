<?php

namespace App\LXC;

class Object implements \JsonKit\JsonSerializer, \ArrayAccess
{
    protected $attributes = array();
    protected $options = array();

    public function __construct($attributes = array(), $options = array())
    {
        $this->attributes = array_merge($this->options, $attributes ?: array());
        $this->options = array_merge($this->options, $options ?: array());
    }

    public function jsonSerialize()
    {
        return $this->attributes;
    }

    public function offsetExists ($offset)
    {
        return array_key_exists($offset, $this->attributes);
    }

    public function offsetGet ($offset)
    {
        return isset($this->attributes[$offset]) ? $this->attributes[$offset] : null;
    }

    public function offsetSet ($offset, $value)
    {
        $this->attributes[$offset] = $value;
    }

    public function offsetUnset ($offset)
    {
        unset($this->attributes[$offset]);
    }

    public function toArray()
    {
        return $this->attributes;
    }
}
