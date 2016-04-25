<?php

/*
 * HTML Server Components compiler
 * http://ivopetkov.com/b/html-server-components/
 * Copyright 2015-2016, Ivo Petkov
 * Free to use under the MIT license.
 */

namespace IvoPetkov;

/**
 * The class that is used to send data to the component
 */
class HTMLServerComponent
{

    /**
     *
     * @var array 
     */
    public $attributes = [];

    /**
     *
     * @var string 
     */
    public $innerHTML = '';

    /**
     * 
     * @param string $name
     * @param string|null $defaultValue
     * @return string|null
     */
    public function getAttribute($name, $defaultValue = null)
    {
        $name = strtolower($name);
        return isset($this->attributes[$name]) ? (string) $this->attributes[$name] : $defaultValue;
    }

    /**
     * 
     * @param string $name
     * @param string $value
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[strtolower($name)] = $value;
    }

    /**
     * 
     * @param string $name
     */
    public function removeAttribute($name)
    {
        $name = strtolower($name);
        if (isset($this->attributes[$name])) {
            unset($this->attributes[$name]);
        }
    }

    /**
     * 
     * @param string $name
     * @return string|null
     */
    function __get($name)
    {
        return $this->getAttribute($name);
    }

    /**
     * 
     * @param string $name
     * @param string $value
     * @return void
     */
    function __set($name, $value)
    {
        $this->setAttribute($name, $value);
    }

    /**
     * 
     * @param string $name
     * @return boolean
     */
    function __isset($name)
    {
        return isset($this->attributes[strtolower($name)]);
    }

    /**
     * 
     * @param string $name
     */
    function __unset($name)
    {
        $this->removeAttribute($name);
    }

}
