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
            $posts[$i] = new stdClass();
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

    public function testEntity()
    {
        $data = self::$objects;
        $a = \JsonApiBuilder::setData($data['user'])
                            ->entity(['email', 'name', 'gender'])
                            ->relationship(['comments', 'posts'])
                            ->included(['posts', 'comments' => ['post_id', 'content']]);
        $response = $a->parse();
        dd($response);
        foreach ($response['data'] as $key => $value) {
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
