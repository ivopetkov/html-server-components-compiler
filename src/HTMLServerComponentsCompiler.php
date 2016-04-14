<?php

/*
 * HTML Server Components compiler
 * http://ivopetkov.com/b/html-server-components/
 * Copyright 2015-2016, Ivo Petkov
 * Free to use under the MIT license.
 */

/**
 * The class that processes components
 */
class HTMLServerComponentsCompiler
{

    /**
     * 
     */
    const VERSION = '0.2.3';

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
     * @return string
     */
    public function process($html)
    {
        $domDocument = new IvoPetkov\HTML5DOMDocument();
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
                        if ($scheme === 'data') {
                            $componentHTML = $this->processData($sourceParts[1]);
                        } elseif ($scheme === 'file') {
                            $componentHTML = $this->processFile(urldecode($sourceParts[1]), $attributes, $component->innerHTML);
                        } else {
                            throw new \Exception('URI scheme not valid!' . $domDocument->saveHTML($component));
                        }
                    } else {
                        throw new \Exception('URI scheme not found!' . $domDocument->saveHTML($component));
                    }
                } else {
                    throw new \Exception('Component src attribute missing! ' . $domDocument->saveHTML($component));
                }
                $insertTargetName = 'html-server-components-compiler-target-' . uniqid();
                $component->parentNode->insertBefore($domDocument->createInsertTarget($insertTargetName), $component);
                $domDocument->insertHTML($componentHTML, $insertTargetName);

                $component->parentNode->removeChild($component);
            }
        }

        return $domDocument->saveHTML();
    }

    /**
     * 
     * @param string $data
     * @return string
     */
    public function processData($data)
    {
        $html = $data;
        if (substr($data, 0, 7) === 'base64,') {
            $html = base64_decode(substr($data, 7));
        }
        return $this->process($html);
    }

    /**
     * 
     * @param string $file
     * @param array $attributes
     * @param string $innerHTML
     * @return string
     */
    public function processFile($file, $attributes = [], $innerHTML = '')
    {
        $component = $this->constructComponent($attributes, $innerHTML);
        return $this->process($this->getComponentFileContent($file, $component));
    }

    /**
     * 
     * @param array $attributes
     * @param string $innerHTML
     * @return \HTMLServerComponent
     */
    protected function constructComponent($attributes = [], $innerHTML = '')
    {
        $component = new HTMLServerComponent();
        $component->attributes = $attributes;
        $component->innerHTML = $innerHTML;
        return $component;
    }

    /**
     * 
     * @param string $file
     * @param HTMLServerComponent $component
     * @throws \Exception
     * @return string
     */
    protected function getComponentFileContent($file, $component)
    {
        if (is_file($file)) {
            ob_start();
            include $file;
            $content = ob_get_clean();
            return $content;
        } else {
            throw new \Exception('Component file cannot be found');
        }
    }

}
