<?php

/*
 * HTML Server Components PHP Compiler
 * http://ivopetkov.com/b/html-server-components/
 * Copyright 2015, Ivo Petkov
 * Free to use under the MIT license.
 */

/**
 * The class that processes components
 */
class HTMLServerComponents
{

    /**
     *
     * @var array 
     */
    static $registeredComponents = [];

    /**
     * Registers a component so it can be used by name instead of file
     * @param string $name
     * @param string $file
     */
    static function register($name, $file)
    {
        self::$registeredComponents[$name] = $file;
    }

    /**
     * Process (merge) components
     * @param string $html
     * @return string
     */
    static function process($html)
    {
        $domDocument = self::getDOMDocument($html);
        $components = $domDocument->getElementsByTagName('component');
        $componentsCount = $components->length;
        if ($componentsCount > 0) {
            $domBodyElement = $domDocument->getElementsByTagName('body')->item(0);
            $domHeadElement = $domDocument->getElementsByTagName('head')->item(0);
            for ($i = 0; $i < $componentsCount; $i++) {
                $component = $components->item(0); // the component is removed later
                if (isset($component->parentNode)) {

                    $attributes = self::getDOMElementAttributes($component);

                    if (isset($attributes['file'])) {
                        $file = $attributes['file'];
                    } else {
                        if (isset($attributes['name'], self::$registeredComponents[$attributes['name']])) {
                            $file = self::$registeredComponents[$attributes['name']];
                        } else {
                            throw new \Exception('Component file not found! ' . $domDocument->saveHTML($component));
                        }
                    }

                    $componentHTML = self::processFile($file, $attributes, self::getInnerHTML($component));

                    $componentDOMDocument = self::getDOMDocument($componentHTML);

                    $componentBodyElement = $componentDOMDocument->getElementsByTagName('body')->item(0);
                    $componentBodyChildrenCount = $componentBodyElement->childNodes->length;

                    // keep scripts at the bottom
                    $scriptElementsMovedIndexes = [];
                    for ($j = $componentBodyChildrenCount - 1; $j >= 0; $j--) {
                        $componentChild = $componentBodyElement->childNodes->item($j);
                        if ($componentChild instanceof DOMText) {
                            continue;
                        }
                        if ($componentChild instanceof DOMElement && $componentChild->tagName === 'script') {
                            $componentChildAttributes = self::getDOMElementAttributes($componentChild);
                            if (!isset($componentChildAttributes['type']) || $componentChildAttributes['type'] === '' || $componentChildAttributes['type'] === 'text/javascript') {
                                $domBodyElement->appendChild($domDocument->importNode($componentChild, true));
                                $scriptElementsMovedIndexes[$j] = 1;
                            }
                        } else {
                            break;
                        }
                    }

                    for ($j = 0; $j < $componentBodyChildrenCount; $j++) {
                        if (!isset($scriptElementsMovedIndexes[$j])) {
                            $component->parentNode->insertBefore($domDocument->importNode($componentBodyElement->childNodes->item($j), true), $component);
                        }
                    }

                    $componentHeadElement = $componentDOMDocument->getElementsByTagName('head')->item(0);
                    $componentHeadElementChindrenCount = $componentHeadElement->childNodes->length;
                    for ($j = 0; $j < $componentHeadElementChindrenCount; $j++) {
                        $domHeadElement->appendChild($domDocument->importNode($componentHeadElement->childNodes->item($j), true));
                    }
                    $component->parentNode->removeChild($component);
                }
            }
        }

        $domDocument2 = new DOMDocument;
        $domDocument2->formatOutput = true;
        @$domDocument2->loadHTML($domDocument->saveHTML());
        return $domDocument2->saveHTML();
    }

    /**
     * 
     * @param string $file
     * @param array $attributes
     * @param string $innerHTML
     * @return string
     */
    static function processFile($file, $attributes = [], $innerHTML = '')
    {
        return self::process(self::getComponentContent($file, $attributes, $innerHTML));
    }

    /**
     * 
     * @param string $file
     * @param array $attributes
     * @param string $innerHTML
     * @return string
     */
    static function getComponentContent($file, $attributes = [], $innerHTML = '')
    {
        if (!isset($attributes['id'])) {
            $attributes['id'] = '__cmp' . uniqid();
        }
        $component = new HTMLServerComponent();
        $component->attributes = $attributes;
        $component->innerHTML = $innerHTML;
        ob_start();
        include $file;
        $content = ob_get_clean();
        return $content;
    }

    /**
     * 
     * @param \DOMElement $domElement
     * @return array
     */
    static function getDOMElementAttributes($domElement)
    {
        $attributes = [];
        $attributesCount = $domElement->attributes->length;
        $attributes = [];
        for ($j = 0; $j < $attributesCount; $j++) {
            $attribute = $domElement->attributes->item($j);
            $attributes[$attribute->name] = $attribute->value;
        }
        return $attributes;
    }

    /**
     * 
     * @param string $html
     * @return \DOMDocument
     */
    static function getDOMDocument($html)
    {
        $domDocument = new DOMDocument;
        @$domDocument->loadHTML($html);

        $headElements = $domDocument->getElementsByTagName('head');
        if ($headElements->length === 0) {
            $headElement = new DOMElement('head');
            $domDocument->getElementsByTagName('html')->item(0)->insertBefore($headElement, $domDocument->getElementsByTagName('body')->item(0));
        }
        return $domDocument;
    }

    /**
     * 
     * @param \DOMElement $domElement
     * @return string
     */
    static function getInnerHTML($domElement)
    {
        $html = trim($domElement->ownerDocument->saveHTML($domElement));
        $nodeName = $domElement->nodeName;
        return preg_replace('@^<' . $nodeName . '[^>]*>|</' . $nodeName . '>$@', '', $html);
    }

}

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
        return $this->getAttribute($name);
    }

    /**
     * 
     * @param string $name
     * @param string $defaultValue
     * @return string|null
     */
    function getAttribute($name, $defaultValue = null)
    {
        return isset($this->attributes[$name]) ? (string) $this->attributes[$name] : ($defaultValue === null ? null : (string) $defaultValue);
    }

}
