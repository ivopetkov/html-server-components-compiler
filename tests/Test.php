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
        $expectedResult = '<!DOCTYPE html><html><head><meta custom="value"></head><body>text1</body></html>';
        $this->assertTrue($result === $expectedResult);

        $compiler = new \IvoPetkov\HTMLServerComponentsCompiler();
        $result = $compiler->process('<html><body>'
                . 'text0'
                . '<component src="file:' . $fullFilename . '"/>'
                . 'text2'
                . '</body></html>');
        $expectedResult = '<!DOCTYPE html><html><head><meta custom="value"></head><body>'
                . 'text0'
                . 'text1'
                . 'text2'
                . '</body></html>';
        $this->assertTrue($result === $expectedResult);
    }

    /**
     * 
     */
    public function testVariables()
    {
        $fullFilename = $this->createFile('component1.php', '<html><body><?= $component->test1?><?= $test2?></body></html>');

        $compiler = new \IvoPetkov\HTMLServerComponentsCompiler();
        $result = $compiler->processFile($fullFilename, ['test1' => '1'], '', ['test2' => 2]);
        $expectedResult = '<!DOCTYPE html><html><body>12</body></html>';
        $this->assertTrue($result === $expectedResult);
    }

    /**
     * 
     */
    public function testProccessRecursion()
    {
        $fullFilename1 = $this->createFile('component1.php', '<html><head><meta custom="value1"></head><body>text1</body></html>');
        $fullFilename2 = $this->createFile('component1.php', '<html><head><meta custom="value2"></head><body><component src="file:' . urlencode($fullFilename1) . '"></component>text2</body></html>');
        $fullFilename3 = $this->createFile('component1.php', '<html><head><meta custom="value3"></head><body><component src="file:' . urlencode($fullFilename2) . '"></component>text3</body></html>');

        $compiler = new \IvoPetkov\HTMLServerComponentsCompiler();
        $result = $compiler->process('<component src="file:' . $fullFilename3 . '"/>');
        $expectedResult = '<!DOCTYPE html><html><head><meta custom="value3"><meta custom="value2"><meta custom="value1"></head><body>text1text2text3</body></html>';
        $this->assertTrue($result === $expectedResult);

        $result = $compiler->process('<component src="file:' . $fullFilename3 . '"/>', ['recursive' => false]);
        $expectedResult = '<!DOCTYPE html><html><head><meta custom="value3"></head><body><component src="file:' . urlencode($fullFilename2) . '"></component>text3</body></html>';
        $this->assertTrue($result === $expectedResult);
    }

}
