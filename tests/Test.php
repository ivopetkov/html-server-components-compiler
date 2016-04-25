<?php

/*
 * HTML Server Components compiler
 * http://ivopetkov.com/b/html-server-components/
 * Copyright 2015-2016, Ivo Petkov
 * Free to use under the MIT license.
 */

/**
 * @runTestsInSeparateProcesses
 */
class Test extends HTMLServerComponentTestCase
{

    /**
     * 
     */
    public function testProccessHTML()
    {
        $fullFilename = $this->createFile('component1.php', '<html><head><meta custom="value"></head><body>text1</body></html>');

        $compiler = new \IvoPetkov\HTMLServerComponentsCompiler();
        $result = $compiler->process('<component src="file:' . $fullFilename . '"/>');
        $expectedResult = '<!DOCTYPE html>' . "\n" . '<html><head><meta custom="value"></head><body>text1</body></html>';
        $this->assertTrue($result === $expectedResult);

        $compiler = new \IvoPetkov\HTMLServerComponentsCompiler();
        $result = $compiler->process('<html><body>'
                . 'text0'
                . '<component src="file:' . $fullFilename . '"/>'
                . 'text2'
                . '</body></html>');
        $expectedResult = '<!DOCTYPE html>' . "\n" . '<html><head><meta custom="value"></head><body>'
                . 'text0'
                . 'text1'
                . 'text2'
                . '</body></html>';
        $this->assertTrue($result === $expectedResult);
    }

}
