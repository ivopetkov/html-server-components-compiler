<?php

/*
 * HTML Server Components compiler
 * http://ivopetkov.com/b/html-server-components/
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

namespace IvoPetkov;

use IvoPetkov\HTML5DOMDocument;

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
     * Stores the defined tags.
     * 
     * @var array 
     */
    private $tags = [];

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
     * Defines a new tag.
     * 
     * @param string $tagName The tag name.
     * @param string $src The tag source.
     * @return void No value is returned.
     * @throws \InvalidArgumentException
     */
    public function addTag(string $tagName, string $src)
    {
        if (preg_match('/^[a-z\-]+$/', $tagName) !== 1) {
            throw new \InvalidArgumentException('The tag name provided is not valid! It may contain letters (a-z) and dashes (-).');
        }
        $this->tags[strtolower(trim($tagName))] = $src;
    }

    /**
     * Converts components code (if any) into HTML code.
     * 
     * @param string|\IvoPetkov\HTMLServerComponent $content The content to be processed.
     * @param array $options Compiler options.
     * @return string The result HTML code.
     */
    public function process($content, array $options = [])
    {
        $tagNames = array_keys($this->tags);
        $tagNames[] = 'component';
        if (is_string($content)) {
            $found = false;
            foreach ($tagNames as $tagName) {
                if (strpos($content, '<' . $tagName) !== false) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return $content;
            }
        } elseif (!($content instanceof \IvoPetkov\HTMLServerComponent)) {
            throw new \InvalidArgumentException('');
        }

        $getComponentFileContent = static function ($file, $component, $variables) {
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

        $getComponentResultHTML = function ($component) use (&$getComponentFileContent, $options) {
            $srcAttributeValue = $component->getAttribute('src');
            if ($srcAttributeValue === null) {
                if (isset($this->tags[$component->tagName])) {
                    $srcAttributeValue = $this->tags[$component->tagName];
                } else {
                    throw new \Exception('Component tag name is not defined at ' . (string) $component . '!');
                }
            }
            if ($srcAttributeValue !== null) {
                // todo check alias of alias
                $sourceParts = explode(':', isset($this->aliases[$srcAttributeValue]) ? $this->aliases[$srcAttributeValue] : $srcAttributeValue, 2);
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
        $domDocument = new HTML5DOMDocument();
        if ($content instanceof \IvoPetkov\HTMLServerComponent) {
            $domDocument->loadHTML($getComponentResultHTML($content), HTML5DOMDocument::ALLOW_DUPLICATE_IDS);
            if (isset($options['recursive']) && $options['recursive'] === false) {
                $disableLevelProcessing = true;
            }
        } else {
            $domDocument->loadHTML($content, HTML5DOMDocument::ALLOW_DUPLICATE_IDS);
        }
        if (!$disableLevelProcessing) {
            $tagsQuerySelector = implode(',', $tagNames);
            for ($level = 0; $level < 1000; $level++) {
                $componentElements = $domDocument->querySelectorAll($tagsQuerySelector);
                if ($componentElements->length === 0) {
                    break;
                }
                $insertHTMLSources = [];
                $list = []; // Save the elements into an array because removeChild() messes up the NodeList
                foreach ($componentElements as $index => $componentElement) {
                    $parentNode = $componentElement->parentNode;
                    $list[$index] = [$componentElement, $parentNode, []]; // The last one will contain the parents tag names
                    while ($parentNode !== null && isset($parentNode->tagName)) {
                        $tagName = $parentNode->tagName;
                        $list[$index][2][] = $tagName;
                        if ($tagName === 'head' || $tagName === 'body') {
                            break;
                        }
                        $parentNode = $parentNode->parentNode;
                    }
                }
                foreach ($list as $index => $componentData) {
                    if (!empty(array_intersect($componentData[2], $tagNames))) {
                        continue;
                    }
                    $componentElement = $componentData[0];
                    $component = $this->makeComponent($componentElement->getAttributes(), $componentElement->innerHTML, $componentElement->tagName);
                    $componentResultHTML = $getComponentResultHTML($component);
                    if (array_search('body', $componentData[2]) !== false) {
                        $insertTargetName = 'html-server-components-compiler-insert-target-' . $index;
                        $componentData[1]->insertBefore($domDocument->createInsertTarget($insertTargetName), $componentElement);
                        $componentData[1]->removeChild($componentElement); // must be before insertHTML because a duplicate elements IDs can occur.
                        $insertHTMLSources[] = ['source' => $componentResultHTML, 'target' => $insertTargetName];
                    } else {
                        $componentData[1]->removeChild($componentElement);
                        $insertHTMLSources[] = ['source' => $componentResultHTML];
                    }
                }
                $domDocument->insertHTMLMulti($insertHTMLSources);
                if (isset($options['recursive']) && $options['recursive'] === false) {
                    break;
                }
            }
        }

        $domDocument->modify(HTML5DOMDocument::FIX_MULTIPLE_TITLES | HTML5DOMDocument::FIX_DUPLICATE_METATAGS | HTML5DOMDocument::FIX_MULTIPLE_HEADS | HTML5DOMDocument::FIX_MULTIPLE_BODIES | HTML5DOMDocument::OPTIMIZE_HEAD | HTML5DOMDocument::FIX_DUPLICATE_STYLES);
        return $domDocument->saveHTML();
    }

    /**
     * Constructs a component object.
     * 
     * @param array $attributes The attributes of the component object.
     * @param string $innerHTML The innerHTML of the component object.
     * @param string $tagName The tag name of the component object.
     * @return \IvoPetkov\HTMLServerComponent A component object.
     */
    public function makeComponent(array $attributes = [], string $innerHTML = '', string $tagName = 'component')
    {
        if (self::$newComponentCache === null) {
            self::$newComponentCache = new \IvoPetkov\HTMLServerComponent();
        }
        $component = clone (self::$newComponentCache);
        $component->setAttributes($attributes);
        $component->innerHTML = $innerHTML;
        $component->tagName = $tagName;
        return $component;
    }
}
