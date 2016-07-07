<?php
class TestCase extends Orchestra\Testbench\TestCase
{
    public static $objects = array();

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

        Route::get('user/{id}', ['as' => 'get_user', function ($id) {
            return $id;
        }]);

        Route::get('comment/{id}', ['as' => 'get_comment', function ($id) {
            return $id;
        }]);

        Route::get('post/{id}', ['as' => 'get_post', function ($id) {
            return $id;
        }]);
    }

    public static function setUpBeforeClass()
    {
        $faker = Faker\Factory::create();

        $user = new User();
        $user->id = 1;
        $user->name = $faker->name;
        $user->gender = 'male';
        $user->email = $faker->email;
        $user->password = $faker->md5;

        $comments = [];
        for ($i=0; $i < 10; $i++) {
            $comments[$i] = new stdClass();
            $comments[$i]->id = $i;
            $comments[$i]->user_id = 1;
            $comments[$i]->user = $user;
            $comments[$i]->post_id = rand(1, 100);
            $comments[$i]->content = $faker->sentence($nbWords = 6, $variableNbWords = true);
        }

        $posts = [];
        for ($i=0; $i < 10; $i++) {
            $posts[$i] = new Posts();
            $posts[$i]->id = $i;
            $posts[$i]->user_id = 1;
            $posts[$i]->title = $faker->sentence($nbWords = 6, $variableNbWords = true);
            $posts[$i]->content = $faker->text($maxNbChars = 200);
        }

        $user->comments = $comments;
        $user->posts = $posts;
        self::$objects['user'] = $user;
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
