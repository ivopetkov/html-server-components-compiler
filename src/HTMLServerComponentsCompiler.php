<?php

/*
 * HTML Server Components compiler
 * http://ivopetkov.com/b/html-server-components/
 * Copyright 2015-2016, Ivo Petkov
 * Free to use under the MIT license.
 */

namespace IvoPetkov;

/**
 * The class that processes components
 */
class HTMLServerComponentsCompiler
{

    /**
     * 
     */
    const VERSION = '0.3.0';

    /**
     *
     * @var array 
     */
    private $aliases = [];

    /**
     * Registers an alias
     * @param string $alias
     * @param string $original
     */
    function addAlias($alias, $original)
    {
        $this->aliases[$alias] = $original;
    }

    /**
     * Process (merge) components
     * @param string $html
     * @param array $options
     * @return string
     */
    public function process($html, $options = [])
    {
        if (isset($options['_internal_process_components']) && $options['_internal_process_components'] === false) {
            return $html;
        }
        $domDocument = new \IvoPetkov\HTML5DOMDocument();
        $domDocument->loadHTML($html);
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
     * 
     * @param string $data
     * @param array $options
     * @return string
     */
    public function processData($data, $options = [])
    {
        $html = $data;
        if (substr($data, 0, 7) === 'base64,') {
            $html = base64_decode(substr($data, 7));
        }
        return $this->process($html, $options);
    }

    /**
     * 
     * @param string $file
     * @param array $attributes
     * @param string $innerHTML
     * @param array $variables
     * @param array $options
     * @return string
     */
    public function processFile($file, $attributes = [], $innerHTML = '', $variables = [], $options = [])
    {
        $component = $this->constructComponent($attributes, $innerHTML);
        return $this->process($this->getComponentFileContent($file, array_merge($variables, ['component' => $component])), $options);
    }

    /**
     * 
     * @param array $attributes
     * @param string $innerHTML
     * @return \IvoPetkov\HTMLServerComponent
     */
    protected function constructComponent($attributes = [], $innerHTML = '')
    {
        $component = new \IvoPetkov\HTMLServerComponent();
        $component->attributes = $attributes;
        $component->innerHTML = $innerHTML;
        return $component;
    }

    /**
     * 
     * @param string $file
     * @param array $variables
     * @throws \Exception
     * @return string
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
