<?php

/*
 * HTML Server Components compiler
 * http://ivopetkov.com/b/html-server-components/
 * Copyright (c) Ivo Petkov
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
        $fullFilename = $this->makeFile('component1.php', '<html><head><meta custom="value"></head><body>text1</body></html>');

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

    /**
     * 
     */
    public function testAlias()
    {
        $fullFilename = $this->makeFile('component1.php', '<html><body>text1</body></html>');

        $compiler = new \IvoPetkov\HTMLServerComponentsCompiler();
        $compiler->addAlias('component1', 'file:' . $fullFilename);

        $expectedResult = '<!DOCTYPE html>' . "\n" . '<html><body>text1</body></html>';

        $result = $compiler->process('<component src="file:' . $fullFilename . '" />');
        $this->assertTrue($result === $expectedResult);

        $result = $compiler->process('<component src="component1" />');
        $this->assertTrue($result === $expectedResult);
    }

    /**
     * 
     */
    public function testTags()
    {
        $fullFilename = $this->makeFile('component1.php', '<html><body><?= $component->value; ?></body></html>');

        $compiler = new \IvoPetkov\HTMLServerComponentsCompiler();
        $compiler->addAlias('component1', 'file:' . $fullFilename);
        $compiler->addTag('mytag', 'file:' . $fullFilename);
        $compiler->addTag('my-tag', 'component1');

        $result = $compiler->process('<mytag value="text1"/>');
        $this->assertTrue($result === '<!DOCTYPE html>' . "\n" . '<html><body>text1</body></html>');

        $result = $compiler->process('<my-tag value="text2"/>');
        $this->assertTrue($result === '<!DOCTYPE html>' . "\n" . '<html><body>text2</body></html>');

        $result = $compiler->process('<mytag value="text1"/><my-tag value="text2"/><component src="component1" value="text3"/><component src="file:' . $fullFilename . '" value="text4"/>');
        $this->assertTrue($result === '<!DOCTYPE html>' . "\n" . '<html><body>text1text2text3text4</body></html>');
    }

    /**
     * 
     */
    public function testMakeComponent()
    {
        $fullFilename = $this->makeFile('component1.php', '<html><body>text1</body></html>');

        $compiler = new \IvoPetkov\HTMLServerComponentsCompiler();
        $component = $compiler->makeComponent(['var1' => '1'], 'hi');

        $expectedResult = '<component var1="1">hi</component>';
        $this->assertTrue((string) $component === $expectedResult);
    }

    /**
     * 
     */
    public function testVariables()
    {
        $fullFilename = $this->makeFile('component1.php', '<html><body><?= $component->test1?><?= $test2?></body></html>');

        $compiler = new \IvoPetkov\HTMLServerComponentsCompiler();
        $component = new \IvoPetkov\HTMLServerComponent();
        $component->src = 'file:' . $fullFilename;
        $component->test1 = '1';
        $result = $compiler->process($component, [
            'variables' => [
                'test2' => 2
            ]
        ]);
        $expectedResult = '<!DOCTYPE html>' . "\n" . '<html><body>12</body></html>';
        $this->assertTrue($result === $expectedResult);
    }

    /**
     * 
     */
    public function testProccessRecursion()
    {
        $fullFilename1 = $this->makeFile('component1.php', '<html><head><meta custom="value1"></head><body>text1</body></html>');
        $fullFilename2 = $this->makeFile('component2.php', '<html><head><meta custom="value2"></head><body><component src="file:' . urlencode($fullFilename1) . '"></component>text2</body></html>');
        $fullFilename3 = $this->makeFile('component3.php', '<html><head><meta custom="value3"></head><body><component src="file:' . urlencode($fullFilename2) . '"></component>text3</body></html>');

        $compiler = new \IvoPetkov\HTMLServerComponentsCompiler();
        $result = $compiler->process('<component src="file:' . $fullFilename3 . '"/>');
        $expectedResult = '<!DOCTYPE html>' . "\n" . '<html><head><meta custom="value3"><meta custom="value2"><meta custom="value1"></head><body>text1text2text3</body></html>';
        $this->assertTrue($result === $expectedResult);

        $result = $compiler->process('<component src="file:' . $fullFilename3 . '"/>', ['recursive' => false]);
        $expectedResult = '<!DOCTYPE html>' . "\n" . '<html><head><meta custom="value3"></head><body><component src="file:' . urlencode($fullFilename2) . '"></component>text3</body></html>';
        $this->assertTrue($result === $expectedResult);
    }

    /**
     * 
     */
    public function testProccessData()
    {

        $compiler = new \IvoPetkov\HTMLServerComponentsCompiler();
        $result = $compiler->process('<component src="data:base64,' . base64_encode('<html><body>text1</body></html>') . '" />');
        $expectedResult = '<!DOCTYPE html>' . "\n" . '<html><body>text1</body></html>';
        $this->assertTrue($result === $expectedResult);
    }

    /**
     * 
     */
    public function testComponentInComponentInnerHTML()
    {

        $fullFilename1 = $this->makeFile('component1.php', '<html><body>text1</body></html>');
        $fullFilename2 = $this->makeFile('component2.php', '<html><head><title>hi</title><body><?= $component->innerHTML;?></body>/head></html>');

        $compiler = new \IvoPetkov\HTMLServerComponentsCompiler();
        $result = $compiler->process('<component src="file:' . $fullFilename2 . '"><component src="file:' . $fullFilename1 . '"/></component>');
        $expectedResult = '<!DOCTYPE html>' . "\n" . '<html><head><title>hi</title></head><body>text1</body></html>';
        $this->assertTrue($result === $expectedResult);
    }

    /**
     * 
     */
    public function testComponentAttribute()
    {
        $fullFilename = $this->makeFile('component1.php', '<html><body><?php '
            . 'echo $component->test1;' // 1
            . '$component->test1 = "2";'
            . 'echo $component->test1;' // 2
            . 'echo $component->test2;' // null
            . 'echo (int)isset($component->test1);' // 1
            . 'unset($component->test1);'
            . 'echo (int)isset($component->test1);' // 0
            . '?></body></html>');

        $compiler = new \IvoPetkov\HTMLServerComponentsCompiler();
        $component = new \IvoPetkov\HTMLServerComponent();
        $component->src = 'file:' . $fullFilename;
        $component->test1 = '1';
        $result = $compiler->process($component);
        $expectedResult = '<!DOCTYPE html>' . "\n" . '<html><body>1210</body></html>';
        $this->assertTrue($result === $expectedResult);
    }

    /**
     * 
     */
    public function testHeadContent()
    {

        $compiler = new \IvoPetkov\HTMLServerComponentsCompiler();
        $result = $compiler->process('<html><head><title>111</title></head><body><component src="data:base64,' . base64_encode('<html><head><title>222</title></head></html>') . '" /></body></html>');
        $expectedResult = '<!DOCTYPE html>' . "\n" . '<html><head><title>222</title></head><body></body></html>';
        $this->assertTrue($result === $expectedResult);
    }
}
