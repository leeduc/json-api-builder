<?php
class TestCase extends Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return array('PhpSoft\JsonApiBuilder\JsonApiBuilderServiceProvider');
    }

    protected function getPackageAliases($app)
    {
        return array(
            'JsonApiBuilder' => 'PhpSoft\JsonApiBuilder\Facades\JsonApiBuilder'
        );
    }

    public function setUp()
    {
        parent::setUp();
    }

    public function testExample()
    {
        $this->assertTrue(true);
    }

    public function assertArrayStructure($expected, $actual, $msg = '')
    {
        ksort($expected);
        ksort($actual);
        $this->assertSame($expected, $actual, $msg);
    }
}