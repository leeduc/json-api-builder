<?php

class TestObject extends TestCase
{
    public static $objects = array();

    public static function setUpBeforeClass()
    {
        $faker = Faker\Factory::create();

        $user = new stdClass();
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
        $a = \JsonApiBuilder::setData($data['user']);
        $response = $a->getData();

        $this->assertArrayStructure((array) [$data['user']], (array) $response);
    }

    public function testErrorNotObjectOrArray()
    {
        try {
            \JsonApiBuilder::setData('String');
        } catch (\Exception $e) {
            $this->assertEquals($e->getCode(), 400);
            $this->assertEquals($e->getMessage(), 'Resource must be array or object.');
        }
    }

    public function testGetData()
    {
        $data = self::$objects;
        $a = \JsonApiBuilder::setData($data['user'])
                          ->entity(['email', 'name', 'gender'])
                          ->relationship(['comments', 'posts'])
                          ->included(['posts', 'comments' => ['post_id', 'content']]);

        foreach ($a->data as $resource) {
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


        foreach ($a->included as $k => $v) {
            $type = $v['type'];
            $resources = [];
            if (isset($data['user']->$type)) {
                foreach ($data['user']->$type as $key => $value) {
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

    public function testEntityObject()
    {
        $data = self::$objects;
        $a = \JsonApiBuilder::setData($data['user'])
                            ->entity(['email', 'name', 'gender'], function($data) {
                                $data[0]['id'] = 100;
                                return $data;
                            })
                            ->relationship(['comments', 'posts'])
                            ->included(['posts', 'comments'], function($data) {
                                foreach ($data as $key => $value) {
                                    if($value['type'] == 'comments') {
                                        $data[$key]['attributes']['id'] = 100;
                                    }
                                }
                                return $data;
                            });
        $response = $a->parse();

        foreach ($response['data'] as $key => $value) {
            switch ($key) {
              case 'id':
                $this->assertEquals($value, 100);
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

        foreach ($response['included'] as $k => $v) {
            $type = $v['type'];
            $resources = [];
            if (isset($data['user']->$type)) {
                foreach ($data['user']->$type as $key => $value) {
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
                        if($v['type'] == 'comments' && $k1 == 'id') {
                          $this->assertEquals($v1, 100);
                        } else {
                          $this->assertEquals($v1, $resource[$k1]);
                        }
                    }
                    break;
                }
            }
        }
    }

    public function testEntityArray()
    {
        $data = self::$objects;
        $a = \JsonApiBuilder::setData([$data['user']])
                            ->entity(['email', 'name', 'gender'])
                            ->relationship(['comments', 'posts'])
                            ->included(['posts', 'comments' => ['post_id', 'content']]);
        $response = $a->parse();

        foreach ($response['data'] as $key => $resource) {
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

        foreach ($response['included'] as $k => $v) {
            $type = $v['type'];
            $resources = [];
            if (isset($data['user']->$type)) {
                foreach ($data['user']->$type as $key => $value) {
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

class Posts
{
    public function __call($method, $args)
    {
        if (isset($this->$method)) {
            $func = $this->$method;
            return call_user_func_array($func, $args);
        }
    }

    public function toArray()
    {
        return [
          'id' => $this->id,
          'user_id' => $this->user_id,
          'title' => $this->title,
          'content' => $this->content
        ];
    }
}
