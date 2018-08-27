<?php

/*
 * HTML Server Components compiler
 * http://ivopetkov.com/b/html-server-components/
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

namespace IvoPetkov;

/**
 * HTML Server Components compiler. Converts components code into HTML code.
 */
class HTMLServerComponentsCompiler
{

    /**
     * Stores the added aliases.
     * 
     * @var array 
     */
    private $aliases = [];

    /**
     *
     */
    private static $newComponentCache = null;

    /**
     * Adds an alias.
     * 
     * @param string $alias The alias.
     * @param string $original The original source name.
     * @return void No value is returned.
     */
    public function addAlias(string $alias, string $original)
    {
        $this->aliases[$alias] = $original;
    }

    /**
     * Converts components code (if any) into HTML code.
     * 
     * @param string|\IvoPetkov\HTMLServerComponent $content The content to be processed.
     * @param array $options Compiler options.
     * @return string The result HTML code.
     */
    public function process(string $content, array $options = [])
    {
        if (is_string($content)) {
            if (strpos($content, '<component') === false) {
                return $content;
            }
        } elseif (!($content instanceof \IvoPetkov\HTMLServerComponent)) {
            throw new \InvalidArgumentException('');
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
            $srcAttributeValue = $component->getAttribute('src');
            if ($srcAttributeValue !== null) {
                // todo check alias of alias
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

        $disableLevelProcessing = false;
        $domDocument = new \IvoPetkov\HTML5DOMDocument();
        if ($content instanceof \IvoPetkov\HTMLServerComponent) {
            $domDocument->loadHTML($getComponentResultHTML($content));
            if (isset($options['recursive']) && $options['recursive'] === false) {
                $disableLevelProcessing = true;
            }
        } else {
            $domDocument->loadHTML($content);
        }
        if (!$disableLevelProcessing) {
            for ($level = 0; $level < 1000; $level++) {
                $componentElements = $domDocument->getElementsByTagName('component');
                if ($componentElements->length === 0) {
                    break;
                }
                $insertHTMLSources = [];
                $list = []; // Save the elements into an array because removeChild() messes up the NodeList
                foreach ($componentElements as $componentElement) {
                    $isInOtherComponentTag = false;
                    $parentNode = $componentElement->parentNode;
                    while ($parentNode !== null && isset($parentNode->tagName)) {
                        if ($parentNode->tagName === 'component') {
                            $isInOtherComponentTag = true;
                            break;
                        }
                        $parentNode = $parentNode->parentNode;
                    }
                    if (!$isInOtherComponentTag) {
                        $list[] = $componentElement;
                    }
                }
                foreach ($list as $i => $componentElement) {
                    $component = $this->makeComponent($componentElement->getAttributes(), $componentElement->innerHTML);
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
                        $insertTargetName = 'html-server-components-compiler-insert-target-' . $i;
                        $componentElement->parentNode->insertBefore($domDocument->createInsertTarget($insertTargetName), $componentElement);
                        $componentElement->parentNode->removeChild($componentElement); // must be before insertHTML because a duplicate elements IDs can occur.
                        $insertHTMLSources[] = ['source' => $componentResultHTML, 'target' => $insertTargetName];
                    } else {
                        $componentElement->parentNode->removeChild($componentElement);
                        $insertHTMLSources[] = ['source' => $componentResultHTML];
                    }
                }
                $domDocument->insertHTMLMulti($insertHTMLSources);
                if (isset($options['recursive']) && $options['recursive'] === false) {
                    break;
                }
            }
        }

        return $domDocument->saveHTML();
    }

    /**
     * Constructs a component object.
     * 
     * @param array $attributes The attributes of the component object.
     * @param string $innerHTML The innerHTML of the component object.
     * @return \IvoPetkov\HTMLServerComponent A component object.
     */
    public function makeComponent(array $attributes = [], string $innerHTML = '')
    {
        if (self::$newComponentCache === null) {
            self::$newComponentCache = new \IvoPetkov\HTMLServerComponent();
        }
        $component = clone(self::$newComponentCache);
        foreach ($attributes as $name => $value) {
            $component->setAttribute($name, $value);
        }
        $component->innerHTML = $innerHTML;
        return $component;
    }

}
