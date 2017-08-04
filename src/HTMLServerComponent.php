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
     */
    public function getAttribute(string $name, $defaultValue = null)
    {
        $name = strtolower($name);
        return isset($this->attributes[$name]) ? (string) $this->attributes[$name] : $defaultValue;
    }

    /**
     * Sets new value to the attribute specified
     * 
     * @param string $name The name of the attribute
     * @param string $value The new value of the attribute
     * @return void No value is returned
     */
    public function setAttribute(string $name, $value)
    {
        $this->attributes[strtolower($name)] = $value;
    }

    /**
     * Removes attribute
     * 
     * @param string $name The name of the attribute
     * @return void No value is returned
     */
    public function removeAttribute(string $name)
    {
        $name = strtolower($name);
        if (isset($this->attributes[$name])) {
            unset($this->attributes[$name]);
        }
    }

    /**
     * Provides access to the component attributes via properties
     * 
     * @param string $name The name of the attribute
     * @return string|null The value of the attribute or null if missing
     */
    public function __get($name)
    {
        $name = strtolower($name);
        return isset($this->attributes[$name]) ? (string) $this->attributes[$name] : null;
    }

    /**
     * Provides access to the component attributes via properties
     * 
     * @param string $name The name of the attribute
     * @param string $value The new value of the attribute
     * @return void No value is returned
     */
    public function __set(string $name, $value)
    {
        $this->attributes[strtolower($name)] = $value;
    }

    /**
     * Provides access to the component attributes via properties
     * 
     * @param string $name The name of the attribute
     * @return boolean TRUE if the attribute exists, FALSE otherwise
     */
    public function __isset(string $name): bool
    {
        return isset($this->attributes[strtolower($name)]);
    }

    /**
     * Provides access to the component attributes via properties
     * 
     * @param string $name The name of the attribute
     * @return void No value is returned
     */
    public function __unset(string $name)
    {
        $name = strtolower($name);
        if (isset($this->attributes[$name])) {
            unset($this->attributes[$name]);
        }
    }

    /**
     * Returns a HTML representation of the component
     */
    public function __toString(): string
    {
        $html = '<component';
        foreach ($this->attributes as $name => $value) {
            $html .= ' ' . $name . '="' . htmlspecialchars($value) . '"';
        }
        return $html . '>' . $this->innerHTML . '</component>';
    }

}
