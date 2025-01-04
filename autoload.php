<?php

/*
 * HTML Server Components compiler
 * http://ivopetkov.com/b/html-server-components/
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

$classes = array(
    'IvoPetkov\HTMLServerComponent' => __DIR__ . '/src/HTMLServerComponent.php',
    'IvoPetkov\HTMLServerComponentsCompiler' => __DIR__ . '/src/HTMLServerComponentsCompiler.php'
);

spl_autoload_register(function ($class) use ($classes): void {
    if (isset($classes[$class])) {
        require $classes[$class];
    }
});
