<?php
class TestCase extends Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return array('Leeduc\JsonApiBuilder\JsonApiBuilderServiceProvider');
    }

    protected function getPackageAliases($app)
    {
        return array(
            'JsonApiBuilder' => 'Leeduc\JsonApiBuilder\Facades\JsonApiBuilder'
        );
    }

    public function setUp()
    {
        parent::setUp();

        Route::get('test', function () {
            echo \JsonApiBuilder::json()->pagination(['next' => 123213, 'custom' => 'abc'])->meta(['version' => '1.0'])->response();
            die;
        });

        Route::get('user/{id}', ['as' => 'stdclass', function ($id) {
            return $id;
        }]);

        Route::get('comment/{id}', ['as' => 'comments', function ($id) {
            return $id;
        }]);

        Route::get('comment/{id}', ['as' => 'posts', function ($id) {
            return $id;
        }]);
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
