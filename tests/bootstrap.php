<?php

/*
 * HTML Server Components compiler
 * http://ivopetkov.com/b/html-server-components/
 * Copyright (c) 2015 Ivo Petkov
 * Free to use under the MIT license.
 */

require __DIR__ . '/../vendor/autoload.php';

class HTMLServerComponentTestCase extends PHPUnit_Framework_TestCase
{

    function createFile($name, $content)
    {
        $dir = sys_get_temp_dir() . '/htmlservercomponentstestdir' . uniqid() . '/';
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        file_put_contents($dir . $name, $content);
        return $dir . $name;
    }

}
