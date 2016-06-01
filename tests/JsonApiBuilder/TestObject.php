<?php

class TestObject extends TestCase
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
        $relationships = ['comments', 'posts'];
        $a = \JsonApiBuilder::setData($data['user'])
                          ->entity('auth.show')
                          ->relationships($relationships)
                          ->included(['posts', 'comments']);

        foreach ($a->data as $resource) {
            foreach ($resource as $key => $value) {
                switch ($key) {
                case 'id':
                  $this->assertEquals($value, $data['user']->id);
                  break;
                // case 'type':
                //   $this->assertEquals($value, strtolower(class_basename($data['user'])));
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
            foreach ($a->included as $k => $v) {
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

    public function testEntityObject()
    {
        $data = self::$objects;
        $relationships = ['comments', 'posts'];
        $a = \JsonApiBuilder::setData($data['user'])
                            ->entity('auth.show', function ($data) {
                                $data[0]['id'] = 100;
                                return $data;
                            })
                            ->relationships($relationships)
                            ->included(['posts', 'comments'], function ($data) {
                                foreach ($data as $key => $value) {
                                    if ($value['type'] == 'comments') {
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
              // case 'type':
              //   $this->assertEquals($value, strtolower(class_basename($data['user'])));
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

        foreach ($relationships as $ele) {
            foreach ($response['included'] as $k => $v) {
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

    public function testEntityArray()
    {
        $data = self::$objects;
        $relationships = ['comments', 'posts'];
        $a = \JsonApiBuilder::setData([$data['user']])
                            ->entity('auth.show')
                            ->relationships($relationships)
                            ->included(['posts', 'comments' => ['post_id', 'content']]);
        $response = $a->parse();

        foreach ($response['data'] as $key => $resource) {
            foreach ($resource as $key => $value) {
                switch ($key) {
                case 'id':
                $this->assertEquals($value, $data['user']->id);
                break;
                // case 'type':
                // $this->assertEquals($value, strtolower(class_basename($data['user'])));
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
            foreach ($response['included'] as $k => $v) {
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

    public function testGetAttributes()
    {
        $data = self::$objects;
        $a = \JsonApiBuilder::setData([$data['user']])
                            ->entity(['id' => 'id', 'type' => 'user']);
        $response = $a->parse();

        foreach ($response['data'] as $key => $resource) {
            foreach ($resource as $key => $value) {
                switch ($key) {
              case 'id':
                $this->assertEquals($value, $data['user']->id);
              break;
              case 'type':
                $this->assertEquals($value, 'user');
              break;
              case 'attributes':
                foreach ($value as $k => $v) {
                    $this->assertEquals($v, $data['user']->$k);
                }
              break;
            }
            }
        }
    }

    public function testRelationshipNotExists()
    {
        $data = self::$objects;
        $relationships = ['interests'];
        try {
            $a = \JsonApiBuilder::setData([$data['user']])
                              ->entity(['id' => 'id', 'type' => 'user'])
                              ->relationships($relationships);
            $response = $a->parse();
        } catch (\Exception $e) {
            $this->assertEquals($e->getCode(), 500);
            $this->assertEquals($e->getMessage(), "Relationship [interests] does not exists.");
        }
    }

    public function testRelationshipIsObject()
    {
        $user = new stdClass;
        $user->id = 1;
        $user->name = 'Le Duc';
        $user->gender = 'male';
        $user->email = 'Lee.duc55@gmail.com';

        $user->profile = new stdClass;
        $user->profile->id = 1;
        $user->profile->address = '172 Cu Chinh Lan';
        $user->profile->city = 'Da Nang';
        $user->profile->country = 'Viet Nam';
        $user->profile->phone = '01266691391';

        $relationships = ['profile'];
        $a = \JsonApiBuilder::setData($user)
                            ->entity([
                              'id' => 'id',
                              'type' => 'user',
                              'attributes' => [
                                  'name' => 'name',
                                  'gender' => 'gender',
                                  'email' => 'email'
                              ],
                              'relationships' => [
                                'profile' => [
                                  'partial' => [
                                    'id' => 'id',
                                    'type' => 'profile',
                                    'attributes' => [
                                        'address' => 'address',
                                        'city' => 'city',
                                        'phone' => 'phone',
                                        'country' => 'country'
                                    ]
                                  ]
                                ]
                              ]
                            ])
                            ->relationships($relationships)
                            ->included($relationships);
        $response = $a->parse();

        foreach ($response['data'] as $key => $value) {
            switch ($key) {
          case 'id':
          $this->assertEquals($value, $user->id);
          break;
          case 'type':
          $this->assertEquals($value, 'user');
          break;
          case 'attributes':
          foreach ($value as $k => $v) {
              $this->assertEquals($v, $user->$k);
          }
          break;
          case 'relationships':
          foreach ($value as $k => $v) {
              foreach ($v['data'] as $k1 => $v1) {
                  $this->assertEquals($v1['id'], $user->profile->id);
              }
          }
          break;
        }
        }

        foreach ($relationships as $ele) {
            foreach ($response['included'] as $k => $v) {
                $type = $v['type'];
                $resources = [];

                if (strpos($ele, $type) === false) {
                    continue;
                }

                if (isset($user->$ele)) {
                    $resources[] = (array) $user->$ele;
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

    public function testIncludeNotExists()
    {
        $data = self::$objects;
        try {
            $a = \JsonApiBuilder::setData([$data['user']])
                            ->entity('auth.show')
                            ->relationships(['comments', 'posts'])
                            ->included(['interests']);
        } catch (\Exception $e) {
            $this->assertEquals($e->getCode(), 500);
            $this->assertEquals($e->getMessage(), "Included [interests] data does not exists.");
        }
    }

    public function testViewNotFound()
    {
        $data = self::$objects;
        try {
            $a = \JsonApiBuilder::setData([$data['user']])
                            ->entity('auth.show1')
                            ->relationships(['comments', 'posts'])
                            ->included(['interests']);
        } catch (\Exception $e) {
            $this->assertEquals($e->getCode(), 404);
            $this->assertEquals($e->getMessage(), "View [auth.show1] not found.");
        }
    }

    public function testPartialWrongSyntax()
    {
        $data = self::$objects;
        $a = \JsonApiBuilder::setData([$data['user']])
                          ->entity([
                            'id' => 'id',
                            'type' => 'user',
                            'attributes' => [
                                'name' => 'name',
                                'gender' => 'gender',
                                'email' => 'email'
                            ],
                            'relationships' => [
                              'profile' => 'profile.show',
                              'comments' => [
                                  'partial' => 'comments.show'
                              ]
                            ]
                          ])->relationships(['comments'])
                          ->included(['comments']);
        $response = $a->parse();

        foreach ($response['data'][0]['relationships'] as $key => $value) {
            $this->assertTrue($key != 'profile');
        }
    }
}

class User
{
    public $id;
    public $name;
    public $gender;
    public $email;
    public $password;
    public $comments;
    public $posts;

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
          'name' => $this->name,
          'email' => $this->email,
          'gender' => $this->gender
        ];
    }

    public function getAttributes()
    {
        return [
          'id' => $this->id,
          'name' => $this->name,
          'email' => $this->email,
          'gender' => $this->gender
        ];
    }

    public function perPage()
    {
        return 10;
    }

    public function appends($params)
    {
        return $this;
    }

    public function url($id)
    {
        return 'examlple.com/' . $id;
    }

    public function lastPage()
    {
        return 10;
    }

    public function previousPageUrl()
    {
        return 'example.com/previous';
    }

    public function nextPageUrl()
    {
        return 'example.com/next';
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
