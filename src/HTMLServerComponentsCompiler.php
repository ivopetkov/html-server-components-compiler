<?php

/*
 * HTML Server Components Compiler
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
    const VERSION = '0.2.0';

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
        $domDocument = $this->getDOMDocument($html);
        $components = $domDocument->getElementsByTagName('component');
        $componentsCount = $components->length;
        if ($componentsCount > 0) {
            $domBodyElement = $domDocument->getElementsByTagName('body')->item(0);
            $domHeadElement = $domDocument->getElementsByTagName('head')->item(0);
            for ($i = 0; $i < $componentsCount; $i++) {
                $component = $components->item(0); // the component is removed later
                if (isset($component->parentNode)) {

                    $attributes = $this->getDOMElementAttributes($component);

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
                                $componentHTML = $this->processFile($sourceParts[1], $attributes, $this->getInnerHTML($component));
                            } else {
                                throw new \Exception('URI scheme not valid!' . $domDocument->saveHTML($component));
                            }
                        } else {
                            throw new \Exception('URI scheme not found!' . $domDocument->saveHTML($component));
                        }
                    } else {
                        throw new \Exception('Component src attribute missing! ' . $domDocument->saveHTML($component));
                    }

                    $componentDOMDocument = $this->getDOMDocument($componentHTML);

                    $componentBodyElement = $componentDOMDocument->getElementsByTagName('body')->item(0);
                    $this->trimDOMElement($componentBodyElement);
                    $componentBodyChildrenCount = $componentBodyElement->childNodes->length;

                    // keep scripts at the bottom
//                    $scriptElementsMovedIndexes = [];
//                    for ($j = $componentBodyChildrenCount - 1; $j >= 0; $j--) {
//                        $componentChild = $componentBodyElement->childNodes->item($j);
//                        if ($componentChild instanceof DOMText) {
//                            continue;
//                        }
//                        if ($componentChild instanceof DOMElement && $componentChild->tagName === 'script') {
//                            $componentChildAttributes = $this->getDOMElementAttributes($componentChild);
//                            if (!isset($componentChildAttributes['type']) || $componentChildAttributes['type'] === '' || $componentChildAttributes['type'] === 'text/javascript') {
//                                $domBodyElement->appendChild($domDocument->importNode($componentChild, true));
//                                $scriptElementsMovedIndexes[$j] = 1;
//                            }
//                        } else {
//                            break;
//                        }
//                    }

                    for ($j = 0; $j < $componentBodyChildrenCount; $j++) {
                        //if (!isset($scriptElementsMovedIndexes[$j])) {
                        $component->parentNode->insertBefore($domDocument->importNode($componentBodyElement->childNodes->item($j), true), $component);
                        //}
                    }

                    // add head elements
                    $componentHeadElement = $componentDOMDocument->getElementsByTagName('head')->item(0);
                    $componentHeadElementChildrenCount = $componentHeadElement->childNodes->length;
                    for ($j = 0; $j < $componentHeadElementChildrenCount; $j++) {
                        $domHeadElement->appendChild($domDocument->importNode($componentHeadElement->childNodes->item($j), true));
                    }

                    // remove title tags if more than one - the last one stays
                    $titleElements = $domHeadElement->getElementsByTagName('title');
                    $titleElementsCount = $titleElements->length;
                    if ($titleElementsCount > 1) {
                        for ($j = $titleElementsCount - 2; $j >= 0; $j--) {
                            $domHeadElement->removeChild($titleElements->item($j));
                        }
                    }

                    // remove the component tag
                    $component->parentNode->removeChild($component);
                }
            }
        }

        $domDocument2 = new DOMDocument;
        $domDocument2->formatOutput = false;
        set_error_handler([$this, "handleInvalidHTMLErrors"]);
        $domDocument2->loadHTML($domDocument->saveHTML());
        restore_error_handler();
        return $domDocument2->saveHTML();
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

    /**
     * 
     * @param \DOMElement $domElement
     * @return array
     */
    private function getDOMElementAttributes($domElement)
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
    private function getDOMDocument($html)
    {
        $html = trim($html);
        if (stripos($html, '<body') === false) {
            $html = '<body>' . $html . '</body>';
        }
        if (stripos($html, '<!DOCTYPE') === false) {
            $html = '<!DOCTYPE html>' . $html;
        }
        $domDocument = new DOMDocument();
        set_error_handler([$this, "handleInvalidHTMLErrors"]);
        $result = $domDocument->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        restore_error_handler();
        if ($result === false) {
            throw new Exception('');
        }
        $domDocument->removeChild($domDocument->childNodes->item(1)); // remove xml instruction

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
    private function getInnerHTML($domElement)
    {
        $html = trim($domElement->ownerDocument->saveHTML($domElement));
        $nodeName = $domElement->nodeName;
        return preg_replace('@^<' . $nodeName . '[^>]*>|</' . $nodeName . '>$@', '', $html);
    }

    /**
     * 
     * @param \DOMElement $domElement
     */
    private function trimDOMElement(&$domElement)
    {
        //remove from beginning
        $childrenCount = $domElement->childNodes->length;
        for ($i = 0; $i < $childrenCount; $i++) {
            $firstChild = $domElement->childNodes->item(0);
            if ($firstChild instanceof DOMText && $firstChild->isWhitespaceInElementContent()) {
                $domElement->removeChild($firstChild);
            } else {
                break;
            }
        }
        //remove from end
        $childrenCount = $domElement->childNodes->length;
        for ($i = $childrenCount - 1; $i >= 0; $i--) {
            $firstChild = $domElement->childNodes->item($i);
            if ($firstChild instanceof DOMText && $firstChild->isWhitespaceInElementContent()) {
                $domElement->removeChild($firstChild);
            } else {
                break;
            }
        }
    }

    /**
     * 
     * @param int $errorNumber
     * @param string $errorMessage
     * @return boolean
     */
    public function handleInvalidHTMLErrors($errorNumber, $errorMessage)
    {
        return true;
    }

}
