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

Build Array

``` php
$builder = \JsonApiBuilder::setData($data)
                    ->entity(['email', 'name', 'gender'], function($data) {
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
                    ->entity(['email', 'name', 'gender'], function($data) {
                        $data['id'] = 100;
                        return $data;
                    })
                    ->relationship(['comments'])
                    ->included(['comments' => ['post_id', 'content']])
                    ->json()
                    ->meta([
                      'version' => '1.0'
                    ])
                    ->pagination([
                      'next' => 'example/next',
                      'pre' => 'example/pre'
                    ])
                    ->response();

dd($builder); // Class Symfony\Component\HttpFoundation\Response
dd(builder->getContent()); // Get Json
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
