<?php

/*
 * HTML Server Components compiler
 * http://ivopetkov.com/b/html-server-components/
 * Copyright (c) 2015 Ivo Petkov
 * Free to use under the MIT license.
 */

class HTMLServerComponentTestCase extends PHPUnit\Framework\TestCase
{

    /**
     * 
     * @param string $name
     * @param string $content
     * @return string
     */
    function makeFile(string $name, string $content): string
    {
        $dir = sys_get_temp_dir() . '/htmlservercomponentstestdir' . uniqid() . '/';
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        file_put_contents($dir . $name, $content);
        return $dir . $name;
    }

}
