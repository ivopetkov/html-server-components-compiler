<?php

/*
 * HTML Server Components compiler
 * http://ivopetkov.com/b/html-server-components/
 * Copyright 2015-2016, Ivo Petkov
 * Free to use under the MIT license.
 */

namespace IvoPetkov;

/**
 * Used to create the $component object that is passed to the corresponding file
 */
class HTMLServerComponent
{

    /**
     * Component tag attributes
     * 
     * @var array 
     */
    public $attributes = [];

    /**
     * Component tag innerHTML
     * 
     * @var string 
     */
    public $innerHTML = '';

    /**
     * Returns value of an attribute
     * 
     * @param string $name The name of the attribute
     * @param string|null $defaultValue The default value of the attribute (if missing)
     * @return string|null The value of the attribute or the defaultValue specified
     * @throws \InvalidArgumentException
     */
    public function getAttribute($name, $defaultValue = null)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException('');
        }
        if (!is_string($defaultValue) && $defaultValue !== null) {
            throw new \InvalidArgumentException('');
        }
        $name = strtolower($name);
        return isset($this->attributes[$name]) ? (string) $this->attributes[$name] : $defaultValue;
    }

    /**
     * Sets new value to the attribute specified
     * 
     * @param string $name The name of the attribute
     * @param string $value The new value of the attribute
     * @return void No value is returned
     * @throws \InvalidArgumentException
     */
    public function setAttribute($name, $value)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException('');
        }
        if (!is_string($value)) {
            throw new \InvalidArgumentException('');
        }
        $this->attributes[strtolower($name)] = $value;
    }

    /**
     * Removes attribute
     * 
     * @param string $name The name of the attribute
     * @return void No value is returned
     * @throws \InvalidArgumentException
     */
    public function removeAttribute($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException('');
        }
        $name = strtolower($name);
        if (isset($this->attributes[$name])) {
            unset($this->attributes[$name]);
        }
    }

    /**
     * Provides acccess to the component attributes via properties
     * 
     * @param string $name The name of the attribute
     * @return string|null The value of the attribute or null if missing
     */
    function __get($name)
    {
        return $this->getAttribute($name);
    }

    /**
     * Provides acccess to the component attributes via properties
     * 
     * @param string $name The name of the attribute
     * @param string $value The new value of the attribute
     * @return void No value is returned
     * @throws \InvalidArgumentException
     */
    function __set($name, $value)
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException('');
        }
        $this->setAttribute($name, $value);
    }

    /**
     * Provides acccess to the component attributes via properties
     * 
     * @param string $name The name of the attribute
     * @return boolean TRUE if the attribute exists, FALSE otherwise
     */
    function __isset($name)
    {
        return isset($this->attributes[strtolower($name)]);
    }

    /**
     * Provides acccess to the component attributes via properties
     * 
     * @param string $name The name of the attribute
     * @return void No value is returned
     */
    function __unset($name)
    {
        $this->removeAttribute($name);
    }

}
