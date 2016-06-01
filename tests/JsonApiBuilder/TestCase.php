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
        View::addLocation(__DIR__ . '/../views');
        Route::get('test', function () {
            echo \JsonApiBuilder::json()->pagination(['next' => 123213, 'custom' => 'abc'])->meta(['version' => '1.0'])->response();
            die;
        });

        Route::get('user/{id}', ['as' => 'get_user', function ($id) {
            return $id;
        }]);

        Route::get('comment/{id}', ['as' => 'get_comment', function ($id) {
            return $id;
        }]);

        Route::get('comment/{id}', ['as' => 'get_post', function ($id) {
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
