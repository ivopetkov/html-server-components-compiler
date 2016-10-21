<?php

$classes = array(
    'IvoPetkov\HTMLServerComponent' => __DIR__ . '/src/HTMLServerComponent.php',
    'IvoPetkov\HTMLServerComponentsCompiler' => __DIR__ . '/src/HTMLServerComponentsCompiler.php'
);

spl_autoload_register(function ($class) use ($classes) {
    if (isset($classes[$class])) {
        require $classes[$class];
    }
});

