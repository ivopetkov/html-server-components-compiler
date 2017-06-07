<?php

/*
 * HTML Server Components compiler
 * http://ivopetkov.com/b/html-server-components/
 * Copyright 2015-2016, Ivo Petkov
 * Free to use under the MIT license.
 */

namespace IvoPetkov;

/**
 * HTML Server Components compiler. Converts components code into HTML code.
 */
class HTMLServerComponentsCompiler
{

    /**
     * Library version
     */
    const VERSION = '0.5.0';

    /**
     * Stores the added aliases
     * 
     * @var array 
     */
    private $aliases = [];

    /**
     * Adds an alias
     * 
     * @param string $alias The alias
     * @param string $original The original source name
     * @return void No value is returned
     * @throws \InvalidArgumentException
     */
    public function addAlias($alias, $original)
    {
        if (!is_string($alias)) {
            throw new \InvalidArgumentException('');
        }
        if (!is_string($original)) {
            throw new \InvalidArgumentException('');
        }
        $this->aliases[$alias] = $original;
    }

    /**
     * Converts components code (if any) into HTML code
     * 
     * @param string|\IvoPetkov\HTMLServerComponent $content The content to be processed
     * @param array $options Compiler options
     * @throws \InvalidArgumentException
     * @return string The result HTML code
     */
    public function process($content, $options = [])
    {
        if (!is_string($content) && !($content instanceof \IvoPetkov\HTMLServerComponent)) {
            throw new \InvalidArgumentException('');
        }
        if (!is_array($options)) {
            throw new \InvalidArgumentException('');
        }
        if (is_string($content) && strpos($content, '<component') === false) {
            return $content;
        }

        $getComponentFileContent = static function($file, $component, $variables) {
            if (is_file($file)) {
                $__componentFile = $file;
                unset($file);
                if (!empty($variables)) {
                    extract($variables, EXTR_SKIP);
                }
                unset($variables);
                ob_start();
                include $__componentFile;
                $content = ob_get_clean();
                return $content;
            } else {
                throw new \Exception('Component file cannot be found (' . $file . ')');
            }
        };

        $getComponentResultHTML = function($component) use (&$getComponentFileContent, $options) {
            if (isset($component->attributes['src'])) {
                // todo check alias of alias
                $srcAttributeValue = $component->attributes['src'];
                if (isset($this->aliases[$srcAttributeValue])) {
                    $sourceParts = explode(':', $this->aliases[$srcAttributeValue], 2);
                } else {
                    $sourceParts = explode(':', $srcAttributeValue, 2);
                }
                if (isset($sourceParts[0], $sourceParts[1])) {
                    $scheme = $sourceParts[0];
                    if ($scheme === 'data') {
                        if (substr($sourceParts[1], 0, 7) === 'base64,') {
                            return base64_decode(substr($sourceParts[1], 7)); //$this->process(, isset($componentOptions) ? $componentOptions : $options);
                        }
                        throw new \Exception('Components data URI scheme only supports base64 (data:base64,ABCD...)!');
                    } elseif ($scheme === 'file') {
                        return $getComponentFileContent(urldecode($sourceParts[1]), $component, isset($options['variables']) && is_array($options['variables']) ? $options['variables'] : []); //$this->process(isset($componentOptions) ? $componentOptions : $options);
                    }
                    throw new \Exception('Components URI scheme not valid! It must be \'file:\', \'data:\' or an alias.');
                }
                throw new \Exception('Components URI scheme or alias not found at ' . (string) $component . '!');
            }
            throw new \Exception('Component src attribute is missing at ' . (string) $component . '!');
        };

        $domDocument = new \IvoPetkov\HTML5DOMDocument();
        $domDocument->loadHTML($content instanceof \IvoPetkov\HTMLServerComponent ? $getComponentResultHTML($content) : $content);
        for ($level = 0; $level < 1000; $level++) {
            $componentElements = $domDocument->getElementsByTagName('component');
            $componentElementsCount = $componentElements->length;
            if ($componentElementsCount === 0) {
                break;
            }
            for ($i = 0; $i < $componentElementsCount; $i++) {
                $componentElement = $componentElements->item($i);
                if ($componentElement === null) { // component in component innerHTML case
                    continue;
                }
                $component = $this->constructComponent($componentElement->getAttributes(), $componentElement->innerHTML);
                $componentResultHTML = $getComponentResultHTML($component);
                $isInBodyTag = false;
                $parentNode = $componentElement->parentNode;
                while ($parentNode !== null && isset($parentNode->tagName)) {
                    if ($parentNode->tagName === 'body') {
                        $isInBodyTag = true;
                        break;
                    }
                    $parentNode = $parentNode->parentNode;
                }
                if ($isInBodyTag) {
                    $insertTargetName = 'html-server-components-compiler-insert-target';
                    $componentElement->parentNode->replaceChild($domDocument->createInsertTarget($insertTargetName), $componentElement);
                    $domDocument->insertHTML($componentResultHTML, $insertTargetName);
                } else {
                    $componentElement->parentNode->removeChild($componentElement);
                    $domDocument->insertHTML($componentResultHTML);
                }
            }
            if (isset($options['recursive']) && $options['recursive'] === false) {
                break;
            }
        }

        return $domDocument->saveHTML();
    }

    /**
     * Constructs a component object
     * 
     * @param array $attributes The attributes of the component object
     * @param string $innerHTML The innerHTML of the component object
     * @return \IvoPetkov\HTMLServerComponent A component object
     * @throws \InvalidArgumentException
     */
    public function constructComponent($attributes = [], $innerHTML = '')
    {
        if (!is_array($attributes)) {
            throw new \InvalidArgumentException('');
        }
        if (!is_string($innerHTML)) {
            throw new \InvalidArgumentException('');
        }
        $component = new \IvoPetkov\HTMLServerComponent();
        $component->attributes = $attributes;
        $component->innerHTML = $innerHTML;
        return $component;
    }

}
