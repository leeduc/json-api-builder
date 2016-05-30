<?php

class TestJson extends TestCase
{
    public static $objects = array();

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

    public function testSetData()
    {
        $data = self::$objects;
        $a = \JsonApiBuilder::setData($data['user'])
                      ->entity('auth.show')
                      ->parse();
        $b = \JsonApiBuilder::json($a)
                            ->response();
        if ($b->content()) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }

    public function testParse()
    {
        $data = self::$objects;
        $relationships = ['comments', 'posts'];
        $a = \JsonApiBuilder::setData($data['user'])
                          ->entity('auth.show')
                          ->relationships($relationships)
                          ->included(['posts', 'comments' => ['post_id', 'content']])
                          ->json()
                          ->response();

        $a = json_decode($a->content(), true);

        foreach ($a['data'] as $resource) {
            foreach ($resource as $key => $value) {
                switch ($key) {
            case 'id':
              $this->assertEquals($value, $data['user']->id);
              break;
            case 'type':
              $this->assertEquals($value, strtolower(class_basename($data['user'])));
              break;
            case 'attributes':
              foreach ($value as $k => $v) {
                  $this->assertEquals($v, $data['user']->$k);
              }
              break;
            case 'relationships':
              foreach ($value as $k => $v) {
                  foreach ($v['data'] as $k1 => $v1) {
                      $rel = $data['user']->$k;
                      foreach ($rel as $k2 => $v2) {
                          $rel[$k2] = (array) $v2;
                      }

                      $this->assertTrue(array_search($v1['id'], array_column($rel, 'id')) !== false);
                  }
              }
              break;
            }
            }
        }


        foreach ($relationships as $ele) {
            foreach ($a['included'] as $k => $v) {
                $type = $v['type'];
                $resources = [];

                if (strpos($ele, $type) === false) {
                    continue;
                }

                if (isset($data['user']->$ele)) {
                    foreach ($data['user']->$ele as $key => $value) {
                        $resources[] = (array) $value;
                    }
                }

                $list_ids = array_column($resources, 'id');
                foreach ($v as $key => $value) {
                    $resource = $resources[array_search($v['id'], $list_ids)];

                    switch ($key) {
                case 'id':
                  $this->assertEquals($value, $resource['id']);
                  break;
                case 'type':
                  $this->assertEquals($value, $type);
                  break;
                case 'attributes':
                  foreach ($value as $k1 => $v1) {
                      $this->assertEquals($v1, $resource[$k1]);
                  }
                  break;
              }
                }
            }
        }
    }

    public function testPagination()
    {
        $data = self::$objects;

        $a = \JsonApiBuilder::setData($data['user'])
                      ->entity('auth.show')
                      ->json()
                      ->pagination()
                      ->response();

        $a = json_decode($a->content(), true);

        foreach ($a['data'] as $resource) {
            foreach ($resource as $key => $value) {
                switch ($key) {
            case 'id':
              $this->assertEquals($value, $data['user']->id);
              break;
            case 'type':
              $this->assertEquals($value, strtolower(class_basename($data['user'])));
              break;
            case 'attributes':
              foreach ($value as $k => $v) {
                  $this->assertEquals($v, $data['user']->$k);
              }
              break;
            case 'relationships':
              foreach ($value as $k => $v) {
                  foreach ($v['data'] as $k1 => $v1) {
                      $rel = $data['user']->$k;
                      foreach ($rel as $k2 => $v2) {
                          $rel[$k2] = (array) $v2;
                      }

                      $this->assertTrue(array_search($v1['id'], array_column($rel, 'id')) !== false);
                  }
              }
              break;
            }
            }
        }
    }

    public function testPaginationPassCheck()
    {
        $data = self::$objects;
        $relationships = ['comments', 'posts'];
        $a = \JsonApiBuilder::setData([$data['user']])
                            ->entity('auth.show')
                            ->relationships($relationships)
                            ->included(['posts', 'comments' => ['post_id', 'content']]);
        $response = $a->parse();

        $parse = \Mockery::mock('Leeduc\JsonApiBuilder\JsonApiBuilder\Parse[checkPaginationObject]', [$this->app->request, $this->app->view, $response, $a->getData()]);
        $parse->shouldReceive('checkPaginationObject')->andReturn(true);

        \JsonApiBuilder::shouldReceive('json')->andReturn($parse);
        \JsonApiBuilder::shouldReceive('getPath')->andReturn('/home/vagrant/Code/laravel5/test/packages/json-api-builder/tests/JsonApiBuilder/../views/auth/show.schema.yaml');
        \JsonApiBuilder::getFacadeRoot()->makePartial();

        $a = \JsonApiBuilder::setData($data['user'])
                      ->entity('auth.show')
                      ->json()
                      ->pagination()
                      ->response();

        $a = json_decode($a->content(), true);

        foreach ($a['data'] as $resource) {
            foreach ($resource as $key => $value) {
                switch ($key) {
            case 'id':
              $this->assertEquals($value, $data['user']->id);
              break;
            case 'type':
              $this->assertEquals($value, strtolower(class_basename($data['user'])));
              break;
            case 'attributes':
              foreach ($value as $k => $v) {
                  $this->assertEquals($v, $data['user']->$k);
              }
              break;
            case 'relationships':
              foreach ($value as $k => $v) {
                    foreach ($v['data'] as $k1 => $v1) {
                        $rel = $data['user']->$k;
                        foreach ($rel as $k2 => $v2) {
                            $rel[$k2] = (array) $v2;
                        }

                        $this->assertTrue(array_search($v1['id'], array_column($rel, 'id')) !== false);
                    }
                }
                break;
              }
            }
        }


        foreach ($relationships as $ele) {
            foreach ($a['included'] as $k => $v) {
                $type = $v['type'];
                $resources = [];

                if (strpos($ele, $type) === false) {
                    continue;
                }

                if (isset($data['user']->$ele)) {
                    foreach ($data['user']->$ele as $key => $value) {
                        $resources[] = (array) $value;
                    }
                }

                $list_ids = array_column($resources, 'id');
                foreach ($v as $key => $value) {
                    $resource = $resources[array_search($v['id'], $list_ids)];

                    switch ($key) {
                case 'id':
                  $this->assertEquals($value, $resource['id']);
                  break;
                case 'type':
                  $this->assertEquals($value, $type);
                  break;
                case 'attributes':
                  foreach ($value as $k1 => $v1) {
                      $this->assertEquals($v1, $resource[$k1]);
                  }
                  break;
              }
                }
            }
        }
    }

    public function testSetPagination()
    {
        $data = [
          'prev' => 'example.com/prev',
          'next' => 'example.com/next',
          'first' => 'example.com/first',
          'last' => 'example.com/last',
        ];

        $a = \JsonApiBuilder::json()
                              ->pagination($data)
                              ->response();

        $res = json_decode($a->content(), true);
        foreach ($res['links'] as $key => $value) {
            $this->assertEquals($data[$key], $value);
        }
    }

    public function testWith()
    {
        $default = self::$objects;
        $data = [
            'version' => '1.0',
            'copyright' => 'ducl'
        ];

        $a = \JsonApiBuilder::json()
                              ->meta($data)
                              ->response();

        $res = json_decode($a->content(), true);
        foreach ($res['meta'] as $key => $value) {
            $this->assertEquals($data[$key], $value);
        }
    }
}
