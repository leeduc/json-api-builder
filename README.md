# Json Api Builder

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-coverall]][link-coverrall]
[![Total Downloads][ico-downloads]][link-downloads]
[![Software License][ico-license]](LICENSE.md)
<!-- [![Coverage Status][ico-scrutinizer]][link-scrutinizer] -->
<!-- [![Quality Score][ico-code-quality]][link-code-quality] -->

This package is auto generate data follow jsonapi.org.

## Install


Via Composer

``` bash
$ composer require leeduc/json-api-builder
```

Once this has finished, you will need to add the service provider to the providers array in your app.php config as follows:

``` php
'providers' => [
    // ...
    Leeduc\JsonApiBuilder\JsonApiBuilderServiceProvider::class,
]
```

Next, also in the app.php config file, under the aliases array, you may want to add facades.

``` php
'aliases' => [
    // ...
    'JsonApiBuilder' => Leeduc\JsonApiBuilder\Facades\JsonApiBuilder::class,
]
```

## Usage

Build Schema in folder views of resource

`posts.view` = `app\resources\views\posts\show.schema.yaml`

``` yaml
id: id
type: user
attributes:
  name: name
  email: email
relationships:
  posts:
    partial: posts.show # Yaml view path
    links:
      self: get_user # Route name
      related: get_user # Route name
  comments:
    partial: comments.show # Yaml view path
    links:
      self: get_user # Route name
      related: get_user # Route name
links:
  self: get_user # Route name
```

Build Array

``` php
$data = $users = User::with('comments')->paginate(10); // List
$data = $users = User::with('comments')->first(); // Object

$builder = \JsonApiBuilder::setData($data)
                    ->entity('view.path.name', function($data) {
                        $data['id'] = 100;
                        return $data;
                    })
                    ->relationship(['comments'])
                    ->included(['comments' => ['post_id', 'content']]);

dd($builder->parse()); // Array data
```

Build Json

``` php
$builder = \JsonApiBuilder::setData($data)
                    ->entity([
                        'id' => 'id',
                        'type' => 'type',
                        'attributes' => [
                            'name' => 'name'
                            'email' => 'email'
                        ],
                        'relationships' => [
                            'posts' => [
                              'partial' => 'posts.show',
                              'links' => [
                                'self' => 'route_name',
                                'related' => 'route_name'
                              ],
                            ],
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
                              ],
                              'links' => [
                                'self' => 'route_name',
                                'related' => 'route_name'
                              ],
                            ]
                        ]
                    ], function($data) {
                        // custom entity data
                        return $data;
                    })
                    ->relationship(['comments'])
                    ->included(['comments'])
                    ->json(['version' => '1.0'])
                    ->meta([
                      'version' => '1.0'
                    ])
                    ->pagination([
                      'next' => 'example/next',
                      'pre' => 'example/pre'
                    ])
                    ->response();

dd($builder); // Class Symfony\Component\HttpFoundation\Response
dd($builder->getContent()); // Get Json
```

Json response
``` json
{
  "data": [
    {
      "id": 1,
      "type": "user",
      "attributes": {
        "name": "Pj2EHmiLOH",
        "email": "Tqxfq6aZDk@gmail.com"
      },
      "links": {
        "self": "http://example.com/user\/1"
      },
      "relationships": {
        "comments": {
          "data": [
            {
              "id": 2,
              "type": "comment"
            },
            {
              "id": 8,
              "type": "comment"
            }
          ],
          "links": {
            "self": "http://example.com/user\/1\/relationships\/comments",
            "related": "http://example.com/user\/1\/comments"
          }
        }
      }
    }
  ],
  "included": [
    {
      "id": 2,
      "type": "comment",
      "attributes": {
        "post_id": "3",
        "user_id": "1",
        "content": "UHXLbmJxySxiTTYdjzR539bNXjohgpCVj0WfwvmZWKUonhUipxJeHPh0AtTWqIZpzLZfixawJJEQwqILf93Co5edPOrKDfaqvkSQ"
      },
      "relationships": {
        "user": {
          "data": [
            {
              "id": 1,
              "type": "user"
            }
          ],
          "links": {
            "self": "http://example.com/comment\/2\/relationships\/user"
          }
        }
      }
    },
    {
      "id": 8,
      "type": "comment",
      "attributes": {
        "post_id": "2",
        "user_id": "1",
        "content": "Y8kDX5EOQFtqoy4171bGFVNrvYgMRr9UVHQvD7Eed43YgzeZ1KFJipTFCMJVu6rtb4V8Fm14mv2t3aN26CRNgiOqDsGiMPbQyVJF"
      },
      "relationships": {
        "user": {
          "data": [
            {
              "id": 1,
              "type": "user"
            }
          ],
          "links": {
            "self": "http://example.com/comment\/8\/relationships\/user"
          }
        }
      }
    }
  ],
  "jsonapi": {
    "version": "1.0"
  },
  "links": {
    "self": "http://example.com/test",
    "first": "http://example.com/test?page%5Bsize%5D=1&page%5Bnumber%5D=1",
    "next": "http://example.com/test?page%5Bsize%5D=1&page%5Bnumber%5D=2",
    "last": "http://example.com/test?page%5Bsize%5D=1&page%5Bnumber%5D=40"
  }
}
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email lee.duc55@gmail.com instead of using the issue tracker.

## Credits

- [Le Duc][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/leeduc/json-api-builder.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/leeduc/json-api-builder/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/leeduc/json-api-builder.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/leeduc/json-api-builder.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/leeduc/json-api-builder.svg?style=flat-square
[ico-coverall]: https://img.shields.io/coveralls/leeduc/json-api-builder.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/leeduc/json-api-builder
[link-travis]: https://travis-ci.org/leeduc/json-api-builder
[link-scrutinizer]: https://scrutinizer-ci.com/g/leeduc/json-api-builder/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/leeduc/json-api-builder
[link-downloads]: https://packagist.org/packages/leeduc/json-api-builder
[link-author]: https://github.com/leeduc
[link-contributors]: ../../contributors
[link-coverrall]: https://coveralls.io/github/leeduc/json-api-builder?branch=master
