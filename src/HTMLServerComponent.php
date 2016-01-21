<?php

/*
 * HTML Server Components Compiler
 * http://ivopetkov.com/b/html-server-components/
 * Copyright 2015-2016, Ivo Petkov
 * Free to use under the MIT license.
 */

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
     * @return string|null
     */
    function __get($name)
    {
        return $this->getAttribute(strtolower($name));
    }

    /**
     * 
     * @param string $name
     * @param string|null $defaultValue
     * @return string|null
     */
    public function getAttribute($name, $defaultValue = null)
    {
        return isset($this->attributes[$name]) ? (string) $this->attributes[$name] : ($defaultValue === null ? null : (string) $defaultValue);
    }

}
