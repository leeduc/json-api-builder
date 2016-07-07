<?php

class TestJson extends TestCase
{
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
                          ->included($relationships)
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
        $parse->shouldReceive('checkPaginationObject')->once()->andReturn(true);

        $parse1 = \Mockery::mock('Leeduc\JsonApiBuilder\JsonApiBuilder\Generate[json]', [$this->app->request, $this->app->view]);
        $parse1->shouldReceive('json')->once()->andReturn($parse);

        $a = $parse1->setData($data['user'])
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

    public function testPaginationPaginationClass()
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

        $parse1 = \Mockery::mock('Leeduc\JsonApiBuilder\JsonApiBuilder\Generate[json]', [$this->app->request, $this->app->view]);
        $parse1->shouldReceive('json')->once()->andReturn($parse);

        $a = $parse1->setData($data['user'])
                    ->entity('auth.show')
                    ->json()
                    ->pagination()
                    ->response();

        $a = json_decode($a->content(), true);

        $compare = [
          "self" => "http://localhost",
             "first" => "examlple.com/1",
             "prev" => "example.com/previous",
             "next" => "example.com/next",
             "last" => "examlple.com/10"
        ];

        foreach ($a['links'] as $key => $value) {
            $this->assertEquals($compare[$key], $value);
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

    public function testSetJsonapi()
    {
        $data = [
          'version' => '1.0',
        ];

        $a = \JsonApiBuilder::json($data)
                              ->response();

        $res = json_decode($a->content(), true);
        foreach ($res['jsonapi'] as $key => $value) {
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
