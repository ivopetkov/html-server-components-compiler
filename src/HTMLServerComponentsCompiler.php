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
    const VERSION = '0.4.0';

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
     * @throws \InvalidArgumentException
     * @return void No value is returned
     */
    function addAlias($alias, $original)
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
     * @param string $content The content to be processed
     * @param array $options Compiler options
     * @throws \InvalidArgumentException
     * @return string The result HTML code
     */
    public function process($content, $options = [])
    {
        if (isset($options['_internal_process_components']) && $options['_internal_process_components'] === false) {
            return $content;
        }
        $domDocument = new \IvoPetkov\HTML5DOMDocument();
        $domDocument->loadHTML($content);
        $componentElements = $domDocument->getElementsByTagName('component');
        $componentElementsCount = $componentElements->length;
        if ($componentElementsCount > 0) {
            for ($i = 0; $i < $componentElementsCount; $i++) {
                $component = $componentElements->item(0);
                $attributes = $component->getAttributes();
                if (isset($attributes['src'])) {
                    $srcAttributeValue = $attributes['src'];
                    if (isset($this->aliases[$srcAttributeValue])) {
                        $sourceParts = explode(':', $this->aliases[$srcAttributeValue], 2);
                    } else {
                        $sourceParts = explode(':', $srcAttributeValue, 2);
                    }
                    if (sizeof($sourceParts) === 2) {
                        $scheme = $sourceParts[0];
                        if (isset($options['recursive']) && $options['recursive'] === false && ($scheme === 'data' || $scheme === 'file')) {
                            $componentOptions = array_values($options);
                            $componentOptions['_internal_process_components'] = false;
                        }
                        if ($scheme === 'data') {
                            $componentHTML = $this->processData($sourceParts[1], isset($componentOptions) ? $componentOptions : $options);
                        } elseif ($scheme === 'file') {
                            $componentHTML = $this->processFile(urldecode($sourceParts[1]), $attributes, $component->innerHTML, [], isset($componentOptions) ? $componentOptions : $options);
                        } else {
                            throw new \Exception('URI scheme not valid!' . $domDocument->saveHTML($component));
                        }
                    } else {
                        throw new \Exception('URI scheme not found!' . $domDocument->saveHTML($component));
                    }
                } else {
                    throw new \Exception('Component src attribute missing! ' . $domDocument->saveHTML($component));
                }

                $isInBodyTag = false;
                $parentNode = $component->parentNode;
                while ($parentNode !== null && isset($parentNode->tagName)) {
                    if ($parentNode->tagName === 'body') {
                        $isInBodyTag = true;
                        break;
                    }
                    $parentNode = $parentNode->parentNode;
                }
                if ($isInBodyTag) {
                    $insertTargetName = 'html-server-components-compiler-target-' . uniqid();
                    $component->parentNode->insertBefore($domDocument->createInsertTarget($insertTargetName), $component);
                    $domDocument->insertHTML($componentHTML, $insertTargetName);
                } else {
                    $domDocument->insertHTML($componentHTML);
                }

                $component->parentNode->removeChild($component);
            }
        }

        return $domDocument->saveHTML();
    }

    /**
     * Creates a component from the data specified and processes the content
     * 
     * @param string $data The data to be used as component content. Currently only base64 encoded data is allowed.
     * @param array $options Compiler options
     * @return string The result HTML code
     */
    public function processData($data, $options = [])
    {
        $content = $data;
        if (substr($data, 0, 7) === 'base64,') {
            $content = base64_decode(substr($data, 7));
        }
        return $this->process($content, $options);
    }

    /**
     * Creates a component from the file specified and processes the content
     * 
     * @param string $file The file to be run as component
     * @param array $attributes Component object attributes
     * @param string $innerHTML Component object innerHTML
     * @param array $variables List of variables that will be passes to the file. They will be available in the file scope.
     * @param array $options Compiler options
     * @return string The result HTML code
     */
    public function processFile($file, $attributes = [], $innerHTML = '', $variables = [], $options = [])
    {
        $component = $this->constructComponent($attributes, $innerHTML);
        return $this->process($this->getComponentFileContent($file, array_merge($variables, ['component' => $component])), $options);
    }

    /**
     * Constructs a component object
     * 
     * @param array $attributes The attributes of the component object
     * @param string $innerHTML The innerHTML of the component object
     * @return \IvoPetkov\HTMLServerComponent A component object
     */
    protected function constructComponent($attributes = [], $innerHTML = '')
    {
        $component = new \IvoPetkov\HTMLServerComponent();
        $component->attributes = $attributes;
        $component->innerHTML = $innerHTML;
        return $component;
    }

    /**
     * Includes a component file and returns its content
     * 
     * @param string $file The filename
     * @param array $variables List of variables that will be passes to the file. They will be available in the file scope.
     * @throws \Exception
     * @return string The content of the file
     */
    protected function getComponentFileContent($file, $variables)
    {
        if (is_file($file)) {
            $__componentFile = $file;
            unset($file);
            if (!empty($variables)) {
                extract($variables, EXTR_SKIP);
            }
            ob_start();
            include $__componentFile;
            $content = ob_get_clean();
            return $content;
        } else {
            throw new \Exception('Component file cannot be found (' . $file . ')');
        }
    }

}
