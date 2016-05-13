# json-api-builder

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-downloads]][link-downloads]
<!-- [![Coverage Status][ico-scrutinizer]][link-scrutinizer] -->
<!-- [![Quality Score][ico-code-quality]][link-code-quality] -->

This package is auto generate data follow jsonapi.org.

## Install


Via Composer

``` bash
$ composer require php-soft/json-api-builder
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

``` php
$builder = \JsonApiBuilder::setData($data)
                    ->entity(['email', 'name', 'gender'], function($data) {
                        $data['id'] = 100;
                        return $data;
                    })
                    ->relationship(['comments'])
                    ->included(['comments' => ['post_id', 'content']]);
dd($builder->parse());
```
Response

``` php
Array
(
    [data] => Array
        (
            [0] => Array
                (
                    [id] => 100
                    [type] => user
                    [attributes] => Array
                        (
                            [email] => Tqxfq6aZDk@gmail.com
                            [name] => Pj2EHmiLOH
                        )

                    [links] => Array
                        (
                            [self] => http://example/user/1
                        )

                    [relationships] => Array
                        (
                            [comments] => Array
                                (
                                    [data] => Array
                                        (
                                            [0] => Array
                                                (
                                                    [id] => 2
                                                    [type] => comments
                                                )

                                            [1] => Array
                                                (
                                                    [id] => 8
                                                    [type] => comments
                                                )

                                            [2] => Array
                                                (
                                                    [id] => 11
                                                    [type] => comments
                                                )

                                            [3] => Array
                                                (
                                                    [id] => 12
                                                    [type] => comments
                                                )

                                            [4] => Array
                                                (
                                                    [id] => 13
                                                    [type] => comments
                                                )

                                            [5] => Array
                                                (
                                                    [id] => 17
                                                    [type] => comments
                                                )

                                            [6] => Array
                                                (
                                                    [id] => 18
                                                    [type] => comments
                                                )

                                            [7] => Array
                                                (
                                                    [id] => 21
                                                    [type] => comments
                                                )

                                            [8] => Array
                                                (
                                                    [id] => 24
                                                    [type] => comments
                                                )

                                        )

                                    [links] => Array
                                        (
                                            [self] => http://example/user/1/relationships/comments
                                            [related] => http://example/user/1/comments
                                        )

                                )

                        )

                )

        )

    [included] => Array
        (
            [0] => Array
                (
                    [type] => comments
                    [id] => 2
                    [attributes] => Array
                        (
                            [post_id] => 3
                            [content] => UHXLbmJxySxiTTYdjzR539bNXjohgpCVj0WfwvmZWKUonhUipxJeHPh0AtTWqIZpzLZfixawJJEQwqILf93Co5edPOrKDfaqvkSQ
                        )

                )

            [1] => Array
                (
                    [type] => comments
                    [id] => 8
                    [attributes] => Array
                        (
                            [post_id] => 2
                            [content] => Y8kDX5EOQFtqoy4171bGFVNrvYgMRr9UVHQvD7Eed43YgzeZ1KFJipTFCMJVu6rtb4V8Fm14mv2t3aN26CRNgiOqDsGiMPbQyVJF
                        )

                )

            [2] => Array
                (
                    [type] => comments
                    [id] => 11
                    [attributes] => Array
                        (
                            [post_id] => 8
                            [content] => jNOwxrHpJEvFt5wTdRKOeXNS5pmUMcX6JvwtxOeZ3S7HVbdkvej8wk3L3fFBxc2BzHiJQMqP8RmgJRWx5GAJ4In2wkPOkAlMHn9W
                        )

                )

            [3] => Array
                (
                    [type] => comments
                    [id] => 12
                    [attributes] => Array
                        (
                            [post_id] => 4
                            [content] => k42w3zBjLGaKH6QKFyGljyQl9KjPjRYHhcYmpGcAoB2D3k2OI1vjY5yj2um4A8XFRcLgfMEgqzRamPL8dpb4m6Hsrfclrg0e2rmI
                        )

                )

            [4] => Array
                (
                    [type] => comments
                    [id] => 13
                    [attributes] => Array
                        (
                            [post_id] => 7
                            [content] => jUpIlLJ99f7IeXecGVkNQWRZRHqZ1iyxifKNvAMWl8nGFISxAGGsgIVdmZCHiaahYK0xFyQ51DvH7UuWmoK6yGlhxX7RLBTdHWzM
                        )

                )

            [5] => Array
                (
                    [type] => comments
                    [id] => 17
                    [attributes] => Array
                        (
                            [post_id] => 4
                            [content] => 7lscIcVwSgswNapo8fJK9oUttFqQHo9ThKtlnRtCmWcCTU8ezGm9hmc3DGhM73Gf6Krp8Y9sxydJ1UiiA6m8FrfTBeNylUaUTf4c
                        )

                )

            [6] => Array
                (
                    [type] => comments
                    [id] => 18
                    [attributes] => Array
                        (
                            [post_id] => 4
                            [content] => Yc5wGWG0BLP5bCGeYIzuuOh0YWIoTRSePBn7vYTkH4Cy6BykViybuQycW8FzluM89Axw1Iak1ptALwJLBGHUIDFSUZ1hp5h2Iz0K
                        )

                )

            [7] => Array
                (
                    [type] => comments
                    [id] => 21
                    [attributes] => Array
                        (
                            [post_id] => 6
                            [content] => 26ddTqewwOnW2CYZMNhgTgLe00DFyXZLCH6L9xkvVGCtMxMa9xd5vehpSqMZQKb4jORPnOEw0pt3aszZckBjLeAucSk4v7cFfeSu
                        )

                )

            [8] => Array
                (
                    [type] => comments
                    [id] => 24
                    [attributes] => Array
                        (
                            [post_id] => 2
                            [content] => YsG4e8SNVlKGBA9C7UjspS4XyBrGMlON7x0kEL9ozjiGLy3kIDdIzFXTWvSPJOb0MgRnJNJ1BI2jaT9tk0uyt5oxPtVe7n8xLlmn
                        )

                )

        )

)
```


## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email leeduc55@gmail.com instead of using the issue tracker.

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

[link-packagist]: https://packagist.org/packages/leeduc/json-api-builder
[link-travis]: https://travis-ci.org/leeduc/json-api-builder
[link-scrutinizer]: https://scrutinizer-ci.com/g/leeduc/json-api-builder/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/leeduc/json-api-builder
[link-downloads]: https://packagist.org/packages/leeduc/json-api-builder
[link-author]: https://github.com/leeduc
[link-contributors]: ../../contributors
